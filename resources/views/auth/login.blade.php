<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход — РАСПИСАНИЕ НСМК</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="login-page">
    <main class="login-shell">
        <section class="login-card">
            <section class="login-content">
                <div class="login-kicker">Авторизация</div>
                <h1>Вход в систему</h1>
                <p class="login-description">Введите данные учётной записи для продолжения.</p>

                @if($errors->any())
                    <div class="alert alert--error login-alert">
                        @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" class="login-form">
                    @csrf
                    <label class="form-field">
                        <span>Email</span>
                        <input type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
                    </label>
                    <label class="form-field">
                        <span>Пароль</span>
                        <input type="password" name="password" autocomplete="current-password" required>
                    </label>
                    <label class="login-remember">
                        <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                        <span>Запомнить меня</span>
                    </label>
                    <button type="submit" class="button button--primary button--login">Войти</button>
                </form>
            </section>
        </section>
    </main>
</body>
</html>
