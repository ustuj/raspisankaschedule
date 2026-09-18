<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\Group;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()->with(['teacher', 'group'])->orderBy('name')->get();
        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        return view('users.form', [
            'user' => new User(['role' => 'student']),
            'roles' => $this->roles(),
            'groups' => Group::query()->orderBy('name')->get(),
            'formTitle' => 'Добавление пользователя',
            'formAction' => route('users.store'),
            'method' => 'POST',
        ]);
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use (&$data): void {
            $teacherId = null;

            if (($data['role'] ?? null) === 'teacher') {
                $teacher = $this->findOrCreateTeacherProfile($data['name']);
                $teacherId = $teacher->id;
            }

            $data['teacher_id'] = $teacherId;
            User::create($data);
        });

        return redirect()->route('users.index')->with('success', 'Пользователь добавлен.');
    }

    public function edit(User $user): View
    {
        return view('users.form', [
            'user' => $user,
            'roles' => $this->roles(),
            'groups' => Group::query()->orderBy('name')->get(),
            'formTitle' => 'Редактирование пользователя',
            'formAction' => route('users.update', $user),
            'method' => 'PUT',
        ]);
    }

    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        DB::transaction(function () use ($data, $user): void {
            $role = $data['role'] ?? $user->role;

            if ($role === 'teacher') {
                $teacher = $user->teacher ?? $this->findOrCreateTeacherProfile($data['name']);
                $teacher->update(['name' => $data['name']]);
                $data['teacher_id'] = $teacher->id;
                $data['group_id'] = null;
            } else {
                $data['teacher_id'] = null;
            }

            $user->update($data);
        });

        return redirect()->route('users.index')->with('success', 'Пользователь изменён.');
    }

    private function findOrCreateTeacherProfile(string $name): Teacher
    {
        $teacher = Teacher::query()->where('name', $name)->first();
        if ($teacher) {
            return $teacher;
        }

        $parts = preg_split('/\s+/', trim($name)) ?: [];
        $shortName = mb_strtoupper(
            mb_substr($parts[0] ?? '', 0, 1) . mb_substr($parts[1] ?? '', 0, 1)
        );
        $shortName = $shortName !== '' ? $shortName : 'ПР';

        $colors = [
            '#2563eb', '#16a34a', '#eab308', '#9333ea',
            '#dc2626', '#ea580c', '#0891b2', '#db2777',
        ];
        $usedColors = Teacher::query()->pluck('color')->all();
        $color = collect($colors)->first(fn (string $value) => !in_array($value, $usedColors, true)) ?? $colors[Teacher::query()->count() % count($colors)];

        return Teacher::create([
            'name' => $name,
            'short_name' => $shortName,
            'color' => $color,
        ]);
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->is(auth()->user())) {
            return back()->with('error', 'Нельзя удалить текущую учётную запись.');
        }
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Пользователь удалён.');
    }

    private function roles(): array
    {
        return [
            'admin' => 'Администратор',
            'teacher' => 'Преподаватель',
            'student' => 'Студент',
        ];
    }
}
