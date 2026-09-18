<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'short_name' => ['required', 'string', 'max:30'],
            'lesson_type' => ['required', 'string', 'max:50'],
            'teachers' => ['nullable', 'array'],
            'teachers.*' => ['integer', 'exists:teachers,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'название дисциплины',
            'short_name' => 'сокращение',
            'lesson_type' => 'тип занятия',
            'teachers' => 'преподаватели',
        ];
    }
}