<?php

namespace App\Support;

/** 登録・変更・再設定で使う新しいパスワードの検証ルール。 */
class PasswordRules
{
    public static function newPassword(): array
    {
        return ['required', 'string', 'min:8', 'confirmed'];
    }
}
