<?php

namespace App\Http\Responses;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Laravel\Fortify\Contracts\VerifyEmailResponse as VerifyEmailResponseContract;

class VerifyEmailResponse implements VerifyEmailResponseContract
{
    public function toResponse($request): Response|RedirectResponse
    {
        if ($request->wantsJson()) {
            return response()->noContent();
        }

        return redirect()->away(config('app.frontend_url').'/verify-email?verified=1');
    }
}
