<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

/** 有効なユーザーのログインとログアウトを扱う。 */
class LoginController extends Controller
{
    // ログイン・ログアウトの基本処理は Laravel の trait を利用し、必要な動作だけ変更する。
    use AuthenticatesUsers;

    public function __construct()
    {
        // ログイン済みユーザーの再ログインを防ぎ、ログアウトは許可する。
        $this->middleware('guest')->except('logout');
    }

    protected function credentials(Request $request)
    {
        // 削除済みアカウントを認証対象から除外する。
        return $request->only($this->username(), 'password') + ['status' => User::STATUS_ACTIVE];
    }

    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt($this->credentials($request), $request->boolean('remember'));
    }

    protected function authenticated(Request $request, $user)
    {
        // 画面遷移はフロントエンドに任せ、API は成功ステータスだけ返す。
        return response()->noContent();
    }

    protected function loggedOut(Request $request)
    {
        return response()->noContent();
    }
}
