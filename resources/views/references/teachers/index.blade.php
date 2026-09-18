@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h2>Преподаватели</h2>
        <p class="page-header__description">Преподаватели и закреплённые за ними дисциплины.</p>
    </div>
    <a href="{{ route('teachers.create') }}" class="button button--primary">Добавить преподавателя</a>
</div>

<div class="card">
    @if ($teachers->isEmpty())
        <div class="empty-state">
            <div class="empty-state__title">Преподавателей пока нет</div>
            <div class="empty-state__text">Добавьте первого преподавателя.</div>
        </div>
    @else
        <div class="table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Преподаватель</th>
                        <th>Обозначение</th>
                        <th>Цвет</th>
                        <th>Дисциплины</th>
                        <th class="actions-column">Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $colorNames = [
                            '#2563eb' => 'Синий', '#16a34a' => 'Зелёный', '#eab308' => 'Жёлтый', '#9333ea' => 'Фиолетовый',
                            '#dc2626' => 'Красный', '#ea580c' => 'Оранжевый', '#0891b2' => 'Бирюзовый', '#db2777' => 'Розовый',
                        ];
                    @endphp
                    @foreach ($teachers as $teacher)
                        <tr>
                            <td><strong>{{ $teacher->name }}</strong></td>
                            <td>{{ $teacher->short_name }}</td>
                            <td>
                                <span class="teacher-color-cell">
                                    <span class="teacher-color-dot" style="background-color: {{ $teacher->color }}"></span>
                                    <span class="teacher-color-value">{{ $colorNames[strtolower($teacher->color)] ?? $teacher->color }}</span>
                                </span>
                            </td>
                            <td>{{ $teacher->subjects->isEmpty() ? 'Не назначены' : $teacher->subjects->pluck('name')->join(', ') }}</td>
                            <td>
                                <div class="table-actions">
                                    <a href="{{ route('teachers.edit', $teacher) }}" class="button button--secondary button--small">Изменить</a>
                                    <form action="{{ route('teachers.destroy', $teacher) }}" method="POST" onsubmit="return confirm('Удалить преподавателя?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="button button--danger button--small">Удалить</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
