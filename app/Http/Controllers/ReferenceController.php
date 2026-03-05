<?php

namespace App\Http\Controllers;

use App\Models\Reference;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

use App\Helpers\AppHelper;
use App\Imports\ReferencesImport;

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

            $query = Reference::with('details');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('ref_code', 'like', "%$search%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%$search%")
                                ->orWhere('email', 'like', "%$search%");
                        });
                });
            }

            if ($user->user_type === 'employer' || $user->user_type === 'manpower_agency') {
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

    public function show(string $id)
    {
        try {
            $data = Reference::with('user:id,name,email')->findOrFail($id);
            return $this->successResponse($data, 'Reference fetched successfully', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Reference not found.', 404, $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'title' => 'required|string',
                'month' => 'required|string|max:50',
                'year'  => 'required|integer',
                'file'  => 'nullable|max:10240',
            ]);

            $user = $request->user();

            $exists = Reference::where('user_id', $user->id)
                ->where('month', $validated['month'])
                ->where('year', $validated['year'])
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reference already exists for this month and year.',
                ], 422);
            }

            $reference = Reference::create([
                'user_id'  => $user->id,
                'title'    => $validated['title'],
                'month'    => $validated['month'],
                'year'     => $validated['year'],
                'ref_code' => $this->generateUniqueRefCode(),
            ]);

            $import = null;
            $importData = null;

            if ($request->hasFile('file')) {
                $import = new ReferencesImport($user, $reference->id);
                Excel::import($import, $request->file('file'));

                $importData = [
                    'processed' => $import->getRowCount(),
                    'success'   => $import->getSuccessCount(),
                    'failed'    => $import->getFailureCount(),
                    'failures'  => $import->failures(),
                ];
            }

            DB::commit();

            AppHelper::userLog(
                $user->id,
                "Created reference '{$reference->ref_code}' (ID: {$reference->id})"
            );

            return response()->json([
                'success' => true,
                'message' => $request->hasFile('file')
                    ? 'References imported successfully!'
                    : 'Reference created successfully.',
                'data' => $importData,
            ], 200);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Validation failed on some rows.',
                'errors'  => $e->failures(),
            ], 422);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to import references.',
                'errors'  => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $reference = Reference::findOrFail($id);

            $validated = $request->validate([
                'title'  => 'sometimes|string',
                'month'  => 'sometimes|string|max:50',
                'year'   => 'sometimes|integer',
            ]);

            $month = $validated['month'] ?? $reference->month;
            $year  = $validated['year'] ?? $reference->year;

            $exists = Reference::where('user_id', $request->user()->id)
                ->where('month', $month)
                ->where('year', $year)
                ->where('id', '!=', $reference->id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reference already exists for this month and year.',
                ], 422);
            }

            $reference->update($validated);

            AppHelper::userLog(
                $request->user()->id,
                "Updated reference '{$reference->ref_code}' (ID: {$reference->id})"
            );

            return response()->json([
                'success' => true,
                'message' => 'Reference updated successfully.',
                'data'    => $reference,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update reference.',
                'errors'  => $e->getMessage(),
            ], 500);
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
