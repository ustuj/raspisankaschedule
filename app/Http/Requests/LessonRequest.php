<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LessonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $dayRule = $this->isMethod('POST')
            ? ['required', 'array', 'min:1']
            : ['required', 'string', 'in:Понедельник,Вторник,Среда,Четверг,Пятница,Суббота'];

        return [
            'group_id' => ['required', 'integer', 'exists:groups,id'],
            'day' => $dayRule,
            'day.*' => ['string', 'in:Понедельник,Вторник,Среда,Четверг,Пятница,Суббота'],
            'teacher_id' => ['required', 'integer', 'exists:teachers,id'],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'week1_lesson' => ['nullable', 'array'],
            'week1_lesson.*' => ['integer', 'between:1,4'],
            'week2_lesson' => ['nullable', 'array'],
            'week2_lesson.*' => ['integer', 'between:1,4'],
            'classroom_id' => ['nullable', 'integer', 'exists:classrooms,id'],
            'lesson_type' => ['required', 'string', 'in:Лекция,Практика,Лабораторная'],
            'note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
