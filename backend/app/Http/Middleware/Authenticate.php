<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;

/** 認証とアカウントの利用状態を確認する。 */
class Authenticate extends Middleware
{
    public function handle($request, Closure $next, ...$guards)
    {
        return parent::handle($request, function ($request) use ($next) {
            // 毎回 DB の状態を確認し、削除済みアカウントの既存セッションも拒否する。
            $account = $request->user()->fresh();
            if (! $account || $account->status !== User::STATUS_ACTIVE) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return response()->json(['message' => __('errors.api.account_unavailable')], 401);
            }

            return $next($request);
        }, ...$guards);
    }

    protected function redirectTo($request)
    {
        return null;
    }
}
