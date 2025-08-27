<?php

namespace Nikolaynesov\LaravelCommandAssistant;

use Closure;
use Illuminate\Http\Request;


class VerifyKeyMiddleware
{
    public function handle(Request $request, Closure $next)
    {

        if (! config('command-assistant.enabled')) {
            return response()->json(['error' => 'Command assistant is disabled on this environment.'], 403);
        }

        $token = $request->header('Authorization');

        if (!$token || $token !== 'Bearer ' . config('command-assistant.key')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}