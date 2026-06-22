<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
class AuthController extends Controller
{
    public function SignUp(Request $request){
        $request->validate([
            'name'=>'required|string|max:255|unique:users',
            'email'=>'required|string|email|max:255|unique:users',
            'password'=>'required|string|min:8|confirmed',
        ]);
        $user=User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
            'email_verified_at'=>now(),
        ]);
        return response()->json([
            'message'=>'User created successfully', 
        ]);
    }
    public function Login(Request $request){
        $request->validate([
            'name'=>'required|string|max:255',
            'password'=>'required|string',      
        ]);
        $user=User::where('name',$request->name)->first();
        if($user && Hash::check($request->password,$user->password)){
            $token=$user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'message'=>'Login successful',
                'access_token'=>$token,
                'token_type'=>'Bearer',
            ],200);
        }else{
            return response()->json([
                'message'=>'Invalid credentials',
            ],401);
        }
    } 
    public function Logout(){
        auth()->user()->tokens()->delete();
        return response()->json([
            'message'=>'Logged out successfully',
        ]);
    }
}
