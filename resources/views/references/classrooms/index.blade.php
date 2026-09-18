@extends('layouts.app')
@section('content')
<div class="page-header"><div><h1>Кабинеты</h1><p class="page-header__description">Номер, этаж и вместимость аудитории.</p></div><a href="{{ route('classrooms.create') }}" class="button button--primary">Добавить кабинет</a></div>
<div class="card table-wrapper"><table class="data-table"><thead><tr><th>Название</th><th>Номер</th><th>Этаж</th><th>Вместимость</th><th>Занятий</th><th class="actions-column">Действия</th></tr></thead><tbody>
@forelse($classrooms as $classroom)<tr><td>{{ $classroom->name }}</td><td>{{ $classroom->number }}</td><td>{{ $classroom->floor }}</td><td>{{ $classroom->capacity ? $classroom->capacity.' мест' : '—' }}</td><td>{{ $classroom->lessons_count }}</td><td><div class="table-actions"><a class="button button--small" href="{{ route('classrooms.edit',$classroom) }}">Изменить</a><form method="POST" action="{{ route('classrooms.destroy',$classroom) }}" onsubmit="return confirm('Удалить кабинет?')">@csrf @method('DELETE')<button class="button button--small button--danger">Удалить</button></form></div></td></tr>@empty
<tr><td colspan="6"><div class="empty-state">Кабинетов нет.</div></td></tr>
@endforelse
</tbody></table></div>
@endsection
