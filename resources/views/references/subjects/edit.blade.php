@extends('layouts.app')

@section('content')

<div class="page-header">
    <div>
        <h2>Редактирование дисциплины</h2>
    </div>
</div>

<div class="card form-card">

    <form
        action="{{ route('subjects.update', $subject) }}"
        method="POST"
    >
        @csrf
        @method('PUT')

        @php
            $selectedTeachers = old(
                'teachers',
                $subject->teachers->pluck('id')->toArray()
            );
        @endphp

        <div class="form-grid">

            <div class="form-field form-field--full">

                <label for="name">
                    Название
                </label>

                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name', $subject->name) }}"
                    required
                >

            </div>

            <div class="form-field">

                <label for="short_name">
                    Сокращение
                </label>

                <input
                    id="short_name"
                    name="short_name"
                    type="text"
                    value="{{ old('short_name', $subject->short_name) }}"
                    required
                >

            </div>

            <div class="form-field">

                <label for="lesson_type">
                    Тип занятия
                </label>

                <select
                    id="lesson_type"
                    name="lesson_type"
                    class="form-select"
                    required
                >
                    <option
                        value="Лекция"
                        @selected(old('lesson_type', $subject->lesson_type) === 'Лекция')
                    >
                        Лекция
                    </option>

                    <option
                        value="Практика"
                        @selected(old('lesson_type', $subject->lesson_type) === 'Практика')
                    >
                        Практика
                    </option>

                    <option
                        value="Лабораторная"
                        @selected(old('lesson_type', $subject->lesson_type) === 'Лабораторная')
                    >
                        Лабораторная
                    </option>
                </select>

            </div>

            <div class="form-field form-field--full">

                <label>Преподаватели</label>

                <div class="checkbox-list">

                    @forelse ($teachers as $teacher)

                        <label class="checkbox-item">

                            <input
                                type="checkbox"
                                name="teachers[]"
                                value="{{ $teacher->id }}"
                                @checked(in_array($teacher->id, $selectedTeachers))
                            >

                            <span>
                                {{ $teacher->name }}
                            </span>

                        </label>

                    @empty

                        <p class="muted">
                            Преподавателей пока нет.
                        </p>

                    @endforelse

                </div>

            </div>

        </div>

        <div class="form-actions">

            <a
                href="{{ route('subjects.index') }}"
                class="button button--secondary"
            >
                Отмена
            </a>

            <button
                type="submit"
                class="button button--primary"
            >
                Сохранить
            </button>

        </div>

    </form>

</div>

@endsection