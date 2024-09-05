<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Register a new user.
     *
     * @param UserRegisterRequest $request
     * @return JsonResponse
     */
    public function register(UserRegisterRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'],
        ]);

        $token = $user->createToken('Personal Access Token')->accessToken;

        return response()->json([
            'success' => true,
            'user' => $user,
            'token' => $token
        ]);
    }

    /**
     * Log in a user.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = auth()->user();
        $token = $user->createToken('Personal Access Token')->accessToken;

        return response()->json([
            'success' => true,
            'user' => $user,
            'token' => $token
        ]);
    }

    /**
     * Log out a user.
     *
     * @return JsonResponse
     */
    public function logout()
    {
        $user = auth()->user();
        $user->tokens->each(function ($token) {
            $token->delete();
        });

        return response()->json(['success' => true]);
    }
}
