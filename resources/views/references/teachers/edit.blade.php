@extends('layouts.app')
@section('content')
<div class="page-header"><h2>Редактирование преподавателя</h2></div>
<div class="card form-card">
<form action="{{ route('teachers.update', $teacher) }}" method="POST">
@csrf @method('PUT')
<div class="form-grid">
<div class="form-field form-field--full"><label for="name">ФИО преподавателя</label><input id="name" name="name" type="text" value="{{ old('name', $teacher->name) }}" required></div>
<div class="form-field"><label for="short_name">Краткое обозначение</label><input id="short_name" name="short_name" type="text" value="{{ old('short_name', $teacher->short_name) }}" required></div>
<div class="form-field"><label for="color">Цвет</label><select id="color" name="color" required>
@foreach (['#2563eb'=>'Синий','#16a34a'=>'Зелёный','#eab308'=>'Жёлтый','#9333ea'=>'Фиолетовый','#dc2626'=>'Красный','#ea580c'=>'Оранжевый','#0891b2'=>'Бирюзовый','#db2777'=>'Розовый'] as $color=>$label)
<option value="{{ $color }}" @selected(old('color', $teacher->color) === $color)>{{ $label }}</option>
@endforeach
</select>
</div>
</div>
<div class="form-field form-field--full">
<label>Дисциплины</label>
@php $selectedSubjects = old('subjects', $teacher->subjects->pluck('id')->toArray()); @endphp
<div class="checkbox-list">
@forelse($subjects as $subject)
<label class="checkbox-item"><input type="checkbox" name="subjects[]" value="{{ $subject->id }}" @checked(in_array($subject->id, $selectedSubjects))><span>{{ $subject->name }}{{ $subject->short_name ? ' ('.$subject->short_name.')' : '' }}</span></label>
@empty
<div class="muted">Дисциплин пока нет.</div>
@endforelse
</div>
</div>
<div class="form-actions"><a href="{{ route('teachers.index') }}" class="button button--secondary">Отмена</a><button type="submit" class="button button--primary">Сохранить</button></div>
</form></div>
@endsection
