<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Laravel\Passport\PersonalAccessTokenResult;

class TokenController extends Controller
{
    /**
     * Generate a new token
     *
     * @return JsonResponse
     */
    public function generateToken(): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        /** @var PersonalAccessTokenResult $tokenResult */
        $tokenResult = $user->createToken('registration_token')->accessToken;

        return response()->json([
            'success' => true,
            'token' => $tokenResult
        ]);
    }
}
