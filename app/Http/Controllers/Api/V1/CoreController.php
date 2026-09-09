<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CoreController extends Controller
{
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();
        return response()->json(['user' => $user->only(['id', 'name', 'email', 'role', 'status', 'is_verified'])]);
    }

    public function subscription(Request $request): JsonResponse
    {
        return response()->json($request->user()->only([
            'subscription_plan', 'subscription_status', 'subscription_expires_at',
        ]));
    }
}
