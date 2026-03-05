<?php

namespace App\Http\Controllers;

use App\Models\{Reference};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

use App\Imports\ReferencesImport;
use App\Traits\ApiResponseTrait;

class ReferenceDetailController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search  = $request->input('search');
            $code    = $request->input('code');

            $query = Reference::with([
                'user',
                'details' => function ($q) use ($search) {
                    if (!empty($search)) {
                        $q->where(function ($sub) use ($search) {
                            $sub->where('name', 'like', "%{$search}%")
                                ->orWhere('position', 'like', "%{$search}%")
                                ->orWhere('category', 'like', "%{$search}%");
                        });
                    }
                }
            ])
                ->where('ref_code', $code);

            $reference = $query->firstOrFail();

            $details = $reference->details()
                ->latest()
                ->paginate($perPage);

            $data = [
                'reference' => $reference,
                'items' => $details->items(),
                'total' => $details->total(),
                'per_page' => $details->perPage(),
                'current_page' => $details->currentPage(),
            ];

            return $this->successResponse($data, 'Fetched data successfully.', 200);
        } catch (\Throwable $th) {
            Log::error('Reference index error: ' . $th->getMessage());

            return $this->errorResponse(
                'Failed to fetch references.',
                500,
                $th->getMessage()
            );
        }
    }

    public function show(Request $request, $type)
    {
        if ($type !== 'template') {
            return response()->json(['message' => 'Invalid file type'], 400);
        }

        if ($request->user()->user_type === 'employer') {
            $filePath = public_path('template/reference_emp.xlsx');
        } else {
            $filePath = public_path('template/reference_man.xlsx');
        }

        if (!file_exists($filePath)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return response()->download($filePath, 'reference_file.xlsx');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'code' => 'required|string|exists:references,ref_code',
        ]);

        DB::beginTransaction();

        try {
            $reference = Reference::where('ref_code', $request->input('code'))->first();

            // Initialize import
            $import = new ReferencesImport($request->user(), $reference->id);

            // Import the Excel file
            Excel::import($import, $request->file('file'));

            DB::commit();

            // Return statistics about import
            $importStats = [
                'processed' => $import->getRowCount(),
                'success'   => $import->getSuccessCount(),
                'failed'    => $import->getFailureCount(),
                'failures'  => $import->failures(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'References imported successfully!',
                'data'    => $importStats,
            ], 200);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollBack();
            $failures = $e->failures();

            Log::error('Reference import validation error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Validation failed on some rows.',
                'errors'  => $failures,
            ], 422);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Reference import error: ' . $th->getMessage(), ['trace' => $th->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to import references.',
                'errors'  => $th->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $reference = Reference::where('user_id', $request->user()->id)
                ->findOrFail($id);

            $reference->delete();

            return $this->successResponse([], 'Reference deleted successfully!', 200);
        } catch (\Throwable $th) {
            Log::error('Reference delete error: ' . $th->getMessage());

            return $this->errorResponse(
                'Failed to delete reference.',
                500,
                $th->getMessage()
            );
        }
    }
}
