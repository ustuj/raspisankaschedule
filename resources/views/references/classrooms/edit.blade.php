@extends('layouts.app')
@section('content')
<div class="page-header"><div><h1>Редактирование кабинета</h1></div></div>
<form method="POST" action="{{ route('classrooms.update',$classroom) }}" class="form-card">@csrf @method('PUT')
<div class="form-grid">
<label class="form-field"><span>Название</span><input name="name" value="{{ old('name',$classroom->name) }}" required></label>
<label class="form-field"><span>Номер</span><input name="number" value="{{ old('number',$classroom->number) }}" required></label>
<label class="form-field"><span>Этаж</span><input name="floor" value="{{ old('floor',$classroom->floor) }}" required></label>
<label class="form-field"><span>Вместимость</span><input type="number" min="1" max="500" name="capacity" value="{{ old('capacity',$classroom->capacity) }}"></label>
</div><div class="form-actions"><a class="button" href="{{ route('classrooms.index') }}">Отмена</a><button class="button button--primary">Сохранить</button></div></form>
@endsection
