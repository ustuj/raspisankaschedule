<?php

namespace App\Http\Controllers;

use App\Http\Requests\TeacherRequest;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TeacherController extends Controller
{
    public function index(): View
    {
        $teachers = Teacher::query()->with('subjects')->orderBy('name')->get();

        return view('references.teachers.index', compact('teachers'));
    }

    public function create(): View
    {
        return view('references.teachers.create', ['subjects' => Subject::query()->orderBy('name')->get()]);
    }

    public function store(TeacherRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $teacher = Teacher::create($request->safe()->only([
                'name',
                'short_name',
                'color',
            ]));

            $teacher->subjects()->sync($request->validated('subjects', []));
        });

        return redirect()->route('teachers.index')->with('success', 'Преподаватель успешно добавлен.');
    }

    public function edit(Teacher $teacher): View
    {
        $teacher->load('subjects');
        $subjects = Subject::query()->orderBy('name')->get();

        return view('references.teachers.edit', compact('teacher', 'subjects'));
    }

    public function update(TeacherRequest $request, Teacher $teacher): RedirectResponse
    {
        DB::transaction(function () use ($request, $teacher): void {
            $teacher->update($request->safe()->only([
                'name',
                'short_name',
                'color',
            ]));

            $teacher->subjects()->sync($request->validated('subjects', []));
        });

        return redirect()->route('teachers.index')->with('success', 'Преподаватель успешно изменён.');
    }

    public function destroy(Teacher $teacher): RedirectResponse
    {
        if ($teacher->lessons()->exists()) {
            return redirect()->route('teachers.index')->with('error', 'Нельзя удалить преподавателя, пока он используется в расписании.');
        }

        if ($teacher->users()->exists()) {
            return redirect()->route('teachers.index')->with('error', 'Нельзя удалить преподавателя, пока к нему привязана учётная запись. Сначала удалите или измените пользователя.');
        }

        $teacher->subjects()->detach();
        $teacher->delete();

        return redirect()->route('teachers.index')->with('success', 'Преподаватель удалён.');
    }
}
