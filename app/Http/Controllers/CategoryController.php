<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

use App\Models\Category;

class CategoryController extends Controller
{
    public function addCategory (Request $request) {
        $requestData = $request->only(['description', 'is_assignable']);

        $validator = Validator::make($requestData, [
            'description' => 'required|string|min:3',
            'is_assignable' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 409);
        }

        try {
            DB::beginTransaction();

            $category = new Category([
                'description' => $requestData['description'],
                'is_assignable' => $requestData['is_assignable']
            ]);

            if ($category->save()) {
                DB::commit();
                return response()->json(['data' => $category, 'message' => 'Successfully added a category.'], 201);
            }

        } catch (\Exception$e) {
            report($e);
        }

        DB::rollBack();
        return response()->json(['message' => 'Failed to add the category.'], 400);
    }

    public function editCategory (Request $request, $id) {
        $requestData = $request->only(['description', 'is_assignable']);
       

        $validator = Validator::make($requestData, [
            'description' => 'required|string|min:3',
            'is_assignable' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 409);
        }

        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found.'], 404);
        }

        try {
            $category->description = $requestData['description'];
            $category->is_assignable = $requestData['is_assignable'];

            if ($category->save()) {
                return response()->json(['data' => $category, 'message' => 'Successfully updated the category.'], 201);
            }
        } catch (\Exception $e) {
            report($e);
        }
        
        return response()->json(['message' => 'Failed to update the category'], 400);
    }

    public function getCategories (Request $request) {
        // $ched = ChedOffice::paginate(10);
        $categories = Category::get();

        return response()->json(['data' => $categories, 'message' => 'Successfully fetched the Categories.'], 200);
    }

    public function getCategory (Request $request, $id) {
        $category = Category::find($id); 

        if (!$category) {
            return response()->json(['message' => 'Category not found.'], 404);
        }

        return response()->json(['data' => $category, 'message' => 'Successfully fetched the Category.'], 200);
    }

    public function deleteCategory (Request $request, $id) {
        $category = Category::find($id);

        if (!$category) {
            return response()->json(['message' => 'Category not found.'], 404);
        }

        try {
            $category->delete();

            return response()->json(['message' => 'Successfully deleted the category.'], 200);

        } catch (\Exception $e) {
            report($e);
        }

        return response()->json(['message' => 'Failed to delete the category.'], 400);
    }

}
