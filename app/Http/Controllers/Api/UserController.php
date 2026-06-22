<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;    
use App\Models\Cart;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(User::all());
    } 
    public function getCartItems(){
        $user=auth()->user();
        if($user){
        $cartItems = $user->cart()
    ->whereHas('product')       // only carts that have a product
   ->with(['product:id,name,price,image_url'])           // eager load product
    ->get();
            return response()->json($cartItems);
        }else{
            return response()->json(['message'=>'Unauthorized'],401);   
    }
    }  
}
