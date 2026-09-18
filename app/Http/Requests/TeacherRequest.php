<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TeacherRequest extends FormRequest
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
            'color' => [
                'required',
                'string',
                'max:20',
                Rule::in([
                    '#2563eb',
                    '#16a34a',
                    '#eab308',
                    '#9333ea',
                    '#dc2626',
                    '#ea580c',
                    '#0891b2',
                    '#db2777',
                ]),
            ],
            'subjects' => ['nullable', 'array'],
            'subjects.*' => ['integer', 'exists:subjects,id'],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'ФИО преподавателя',
            'short_name' => 'краткое обозначение',
            'color' => 'цвет',
            'subjects' => 'дисциплины',
        ];
    }
}