@extends('layouts.app')
@section('content')
@php
    $visibleGroups = $selectedGroup ? $groups->where('id', $selectedGroup) : $groups;
    $editable = auth()->user()->isAdmin() || auth()->user()->isTeacher();
@endphp
<div class="schedule-page">
    <div class="schedule-head">
        <div><h1>Расписание НСМК</h1><div class="schedule-meta">Неделя {{ $selectedWeek }}</div></div>
        <div class="schedule-head__actions">
            @if(auth()->user()->isStudent())
                <div class="student-tabs">
                    <a class="button button--small {{ $view !== 'all' ? 'button--primary' : '' }}" href="{{ route('schedule.index',['week'=>$selectedWeek,'view'=>'mine']) }}">Моя группа</a>
                    <a class="button button--small {{ $view === 'all' ? 'button--primary' : '' }}" href="{{ route('schedule.index',['week'=>$selectedWeek,'view'=>'all']) }}">Все расписание</a>
                </div>
            @endif
            <button type="button" class="button button--small schedule-controls-toggle" data-schedule-controls-toggle aria-expanded="true">Скрыть панель</button>
            
        </div>
    </div>

    <form class="schedule-filters" data-schedule-controls method="GET" action="{{ route('schedule.index') }}">
        @if(auth()->user()->isStudent())<input type="hidden" name="view" value="{{ $view }}">@endif
        <div class="filter-group"><label for="week">Неделя</label><select id="week" name="week" onchange="this.form.submit()"><option value="1" @selected($selectedWeek===1)>1 неделя</option><option value="2" @selected($selectedWeek===2)>2 неделя</option></select></div>
        @if(!auth()->user()->isStudent())
        <div class="filter-group"><label for="group_id">Группа</label><select id="group_id" name="group_id" onchange="this.form.submit()"><option value="">Все группы</option>@foreach($groups as $group)<option value="{{ $group->id }}" @selected($selectedGroup===$group->id)>{{ $group->name }}</option>@endforeach</select></div>
        @else
        <div class="filter-group"><label>Группа</label><input value="{{ $selectedGroup ? ($groups->firstWhere('id',$selectedGroup)?->name ?? '—') : 'Все группы' }}" readonly></div>
        @endif
        <div class="filter-group filter-group--search"><label for="schedule-search">Поиск</label><input id="schedule-search" type="search" autocomplete="off" placeholder="Преподаватель, дисциплина, группа"></div>
        <div class="schedule-filter-action schedule-filter-action--buttons">
            @if($editable && auth()->user()->isAdmin())<a class="button button--primary" href="{{ route('lessons.create') }}">Добавить занятие</a>@endif
            <a class="button" href="{{ route('schedule.export-ical',['group_id'=>$selectedGroup,'view'=>$view]) }}">Экспорт iCal</a>
            <a class="button" href="{{ route('schedule.print',['group_id'=>$selectedGroup,'week'=>$selectedWeek,'view'=>$view]) }}" target="_blank">Печать</a>
        </div>
    </form>

    <div class="schedule-workspace">
        <section class="schedule-board" data-schedule-board data-selected-week="{{ $selectedWeek }}" data-can-edit="{{ $editable ? '1' : '0' }}">
            
            <div class="schedule-table" style="--group-count: {{ max(1,$visibleGroups->count()) }}">
                <div class="schedule-header-row">
                    <div class="schedule-corner">День / Пара</div>
                    @foreach($visibleGroups as $group)<div class="group-header"><strong>{{ $group->name }}</strong><span>{{ $group->speciality }}</span><small>{{ $group->course }} курс</small></div>@endforeach
                </div>
                @foreach($days as $day)
                    <section class="day-section" data-day-block>
                        <header class="day-header"><div class="day-heading"><strong>{{ $day }}</strong></div><button type="button" class="day-toggle" data-day-toggle aria-expanded="true"><span>Свернуть</span></button></header>
                        <div class="day-rows" data-day-content>
                            @if ($day === 'Понедельник')
                                <div class="schedule-row schedule-row--special">
                                    <div class="pair-label">
                                        <strong>Классный час</strong>
                                        <span>09:00–09:50</span>
                                    </div>
                                    @foreach($visibleGroups as $group)
                                        <div class="schedule-cell">
                                            <div class="special-lesson-card">Классный час</div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @foreach($pairs as $pair=>$normalTime)
                                @php $time=$day==='Понедельник' ? $mondayPairs[$pair] : $normalTime; @endphp
                                <div class="schedule-row">
                                    <div class="pair-label"><strong>{{ $pair }} пара</strong><span>{{ $time }}</span></div>
                                    @foreach($visibleGroups as $group)
                                        @php
                                            $lesson = $lessons->first(function($item) use($group,$day,$pair,$selectedWeek){
                                                return $item->group_id===$group->id && $item->day===$day && (int)($selectedWeek===1 ? $item->week1_lesson : $item->week2_lesson)===(int)$pair;
                                            });
                                            $searchText=$lesson ? implode(' ',[$group->name,$day,$lesson->teacher->name,$lesson->teacher->short_name,$lesson->subject->name,$lesson->subject->short_name]) : implode(' ',[$group->name,$day,$pair]);
                                        @endphp
                                        <div class="schedule-cell" data-drop-zone data-group-id="{{ $group->id }}" data-day="{{ $day }}" data-pair="{{ $pair }}" data-search-text="{{ $searchText }}" @if($editable && auth()->user()->isAdmin()) data-empty-url="{{ route('lessons.create',['group_id'=>$group->id,'day'=>$day,'week'=>$selectedWeek]) }}" @endif>
                                            @if($lesson)
                                                <article class="lesson-card" data-lesson-card data-lesson-id="{{ $lesson->id }}" data-teacher-id="{{ $lesson->teacher_id }}" data-subject-id="{{ $lesson->subject_id }}" data-move-url="{{ route('lessons.move',$lesson) }}" data-note-url="{{ route('lessons.note',$lesson) }}" data-create-url="{{ route('lessons.store') }}" data-week1="{{ $lesson->week1_lesson }}" data-week2="{{ $lesson->week2_lesson }}" style="--teacher-color:{{ $lesson->teacher->color }}">
                                                    <button type="button" class="lesson-card__body lesson-note-trigger" data-note="{{ $lesson->note ?? '' }}" data-subject="{{ $lesson->subject->name }}" data-teacher="{{ $lesson->teacher->name }}" data-room="{{ $lesson->classroom?->number ?? $lesson->classroom?->name ?? '—' }}">
                                                        <div class="lesson-topline"><span class="lesson-type">{{ $lesson->lesson_type }}</span><span class="lesson-room">{{ $lesson->classroom?->number ?? $lesson->classroom?->name ?? '—' }}</span></div>
                                                        <strong>{{ $lesson->subject->short_name ?: $lesson->subject->name }}</strong>
                                                        <span class="lesson-subject">{{ $lesson->subject->name }}</span>
                                                        <span class="lesson-teacher">{{ $lesson->teacher->name }}</span>
                                                        <span class="lesson-weeks"><span>1 нед.: {{ $lesson->week1_lesson ?? '—' }}</span><i></i><span>2 нед.: {{ $lesson->week2_lesson ?? '—' }}</span></span>
                                                        @if($lesson->note)<span class="lesson-note-preview" title="{{ $lesson->note }}">{{ mb_strlen(trim($lesson->note)) > 15 ? mb_substr(trim($lesson->note), 0, 15).'…' : trim($lesson->note) }}</span>@endif
                                                    </button>
                                                    @if($editable)
                                                        <div class="lesson-actions"><a href="{{ route('lessons.edit',$lesson) }}" class="button button--small">Изменить</a><form method="POST" action="{{ route('lessons.destroy',$lesson) }}" onsubmit="return confirm('Удалить занятие?')">@csrf @method('DELETE')<button class="button button--small button--danger">Удалить</button></form></div>
                                                    @endif
                                                </article>
                                            @elseif($editable && auth()->user()->isAdmin())
                                                <a class="empty-cell" href="{{ route('lessons.create',['group_id'=>$group->id,'day'=>$day,'week'=>$selectedWeek]) }}">Добавить</a>
                                            @else
                                                <div class="empty-cell">—</div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        </section>

        @if(!auth()->user()->isStudent())
        <div class="resource-panel-wrap" data-resource-wrap>
            <button type="button" class="resource-panel-toggle" data-resource-toggle aria-label="Свернуть панель">›</button>
            <aside class="resource-panel">
                <div class="resource-panel__header"><strong>Преподаватели и дисциплины</strong><span>{{ auth()->user()->isAdmin() ? 'Выберите дисциплину и перетащите её в расписание.' : 'Справочная информация.' }}</span></div>
                <div class="resource-search"><input type="search" id="teacher-search" placeholder="Поиск преподавателя..."></div>
                <div class="resource-list">
                    @forelse($teachers as $teacher)
                        @php $parts=preg_split('/\s+/',trim($teacher->name)); $label=($parts[0]??'').' '.collect(array_slice($parts,1,2))->map(fn($part)=>mb_substr($part,0,1))->implode(''); @endphp
                        <details class="teacher-resource" data-teacher-resource data-teacher-search="{{ $teacher->name }} {{ $teacher->short_name }}"><summary><span class="teacher-arrow"></span><span class="teacher-color" style="background:{{ $teacher->color }}"></span><span class="teacher-name">{{ $label }}</span><span class="teacher-short">{{ $teacher->short_name }}</span></summary><div class="subject-resource-list">
                            @forelse($teacher->subjects as $subject)
                                @if(auth()->user()->isAdmin())<div class="subject-resource" data-subject-source data-teacher-id="{{ $teacher->id }}" data-subject-id="{{ $subject->id }}" data-create-url="{{ route('lessons.store') }}" data-teacher-color="{{ $teacher->color }}"><strong>{{ $subject->short_name ?: $subject->name }}</strong><span>{{ $subject->name }}</span></div>@else<div class="subject-resource"><strong>{{ $subject->short_name ?: $subject->name }}</strong><span>{{ $subject->name }}</span></div>@endif
                            @empty <div class="resource-empty">Дисциплины не назначены.</div>@endforelse
                        </div></details>
                    @empty <div class="resource-empty">Преподаватели пока не добавлены.</div>@endforelse
                </div>
            </aside>
        </div>
        @endif
    </div>
</div>

<div class="modal" data-note-modal aria-hidden="true"><div class="modal__backdrop" data-note-close></div><section class="modal__card" role="dialog" aria-modal="true" aria-labelledby="note-title"><button type="button" class="modal__close" data-note-close>×</button><h2 id="note-title">Заметка к занятию</h2><div class="note-details"><strong data-note-subject></strong><span data-note-teacher></span><span>Кабинет: <b data-note-room></b></span></div><textarea data-note-input rows="7" maxlength="2000" placeholder="Заметка"></textarea><div class="modal__actions"><button type="button" class="button" data-note-close>Закрыть</button>@if($editable)<button type="button" class="button button--primary" data-note-save>Сохранить заметку</button>@endif</div><div class="note-status" data-note-status></div></section></div>
@endsection
