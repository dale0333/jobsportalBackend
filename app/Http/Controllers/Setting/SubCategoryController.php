<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Category, SubCategory};
use App\Helpers\AppHelper;
use Illuminate\Support\Str;
use App\Traits\ApiResponseTrait;

class SubCategoryController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $perPage   = $request->input('per_page', 10);
            $search    = $request->input('search');
            $status    = $request->input('status', null);
            $categoryId  = $request->input('category_id');

            $categories = Category::where('is_active', true)
                ->select('id', 'name', 'description', 'slug')
                ->get();

            $query = SubCategory::with('category');

            if ($categoryId) {
                $query->where('category_id', $categoryId);
            }

            if ($search) {
                $query->where('name', 'like', "%{$search}%");
            }

            if ($status !== null) {
                $query->where('is_active', $status);
            }

            $data = $query->latest()->paginate($perPage);

            $data = ([
                'categories'      => $categories,
                'items'        => $data->items(),
                'total'        => $data->total(),
                'per_page'     => $data->perPage(),
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
            'category_id' => 'required|exists:categories,id',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'is_active'     => 'boolean',
        ]);

        try {
            $data = SubCategory::create([
                'category_id' => $validated['category_id'],
                'name'          => $validated['name'],
                'description'   => $validated['description'] ?? null,
                'is_active'     => $validated['is_active'] ?? true,
            ]);

            AppHelper::userLog(
                $request->user()->id,
                "Created Sub Category Item '{$data->name}', ID: {$data->id}."
            );

            return $this->successResponse($data, 'Sub Category item created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to process.', 500, $e->getMessage());
        }
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'is_active'     => 'boolean',
        ]);

        try {
            $data = SubCategory::findOrFail($id);

            $data->update([
                'job_config_id' => $validated['job_config_id'],
                'name'          => $validated['name'],
                'description'   => $validated['description'] ?? $data->description,
                'is_active'     => $validated['is_active'] ?? $data->is_active,
            ]);

            AppHelper::userLog(
                $request->user()->id,
                "Updated Sub Category Item '{$data->name}', ID: {$data->id}."
            );

            return $this->successResponse($data, 'Sub Category item updated  successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to process.', 500, $e->getMessage());
        }
    }

    public function destroy(Request $request, string $id)
    {
        try {
            $data = SubCategory::findOrFail($id);
            $name = $data->name;

            $data->delete();

            AppHelper::userLog(
                $request->user()->id,
                "Deleted Sub Category Item '{$name}', ID: {$id}."
            );

            return $this->successResponse($data, 'Sub Category item delete successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to process.', 500, $e->getMessage());
        }
    }
}
