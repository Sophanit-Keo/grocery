<?php

namespace App\Http\Responses;

use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\RegisterResponse as RegisterResponseContract;

class RegisterResponse implements RegisterResponseContract
{
    public function toResponse($request): JsonResponse
    {
        return response()->json([
            'message' => 'Account created. Please verify your email address.',
            'data' => [
                'user' => UserResource::make($request->user())->resolve($request),
            ],
        ], 201);
    }
}
