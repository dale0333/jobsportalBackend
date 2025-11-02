<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BulkUpload;
use App\Traits\ApiResponseTrait;
use App\Helpers\AppHelper;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class BulkUploadController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search');

            $query = BulkUpload::with('user');

            $query->where('created_at', '>=', now()->subMonths(6));

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('filename', 'like', "%{$search}%");
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
            return $this->errorResponse('Failed to fetch.' . $th->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'files' => 'required|array|min:1',
                'files.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png,csv,xlsx|max:5120',
                'purpose' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed', 422, $validator->errors());
            }

            $uploadedFiles = [];
            $failedUploads = [];

            foreach ($request->file('files') as $file) {
                try {
                    // Generate unique filename
                    $originalName = $file->getClientOriginalName();
                    $extension = $file->getClientOriginalExtension();
                    $filename = 'bulk_' . time() . '_' . uniqid() . '.' . $extension;

                    // Store file
                    $filePath = $file->storeAs('bulk-uploads', $filename, 'public');

                    // Create bulk upload record
                    $bulkUpload = BulkUpload::create([
                        'user_id' => $request->user()->id,
                        'original_filename' => $originalName,
                        'filename' => $filename,
                        'file_path' => $filePath,
                        'file_size' => $file->getSize(),
                        'file_type' => $file->getMimeType(),
                        'extension' => $extension,
                        'purpose' => $request->purpose ?? 'CV Bulk Upload',
                        'status' => 'pending',
                        'uploaded_at' => Carbon::now(),
                    ]);

                    $uploadedFiles[] = [
                        'id' => $bulkUpload->id,
                        'original_name' => $originalName,
                        'stored_name' => $filename,
                        'size' => $this->formatFileSize($file->getSize()),
                        'uploaded_at' => $bulkUpload->uploaded_at->format('Y-m-d H:i:s'),
                    ];
                } catch (\Exception $e) {
                    $failedUploads[] = [
                        'filename' => $file->getClientOriginalName(),
                        'error' => $e->getMessage()
                    ];
                    continue;
                }
            }

            DB::commit();

            AppHelper::userLog(
                $request->user()->id,
                "Bulk files has been uploaded successful."
            );

            $response = [
                'success' => true,
                'message' => 'Files uploaded successfully',
                'data' => [
                    'uploaded_files' => $uploadedFiles,
                    'total_uploaded' => count($uploadedFiles),
                    'failed_uploads' => $failedUploads,
                    'total_failed' => count($failedUploads),
                ]
            ];

            // If there were failures, return partial success
            if (!empty($failedUploads)) {
                $response['message'] = 'Some files failed to upload';
                return $this->successResponse($response, 'Partial success', 207);
            }

            return $this->successResponse($response, 'All files uploaded successfully', 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            Log::error('Bulk upload failed: ' . $th->getMessage());

            return $this->errorResponse(
                'Failed to upload files: ' . $th->getMessage(),
                500
            );
        }
    }

    private function formatFileSize($bytes)
    {
        if ($bytes == 0) return '0 Bytes';

        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB'];
        $i = floor(log($bytes) / log($k));

        return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
    }

    public function destroy(Request $request, $id)
    {
        try {
            $bulkUpload = BulkUpload::findOrFail($id);

            // Authorization check
            if ($request->user()->id !== $bulkUpload->user_id) {
                return $this->errorResponse('Unauthorized to delete this file.', 403);
            }

            // ✅ Delete the actual file from storage if it exists
            if ($bulkUpload->file_path && Storage::disk('public')->exists($bulkUpload->file_path)) {
                Storage::disk('public')->delete($bulkUpload->file_path);
            }

            // Capture info for logging before delete
            $fileName = $bulkUpload->original_filename ?? $bulkUpload->filename;

            // Delete DB record
            $bulkUpload->delete();

            // ✅ Log the deletion action
            AppHelper::userLog(
                $request->user()->id,
                "Deleted bulk upload file '{$fileName}'."
            );

            return $this->successResponse(null, 'Bulk upload record deleted successfully.', 200);
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('Bulk upload record not found.', 404);
        } catch (\Throwable $th) {
            return $this->errorResponse('Failed to delete record: ' . $th->getMessage(), 500);
        }
    }
}
