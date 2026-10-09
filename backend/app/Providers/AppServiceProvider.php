<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        ResetPassword::toMailUsing(function ($user, $token) {
            $url = rtrim(config('app.frontend_url'), '/').'/password/reset/'.rawurlencode($token)
                .'?'.http_build_query(['email' => $user->getEmailForPasswordReset()], '', '&', PHP_QUERY_RFC3986);

            return (new MailMessage)
                ->subject('パスワード再設定')
                ->line('パスワード再設定のリクエストを受け付けました。')
                ->action('パスワードを再設定', $url)
                ->line('有効期限は'.config('auth.passwords.users.expire').'分です。')
                ->line('お心当たりがない場合、このメールを無視してください。');
        });
    }
}
