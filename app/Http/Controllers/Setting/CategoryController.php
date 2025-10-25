<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Helpers\AppHelper;
use Illuminate\Support\Str;
use App\Traits\ApiResponseTrait;

class CategoryController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 10);
            $search  = $request->input('search');
            $status = $request->input('status', null);

            $query = Category::query();

            if ($search) {
                $query->where('name', 'like', "%{$search}%");
            }

            if ($status !== null) {
                $query->where('is_active', $status);
            }

            $data = $query->latest()->paginate($perPage);

            $data = ([
                'items'         => $data->items(),
                'total'        => $data->total(),
                'per_page'     => $data->perPage(),
                'current_page' => $data->currentPage(),
            ]);

            return $this->successResponse($data, 'Email SMTP fetch successfully', 200);
        } catch (\Throwable $th) {
            return $this->errorResponse('Failed to process.', 500, $th->getMessage());
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        try {
            $data = Category::create([
                'name'        => $validated['name'],
                'slug'        => Str::slug($validated['name']),
                'description' => $validated['description'] ?? null,
                'is_active'   => $validated['is_active'] ?? true,
            ]);

            AppHelper::userLog(
                $request->user()->id,
                "Created Category '{$data->name}', ID: {$data->id}."
            );

            return $this->successResponse($data, 'Category created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to process.', 500, $e->getMessage());
        }
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active'   => 'boolean',
        ]);

        try {
            $data = Category::findOrFail($id);

            $data->update([
                'name'        => $validated['name'],
                'slug'        => Str::slug($validated['name']),
                'description' => $validated['description'] ?? $data->description,
                'is_active'   => $validated['is_active'] ?? $data->is_active,
            ]);

            AppHelper::userLog(
                $request->user()->id,
                "Updated Category '{$data->name}', ID: {$data->id}."
            );

            return $this->successResponse($data, 'Category created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to process.', 500, $e->getMessage());
        }
    }

    public function destroy(Request $request, string $id)
    {
        try {
            $data = Category::findOrFail($id);
            $name = $data->name;

            $data->delete();

            AppHelper::userLog(
                $request->user()->id,
                "Deleted Category '{$name}', ID: {$id}."
            );

            return $this->successResponse($data, 'Category created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to process.', 500, $e->getMessage());
        }
    }
}
