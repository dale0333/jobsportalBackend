<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponseTrait;
use App\Models\{GetInTouch, User};
use App\Helpers\AppHelper;

class ContactController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search', null);

            $query = GetInTouch::query();

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                        ->orWhere('email', 'like', "%$search%");
                });
            }

            $data = $query->latest()->paginate($perPage);

            $data = ([
                'items' => $data->items(),
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
            ]);

            return $this->successResponse($data, 'Email SMTP fetch successfully', 200);
        } catch (\Throwable $th) {
            return $this->errorResponse('Failed to process.', 500, $th->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'    => 'required|string|max:80',
                'email'   => 'required|email',
                'subject' => 'nullable|string|max:120',
                'message' => 'required|string',
                'is_active' => 'nullable|boolean',
            ]);

            // If setting this one as active, deactivate all others
            if (!empty($validated['is_active']) && $validated['is_active'] === true) {
                GetInTouch::query()->update(['is_active' => false]);
            }

            $data = GetInTouch::create($validated);

            // Notify admins + secretariat
            $admins = User::whereIn('user_type', ['secretariat', 'admin'])->get();

            foreach ($admins as $admin) {
                AppHelper::storedNotification(
                    $admin,
                    'contact_message',
                    'New Contact Form Message Received',
                    "{$validated['name']} has submitted a message through the contact form.",
                    [
                        'name'    => $validated['name'],
                        'email'   => $validated['email'],
                        'subject' => $validated['subject'] ?? 'No subject',
                        'message' => $validated['message'],
                    ]
                );
            }

            return $this->successResponse($data, "Message sent! We'll reach out soon.", 201);
        } catch (\Exception $e) {
            return $this->errorResponse("Failed to process. " . $e->getMessage(), 500);
        }
    }
}
