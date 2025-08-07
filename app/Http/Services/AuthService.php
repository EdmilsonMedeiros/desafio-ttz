<?php

namespace App\Http\Services;

use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;

class AuthService
{
    public function register(RegisterRequest $request){
        try{
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            return $user;
        }catch(\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }

    public function login(LoginRequest $request){
        try {
            $credentials = $request->only('email', 'password');
            
            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                $token = $user->createToken('auth_token');
                
                return [
                    'message' => 'Login successful',
                    'user' => $user,
                    'token' => $token->plainTextToken,
                ];
            }
            
            throw new \Exception('Email or password is incorrect');
        } catch(\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function logout(Request $request){
        try{
            $request->user()->tokens()->delete();
            return response()->json([
                'message' => "Logout successfully",
            ], 200);
        }catch(\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }
}
