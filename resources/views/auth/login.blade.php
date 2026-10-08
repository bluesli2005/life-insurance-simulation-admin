<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ログイン | {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">
</head>
<body>
    <main class="login-page">
        <section class="login-card">
            <h1>管理画面ログイン</h1>

            @if ($errors->any())
                <div class="login-error" role="alert">{{ $errors->first() }}</div>
            @endif

            @if (session('status'))
                <div class="login-status" role="status">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <label for="email">メールアドレス</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">

                <label for="password">パスワード</label>
                <input id="password" type="password" name="password" required autocomplete="current-password">

                <label class="remember-label">
                    <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                    ログイン状態を保持する
                </label>

                <button type="submit">ログイン</button>
            </form>
        </section>
    </main>
</body>
</html>
