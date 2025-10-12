<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserLog;
use App\Traits\ApiResponseTrait;

class UserLogController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search = $request->input('search');
            $type = $request->input('type');

            $query = UserLog::with('user');

            // ✅ Limit logs if not admin
            if ($type === 'user') {
                $query->where('user_id',  $request->user()->id);
            }

            // ✅ Properly group search conditions
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('action', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('emp_id', 'like', "%{$search}%");
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

            return $this->successResponse($data, 'Fetched user logs successfully.', 200);
        } catch (\Throwable $th) {
            return $this->errorResponse('Failed to fetch logs.', 500, $th->getMessage());
        }
    }


    public function show(string $id)
    {
        try {
            $log = UserLog::with('user')->find($id);

            if (!$log) {
                return $this->errorResponse('Log not found.', 404);
            }

            return $this->successResponse($log, 'Fetched user log successfully.', 200);
        } catch (\Throwable $th) {
            return $this->errorResponse('Failed to process.', 500, $th->getMessage());
        }
    }
}
