@extends('layouts.app')
@section('content')
<div class="page-header">
    <div><h1>Пользователи</h1><p class="page-header__description">Учётные записи и назначение ролей.</p></div>
    <a href="{{ route('users.create') }}" class="button button--primary">Добавить пользователя</a>
</div>
<div class="card table-wrapper">
<table class="data-table">
<thead><tr><th>Имя</th><th>Email</th><th>Роль</th><th>Профиль</th><th class="actions-column">Действия</th></tr></thead>
<tbody>
@forelse($users as $user)
<tr>
<td><strong>{{ $user->name }}</strong></td>
<td>{{ $user->email }}</td>
<td>{{ $user->roleLabel() }}</td>
<td>{{ $user->teacher?->name ?? $user->group?->name ?? '—' }}</td>
<td><div class="table-actions">
<a class="button button--small" href="{{ route('users.edit',$user) }}">Изменить</a>
<form method="POST" action="{{ route('users.destroy',$user) }}" onsubmit="return confirm('Удалить пользователя?')">@csrf @method('DELETE')<button class="button button--small button--danger">Удалить</button></form>
</div></td>
</tr>
@empty
<tr><td colspan="5"><div class="empty-state"><div class="empty-state__title">Пользователей нет</div></div></td></tr>
@endforelse
</tbody>
</table>
</div>
@endsection
