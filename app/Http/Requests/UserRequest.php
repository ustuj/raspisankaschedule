<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $user = $this->route('user');
        $userId = is_object($user) ? $user->id : $user;

        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'role' => ['required', Rule::in(['admin', 'teacher', 'student'])],
            'password' => [$this->isMethod('POST') ? 'required' : 'nullable', 'string', 'min:6', 'confirmed'],
            'group_id' => ['nullable', 'required_if:role,student', 'integer', 'exists:groups,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $role = $this->input('role');
        if ($role === 'teacher') $this->merge(['group_id' => null]);
        if ($role === 'student') $this->merge(['teacher_id' => null]);
        if ($role === 'admin') $this->merge(['teacher_id' => null, 'group_id' => null]);
    }
}
