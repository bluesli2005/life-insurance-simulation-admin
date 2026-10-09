<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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
        $user->setRememberToken(Str::random(60));
        $user->save();
        $request->session()->forget('auth.password_confirmed_at');

        return response()->json(['message' => 'パスワードを変更しました。']);
    }
}
