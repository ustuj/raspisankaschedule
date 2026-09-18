@extends('layouts.app')

@section('content')

<div class="page-header">
    <div>
        <h2>Дисциплины</h2>

        <p class="page-header__description">
            Список учебных дисциплин и преподавателей.
        </p>
    </div>

    <a
        href="{{ route('subjects.create') }}"
        class="button button--primary"
    >
        + Добавить дисциплину
    </a>
</div>

<div class="card">

    @if ($subjects->isEmpty())

        <div class="empty-state">
            <div class="empty-state__title">
                Дисциплин пока нет
            </div>

            <div class="empty-state__text">
                Добавьте первую дисциплину.
            </div>
        </div>

    @else

        <div class="table-wrapper">

            <table class="data-table">

                <thead>
                    <tr>
                        <th>Название</th>
                        <th>Сокращение</th>
                        <th>Тип</th>
                        <th>Преподаватели</th>
                        <th class="actions-column">Действия</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach ($subjects as $subject)

                        <tr>

                            <td>
                                <strong>
                                    {{ $subject->name }}
                                </strong>
                            </td>

                            <td>
                                {{ $subject->short_name }}
                            </td>

                            <td>
                                {{ $subject->lesson_type }}
                            </td>

                            <td>
                                @if ($subject->teachers->isEmpty())
                                    <span class="muted">
                                        Не назначены
                                    </span>
                                @else
                                    {{ $subject->teachers->pluck('short_name')->join(', ') }}
                                @endif
                            </td>

                            <td>
                                <div class="table-actions">

                                    <a
                                        href="{{ route('subjects.edit', $subject) }}"
                                        class="button button--secondary button--small"
                                    >
                                        Изменить
                                    </a>

                                    <form
                                        action="{{ route('subjects.destroy', $subject) }}"
                                        method="POST"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="button button--danger button--small"
                                            onclick="return confirm('Удалить дисциплину?')"
                                        >
                                            Удалить
                                        </button>
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