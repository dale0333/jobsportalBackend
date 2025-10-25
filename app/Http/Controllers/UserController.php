<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{User, Employer};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Helpers\AppHelper;
use App\Traits\ApiResponseTrait;

class UserController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search  = $request->input('search');
            $status  = $request->input('status');
            $date    = $request->input('date');
            $type    = $request->input('type');

            $query = User::query();

            // 🔎 Search filter
            if (!empty($search)) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }

            // 👤 User type filter
            if (!empty($type) && $type !== 'all') {
                $query->where('user_type', $type);
            }

            // 📅 Date range filter
            if (!empty($date) && str_contains($date, ' to ')) {
                [$start, $end] = explode(' to ', $date);
                try {
                    $startDate = \Carbon\Carbon::parse($start)->startOfDay();
                    $endDate   = \Carbon\Carbon::parse($end)->endOfDay();
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                } catch (\Exception $e) {
                    // Ignore invalid date format
                }
            }

            // ⚙️ Status filter
            if (!empty($status) && $status !== 'all') {
                $query->where('status', $status);
            }

            // 🧩 Include relations
            $query->with(['jobSeeker', 'employer', 'socialMedias']);

            // ⬇️ Paginate
            $users = $query->latest()->paginate($perPage);

            // 🧮 Count per user type (respecting filters EXCEPT user_type)
            $baseCountQuery = User::query();

            if (!empty($search)) {
                $baseCountQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }

            if (!empty($date) && str_contains($date, ' to ')) {
                [$start, $end] = explode(' to ', $date);
                try {
                    $startDate = \Carbon\Carbon::parse($start)->startOfDay();
                    $endDate   = \Carbon\Carbon::parse($end)->endOfDay();
                    $baseCountQuery->whereBetween('created_at', [$startDate, $endDate]);
                } catch (\Exception $e) {
                    // Ignore invalid date
                }
            }

            if (!empty($status) && $status !== 'all') {
                $baseCountQuery->where('status', $status);
            }

            $countUserTypes = $baseCountQuery
                ->select('user_type', DB::raw('COUNT(*) as count'))
                ->groupBy('user_type')
                ->pluck('count', 'user_type');

            $data = [
                'items'            => $users->items(),
                'total'            => $users->total(),
                'per_page'         => $users->perPage(),
                'current_page'     => $users->currentPage(),
                'count_user_types' => [
                    'secretariat'     => $countUserTypes['secretariat'] ?? 0,
                    'job_seeker'      => $countUserTypes['job_seeker'] ?? 0,
                    'employer'        => $countUserTypes['employer'] ?? 0,
                    'peso_school'     => $countUserTypes['peso_school'] ?? 0,
                    'manpower_agency' => $countUserTypes['manpower_agency'] ?? 0,
                    'admin'           => $countUserTypes['admin'] ?? 0,
                    'all'             => $countUserTypes->sum(),
                ],
            ];

            return $this->successResponse($data, 'Users fetched successfully!', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch users', 500, $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            // Validate the request data
            $validated = $request->validate([
                'user_type' => 'required|in:employer,peso_school,manpower_agency,secretariat,admin',
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:8',
                'telephone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'bio' => 'nullable|string|max:1000',
                'company_size' => 'nullable|required_if:user_type,employer|string|max:50',
                'industry' => 'nullable|required_if:user_type,employer|string|max:255',
                'locator_number' => 'nullable|string|max:100',
            ]);

            // Start database transaction
            DB::beginTransaction();

            // Create user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'user_type' => $validated['user_type'],
                'telephone' => $validated['telephone'] ?? null,
                'address' => $validated['address'] ?? null,
                'bio' => $validated['bio'] ?? null,
                'email_verified_at' => now(),
                'is_active' => true,
            ]);

            // If user is employer, create employer profile
            if ($validated['user_type'] === 'employer') {
                Employer::create([
                    'user_id' => $user->id,
                    'company_size' => $validated['company_size'],
                    'industry' => $validated['industry'],
                    'locator_number' => $validated['locator_number'] ?? null,
                ]);
            }

            // if ($validated['user_type'] === 'peso_school') {
            //     PesoSchool::create([
            //         'user_id' => $user->id,
            //     ]);
            // }

            // if ($validated['user_type'] === 'manpower_agency') {
            //     ManpowerAgency::create([
            //         'user_id' => $user->id,
            //     ]);
            // }

            // Commit transaction
            DB::commit();

            // Send email verification (optional)
            // $user->sendEmailVerificationNotification();

            AppHelper::userLog(
                $request->user()->id ?? $user->id,
                "Created new user '{$user->name}' ({$user->email}), Type: {$user->user_type}."
            );

            return $this->successResponse([], 'User registered successfully!', 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User registration failed: ' . $e->getMessage());

            return $this->errorResponse(
                'Registration failed. Please try again.',
                500,
                config('app.debug') ? $e->getMessage() : null
            );
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $user = User::findOrFail($id);

            // Validate input
            $validated = $request->validate([
                'user_type' => 'required|in:employer,peso_school,manpower_agency,secretariat,admin',
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . $user->id,
                'password' => 'nullable|string|min:8',
                'telephone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'bio' => 'nullable|string|max:1000',
                'company_size' => 'nullable|required_if:user_type,employer|string|max:50',
                'industry' => 'nullable|required_if:user_type,employer|string|max:255',
                'locator_number' => 'nullable|string|max:100',
            ]);

            DB::beginTransaction();

            // Update user info
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'user_type' => $validated['user_type'],
                'telephone' => $validated['telephone'] ?? null,
                'address' => $validated['address'] ?? null,
                'bio' => $validated['bio'] ?? null,
            ]);

            // Update password if given
            if (!empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
                $user->save();
            }

            // Employer handling
            if ($validated['user_type'] === 'employer') {
                $user->employer->update([
                    'company_size' => $validated['company_size'],
                    'industry' => $validated['industry'],
                    'locator_number' => $validated['locator_number'],
                ]);
            }

            // ✅ Use your custom AppHelper logger
            AppHelper::userLog(
                $request->user()->id,
                "Updated User '{$user->name}' ({$user->email}), Type: {$user->user_type}."
            );

            DB::commit();

            return $this->successResponse([], 'User updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('User update failed: ' . $e->getMessage());

            return $this->errorResponse(
                'Update failed. Please try again.',
                500,
                config('app.debug') ? $e->getMessage() : null
            );
        }
    }

    public function destroy(Request $request, string $id)
    {
        try {
            // Find user
            $user = User::findOrFail($id);

            // Delete the user
            $user->delete();

            // Log the action
            AppHelper::userLog(
                $request->user()->id,
                "Deleted user '{$user->name}' (Type: {$user->user_type}, Email: {$user->email})."
            );

            return $this->successResponse([], 'User deleted successfully.');
        } catch (\Exception $e) {
            Log::error('User deletion failed: ' . $e->getMessage());

            return $this->errorResponse(
                'Failed to delete user. Please try again later.',
                500,
                config('app.debug') ? $e->getMessage() : null
            );
        }
    }

    public function show(string $id)
    {
        try {
            // Find the user or fail
            $user = User::findOrFail($id);

            // Load related models
            $user->load(['jobSeeker.experiences', 'employer', 'socialMedias', 'jobSeekerDocuments']);

            return $this->successResponse($user, 'User fetched successfully!', 200);
        } catch (\Throwable $e) {
            return $this->errorResponse('Failed to fetch user details.', 500, $e->getMessage());
        }
    }
}
