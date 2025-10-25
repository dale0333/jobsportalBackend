<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Attribute, SubAttribute};
use App\Helpers\AppHelper;
use Illuminate\Support\Str;
use App\Traits\ApiResponseTrait;

class SubAttributeController extends Controller
{
    use ApiResponseTrait;

    public function index(Request $request)
    {
        try {
            $perPage   = $request->input('per_page', 10);
            $search    = $request->input('search');
            $status    = $request->input('status', null);
            $attributeId  = $request->input('attribute_id');

            $attributes = Attribute::where('is_active', true)
                ->select('id', 'name', 'icon', 'slug')
                ->get();

            $query = SubAttribute::with('attribute');

            if ($attributeId) {
                $query->where('attribute_id', $attributeId);
            }

            if ($search) {
                $query->where('name', 'like', "%{$search}%");
            }

            if ($status !== null) {
                $query->where('is_active', $status);
            }

            $data = $query->latest()->paginate($perPage);

            $data = ([
                'attributes'   => $attributes,
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
            'attribute_id' => 'required|exists:attributes,id',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'is_active'     => 'boolean',
        ]);

        try {
            $data = SubAttribute::create([
                'attribute_id' => $validated['attribute_id'],
                'name'          => $validated['name'],
                'slug'          => Str::slug($validated['name']),
                'description'   => $validated['description'] ?? null,
                'is_active'     => $validated['is_active'] ?? true,
            ]);

            AppHelper::userLog(
                $request->user()->id,
                "Created Attribute Item '{$data->name}', ID: {$data->id}."
            );

            return $this->successResponse($data, 'Attribute item created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to process.', 500, $e->getMessage());
        }
    }

    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'attribute_id' => 'required|exists:attributes,id',
            'name'          => 'required|string|max:255',
            'description'   => 'nullable|string',
            'is_active'     => 'boolean',
        ]);

        try {
            $data = SubAttribute::findOrFail($id);

            $data->update([
                'attribute_id' => $validated['attribute_id'],
                'name'          => $validated['name'],
                'slug'          => Str::slug($validated['name']),
                'description'   => $validated['description'] ?? $data->description,
                'is_active'     => $validated['is_active'] ?? $data->is_active,
            ]);

            AppHelper::userLog(
                $request->user()->id,
                "Updated Attribute Item '{$data->name}', ID: {$data->id}."
            );

            return $this->successResponse($data, 'Attribute item updated  successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to process.', 500, $e->getMessage());
        }
    }

    public function destroy(Request $request, string $id)
    {
        try {
            $data = SubAttribute::findOrFail($id);
            $name = $data->name;

            $data->delete();

            AppHelper::userLog(
                $request->user()->id,
                "Deleted Attribute Item '{$name}', ID: {$id}."
            );

            return $this->successResponse($data, 'Attribute item delete successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to process.', 500, $e->getMessage());
        }
    }
}
