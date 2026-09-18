<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GroupRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'speciality' => ['required', 'string', 'max:150'],
            'course' => ['required', 'integer', 'between:1,4'],
        ];
    }
}
