@extends('layouts.app')
@section('content')
<div class="page-header"><div><h1>{{ $title }}</h1><p class="page-header__description">Настройте занятие и при необходимости добавьте заметку.</p></div></div>
<form method="POST" action="{{ $action }}" class="form-card form-card--wide">
@csrf @if($method!=='POST') @method($method) @endif
<div class="form-grid">
<label class="form-field"><span>Группа</span><select name="group_id" required>@foreach($groups as $group)<option value="{{ $group->id }}" @selected(old('group_id',$lesson->group_id)===$group->id)>{{ $group->name }} — {{ $group->course }} курс</option>@endforeach</select></label>
<label class="form-field"><span>Преподаватель</span><select name="teacher_id" data-teacher-select required><option value="">Выберите преподавателя</option>@foreach($teachers as $teacher)<option value="{{ $teacher->id }}" @selected(old('teacher_id',$lesson->teacher_id)===$teacher->id)>{{ $teacher->name }}</option>@endforeach</select></label>
<label class="form-field"><span>Дисциплина</span><select name="subject_id" data-subject-select required><option value="">Выберите дисциплину</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}" data-teachers="{{ $subject->teachers->pluck('id')->implode(',') }}" @selected(old('subject_id',$lesson->subject_id)===$subject->id)>{{ $subject->short_name ?: $subject->name }} — {{ $subject->name }}</option>@endforeach</select></label>
<label class="form-field"><span>Тип занятия</span><select name="lesson_type" required>@foreach(['Лекция','Практика','Лабораторная'] as $type)<option @selected(old('lesson_type',$lesson->lesson_type)===$type)>{{ $type }}</option>@endforeach</select></label>
<label class="form-field"><span>Кабинет</span><select name="classroom_id"><option value="">Не указан</option>@foreach($classrooms as $room)<option value="{{ $room->id }}" @selected((string)old('classroom_id',$lesson->classroom_id)===(string)$room->id)>{{ $room->number }} — {{ $room->name }} @if($room->capacity) ({{ $room->capacity }} мест) @endif</option>@endforeach</select></label>
@if($method!=='POST')
<label class="form-field"><span>День</span><select name="day" required>@foreach($days as $day)<option @selected(old('day',$lesson->day)===$day)>{{ $day }}</option>@endforeach</select></label>
@else
<fieldset class="day-picker"><legend>Дни</legend><div class="day-picker__grid">@foreach($days as $day)<label class="day-option"><input type="checkbox" name="day[]" value="{{ $day }}" @checked(in_array($day,old('day',[]),true))><span>{{ $day }}</span></label>@endforeach</div></fieldset>
@endif
<fieldset class="pair-picker"><legend>Пары первой недели</legend><div class="pair-picker__grid">@foreach($pairs as $pair=>$time)<label class="pair-option"><input type="checkbox" name="week1_lesson[]" value="{{ $pair }}" @checked(in_array($pair,$selectedWeek1Pairs,true))><span><strong>{{ $pair }} пара</strong><small>{{ $time }}</small></span></label>@endforeach</div></fieldset>
<fieldset class="pair-picker"><legend>Пары второй недели</legend><div class="pair-picker__grid">@foreach($pairs as $pair=>$time)<label class="pair-option"><input type="checkbox" name="week2_lesson[]" value="{{ $pair }}" @checked(in_array($pair,$selectedWeek2Pairs,true))><span><strong>{{ $pair }} пара</strong><small>{{ $time }}</small></span></label>@endforeach</div></fieldset>
<label class="form-field form-field--full"><span>Заметка</span><textarea name="note" rows="4" maxlength="2000" placeholder="Например: перенесено, заменить аудиторию...">{{ old('note',$lesson->note) }}</textarea></label>
</div>
<div class="form-actions"><a href="{{ route('schedule.index') }}" class="button">Отмена</a><button class="button button--primary">Сохранить</button></div>
</form>
@endsection
