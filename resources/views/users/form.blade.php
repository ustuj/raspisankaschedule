@extends('layouts.app')
@section('content')
<div class="page-header"><div><h1>{{ $formTitle }}</h1><p class="page-header__description">Администратор назначает роль и профиль пользователя.</p></div></div>
<form method="POST" action="{{ $formAction }}" class="form-card">
@csrf
@if($method !== 'POST') @method($method) @endif
<div class="form-grid">
<label class="form-field"><span>Имя</span><input name="name" value="{{ old('name',$user->name) }}" required></label>
<label class="form-field"><span>Email</span><input type="email" name="email" value="{{ old('email',$user->email) }}" required></label>
<label class="form-field"><span>Роль</span><select name="role" data-role-select required>@foreach($roles as $value=>$label)<option value="{{ $value }}" @selected(old('role',$user->role)===$value)>{{ $label }}</option>@endforeach</select></label>
<div class="form-field form-field--full role-profile-hint" data-profile-field="teacher">
    <span>Профиль преподавателя</span>
    <div class="role-profile-hint__box">Профиль преподавателя будет создан автоматически. Имя преподавателя будет совпадать с именем пользователя.</div>
</div>
<div class="form-field" data-profile-field="student"><span>Группа</span><select name="group_id"><option value="">Не назначена</option>@foreach($groups as $group)<option value="{{ $group->id }}" @selected((string)old('group_id',$user->group_id)===(string)$group->id)>{{ $group->name }}</option>@endforeach</select></div>
<label class="form-field user-password-field"><span>Пароль {{ $method !== 'POST' ? '(оставьте пустым, чтобы не менять)' : '' }}</span><input type="password" name="password" {{ $method === 'POST' ? 'required' : '' }}></label>
<label class="form-field user-password-confirm-field"><span>Подтверждение пароля</span><input type="password" name="password_confirmation" {{ $method === 'POST' ? 'required' : '' }}></label>
</div>
<div class="form-actions"><a href="{{ route('users.index') }}" class="button">Отмена</a><button class="button button--primary">Сохранить</button></div>
</form>
@endsection
