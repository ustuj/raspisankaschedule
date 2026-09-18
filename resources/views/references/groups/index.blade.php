@extends('layouts.app')

@section('content')

<div class="page-header">
    <div>
        <h2>Учебные группы</h2>

        <p class="page-header__description">
            Управление группами колледжа.
        </p>
    </div>

    <a
        href="{{ route('groups.create') }}"
        class="button button--primary"
    >
        + Добавить группу
    </a>
</div>

<div class="card">

    @if ($groups->isEmpty())

        <div class="empty-state">
            <div class="empty-state__title">
                Групп пока нет
            </div>

            <div class="empty-state__text">
                Добавьте первую учебную группу.
            </div>
        </div>

    @else

        <div class="table-wrapper">

            <table class="data-table">

                <thead>
                    <tr>
                        <th>Группа</th>
                        <th>Специальность</th>
                        <th>Курс</th>
                        <th class="actions-column">Действия</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach ($groups as $group)

                        <tr>

                            <td>
                                <strong>
                                    {{ $group->name }}
                                </strong>
                            </td>

                            <td>
                                {{ $group->speciality }}
                            </td>

                            <td>
                                {{ $group->course }}
                            </td>

                            <td>
                                <div class="table-actions">

                                    <a
                                        href="{{ route('groups.edit', $group) }}"
                                        class="button button--secondary button--small"
                                    >
                                        Изменить
                                    </a>

                                    <form
                                        action="{{ route('groups.destroy', $group) }}"
                                        method="POST"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="button button--danger button--small"
                                            onclick="return confirm('Удалить группу {{ $group->name }}?')"
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