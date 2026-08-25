<?php

namespace App\Http\Middleware;

use App\Models\ModeratorUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureModerator
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('sanctum') ?? Auth::guard('moderator')->user();

        if (!$user instanceof ModeratorUser) {
            return response()->json([
                'success' => false,
                'message' => 'Moderator access required',
            ], 403);
        }

        return $next($request);
    }
}
