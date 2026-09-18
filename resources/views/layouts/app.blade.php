<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'РАСПИСАНИЕ НСМК' }}</title>
    <link rel="stylesheet" href="{{ asset('assets/app.css') }}?v=20260921">
    <script src="{{ asset('assets/app.js') }}?v=20260921" defer></script>
</head>
<body class="{{ request()->routeIs('schedule.index') ? 'schedule-mode' : '' }}">
<div class="site-frame">
    <header class="brand-bar">
        <a href="{{ route('schedule.index') }}" class="brand-block">
            <div class="brand-name">НСМК</div>
            <div class="brand-college">РАСПИСАНИЕ УЧЕБНЫХ ЗАНЯТИЙ</div>
        </a>
        @auth
            <div class="user-bar">
                <button type="button" class="button button--small button--theme" data-theme-toggle aria-pressed="false">Тёмная тема</button>
                <div class="user-bar__text">
                    <strong>{{ auth()->user()->name }}</strong>
                    <span>{{ auth()->user()->roleLabel() }}</span>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="button button--small button--header" type="submit">Выйти</button>
                </form>
            </div>
        @endauth
    </header>

    <div class="app-layout">
        <aside class="sidebar">
            <div class="sidebar-title">Разделы</div>
            <nav class="sidebar-nav">
                <a href="{{ route('schedule.index') }}" class="nav-link {{ request()->routeIs('schedule.*') ? 'active' : '' }}">Расписание</a>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('groups.index') }}" class="nav-link {{ request()->routeIs('groups.*') ? 'active' : '' }}">Группы</a>
                    <a href="{{ route('teachers.index') }}" class="nav-link {{ request()->routeIs('teachers.*') ? 'active' : '' }}">Преподаватели</a>
                    <a href="{{ route('subjects.index') }}" class="nav-link {{ request()->routeIs('subjects.*') ? 'active' : '' }}">Дисциплины</a>
                    <a href="{{ route('classrooms.index') }}" class="nav-link {{ request()->routeIs('classrooms.*') ? 'active' : '' }}">Кабинеты</a>
                    <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">Пользователи</a>
                @endif
            </nav>
        </aside>

        <main class="main-content {{ request()->routeIs('schedule.*') ? 'main-content--schedule' : '' }}">
            @if(session('success'))
                <div class="alert alert--success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert--error">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert--error">
                    @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
