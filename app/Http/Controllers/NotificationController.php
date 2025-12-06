<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use App\Traits\ApiResponseTrait;

class NotificationController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search');

            $query = Notification::with('user')
                ->where('user_id', $request->user()->id)
                ->orderByRaw('CASE WHEN is_read = 0 THEN 0 ELSE 1 END, created_at DESC');

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%");
                });
            }

            $data = $query->latest()->paginate($perPage);

            $data = [
                'items'        => $data->items(),
                'total'        => $data->total(),
                'per_page'     => $data->perPage(),
                'current_page' => $data->currentPage(),
            ];

            return $this->successResponse($data, 'Fetched notifications successfully.', 200);
        } catch (\Throwable $th) {
            return $this->errorResponse('Failed to fetch notifications.', 500, $th->getMessage());
        }
    }

    public function show(Request $request)
    {
        try {
            $userId = $request->user()->id;

            $notifications = Notification::where('user_id', $userId)
                ->latest()
                ->take(15)
                ->get();

            $unreadCount = Notification::where('user_id', $userId)
                ->where('is_read', false)
                ->count();

            $data = [
                'items'  => $notifications,
                'unread' => $unreadCount,
            ];

            return $this->successResponse($data, 'Fetched notification successfully.', 200);
        } catch (\Throwable $th) {
            return $this->errorResponse('Failed to fetch notification.', 500, $th->getMessage());
        }
    }

    public function markAsRead(Request $request)
    {
        try {
            $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:notifications,id'
            ]);

            $notificationIds = $request->input('ids');
            $userId = $request->user()->id;

            // Update multiple notifications as read
            $updated = Notification::where('user_id', $userId)
                ->whereIn('id', $notificationIds)
                ->update(['is_read' => true]);

            return $this->successResponse(
                ['updated_count' => $updated],
                'Notifications marked as read successfully.',
                200
            );
        } catch (\Throwable $th) {
            return $this->errorResponse('Failed to mark notifications as read.', 500, $th->getMessage());
        }
    }

    public function markAllAsRead(Request $request)
    {
        try {
            $userId = $request->user()->id;

            $updated = Notification::where('user_id', $userId)
                ->where('is_read', false)
                ->update(['is_read' => true]);

            return $this->successResponse(
                ['updated_count' => $updated],
                'All notifications marked as read successfully.',
                200
            );
        } catch (\Throwable $th) {
            return $this->errorResponse('Failed to mark all notifications as read.', 500, $th->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $notification = Notification::where('user_id', $request->user()->id)->findOrFail($id);

            // Toggle read status if no specific value provided
            $isRead = $request->input('is_read', !$notification->is_read);
            $notification->update(['is_read' => $isRead]);

            return $this->successResponse(
                $notification,
                'Notification status updated successfully.',
                200
            );
        } catch (\Throwable $th) {
            return $this->errorResponse('Failed to update notification.', 500, $th->getMessage());
        }
    }

    public function destroy(Request $request, $id = null)
    {
        try {
            // Check if we're doing bulk delete
            if ($request->has('ids')) {
                $notificationIds = $request->input('ids');
                $userId = $request->user()->id;

                $deleted = Notification::where('user_id', $userId)
                    ->whereIn('id', $notificationIds)
                    ->delete();

                return $this->successResponse(
                    ['deleted_count' => $deleted],
                    'Notifications deleted successfully.',
                    200
                );
            }

            // Single delete
            $notification = Notification::where('user_id', $request->user()->id)->findOrFail($id);
            $notification->delete();

            return $this->successResponse(null, 'Notification deleted successfully.', 200);
        } catch (\Throwable $th) {
            return $this->errorResponse('Failed to delete notification.', 500, $th->getMessage());
        }
    }
}
