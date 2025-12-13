<?php

namespace App\Http\Controllers;

use App\Models\Reference;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use App\Helpers\AppHelper;

class ReferenceController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search', null);
            $month = $request->input('month', null);
            $year = $request->input('year', null);
            $status = $request->input('status', null);

            $user = $request->user();

            $query = Reference::query();

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('ref_code', 'like', "%$search%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%$search%")
                                ->orWhere('email', 'like', "%$search%");
                        });
                });
            }

            if ($user->user_type === 'employer') {
                $query->where('user_id', $user->id);
            }

            if ($month) {
                $query->where('month', $month);
            }

            if ($year) {
                $query->where('year', $year);
            }

            if ($status) {
                $query->where('status', $status);
            }

            $data = $query->with('user:id,name,email')->latest()->paginate($perPage);

            $data = ([
                'items' => $data->items(),
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
            ]);

            return $this->successResponse($data, 'References fetched successfully', 200);
        } catch (\Throwable $th) {
            return $this->errorResponse('Failed to process.', 500, $th->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title'  => 'required|string',
                'month'  => 'required|string|max:50',
                'year'   => 'required|integer',
                'status' => 'required|in:active,inactive,pending',
            ]);

            $userId = $request->user()->id;

            $exists = Reference::where('user_id', $userId)
                ->where('month', $validated['month'])
                ->where('year', $validated['year'])
                ->exists();

            if ($exists) {
                return $this->errorResponse(
                    'Reference already exists for this user, month, and year.',
                    422
                );
            }

            $validated['user_id']  = $userId;
            $validated['ref_code'] = $this->generateUniqueRefCode();

            $reference = Reference::create($validated);

            AppHelper::userLog(
                $userId,
                "Created new reference with code '{$reference->ref_code}' (ID: {$reference->id})."
            );

            return $this->successResponse(
                $reference,
                'Reference created successfully',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Failed to process.',
                500,
                $e->getMessage()
            );
        }
    }

    public function show(string $id)
    {
        try {
            $data = Reference::with('user:id,name,email')->findOrFail($id);
            return $this->successResponse($data, 'Reference fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Reference not found.', 404, $e->getMessage());
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $data = Reference::findOrFail($id);

            $validated = $request->validate([
                'title'  => 'sometimes|string',
                'month'     => 'sometimes|string|max:50',
                'year'      => 'sometimes|integer',
                'status'    => 'sometimes|string|in:active,inactive,pending',
            ]);

            $data->update($validated);

            AppHelper::userLog(
                $request->user()->id,
                "Updated reference with code '{$data->ref_code}' (ID: {$id})."
            );

            return $this->successResponse($data, 'Reference updated successfully', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to process.', 500, $e->getMessage());
        }
    }

    public function destroy(Request $request, string $id)
    {
        try {
            $data = Reference::findOrFail($id);
            $refCode = $data->ref_code;

            $data->delete();

            AppHelper::userLog(
                $request->user()->id,
                "Deleted reference with code '{$refCode}' (ID: {$id})."
            );

            return $this->successResponse(null, 'Reference deleted successfully', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to process.', 500, $e->getMessage());
        }
    }

    private function generateUniqueRefCode()
    {
        do {
            $refCode = 'REF-' . strtoupper(uniqid());
        } while (Reference::where('ref_code', $refCode)->exists());

        return $refCode;
    }
}
