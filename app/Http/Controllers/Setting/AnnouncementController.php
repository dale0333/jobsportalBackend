<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Announcement, UserLog};
use App\Helpers\AppHelper;
use App\Traits\ApiResponseTrait;

class AnnouncementController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search', null);
            $isActive = $request->input('is_active', null);

            $query = Announcement::query();

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%$search%")
                        ->orWhere('content', 'like', "%$search%");
                });
            }

            if ($isActive !== null) {
                $query->where('is_active', $isActive);
            }

            $data = $query->latest()->paginate($perPage);

            $data = ([
                'items' => $data->items(),
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
            ]);

            return $this->successResponse($data, 'Fetch successfully', 200);
        } catch (\Throwable $th) {
            return $this->errorResponse('Failed to process.', 500, $th->getMessage());
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'     => 'required|string|max:255',
            'content'   => 'required|string',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $data = array_merge($validated, [
                'user_id' => $request->user()->id,
            ]);

            $announce = Announcement::create($data);

            AppHelper::userLog($request->user()->id, "Created announcement titled '{$announce->title}'.");

            return $this->successResponse($data, 'Announcement created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to process.', 500, $e->getMessage());
        }
    }

    public function update(Request $request, string $id)
    {
        $announce = Announcement::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'content' => 'sometimes|string',
            'is_active' => 'nullable|boolean',
        ]);

        try {
            $announce->update($validated);

            AppHelper::userLog($request->user()->id, "Updated announcement titled '{$announce->title}' (ID: {$id}).");

            return $this->successResponse($announce, 'Announcement updated successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to process.', 500, $e->getMessage());
        }
    }

    public function destroy(Request $request, string $id)
    {
        $announce = Announcement::findOrFail($id);

        try {
            $title = $announce->title;
            $announce->delete();

            // 🔒 User log
            AppHelper::userLog($request->user()->id, "Deleted announcement titled '{$title}' (ID: {$id}).");

            return $this->successResponse($announce, 'Announcement deleted successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to process.', 500, $e->getMessage());
        }
    }
}
