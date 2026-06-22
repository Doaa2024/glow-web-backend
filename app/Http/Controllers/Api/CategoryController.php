<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Validation\Rule;
class CategoryController extends Controller
{
     public function getAllCategories()
{
    $categories = Category::withCount('products')->get();
    return response()->json($categories);
}
   public function getAllCategoriesPaginated()
{
    $categories = Category::withCount('products')->paginate(5);
    return response()->json($categories);
}

    public function getCategoryByID($id)
    {
        $category = Category::find($id);
        if ($category) {
            return response()->json($category);
        } else {
            return response()->json(['message' => 'Category not found'], 404);
        }
    }
    public function getProductByCategoryID($id){
        $products=Category::find($id)->products;
        if($products){
            return response()->json($products);
        }else{
            return response()->json(['message'=>'Products not found'],404); 
    }
    }
    public function createCategory(Request $request){

      $request->validate([
    'name' => [
        'required',
        'string',
        'max:255',
        Rule::unique('categories')->whereNull('deleted_at'),
    ],
    'image_url' => 'required|image|mimes:jpg,jpeg,png|max:2048',
]);
        // Handle image upload if provided
    if ($request->hasFile('image_url')) {
        $path = $request->file('image_url')->store('categories', 'public');
    }
        $category=Category::create([
            'name'=>$request->name,
            'image_url'=>$path,
        ]);
        return response()->json(['message'=>'Category created successfully','category'=>$category]);
    }
public function updateCategory(Request $request, $id)
{
    $category = Category::find($id);

    if (!$category) {
        return response()->json(['message' => 'Category not found'], 404);
    }

    // Validate input
    $validated = $request->validate([
       'name' => [
    'required',
    'string',
    'max:255',
    Rule::unique('categories', 'name')
        ->ignore($id)
        ->whereNull('deleted_at'),
],
        'image_url' => 'sometimes|image|mimes:jpg,jpeg,png|max:2048', // optional image
    ]);

    // Handle image upload if provided
    if ($request->hasFile('image_url')) {
        $path = $request->file('image_url')->store('categories', 'public');
        $validated['image_url'] = $path; // add image path to validated data
    }

    // Update category
    $category->update($validated);

    return response()->json($category);
}
    public function deleteCategory($id){
        $category=Category::find($id);
        if($category){
            $category->delete();
            return response()->json(['message'=>'Category deleted successfully']);
        }else{
            return response()->json(['message'=>'Category not found'],404);
        }
    } 
}


