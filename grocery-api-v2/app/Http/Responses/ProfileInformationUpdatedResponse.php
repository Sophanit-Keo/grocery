<?php

namespace App\Http\Responses;

use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\ProfileInformationUpdatedResponse as ProfileInformationUpdatedResponseContract;

class ProfileInformationUpdatedResponse implements ProfileInformationUpdatedResponseContract
{
    public function toResponse($request): JsonResponse
    {
        return response()->json([
            'message' => 'Profile updated successfully.',
            'data' => [
                'user' => UserResource::make($request->user()->fresh())->resolve($request),
            ],
        ]);
    }
}
