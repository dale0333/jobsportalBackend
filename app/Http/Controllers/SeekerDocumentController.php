<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\JobSeeker;
use App\Traits\ApiResponseTrait;
use App\Helpers\AppHelper;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SeekerDocumentController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $documents = $user->jobSeekerDocuments()->latest()->get();

            return $this->successResponse($documents, 'Documents fetched successfully.', 200);
        } catch (\Throwable $th) {
            return $this->errorResponse('Failed to fetch documents.', 500, $th->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|mimes:pdf|max:10240',
                'name' => 'required|string|max:255'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed.', 422, $validator->errors());
            }

            $user = $request->user();

            if (!$user) {
                return $this->errorResponse('Job seeker profile not found.', 404);
            }

            // Handle file upload
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9\.]/', '_', $file->getClientOriginalName());
                $filePath = $file->storeAs('documents/users/' . $user->id, $fileName, 'public');

                // Create document record
                $user->jobSeekerDocuments()->create([
                    'name' => $request->name,
                    'file_path' => $filePath,
                    'type' => 'job-seeker-document',
                ]);

                // Log activity
                AppHelper::userLog(
                    $user->id,
                    "Uploaded PDF document '{$request->name}' for job seeker profile."
                );

                $user->load(['jobSeeker.experiences', 'employer', 'socialMedias', 'jobSeekerDocuments']);

                return $this->successResponse($user, 'PDF document uploaded successfully.', 201);
            }

            return $this->errorResponse('No file uploaded.', 400);
        } catch (\Throwable $th) {
            return $this->errorResponse('Failed to upload document.', 500, $th->getMessage());
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255'
            ]);

            if ($validator->fails()) {
                return $this->errorResponse('Validation failed.', 422, $validator->errors());
            }

            $user = $request->user();
            $document = $user->jobSeekerDocuments()->find($id);

            if (!$document) {
                return $this->errorResponse('Document not found.', 404);
            }

            $document->update([
                'name' => $request->name
            ]);

            // Log activity
            AppHelper::userLog(
                $user->id,
                "Updated document name to '{$request->name}' for job seeker profile."
            );

            $user->load(['jobSeeker.experiences', 'employer', 'socialMedias', 'jobSeekerDocuments']);

            return $this->successResponse($user, 'Document updated successfully.', 200);
        } catch (\Throwable $th) {
            return $this->errorResponse('Failed to update document.', 500, $th->getMessage());
        }
    }

    public function destroy(Request $request, string $id)
    {
        try {
            $user = $request->user();
            $document = $user->jobSeekerDocuments()->find($id);

            if (!$document) {
                return $this->errorResponse('Document not found.', 404);
            }

            // Delete physical file
            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }

            // Delete database record
            $document->delete();

            // Log activity
            AppHelper::userLog(
                $user->id,
                "Deleted PDF document '{$document->name}' from job seeker profile."
            );

            $document = $user->load(['jobSeekerDocuments']);

            return $this->successResponse($document->jobSeekerDocuments, 'Document deleted successfully.', 200);
        } catch (\Throwable $th) {
            return $this->errorResponse('Failed to delete document.', 500, $th->getMessage());
        }
    }

    // View PDF file (instead of download)
    // public function view(Request $request, string $id)
    // {
    //     try {
    //         $user = $request->user();
    //         $document = $user->jobSeekerDocuments()->find($id);

    //         if (!$document) {
    //             return $this->errorResponse('Document not found.', 404);
    //         }

    //         if (!Storage::disk('public')->exists($document->file_path)) {
    //             return $this->errorResponse('File not found.', 404);
    //         }

    //         $filePath = Storage::disk('public')->path($document->file_path);
    //         return response()->file($filePath, [
    //             'Content-Type' => 'application/pdf',
    //             'Content-Disposition' => 'inline; filename="' . $document->name . '.pdf"'
    //         ]);
    //     } catch (\Throwable $th) {
    //         return $this->errorResponse('Failed to view document.', 500, $th->getMessage());
    //     }
    // }
}
