<?php

namespace App\Http\Controllers;

use App\Http\Requests\LessonRequest;
use App\Models\Classroom;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LessonController extends Controller
{
    private const DAYS = ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота'];
    private const PAIRS = [
        1 => '09:00–09:45 / 09:50–10:35',
        2 => '10:45–11:30 / 11:35–12:20',
        3 => '13:05–13:50 / 13:55–14:40',
        4 => '14:50–15:35 / 15:40–16:25',
    ];
    private const MONDAY_PAIRS = [
        1 => '10:00–10:45 / 10:50–11:35',
        2 => '11:45–12:30 / 12:35–13:20',
        3 => '14:05–14:50 / 14:55–15:40',
        4 => '15:50–16:35 / 16:40–17:25',
    ];

    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        $groups = Group::query()->orderBy('name')->get();
        $teachers = Teacher::query()->with('subjects')->orderBy('name')->get();
        $subjects = Subject::query()->with('teachers')->orderBy('name')->get();
        $classrooms = Classroom::query()->orderBy('number')->orderBy('name')->get();
        $selectedWeek = $request->integer('week', 1) === 2 ? 2 : 1;
        $view = $request->query('view', 'mine');

        $selectedGroup = $request->integer('group_id') ?: null;
        if ($user->isStudent()) {
            if ($view === 'all') {
                $selectedGroup = null;
            } else {
                if (! $user->group_id) {
                    $selectedGroup = null;
                } else {
                    $selectedGroup = $user->group_id;
                }
            }
        }

        $lessons = Lesson::query()->with(['group', 'teacher', 'subject', 'classroom'])->get();

        return view('schedule.index', compact(
            'groups', 'teachers', 'subjects', 'classrooms', 'lessons',
            'selectedGroup', 'selectedWeek', 'view'
        ))->with([
            'days' => self::DAYS,
            'pairs' => self::PAIRS,
            'mondayPairs' => self::MONDAY_PAIRS,
        ]);
    }

    public function create(): View
    {
        abort_unless(auth()->user()?->isAdmin(), 403);
        return view('schedule.form', $this->formData(new Lesson, 'Добавление занятия', route('lessons.store'), 'POST'));
    }

    public function store(LessonRequest $request): RedirectResponse|JsonResponse
    {
        $this->ensureAdmin();
        $data = $request->validated();
        $week1Pairs = array_values(array_map('intval', $data['week1_lesson'] ?? []));
        $week2Pairs = array_values(array_map('intval', $data['week2_lesson'] ?? []));
        $this->ensureSelectedWeeks($week1Pairs, $week2Pairs);
        $this->ensureTeacherSubject((int) $data['teacher_id'], (int) $data['subject_id']);

        $created = [];
        DB::transaction(function () use ($data, $week1Pairs, $week2Pairs, &$created): void {
            foreach ($data['day'] as $day) {
                foreach ($this->lessonSlots($week1Pairs, $week2Pairs) as $slot) {
                    $attributes = [
                        'group_id' => (int) $data['group_id'],
                        'teacher_id' => (int) $data['teacher_id'],
                        'subject_id' => (int) $data['subject_id'],
                        'classroom_id' => !empty($data['classroom_id']) ? (int) $data['classroom_id'] : null,
                        'day' => $day,
                        'week1_lesson' => $slot['week1'],
                        'week2_lesson' => $slot['week2'],
                        'lesson_type' => $data['lesson_type'],
                        'note' => $data['note'] ?? null,
                    ];
                    $this->ensureNoConflicts($attributes);
                    $created[] = Lesson::create($attributes);
                }
            }
        });

        if ($request->expectsJson()) {
            $lesson = $created[0]->fresh()->load(['teacher', 'subject', 'classroom', 'group']);
            return response()->json(['message' => 'Занятие добавлено.', 'lesson' => $this->lessonJson($lesson)]);
        }

        return redirect()->route('schedule.index')->with('success', count($created) > 1 ? 'Занятия добавлены.' : 'Занятие добавлено.');
    }

    public function edit(Lesson $lesson): View
    {
        $this->ensureCanEdit($lesson);
        $lesson->load(['group', 'teacher', 'subject', 'classroom']);
        return view('schedule.form', $this->formData($lesson, 'Редактирование занятия', route('lessons.update', $lesson), 'PUT'));
    }

    public function update(LessonRequest $request, Lesson $lesson): RedirectResponse
    {
        $this->ensureCanEdit($lesson);
        $data = $request->validated();
        $week1Pairs = array_values(array_map('intval', $data['week1_lesson'] ?? []));
        $week2Pairs = array_values(array_map('intval', $data['week2_lesson'] ?? []));
        $this->ensureSelectedWeeks($week1Pairs, $week2Pairs);
        $this->ensureTeacherSubject((int) $data['teacher_id'], (int) $data['subject_id']);
        if (auth()->user()->isTeacher() && (int) $data['teacher_id'] !== (int) auth()->user()->teacher_id) {
            abort(403);
        }

        $related = Lesson::query()
            ->where('group_id', $lesson->group_id)
            ->where('teacher_id', $lesson->teacher_id)
            ->where('subject_id', $lesson->subject_id)
            ->where('classroom_id', $lesson->classroom_id)
            ->where('day', $lesson->day)
            ->where('lesson_type', $lesson->lesson_type)
            ->get();

        DB::transaction(function () use ($data, $week1Pairs, $week2Pairs, $related, $lesson): void {
            Lesson::query()->whereIn('id', $related->pluck('id'))->delete();
            $note = $data['note'] ?? $lesson->note;
            foreach ($this->lessonSlots($week1Pairs, $week2Pairs) as $slot) {
                $attributes = [
                    'group_id' => (int) $data['group_id'],
                    'teacher_id' => (int) $data['teacher_id'],
                    'subject_id' => (int) $data['subject_id'],
                    'classroom_id' => !empty($data['classroom_id']) ? (int) $data['classroom_id'] : null,
                    'day' => $data['day'],
                    'week1_lesson' => $slot['week1'],
                    'week2_lesson' => $slot['week2'],
                    'lesson_type' => $data['lesson_type'],
                    'note' => $note,
                ];
                $this->ensureNoConflicts($attributes, $related->all());
                Lesson::create($attributes);
            }
        });

        return redirect()->route('schedule.index')->with('success', 'Занятие изменено.');
    }

    public function destroy(Lesson $lesson): RedirectResponse
    {
        $this->ensureCanEdit($lesson);
        $lesson->delete();
        return redirect()->route('schedule.index')->with('success', 'Занятие удалено.');
    }

    public function updateNote(Request $request, Lesson $lesson): JsonResponse
    {
        $this->ensureCanEdit($lesson);
        $data = $request->validate(['note' => ['nullable', 'string', 'max:2000']]);
        $lesson->update(['note' => $data['note'] ?? null]);
        return response()->json(['message' => 'Заметка сохранена.', 'note' => $lesson->note]);
    }

    public function move(Request $request, Lesson $lesson): JsonResponse
    {
        $user = $request->user();
        if (! $user || (! $user->isAdmin() && (! $user->isTeacher() || (int) $user->teacher_id !== (int) $lesson->teacher_id))) {
            return response()->json(['message' => 'У вас нет прав на перенос этого занятия.'], 403);
        }
        $data = $request->validate([
            'group_id' => ['required', 'integer', 'exists:groups,id'],
            'day' => ['required', 'string', 'in:Понедельник,Вторник,Среда,Четверг,Пятница,Суббота'],
            'pair' => ['required', 'integer', 'between:1,4'],
            'week' => ['required', 'integer', 'in:1,2'],
            'swap_lesson_id' => ['nullable', 'integer', 'exists:lessons,id'],
        ]);

        $week = (int) $data['week'];
        $pair = (int) $data['pair'];

        return DB::transaction(function () use ($data, $lesson, $week, $pair): JsonResponse {
            $lesson = Lesson::query()->lockForUpdate()->findOrFail($lesson->id);

            if (!empty($data['swap_lesson_id'])) {
                $target = Lesson::query()->lockForUpdate()->findOrFail((int) $data['swap_lesson_id']);

                if (
                    $target->group_id !== (int) $data['group_id'] ||
                    $target->day !== $data['day'] ||
                    (int) $target->{'week'.$week.'_lesson'} !== $pair
                ) {
                    throw ValidationException::withMessages([
                        'schedule' => 'Целевая ячейка изменилась. Обновите страницу.',
                    ]);
                }

                $lessonDestination = [
                    'group_id' => $target->group_id,
                    'day' => $target->day,
                    'week1_lesson' => $target->week1_lesson,
                    'week2_lesson' => $target->week2_lesson,
                ];
                $targetDestination = [
                    'group_id' => $lesson->group_id,
                    'day' => $lesson->day,
                    'week1_lesson' => $lesson->week1_lesson,
                    'week2_lesson' => $lesson->week2_lesson,
                ];

                $this->ensureNoConflicts(
                    array_merge($lesson->only(['teacher_id', 'subject_id', 'classroom_id', 'lesson_type', 'note']), $lessonDestination),
                    [$lesson, $target]
                );
                $this->ensureNoConflicts(
                    array_merge($target->only(['teacher_id', 'subject_id', 'classroom_id', 'lesson_type', 'note']), $targetDestination),
                    [$lesson, $target]
                );

                $lesson->update($lessonDestination);
                $target->update($targetDestination);

                return response()->json([
                    'message' => 'Занятия обменены местами.',
                    'swapped' => true,
                    'lesson' => $this->lessonJson($lesson->fresh()->load(['group','teacher','subject','classroom'])),
                    'target' => $this->lessonJson($target->fresh()->load(['group','teacher','subject','classroom'])),
                ]);
            }

            // Для обычного переноса проверяем именно целевую ячейку выбранной недели.
            // Другая неделя этой записи не должна создавать ложный конфликт в пустой клетке.
            $conflictQuery = Lesson::query()
                ->where('day', $data['day'])
                ->where('group_id', (int) $data['group_id'])
                ->where('id', '!=', $lesson->id)
                ->where('week'.$week.'_lesson', $pair);

            if ($conflictQuery->exists()) {
                throw ValidationException::withMessages([
                    'schedule' => "Конфликт группы: {$data['day']}, {$pair} пара, {$week}-я неделя.",
                ]);
            }

            $teacherConflict = Lesson::query()
                ->where('day', $data['day'])
                ->where('teacher_id', $lesson->teacher_id)
                ->where('id', '!=', $lesson->id)
                ->where('week'.$week.'_lesson', $pair)
                ->exists();

            if ($teacherConflict) {
                throw ValidationException::withMessages([
                    'schedule' => "Конфликт преподавателя: {$data['day']}, {$pair} пара, {$week}-я неделя.",
                ]);
            }

            if ($lesson->classroom_id) {
                $classroomConflict = Lesson::query()
                    ->where('day', $data['day'])
                    ->where('classroom_id', $lesson->classroom_id)
                    ->where('id', '!=', $lesson->id)
                    ->where('week'.$week.'_lesson', $pair)
                    ->exists();

                if ($classroomConflict) {
                    throw ValidationException::withMessages([
                        'schedule' => "Конфликт кабинета: {$data['day']}, {$pair} пара, {$week}-я неделя.",
                    ]);
                }
            }

            $attributes = [
                'group_id' => (int) $data['group_id'],
                'teacher_id' => $lesson->teacher_id,
                'subject_id' => $lesson->subject_id,
                'classroom_id' => $lesson->classroom_id,
                'day' => $data['day'],
                'week1_lesson' => $lesson->week1_lesson,
                'week2_lesson' => $lesson->week2_lesson,
                'lesson_type' => $lesson->lesson_type,
                'note' => $lesson->note,
            ];
            $attributes[$week === 1 ? 'week1_lesson' : 'week2_lesson'] = $pair;

            $lesson->update($attributes);

            return response()->json([
                'message' => 'Занятие перенесено.',
                'swapped' => false,
                'lesson' => $this->lessonJson($lesson->fresh()->load(['group','teacher','subject','classroom'])),
            ]);
        });
    }

    public function exportIcal(Request $request)
    {
        $groupId = $request->integer('group_id') ?: null;
        if ($request->user()?->isStudent() && $request->query('view') !== 'all') {
            $groupId = $request->user()->group_id;
        }

        $lessons = Lesson::query()
            ->with(['group','teacher','subject','classroom'])
            ->when($groupId, fn($q) => $q->where('group_id', $groupId))
            ->orderBy('day')
            ->get();

        $timezone = config('schedule.timezone', 'Asia/Novosibirsk');
        $monday = Carbon::now($timezone)->startOfWeek();
        $events = $lessons->flatMap(fn(Lesson $lesson) => $this->icalEventsForLesson($lesson, $monday, $timezone));
        $calendar = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//NSMK//Schedule//RU',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'X-WR-CALNAME:Расписание НСМК',
            'X-WR-TIMEZONE:'.$timezone,
            ...$this->icalVtimezone($timezone),
            ...$events,
            'END:VCALENDAR',
            '',
        ]);

        return response($calendar, 200, [
            'Content-Type' => 'text/calendar; charset=UTF-8; method=PUBLISH',
            'Content-Disposition' => 'attachment; filename="raspisanie.ics"',
            'Content-Length' => strlen($calendar),
        ]);
    }

    public function print(Request $request): View
    {
        $selectedWeek = $request->integer('week', 1) === 2 ? 2 : 1;
        $groupId = $request->integer('group_id') ?: null;
        if ($request->user()?->isStudent() && $request->query('view') !== 'all') $groupId = $request->user()->group_id;
        $groups = Group::query()->when($groupId, fn($q) => $q->whereKey($groupId))->orderBy('name')->get();
        $lessons = Lesson::query()->with(['group','teacher','subject','classroom'])->when($groupId, fn($q)=>$q->where('group_id',$groupId))->get();
        return view('schedule.print', ['groups'=>$groups,'lessons'=>$lessons,'selectedWeek'=>$selectedWeek,'days'=>self::DAYS,'pairs'=>self::PAIRS,'mondayPairs'=>self::MONDAY_PAIRS]);
    }

    private function formData(Lesson $lesson, string $title, string $action, string $method): array
    {
        $groups = Group::query()->orderBy('name')->get();
        $teachers = Teacher::query()->with('subjects')->orderBy('name')->get();
        $subjects = Subject::query()->with('teachers')->orderBy('name')->get();
        $classrooms = Classroom::query()->orderBy('number')->orderBy('name')->get();
        $selectedWeek1Pairs = $lesson->exists ? [$lesson->week1_lesson] : [];
        $selectedWeek2Pairs = $lesson->exists ? [$lesson->week2_lesson] : [];
        if ($lesson->exists) {
            $related = Lesson::query()->where('group_id',$lesson->group_id)->where('teacher_id',$lesson->teacher_id)->where('subject_id',$lesson->subject_id)->where('classroom_id',$lesson->classroom_id)->where('day',$lesson->day)->where('lesson_type',$lesson->lesson_type)->get();
            $selectedWeek1Pairs = $related->pluck('week1_lesson')->filter()->map(fn($p)=>(int)$p)->unique()->values()->all();
            $selectedWeek2Pairs = $related->pluck('week2_lesson')->filter()->map(fn($p)=>(int)$p)->unique()->values()->all();
        }
        return compact('lesson','groups','teachers','subjects','classrooms','title','action','method','selectedWeek1Pairs','selectedWeek2Pairs') + ['days'=>self::DAYS,'pairs'=>self::PAIRS,'mondayPairs'=>self::MONDAY_PAIRS];
    }

    private function ensureAdmin(): void { abort_unless(auth()->user()?->isAdmin(), 403); }

    private function ensureCanEdit(Lesson $lesson): void
    {
        $user = auth()->user();
        abort_unless($user && ($user->isAdmin() || ($user->isTeacher() && (int) $user->teacher_id === (int) $lesson->teacher_id)), 403);
    }

    private function ensureTeacherSubject(int $teacherId, int $subjectId): void
    {
        if (! Teacher::query()->whereKey($teacherId)->whereHas('subjects', fn($q)=>$q->whereKey($subjectId))->exists()) {
            throw ValidationException::withMessages(['subject_id'=>'Дисциплина не закреплена за выбранным преподавателем.']);
        }
    }

    private function ensureSelectedWeeks(array $w1, array $w2): void
    {
        if (! $w1 && ! $w2) throw ValidationException::withMessages(['schedule'=>'Выберите хотя бы одну пару.']);
    }

    private function lessonSlots(array $w1, array $w2): array
    {
        $pairs = array_values(array_unique(array_merge($w1,$w2)));
        return array_map(fn(int $pair)=>['week1'=>in_array($pair,$w1,true)?$pair:null,'week2'=>in_array($pair,$w2,true)?$pair:null], $pairs);
    }

    private function lessonJson(Lesson $lesson): array
    {
        return [
            'id'=>$lesson->id,'group_id'=>$lesson->group_id,'day'=>$lesson->day,
            'week1'=>$lesson->week1_lesson,'week2'=>$lesson->week2_lesson,'lesson_type'=>$lesson->lesson_type,'note'=>$lesson->note,
            'teacher'=>['id'=>$lesson->teacher->id,'name'=>$lesson->teacher->name,'short_name'=>$lesson->teacher->short_name,'color'=>$lesson->teacher->color],
            'subject'=>['id'=>$lesson->subject->id,'name'=>$lesson->subject->name,'short_name'=>$lesson->subject->short_name],
            'classroom'=>$lesson->classroom?->number ?? $lesson->classroom?->name,
            'edit_url'=>route('lessons.edit',$lesson),'move_url'=>route('lessons.move',$lesson),'delete_url'=>route('lessons.destroy',$lesson),'note_url'=>route('lessons.note',$lesson),
        ];
    }

    private function ensureNoConflicts(array $data, Lesson|array|null $ignoredLessons = null): void
    {
        $days = is_array($data['day'] ?? null) ? $data['day'] : [$data['day'] ?? null];
        $ignoredIds = match (true) {
            $ignoredLessons instanceof Lesson => [$ignoredLessons->id],
            is_array($ignoredLessons) => collect($ignoredLessons)->filter(fn($l)=>$l instanceof Lesson)->pluck('id')->all(),
            default => [],
        };
        $messages=[];
        foreach ($days as $day) {
            foreach ([1,2] as $week) {
                $pair=$data['week'.$week.'_lesson']??null;
                if (! $pair) continue;
                $query=Lesson::query()->where('day',$day)->where('week'.$week.'_lesson',$pair)->when($ignoredIds,fn($q)=>$q->whereNotIn('id',$ignoredIds));
                if ($query->clone()->where('group_id',$data['group_id'])->exists()) $messages[]="Конфликт группы: {$day}, {$pair} пара, {$week}-я неделя.";
                if ($query->clone()->where('teacher_id',$data['teacher_id'])->exists()) $messages[]="Конфликт преподавателя: {$day}, {$pair} пара, {$week}-я неделя.";
                if (!empty($data['classroom_id']) && $query->clone()->where('classroom_id',$data['classroom_id'])->exists()) $messages[]="Конфликт кабинета: {$day}, {$pair} пара, {$week}-я неделя.";
            }
        }
        if ($messages) throw ValidationException::withMessages(['schedule'=>array_values(array_unique($messages))]);
    }

    private function icalEventsForLesson(Lesson $lesson, Carbon $monday, string $timezone): array
    {
        $offset = array_flip(self::DAYS)[$lesson->day] ?? 0;
        $events=[];
        foreach ([1,2] as $week) {
            $pair=$lesson->{'week'.$week.'_lesson'}; if(!$pair) continue;
            [$startTime,$endTime]=$this->icalTimeRange($lesson->day,(int)$pair);
            $start=$monday->copy()->addDays($offset)->addWeeks($week-1)->setTimeFromTimeString($startTime);
            $end=$monday->copy()->addDays($offset)->addWeeks($week-1)->setTimeFromTimeString($endTime);
            $room=$lesson->classroom?->number ?? $lesson->classroom?->name ?? 'Не указан';
            $description="Преподаватель: {$lesson->teacher->name}\nТип: {$lesson->lesson_type}\nНеделя: {$week}".($lesson->note?"\nЗаметка: {$lesson->note}":'');
            $events[]=implode("\r\n",[
                'BEGIN:VEVENT',
                'UID:lesson-'.$lesson->id.'-week-'.$week.'@nsmk.local',
                'DTSTAMP:'.Carbon::now('UTC')->format('Ymd\THis\Z'),
                'DTSTART;TZID='.$this->escapeIcal($timezone).':'.$start->format('Ymd\THis'),
                'DTEND;TZID='.$this->escapeIcal($timezone).':'.$end->format('Ymd\THis'),
                'RRULE:FREQ=WEEKLY;INTERVAL=2',
                'SEQUENCE:0',
                'STATUS:CONFIRMED',
                'TRANSP:OPAQUE',
                'SUMMARY:'.$this->escapeIcal($lesson->subject->name.' — '.$lesson->group->name),
                'DESCRIPTION:'.$this->escapeIcal($description),
                'LOCATION:'.$this->escapeIcal('Каб. '.$room),
                'END:VEVENT',
            ]);
        }
        return $events;
    }

    private function icalVtimezone(string $timezone): array
    {
        if ($timezone !== 'Asia/Novosibirsk') {
            return [];
        }

        return [
            'BEGIN:VTIMEZONE',
            'TZID:Asia/Novosibirsk',
            'X-LIC-LOCATION:Asia/Novosibirsk',
            'BEGIN:STANDARD',
            'DTSTART:19700101T000000',
            'TZOFFSETFROM:+0700',
            'TZOFFSETTO:+0700',
            'TZNAME:+07',
            'END:STANDARD',
            'END:VTIMEZONE',
        ];
    }

    private function icalTimeRange(string $day,int $pair): array
    {
        $regular=[1=>['09:00','10:35'],2=>['10:45','12:20'],3=>['13:05','14:40'],4=>['14:50','16:25']];
        $monday=[1=>['10:00','11:35'],2=>['11:45','13:20'],3=>['14:05','15:40'],4=>['15:50','17:25']];
        return ($day==='Понедельник'?$monday:$regular)[$pair];
    }

    private function escapeIcal(string $value): string { return str_replace(['\\',';',',',"\r\n","\n"],['\\\\','\\;','\\,','\\n','\\n'],$value); }
}
