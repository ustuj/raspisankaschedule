<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubjectRequest;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SubjectController extends Controller
{
    public function index(): View
    {
        $subjects = Subject::query()
            ->with('teachers')
            ->orderBy('name')
            ->get();

        return view('references.subjects.index', compact('subjects'));
    }

    public function create(): View
    {
        $teachers = Teacher::query()
            ->orderBy('name')
            ->get();

        return view(
            'references.subjects.create',
            compact('teachers')
        );
    }

    public function store(SubjectRequest $request): RedirectResponse
    {
        $subject = Subject::create(
            $request->safe()->only([
                'name',
                'short_name',
                'lesson_type',
            ])
        );

        $subject->teachers()->sync(
            $request->validated('teachers', [])
        );

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Дисциплина успешно добавлена.');
    }

    public function edit(Subject $subject): View
    {
        $subject->load('teachers');

        $teachers = Teacher::query()
            ->orderBy('name')
            ->get();

        return view(
            'references.subjects.edit',
            compact('subject', 'teachers')
        );
    }

    public function update(
        SubjectRequest $request,
        Subject $subject
    ): RedirectResponse {
        $subject->update(
            $request->safe()->only([
                'name',
                'short_name',
                'lesson_type',
            ])
        );

        $subject->teachers()->sync(
            $request->validated('teachers', [])
        );

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Дисциплина успешно изменена.');
    }

    public function destroy(Subject $subject): RedirectResponse
    {
        $subject->teachers()->detach();
        $subject->delete();

        return redirect()
            ->route('subjects.index')
            ->with('success', 'Дисциплина удалена.');
    }
}