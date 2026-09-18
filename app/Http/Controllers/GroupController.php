<?php

namespace App\Http\Controllers;

use App\Http\Requests\GroupRequest;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GroupController extends Controller
{
    /**
     * Список всех групп.
     */
    public function index(): View
    {
        $groups = Group::query()
            ->orderBy('name')
            ->get();

        return view('references.groups.index', compact('groups'));
    }

    /**
     * Форма создания группы.
     */
    public function create(): View
    {
        return view('references.groups.create');
    }

    /**
     * Сохранение новой группы.
     */
    public function store(GroupRequest $request): RedirectResponse
    {
        Group::create($request->validated());

        return redirect()
            ->route('groups.index')
            ->with('success', 'Группа успешно добавлена.');
    }

    /**
     * Форма редактирования группы.
     */
    public function edit(Group $group): View
    {
        return view('references.groups.edit', compact('group'));
    }

    /**
     * Обновление группы.
     */
    public function update(
        GroupRequest $request,
        Group $group
    ): RedirectResponse {
        $group->update($request->validated());

        return redirect()
            ->route('groups.index')
            ->with('success', 'Группа успешно изменена.');
    }

    /**
     * Удаление группы.
     */
    public function destroy(Group $group): RedirectResponse
    {
        DB::transaction(function () use ($group): void {
            // Сначала отвязываем студентов от группы и удаляем занятия,
            // чтобы внешний ключ lessons.group_id не блокировал удаление группы.
            User::query()
                ->where('group_id', $group->id)
                ->update(['group_id' => null]);

            Lesson::query()
                ->where('group_id', $group->id)
                ->delete();

            $group->delete();
        });

        return redirect()
            ->route('groups.index')
            ->with('success', 'Группа и связанные занятия удалены.');
    }
}