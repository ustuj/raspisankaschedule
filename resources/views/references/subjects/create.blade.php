@extends('layouts.app')

@section('content')

<div class="page-header">
    <div>
        <h2>Добавление дисциплины</h2>
    </div>
</div>

<div class="card form-card">

    <form
        action="{{ route('subjects.store') }}"
        method="POST"
    >
        @csrf

        <div class="form-grid">

            <div class="form-field form-field--full">
                <label for="name">Название</label>

                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name') }}"
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
                    value="{{ old('short_name') }}"
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
                    <option value="Лекция">Лекция</option>
                    <option value="Практика">Практика</option>
                    <option value="Лабораторная">Лабораторная</option>
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
                                @checked(in_array(
                                    $teacher->id,
                                    old('teachers', [])
                                ))
                            >

                            <span>
                                {{ $teacher->name }}
                            </span>

                        </label>

                    @empty

                        <p class="muted">
                            Сначала создайте преподавателей.
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
                Добавить
            </button>

        </div>

    </form>

</div>

@endsection