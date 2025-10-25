<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\{Attribute, Category};

class FetchServiceController extends Controller
{
    public function fetchAttributes()
    {
        try {
            $data = Attribute::with('subAttributes')->get();
            return response()->json($data, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to process. ' . $e->getMessage(),
            ], 500);
        }
    }

    public function fetchCategories()
    {
        try {
            $data = Category::with('subCategories')->get();
            return response()->json($data, 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to process. ' . $e->getMessage(),
            ], 500);
        }
    }
}
