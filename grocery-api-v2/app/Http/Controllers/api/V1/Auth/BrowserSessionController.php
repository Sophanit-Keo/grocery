<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LogoutOtherBrowserSessionsRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BrowserSessionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        if (config('session.driver') !== 'database' || ! $request->hasSession()) {
            return response()->json(['data' => []]);
        }

        $sessions = DB::table(config('session.table', 'sessions'))
            ->where('user_id', $request->user()->getAuthIdentifier())
            ->latest('last_activity')
            ->get()
            ->map(fn (object $session): array => [
                'ip_address' => $session->ip_address,
                'user_agent' => $session->user_agent,
                'is_current_device' => $session->id === $request->session()->getId(),
                'last_active_at' => Carbon::createFromTimestamp($session->last_activity)->toISOString(),
            ]);

        return response()->json(['data' => $sessions]);
    }

    public function destroyOther(LogoutOtherBrowserSessionsRequest $request): Response
    {
        Auth::guard('web')->logoutOtherDevices($request->validated('password'));

        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))
                ->where('user_id', $request->user()->getAuthIdentifier())
                ->where('id', '!=', $request->session()->getId())
                ->delete();
        }

        return response()->noContent();
    }
}
