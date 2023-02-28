<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\Category;

class CategoryController extends Controller
{
    public function getCategories (Request $request) {
        // $ched = ChedOffice::paginate(10);
        $category = Category::get();

        return response()->json(['data' => $category, 'message' => 'Successfully fetched the Category.'], 200);
    }

    public function getCategory (Request $request, $id) {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found.'], 404);
        }

        return response()->json(['data' => $category, 'message' => 'Successfully fetched the Category.'], 200);
    }
}
