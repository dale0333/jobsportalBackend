<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Category, SubCategory, JobConfigDetail};
use App\Traits\ApiResponseTrait;

class GetDataController extends Controller
{
    use ApiResponseTrait;

    public function fetchCategories()
    {
        try {
            $data = Category::with('subCategories')->get();

            return $this->successResponse($data, 'JProcess Success!', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Something went wrong while processing.', 500, $e->getMessage());
        }
    }

    public function fetchSubCategories()
    {
        try {
            $data = SubCategory::get();

            return $this->successResponse($data, 'Process Success!', 201);
        } catch (\Exception $e) {
            return $this->errorResponse('Something went wrong while processing.', 500, $e->getMessage());
        }
    }
}
