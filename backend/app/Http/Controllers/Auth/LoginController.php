<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function credentials(Request $request)
    {
        return $request->only($this->username(), 'password') + ['status' => User::STATUS_ACTIVE];
    }

    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt($this->credentials($request), $request->boolean('remember'));
    }

    protected function authenticated(Request $request, $user)
    {
        return response()->noContent();
    }

    protected function loggedOut(Request $request)
    {
        return response()->noContent();
    }
}
