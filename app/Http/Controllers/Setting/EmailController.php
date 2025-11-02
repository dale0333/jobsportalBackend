<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EmailSmtp;
use App\Helpers\AppHelper;
use App\Traits\ApiResponseTrait;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search', null);

            $query = EmailSmtp::query();

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('host', 'like', "%$search%")
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

    public function show($email)
    {
        AppHelper::mailerConfig();

        $data = [
            'title' => 'SMTP Test Email',
            'smtpName' => config('mail.mailers.smtp.host'),
            'timestamp' => now()->format('Y-m-d H:i:s'),
        ];

        try {
            Mail::send('emails.test', $data, function ($message) use ($email, $data) {
                $message->to($email)
                    ->subject($data['title']);
            });

            return $this->successResponse($data, "Test email sent successfully to {$email}", 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to process. ' . $e->getMessage(), 500, $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'host'       => 'required|string|max:255',
                'port'       => 'required|string|max:255',
                'email'      => 'required|string|email|max:255',
                'password'   => 'required|string|max:255',
                'encryption' => 'required|string|in:tls,ssl,none',
                'is_active'  => 'nullable|boolean',
            ]);

            if (!empty($validated['is_active']) && $validated['is_active'] === true) {
                EmailSmtp::query()->update(['is_active' => false]);
            }

            $data = EmailSmtp::create($validated);

            AppHelper::userLog(
                $request->user()->id,
                "Created new Email SMTP for '{$data->email}' (ID: {$data->id})."
            );

            return $this->successResponse($data, 'Email SMTP created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to process.', 500, $e->getMessage());
        }
    }

    public function update(Request $request, string $id)
    {
        $data = EmailSmtp::findOrFail($id);

        $validated = $request->validate([
            'host'       => 'required|string|max:255',
            'port'       => 'required|string|max:255',
            'email'      => 'required|string|email|max:255',
            'password'   => 'sometimes|string|max:255',
            'encryption' => 'required|string|in:tls,ssl,none',
            'is_active'  => 'nullable|boolean',
        ]);

        try {
            if (!empty($validated['is_active']) && $validated['is_active'] === true) {
                EmailSmtp::query()
                    ->where('id', '!=', $id)
                    ->update(['is_active' => false]);
            }

            $data->update($validated);

            AppHelper::userLog(
                $request->user()->id,
                "Updated Email SMTP for '{$data->email}' (ID: {$id})."
            );

            return $this->successResponse($data, 'Email SMTP updated successfully', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to process.', 500, $e->getMessage());
        }
    }

    public function destroy(Request $request, string $id)
    {
        $data = EmailSmtp::find($id);

        if (!$data) {
            return response()->json([
                'message' => 'Email Smtp not found',
            ], 404);
        }

        try {
            $email = $data->email;
            $data->delete();

            AppHelper::userLog($request->user()->id, "Deleted Email SMTP for '{$email}' (ID: {$id}).");

            return $this->successResponse($data, 'Email SMTP deleted successfully', 200);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to process.', 500, $e->getMessage());
        }
    }
}
