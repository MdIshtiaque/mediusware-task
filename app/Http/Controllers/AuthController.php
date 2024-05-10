<?php

namespace App\Http\Controllers;

use App\Http\Requests\RequestUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function createUser(RequestUser $request): \Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory
    {
        try {
            User::create([
                'name' => $request->name,
                'account_type' => $request->account_type,
                'balance' => $request->balance,
                'email' => $request->email,
                'password' => bcrypt($request->password)
            ]);
        } catch (\Exception $exception) {
            return response([
                'error' => $exception->getMessage()
            ], 500);
        }
        return response([
            'message' => 'User created successfully'
        ], 200);
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string|min:8',
            ]);

            if ($validator->fails()) {
                return response()->json(['error' => $validator->errors()], 422);
            }

            $credentials = $request->only('email', 'password');

            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                $token = $user->createToken('authToken')->plainTextToken;
                return response()->json(['token' => $token], 200);
            }
            return response()->json(['error' => 'Unauthorized'], 401);
        }catch (\Exception $exception) {
            return response([
                'error' => $exception->getMessage()
            ], 500);
        }
    }
}
