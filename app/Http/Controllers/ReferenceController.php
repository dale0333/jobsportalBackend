<?php

namespace App\Http\Controllers;

use App\Models\Reference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

use App\Imports\ReferencesImport;
use App\Http\Requests\ImportReferenceRequest;
use App\Traits\ApiResponseTrait;

class ReferenceController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search');

            $query = Reference::with('user')
                ->where('user_id', $request->user()->id);

            // ✅ Properly group search conditions
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('position', 'like', "%{$search}%")
                        ->orWhere('category', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            }

            $data = $query->latest()->paginate($perPage);

            $data = ([
                'items'        => $data->items(),
                'total'        => $data->total(),
                'per_page'     => $data->perPage(),
                'current_page' => $data->currentPage(),
            ]);

            return $this->successResponse($data, 'Fetched data successfully.', 200);
        } catch (\Throwable $th) {
            Log::error('Reference index error: ' . $th->getMessage());
            return $this->errorResponse('Failed to fetch references.', 500, $th->getMessage());
        }
    }

    public function show($type)
    {
        if ($type !== 'template') {
            return response()->json(['message' => 'Invalid file type'], 400);
        }

        $filePath = public_path('template/reference_file_template.xlsx');

        if (!file_exists($filePath)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return response()->download($filePath, 'reference_file_template.xlsx');
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $user = $request->user();

            // Initialize import
            $import = new ReferencesImport($user->id);

            // Import the Excel file
            Excel::import($import, $request->file('file'));

            DB::commit();

            // Return statistics about import
            $importStats = [
                'processed' => $import->getRowCount(),  // total rows processed
                'success'   => $import->getSuccessCount(), // rows successfully inserted
                'failed'    => $import->getFailureCount(), // rows failed validation
                'failures'  => $import->failures(), // detailed failure messages
            ];

            return response()->json([
                'success' => true,
                'message' => 'References imported successfully!',
                'data'    => $importStats,
            ], 200);
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            DB::rollBack();
            $failures = $e->failures();

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
