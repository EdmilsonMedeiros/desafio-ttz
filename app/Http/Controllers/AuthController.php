<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Http\Request;
use App\Http\Services\AuthService;

class AuthController extends Controller
{
    protected $authService;
    public function __construct(AuthService $authService) {
        $this->authService = $authService;
    }

    /**
     * Login a user
     * @group Auth
     * @param LoginRequest $request['email', 'password']
     * @return \Illuminate\Http\JsonResponse
     * @response 200 {
     *   'message' : 'Login successful',
     *   'user' : {
     *     'id' : 1,
     *     'name' : 'John Doe',
     *     'email' : 'john@example.com'
     *   },
     *   'token' : 'token'
     * }
     * @response 400 {
     *   'message' : 'Login failed',
     *   'error' : 'Error message'
     * }
     */
    public function login(LoginRequest $request){
        try{
            $response = $this->authService->login($request);

            return response()->json([
                'message' => $response['message'], 
                'user' => $response['user'],
                'token' => $response['token'],
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => "Login failed",
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Register a new user
     * @group Auth
     * @param RegisterRequest $request['name', 'email', 'password']
     * @return \Illuminate\Http\JsonResponse
     * @response 200 {
     *   'message' : 'Register successfully',
     *   'user' : {
     *     'id' : 1,
     *     'name' : 'John Doe',
     *     'email' : 'john@example.com'
     *   }
     * }
     * @response 400 {
     *   'message' : 'Register failed',
     *   'error' : 'Error message'
     * }
     */
    public function register(RegisterRequest $request){
        try{
            $user = $this->authService->register($request);

            return response()->json([
                'message' => "Register successfully",
                'user' => $user,
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => "Register failed",
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get authenticated user
     * @group Auth
     * @header Authorization Bearer <token>
     * @return \Illuminate\Http\JsonResponse
     * @response 200 {
     *   'user' : {
     *     'id' : 1,
     *     'name' : 'John Doe',
     *     'email' : 'john@example.com'
     *   }
     * }
     * @response 401 {
     *   'message' : 'Unauthorized'
     * }
     */
    public function user(Request $request){
        try{
            $user = $request->user();
            return response()->json([
                'user' => $user,
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => "Get user failed",
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Logout a user
     * @group Auth
     * @header Authorization Bearer <token>
     * @return \Illuminate\Http\JsonResponse
     * @response 200 {
     *   'message': 'Logout successfully'
     * }
     * @response 401 {
     *   'message': 'Unauthorized'
     * }
     */
    public function logout(Request $request){
        try{
            $this->authService->logout($request);
            return response()->json([
                'message' => "Logout successfully",
            ], 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => "Logout failed",
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
