<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ConfirmsPasswords;
use Illuminate\Http\Request;

/** 重要な操作に必要なパスワード再確認を扱う。 */
class ConfirmPasswordController extends Controller
{
    use ConfirmsPasswords;

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function confirm(Request $request)
    {
        $request->validate($this->rules(), $this->validationErrorMessages());
        // パスワード確認済みの有効期限をここから数え直す。
        $this->resetPasswordConfirmationTimeout($request);

        return response()->noContent();
    }
}
