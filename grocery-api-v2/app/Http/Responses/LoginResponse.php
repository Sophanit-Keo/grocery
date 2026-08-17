<?php

namespace App\Http\Responses;

use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): JsonResponse
    {
        return response()->json([
            'two_factor' => false,
            'data' => [
                'user' => UserResource::make($request->user())->resolve($request),
            ],
        ]);
    }
}
