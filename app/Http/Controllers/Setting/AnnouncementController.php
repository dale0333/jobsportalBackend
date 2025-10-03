<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Announcement, UserLog};
use App\Helpers\AppHelper;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
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

        return response()->json([
            'data' => $data->items(),
            'total' => $data->total(),
            'per_page' => $data->perPage(),
            'current_page' => $data->currentPage(),
        ]);
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

            return response()->json([
                'message' => 'Announcement created successfully!',
                'data'    => $announce,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error creating announcement',
                'error'   => $e->getMessage(),
            ], 500);
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

            return response()->json([
                'message' => 'Announcement updated successfully!',
                'data' => $announce,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error updating announcement',
                'error' => $e->getMessage(),
            ], 500);
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

            return response()->json([
                'message' => 'Announcement deleted successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting announcement',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // System Logs
    public function show(Request $request, string $type)
    {
        $perPage = $request->input('per_page', 10);
        $search  = $request->input('search', null);

        $query = UserLog::with('user');

        if ($type === 'individual') {
            $query->where('user_id', $request->user()->id);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($sub) use ($search) {
                    $sub->where('name', 'like', '%' . $search . '%');
                })
                    ->orWhere('action', 'like', '%' . $search . '%');
            });
        }

        $data = $query->latest()->paginate($perPage);

        return response()->json([
            'data'         => $data->items(),
            'total'        => $data->total(),
            'per_page'     => $data->perPage(),
            'current_page' => $data->currentPage(),
            'type'         => $type,
        ]);
    }
}
