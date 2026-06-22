<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Validation\Rule;
class ProductController extends Controller
{
   public function getAllProducts()
{
    $products = Product::with(['category:id,name'])->get();
    return response()->json($products);
}
    public function getAllProductsPaginated()
{
    $products = Product::with(['category:id,name'])->paginate(5);
    return response()->json($products);
}
    public function getProductByID($id){
        $product=Product::find($id);
        if($product){
            return response()->json($product);
        }else{
            return response()->json(['message'=>'Product not found'],404);
        }   
    }
    public function getRelatedProducts($id){
        $categoryId=Product::find($id)->category_id;
        if(!$categoryId){
            return response()->json(['message'=>'Product or category not found'],404);
        }
        $relatedProducts=Product::where('category_id',$categoryId)->where('id','!=',$id)->limit(5)->get();    
        return response()->json($relatedProducts);
    }
    public function createProduct(Request $request){
        $product=$request->validate([
           'name' => [
        'required',
        'string',
        'max:255',
        Rule::unique('products')->whereNull('deleted_at'),
    ],
            'description'=>'required|string',
            'subtitle'=>'required|string',
            'image_url'=>'required|image|mimes:jpg,jpeg,png|max:2048',
            'price'=>'required|numeric',
            'category_id'=>'required|exists:categories,id'
        ]);
             // Handle image upload if provided
    if ($request->hasFile('image_url')) {
        $path = $request->file('image_url')->store('products', 'public');
        $product['image_url']=$path;
    }
        $productCreated=Product::create($product);
        return response()->json(['message'=>'Product created successfully','product'=>$productCreated]);
    }
    public function updateProduct(Request $request, $id){
        $product=Product::find($id);
        if(!$product){
            return response()->json(['message'=>'Product not found'],404);
        }
    // Validate only the fields sent in the request
    $validated = $request->validate([
        'name' => 'sometimes|required|string|max:255|unique:products,name,' .$id,
        'description' => 'sometimes|required|string',
        'subtitle' => 'sometimes|required|string',
        'image_url' => 'sometimes|image|mimes:jpg,jpeg,png|max:2048',
        'price' => 'sometimes|required|numeric',
        'category_id' => 'sometimes|required|exists:categories,id',
    ]);
         // Handle image upload if provided
    if ($request->hasFile('image_url')) {
        $path = $request->file('image_url')->store('products', 'public');
        $validated['image_url']=$path;
    }
    // Update only the validated fields
    $product->update($validated);

    return response()->json([
        'message' => 'Product updated successfully',
        'product' => $product
    ]);
}
    public function deleteProductByID($id){
        $product=Product::find($id);
        if($product){
            $product->delete();
            return response()->json(['message'=>'Product deleted successfully']);
        }else{
            return response()->json(['message'=>'Product not found'],404);
        }
    }
}
