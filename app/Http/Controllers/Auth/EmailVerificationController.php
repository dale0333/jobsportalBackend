<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Helpers\AppHelper;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use App\Mail\VerifyEmail;

class EmailVerificationController extends Controller
{
    public function verify(Request $request, $id, $hash)
    {
        $user = User::findOrFail($id);

        // Verify hash
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return redirect(AppHelper::backEndUrl('auth-check/invalid'));
        }

        // Already verified
        if ($user->hasVerifiedEmail()) {
            return redirect(AppHelper::backEndUrl('auth-check/already-verified'));
        }

        // Mark as verified
        $user->markEmailAsVerified();

        Log::info("Email verified for user: {$user->email}");

        // Redirect to frontend success page
        return redirect(AppHelper::backEndUrl('auth-check/success'));
    }


    /**
     * Resend verification email
     */
    public function resend(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email already verified'
            ], 400);
        }

        // Generate new verification URL
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHours(24),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        // Resend verification email
        Mail::to($user->email)->send(new VerifyEmail($user, $verificationUrl));

        return response()->json([
            'success' => true,
            'message' => 'Verification email sent successfully!'
        ]);
    }
}
