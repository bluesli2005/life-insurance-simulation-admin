<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/** ログイン中のユーザーのパスワード変更を扱う。 */
class ChangePasswordController extends Controller
{
    public function update(Request $request)
    {
        $data = $request->validate([
            'current_password' => ['required', 'password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = $request->user();
        $user->password = Hash::make($data['password']);
        // ログイン状態保持用のトークンを更新する。現在のセッションは維持する。
        $user->setRememberToken(Str::random(60));
        $user->save();
        // 次の重要な操作では、変更後のパスワードを再確認させる。
        $request->session()->forget('auth.password_confirmed_at');

        return response()->json(['message' => 'パスワードを変更しました。']);
    }
}
