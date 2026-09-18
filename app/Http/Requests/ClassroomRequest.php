<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ClassroomRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'number' => ['required', 'string', 'max:30'],
            'floor' => ['required', 'string', 'max:20'],
            'capacity' => ['nullable', 'integer', 'between:1,500'],
        ];
    }
}
