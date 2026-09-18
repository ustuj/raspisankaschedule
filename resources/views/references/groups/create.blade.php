@extends('layouts.app')

@section('content')

<div class="page-header">
    <div>
        <h2>Добавление группы</h2>

        <p class="page-header__description">
            Создайте новую учебную группу.
        </p>
    </div>
</div>

<div class="card form-card">

    <form
        action="{{ route('groups.store') }}"
        method="POST"
    >

        @csrf

        <div class="form-grid">

            <div class="form-field">
                <label for="name">
                    Название группы
                </label>

                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name') }}"
                    required
                >
            </div>

            <div class="form-field">
                <label for="course">
                    Курс
                </label>

                <input
                    id="course"
                    name="course"
                    type="number"
                    min="1"
                    max="4"
                    step="1"
                    value="{{ old('course') }}"
                    required
                >
            </div>

            <div class="form-field form-field--full">
                <label for="speciality">
                    Специальность
                </label>

                <input
                    id="speciality"
                    name="speciality"
                    type="text"
                    value="{{ old('speciality') }}"
                    required
                >
            </div>

        </div>

        <div class="form-actions">

            <a
                href="{{ route('groups.index') }}"
                class="button button--secondary"
            >
                Отмена
            </a>

            <button
                type="submit"
                class="button button--primary"
            >
                Добавить группу
            </button>

        </div>

    </form>

</div>

@endsection