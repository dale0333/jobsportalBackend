<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

use Illuminate\Support\Str;
use Carbon\Carbon;

use App\Helpers\AppHelper;
use App\Traits\ApiResponseTrait;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        // Check credentials
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // 🚫 Check if user is deactivated
        if (!$user->is_active) {
            return response()->json([
                'message' => 'Your account has been deactivated. Please contact the administrator.'
            ], 403);
        }

        // 🚫 Check if email is not verified
        if (is_null($user->email_verified_at)) {
            return response()->json([
                'message' => 'Please verify your email address before logging in.'
            ], 403);
        }

        // ✅ Create token with abilities
        $token = $user->createTokenWithAbilities('auth-token');

        // ✅ Log user login activity
        AppHelper::userLog($user->id, "User logged in successfully.");

        return response()->json([
            'message' => 'Login successful',
            'user' => $user->load(['jobSeeker.experiences', 'employer', 'socialMedias', 'jobSeekerDocuments']),
            'access_token' => $token->plainTextToken,
            'abilities' => $token->accessToken->abilities,
        ], 200);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'password_confirmation' => 'required|string|min:6',
            'telephone' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => 'required|string|in:male,female,other',
            'education_level' => 'required|string|max:255',
            'field_of_study' => 'required|string|max:255',
            'skills' => 'required|array',
            'services' => 'required|array',
            'preferred_location' => 'nullable|string|max:255',
            'expected_salary' => 'nullable|string',
            'files' => 'nullable|array',
            'files.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        try {
            DB::beginTransaction();

            // ✅ Create User
            $user = User::create([
                'user_type' => 'job_seeker',
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'telephone' => $request->telephone,
                'address' => $request->address,
                'is_active' => true,
            ]);

            // ✅ Create Job Seeker Profile
            $user->jobSeeker()->create([
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'education_level' => $request->education_level,
                'field_of_study' => $request->field_of_study,
                'skills' => $request->skills,
                'services' => collect($request->services)->map(fn($id) => (int) $id)->values()->toArray(),
                'years_of_experience' => 0,
                'preferred_location' => $request->preferred_location,
                'expected_salary' => $request->expected_salary,
                'is_available' => true,
            ]);

            // ✅ Handle File Uploads
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $uniqueName = uniqid() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
                    $folder = 'attachments/job_applicant';
                    $path = $file->storeAs($folder, $uniqueName, 'public');

                    $user->attachments()->create([
                        'name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'type' => 'job-seeker-document',
                    ]);
                }
            }

            // ✅ Send Email Verification
            AppHelper::sendVerificationEmail($user);

            DB::commit();

            $user->load(['jobSeeker', 'attachments']);

            // ✅ Log Event
            AppHelper::userLog($user->id, "New job seeker registered: {$user->name}");

            return $this->successResponse($user, 'Registration successful! Please verify your email.', 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorResponse('Registration failed.', 500, $e->getMessage());
        }
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();

        // ✅ Log user logout
        AppHelper::userLog($user->id, "User logged out.");

        return response()->json([
            'message' => 'You have been logged out successfully.',
        ], 200);
    }

    public function forgotPassword(Request $request)
    {
        // Validate request
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ], [
            'email.exists' => 'No account found with this email address.'
        ]);

        try {
            // Find user
            $user = User::where('email', $request->email)->first();

            // Generate reset token
            $token = Str::random(60);

            // Create or update password reset record
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $request->email],
                [
                    'token' => Hash::make($token),
                    'created_at' => Carbon::now()
                ]
            );

            // Generate reset URL
            $resetUrl = AppHelper::backEndUrl('reset-password') . "?token={$token}&email=" . urlencode($request->email);

            AppHelper::mailerConfig();
            $emailData = [
                'title' => 'Password Reset Request - ' . config('app.name'),
                'user' => $user,
                'resetUrl' => $resetUrl,
                'expiryTime' => config('auth.passwords.users.expire', 60),
                'appName' => config('app.name'),
                'currentYear' => date('Y'),
                'supportEmail' => config('app.support_email'),
            ];

            // Send the email
            Mail::send('emails.password-reset', $emailData, function ($message) use ($user, $emailData) {
                $message->to($user->email)
                    ->subject($emailData['title']);
            });

            return response()->json([
                'message' => 'Password reset instructions have been sent to your email.'
            ]);
        } catch (\Exception $e) {
            Log::error('Password reset error: ' . $e->getMessage());
            return response()->json([
                'message' => 'Unable to process your request. Please try again later.'
            ], 500);
        }
    }

    public function resetPassword(Request $request)
    {
        // Validate request
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|min:8|confirmed',
        ]);

        try {
            // Find the password reset record
            $resetRecord = DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->first();

            if (!$resetRecord) {
                return response()->json([
                    'message' => 'Invalid or expired reset token.'
                ], 400);
            }

            // Check if token is expired (60 minutes)
            $tokenExpiry = config('auth.passwords.users.expire', 60);
            $createdAt = Carbon::parse($resetRecord->created_at);

            if ($createdAt->diffInMinutes(Carbon::now()) > $tokenExpiry) {
                // Delete expired token
                DB::table('password_reset_tokens')->where('email', $request->email)->delete();

                return response()->json([
                    'message' => 'Reset token has expired. Please request a new password reset.'
                ], 400);
            }

            // Verify token
            if (!Hash::check($request->token, $resetRecord->token)) {
                return response()->json([
                    'message' => 'Invalid or expired reset token.'
                ], 400);
            }

            // Find user and update password
            $user = User::where('email', $request->email)->first();
            $user->password = Hash::make($request->password);
            $user->save();

            // Delete the used token
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            // Send success email (optional)
            $this->sendPasswordResetSuccessEmail($user);

            return response()->json([
                'message' => 'Password has been reset successfully. You can now login with your new password.'
            ]);
        } catch (\Exception $e) {
            Log::error('Password reset error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Unable to reset password. Please try again later.'
            ], 500);
        }
    }

    private function sendPasswordResetSuccessEmail($user)
    {
        try {
            AppHelper::mailerConfig();

            $emailData = [
                'title' => 'Password Reset Successful - ' . config('app.name'),
                'user' => $user,
                'appName' => config('app.name'),
                'currentYear' => date('Y'),
                'supportEmail' => config('app.support_email'),
                'loginUrl' => AppHelper::backEndUrl('login'),
            ];

            Mail::send('emails.password-reset-success', $emailData, function ($message) use ($user, $emailData) {
                $message->to($user->email)
                    ->subject($emailData['title']);
            });
        } catch (\Exception $e) {
            Log::error('Password reset success email error: ' . $e->getMessage());
        }
    }
}
