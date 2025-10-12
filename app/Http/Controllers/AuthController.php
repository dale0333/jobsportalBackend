<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use App\Helpers\AppHelper;
use App\Traits\ApiResponseTrait;

class AuthController extends Controller
{
    use ApiResponseTrait;

    /**
     * 🔹 LOGIN
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (!$user->is_active) {
            return response()->json([
                'message' => 'Your account has been deactivated. Please contact the administrator.'
            ], 403);
        }

        // Create token with abilities
        $token = $user->createTokenWithAbilities('auth-token');

        // ✅ Log user login activity
        AppHelper::userLog($user->id, "User logged in successfully.");

        return response()->json([
            'message' => 'Login successful',
            'user' => $user->load(['jobSeeker', 'employer', 'socialMedias']),
            'access_token' => $token->plainTextToken,
            'abilities' => $token->accessToken->abilities,
        ], 200);
    }

    /**
     * 🔹 REGISTER
     */
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
        ]);

        try {
            DB::beginTransaction();

            // ✅ Create user
            $user = User::create([
                'user_type' => 'job_seeker',
                'name' => $request->name,
                'email' => $request->email,
                'email_verified_at' => now(),
                'password' => Hash::make($request->password),
                'telephone' => $request->telephone,
                'address' => $request->address,
                'is_active' => true,
            ]);

            // ✅ Create job seeker profile
            $user->jobSeeker()->create([
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                'education_level' => $request->education_level,
                'field_of_study' => $request->field_of_study,
                'skills' => $request->skills,
                'services' => $request->services,
                'years_of_experience' => 0,
                'preferred_location' => $request->preferred_location,
                'expected_salary' => $request->expected_salary,
                'is_available' => true,
            ]);

            DB::commit();

            $user->load('jobSeeker');

            // ✅ Log registration event
            AppHelper::userLog($user->id, "New job seeker registered: {$user->name}");

            return $this->successResponse($user, 'Registration successful!', 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorResponse('Registration failed.', 500, $e->getMessage());
        }
    }

    /**
     * 🔹 LOGOUT
     */
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
}
