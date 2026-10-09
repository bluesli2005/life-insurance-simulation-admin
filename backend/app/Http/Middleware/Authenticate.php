<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
    public function handle($request, Closure $next, ...$guards)
    {
        return parent::handle($request, function ($request) use ($next) {
            $account = $request->user()->fresh();
            if (! $account || $account->status !== User::STATUS_ACTIVE) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return response()->json(['message' => 'このアカウントは利用できません。'], 401);
            }

            return $next($request);
        }, ...$guards);
    }

    protected function redirectTo($request)
    {
        return null;
    }
}
