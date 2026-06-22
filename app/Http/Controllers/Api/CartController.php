<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;    
use App\Models\Cart;
use Illuminate\Validation\Rule;
class CartController extends Controller
{
      public function addItemToCart(Request $request){
        $user=auth()->user();
        $validatedData=$request->validate([
 'product_id' => [
        'required',
        Rule::exists('products', 'id')->whereNull('deleted_at')
    ],
            'quantity'=>'required|integer|min:1'
        ]);
        if($user){
           $cartItem = Cart::firstOrCreate(
    ['user_id' => $user->id, 'product_id' => $validatedData['product_id']],
    ['quantity' => 0]
);

$cartItem->increment('quantity', $validatedData['quantity']);
            return response()->json($cartItem);
        }else{
            return response()->json(['message'=>'Unauthorized'],401);
        }
      }
    public function removeItemFromCart($id){
        $cartItem=Cart::find($id);
        if($cartItem){
            $cartItem->delete();
            return response()->json(['message'=>'Item removed from cart']);
        }else{
            return response()->json(['message'=>'Cart item not found'],404);    
    }
}
public function updateQuantity(Request $request,$id){
    $validatedData=$request->validate([
        'quantity'=>'required|integer|min:1'
    ]);
    $cartItem=Cart::find($id);
    if($cartItem){
        $cartItem->quantity=$validatedData['quantity'];
        $cartItem->save();
        return response()->json($cartItem);
    }else{
        return response()->json(['message'=>'Cart item not found'],404);    
}
}
}