<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use App\Helpers\AppHelper;
// use Illuminate\Support\Facades\Mail;
// use Illuminate\Support\Facades\URL;
// use App\Mail\VerifyEmail;

use App\Models\{SubAttribute, Employer};

class EmailVerificationController extends Controller
{
    public function verify($id, $hash)
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
    // public function resend(Request $request)
    // {
    //     $request->validate([
    //         'email' => 'required|email|exists:users,email'
    //     ]);

    //     $user = User::where('email', $request->email)->first();

    //     if ($user->hasVerifiedEmail()) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Email already verified'
    //         ], 400);
    //     }

    //     // Generate new verification URL
    //     $verificationUrl = URL::temporarySignedRoute(
    //         'verification.verify',
    //         now()->addHours(24),
    //         [
    //             'id' => $user->getKey(),
    //             'hash' => sha1($user->getEmailForVerification()),
    //         ]
    //     );

    //     // Resend verification email
    //     Mail::to($user->email)->send(new VerifyEmail($user, $verificationUrl));

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Verification email sent successfully!'
    //     ]);
    // }


    // public function updateEmployer()
    // {
    //     $attributes = SubAttribute::where('attribute_id', 1)->pluck('name');

    //     // Normalize attribute names
    //     $normalizedAttributes = $attributes->mapWithKeys(function ($name) {
    //         $clean = $this->clean($name);
    //         return [$clean => $name];
    //     });

    //     $employers = Employer::all();

    //     foreach ($employers as $employer) {
    //         $cleanIndustry = $this->clean($employer->industry);

    //         // Direct match
    //         if (isset($normalizedAttributes[$cleanIndustry])) {
    //             $final = $normalizedAttributes[$cleanIndustry];
    //         } else {
    //             // Try partial match (VERY IMPORTANT for your data)
    //             $final = $this->findClosestMatch($cleanIndustry, $normalizedAttributes);
    //         }

    //         $employer->update([
    //             'industry' => $final ?? 'Other'
    //         ]);
    //     }
    // }

    // /**
    //  * تنظيف / normalize string
    //  */
    // private function clean($value)
    // {
    //     $value = strtolower($value);
    //     $value = preg_replace('/[^a-z0-9]/', '', $value);

    //     // handle plural (services -> service)
    //     if (substr($value, -1) === 's') {
    //         $value = rtrim($value, 's');
    //     }

    //     return $value;
    // }

    // /**
    //  * Try partial / fuzzy match
    //  */
    // private function findClosestMatch($industry, $attributes)
    // {
    //     foreach ($attributes as $key => $name) {
    //         // contains match (ict -> ictindustry)
    //         if (str_contains($key, $industry) || str_contains($industry, $key)) {
    //             return $name;
    //         }
    //     }

    //     return null;
    // }

    public function updateEmployer()
    {
        // $employers = Employer::with('user')
        //     ->where(function ($q) {
        //         $q->whereNull('locator_number')
        //             ->orWhere('locator_number', 'not like', '%#%');
        //     })
        //     ->get();

        // foreach ($employers as $employer) {
        //     optional($employer->user)->delete();
        //     $employer->delete();
        // }

        // 1. Get duplicate locator numbers
        $duplicateLocators = Employer::select('locator_number')
            ->whereNotNull('locator_number')
            ->groupBy('locator_number')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('locator_number');

        // 2. Get all duplicate employers
        $employers = Employer::with(['user', 'references'])
            ->whereIn('locator_number', $duplicateLocators)
            ->get()
            ->groupBy('locator_number');

        foreach ($employers as $locator => $group) {

            // split into with and without reference
            $withoutReference = $group->filter(function ($employer) {
                return !$employer->references; // or ->isEmpty() if hasMany
            });

            $withReference = $group->filter(function ($employer) {
                return $employer->references;
            });

            // CASE 1: delete only from no-reference group
            foreach ($withoutReference->slice(1) as $employer) {
                optional($employer->user)->delete();
                $employer->delete();
            }

            // CASE 2: if ALL have references, still keep 1 only
            if ($withReference->count() > 1) {
                foreach ($withReference->slice(1) as $employer) {
                    optional($employer->user)->delete();
                    $employer->delete();
                }
            }
        }
    }
}
