<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Helpers\AppHelper;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileSettingController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return $this->errorResponse('User not authenticated.', 401);
            }

            $user->load(['jobSeeker', 'employer', 'socialMedias']);

            return $this->successResponse($user, 'User data retrieved successfully!', 200);
        } catch (\Throwable $th) {
            return $this->errorResponse('Failed to fetch user data.', 500, $th->getMessage());
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $user = $request->user();

            // ✅ Conditional validation for Job Seekers
            $jobSeekerValidate = [];
            if ($user->user_type === 'job_seeker') {
                $jobSeekerValidate = [
                    'date_of_birth' => 'nullable|date',
                    'gender' => 'nullable|in:male,female,other,prefer_not_to_say',
                    'education_level' => 'nullable|string|max:255',
                    'field_of_study' => 'nullable|string|max:255',
                    'skills' => 'nullable|array',
                    'services' => 'nullable|array',
                    'years_of_experience' => 'nullable|integer|min:0|max:50',
                    'preferred_location' => 'nullable|string|max:255',
                    'expected_salary' => 'nullable|string|max:255',
                ];
            }

            // ✅ Conditional validation for Employers
            $employerValidate = [];
            if ($user->user_type === 'employer') {
                $employerValidate = [
                    'company_size' => 'nullable|string|max:255',
                    'industry' => 'nullable|string|max:255',
                    'locator_number' => 'nullable|string|max:255',
                ];
            }

            // ✅ Merge base + conditional validations
            $validated = $request->validate(array_merge([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'telephone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:255',
                'bio' => 'nullable|string|max:2000',

                'social_media' => 'nullable|array',
                'social_media.*.name' => 'nullable|string|max:255',
                'social_media.*.url'  => 'nullable|url|max:500',
                // 'social_media.*.name' => 'nullable:social_media.*.url|string|max:255',
                // 'social_media.*.url' => 'nullable:social_media.*.name|url|max:500',
            ], $jobSeekerValidate, $employerValidate));

            // ✅ Update core user info
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'telephone' => $validated['telephone'] ?? null,
                'address' => $validated['address'] ?? null,
                'bio' => $validated['bio'] ?? null,
            ]);

            // ✅ Handle Job Seeker data
            if ($user->user_type === 'job_seeker') {
                $jobSeekerData = [
                    'date_of_birth' => $validated['date_of_birth'] ?? null,
                    'gender' => $validated['gender'] ?? null,
                    'education_level' => $validated['education_level'] ?? null,
                    'field_of_study' => $validated['field_of_study'] ?? null,
                    'skills' => $validated['skills'] ?? [],
                    'services' => $validated['services'] ?? [],
                    'years_of_experience' => $validated['years_of_experience'] ?? null,
                    'preferred_location' => $validated['preferred_location'] ?? null,
                    'expected_salary' => $validated['expected_salary'] ?? null,
                ];

                $user->jobSeeker
                    ? $user->jobSeeker->update($jobSeekerData)
                    : $user->jobSeeker()->create($jobSeekerData);
            }

            // ✅ Handle Employer data
            if ($user->user_type === 'employer') {
                $employerData = [
                    'company_size' => $validated['company_size'] ?? null,
                    'industry' => $validated['industry'] ?? null,
                    'locator_number' => $validated['locator_number'] ?? null,
                ];

                $user->employer
                    ? $user->employer->update($employerData)
                    : $user->employer()->create($employerData);
            }

            // ✅ Handle Social Media data
            if (!empty($validated['social_media'])) {
                $existingSocialMedia = $user->socialMedias->keyBy('name');

                foreach ($validated['social_media'] as $socialMedia) {
                    if (!empty($socialMedia['name']) && !empty($socialMedia['url'])) {
                        if ($existingSocialMedia->has($socialMedia['name'])) {
                            // Update existing
                            $existingSocialMedia[$socialMedia['name']]->update([
                                'url' => $socialMedia['url'],
                            ]);
                        } else {
                            // Create new
                            $user->socialMedias()->create([
                                'name' => $socialMedia['name'],
                                'url' => $socialMedia['url'],
                            ]);
                        }
                    }
                }

                // Delete social media entries that were removed
                $submittedPlatforms = collect($validated['social_media'])->pluck('name')->filter();
                $user->socialMedias()->whereNotIn('name', $submittedPlatforms)->delete();
            }

            DB::commit();

            $user->load(['jobSeeker', 'employer', 'socialMedias']);

            AppHelper::userLog($user->id, "Updated profile information: {$user->name}");

            return $this->successResponse($user, 'Profile updated successfully', 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return $this->errorResponse('Validation failed.', 422, $e->errors());
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Profile update failed: ' . $e->getMessage());
            return $this->errorResponse('Failed to process.', 500, $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $user = $request->user();

            if (!$user || $user->id != $id) {
                return $this->errorResponse('Unauthorized access.', 403);
            }

            $request->validate([
                'image' => 'required|image|mimes:jpg,jpeg,png|max:2048',
                'type' => 'required|in:avatar,cover',
            ]);

            $file = $request->file('image');
            $type = $request->input('type');

            // Delete old file if exists
            if ($type === 'avatar' && $user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            } elseif ($type === 'cover' && $user->cover_photo) {
                Storage::disk('public')->delete($user->cover_photo);
            }

            // Generate unique filename and store
            $uniqueName = uniqid() . '_' . preg_replace('/\s+/', '_', $file->getClientOriginalName());
            $folder = 'attachments/images';
            $path = $file->storeAs($folder, $uniqueName, 'public');

            // Update DB
            if ($type === 'avatar') {
                $user->update(['avatar' => $path]);
            } else {
                $user->update(['cover_photo' => $path]);
            }

            // Prepare URLs for frontend
            $user->avatar_url = $user->avatar ? asset('storage/' . $user->avatar) : null;
            $user->cover_photo_url = $user->cover_photo ? asset('storage/' . $user->cover_photo) : null;

            $user->load(['jobSeeker.experiences', 'employer', 'socialMedias']);

            AppHelper::userLog($user->id, ucfirst($type) . ' updated successfully.');

            return $this->successResponse(
                $user,
                ucfirst($type) . ' updated successfully.',
                200
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->errorResponse('Validation failed.', 422, $e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to upload image.', 500, $e->getMessage());
        }
    }

    public function destroy(Request $request, $password)
    {
        $user = $request->user();

        if (!Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['The provided password is incorrect.'],
            ]);
        }

        try {
            $user->delete();
            $user->tokens()->delete();

            return $this->successResponse([], 'Your account has been deleted successfully.', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete account.', 500, $e->getMessage());
        }
    }


    public function changePassword(Request $request)
    {
        try {
            $user = $request->user();

            // Validate input
            $request->validate([
                'old_password' => 'required|string|min:6',
                'new_password' => 'required|string|min:8|confirmed',
            ]);

            // Check old password
            if (!Hash::check($request->old_password, $user->password)) {
                return $this->errorResponse('Your current password is incorrect.', 422);
            }

            // Update password
            $user->password = Hash::make($request->new_password);
            $user->save();

            AppHelper::userLog($user->id, 'Password changed successfully.');

            return $this->successResponse([], 'Password updated successfully.', 200);
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed.', 422, $e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to change password.', 500, $e->getMessage());
        }
    }

    public function storeJobExpriences(Request $request)
    {
        $validated = $request->validate([
            'experiences' => 'required|array|min:1',
            'experiences.*.id' => 'nullable|integer|exists:experiences,id',
            'experiences.*.job_title' => 'required|string|max:255',
            'experiences.*.company' => 'required|string|max:255',
            'experiences.*.start_year' => 'required|max:4',
            'experiences.*.end_year' => 'required|max:4',
            'experiences.*.job_description' => 'required|string',
        ]);

        try {
            $user = $request->user();

            if (!$user->jobSeeker) {
                return response()->json(['message' => 'Job seeker profile not found.'], 404);
            }

            $jobSeeker = $user->jobSeeker;
            $existingIds = [];

            foreach ($validated['experiences'] as $exp) {
                $experience = $jobSeeker->experiences()->updateOrCreate(
                    [
                        // condition to check if record exists
                        'id' => $exp['id'] ?? null,
                    ],
                    [
                        // fields to update or create
                        'job_title'       => $exp['job_title'],
                        'company'         => $exp['company'],
                        'start_year'      => $exp['start_year'],
                        'end_year'        => $exp['end_year'],
                        'job_description' => $exp['job_description'],
                    ]
                );

                $existingIds[] = $experience->id;
            }

            // Optionally remove experiences not present in the new list
            $jobSeeker->experiences()
                ->whereNotIn('id', $existingIds)
                ->delete();

            AppHelper::userLog($user->id, 'Experiences detials has been updated successfully.');

            // Reload relationships
            $user->load(['jobSeeker.experiences', 'employer', 'socialMedias']);

            return $this->successResponse(
                $user,
                'Experiences updated successfully.',
                200
            );
        } catch (ValidationException $e) {
            return $this->errorResponse('Validation failed.', 422, $e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update experiences.', 500, $e->getMessage());
        }
    }

    public function updateNotificationSettings(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'is_web' => 'required|boolean',
            'is_email' => 'required|boolean',
            'is_sms' => 'required|boolean',
        ]);

        try {
            $user->update($validated);

            AppHelper::userLog($user->id, 'Notification settings has been updated successfully.');

            $user->load(['jobSeeker.experiences', 'employer', 'socialMedias']);

            return $this->successResponse($user, 'Notification settings updated successfully.', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed process.', 500, $e->getMessage());
        }
    }
}
