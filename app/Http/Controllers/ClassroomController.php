<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClassroomRequest;
use App\Models\Classroom;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClassroomController extends Controller
{
    public function index(): View
    {
        $classrooms = Classroom::query()->withCount('lessons')->orderBy('number')->orderBy('name')->get();

        return view('references.classrooms.index', compact('classrooms'));
    }

    public function create(): View
    {
        return view('references.classrooms.create');
    }

    public function store(ClassroomRequest $request): RedirectResponse
    {
        Classroom::create($request->validated());

        return redirect()->route('classrooms.index')->with('success', 'Кабинет добавлен.');
    }

    public function edit(Classroom $classroom): View
    {
        return view('references.classrooms.edit', compact('classroom'));
    }

    public function update(ClassroomRequest $request, Classroom $classroom): RedirectResponse
    {
        $classroom->update($request->validated());

        return redirect()->route('classrooms.index')->with('success', 'Кабинет изменён.');
    }

    public function destroy(Classroom $classroom): RedirectResponse
    {
        if ($classroom->lessons()->exists()) {
            return redirect()->route('classrooms.index')->with('error', 'Нельзя удалить кабинет, пока он используется в расписании.');
        }

        $classroom->delete();

        return redirect()->route('classrooms.index')->with('success', 'Кабинет удалён.');
    }
}
