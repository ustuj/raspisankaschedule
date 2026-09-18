<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Lesson;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchedulePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_schedule_page_contains_groups_and_teacher_panel(): void
    {
        $group = Group::create([
            'name' => 'П-21',
            'speciality' => 'Программирование',
            'course' => 2,
        ]);

        $teacher = Teacher::create([
            'name' => 'Иванов Сергей Владимирович',
            'short_name' => 'ИВ',
            'color' => '#2563eb',
        ]);

        $subject = Subject::create([
            'name' => 'Информатика',
            'short_name' => 'ИНФ',
            'lesson_type' => 'Лекция',
        ]);

        $teacher->subjects()->attach($subject);

        $this->get('/schedule')
            ->assertOk()
            ->assertSee('П-21')
            ->assertSee('Иванов Сергей Владимирович')
            ->assertSee('Информатика');
    }


    public function test_schedule_page_contains_class_hour(): void
    {
        $this->get('/schedule')
            ->assertOk()
            ->assertSee('Классный час');
    }

    public function test_lesson_move_rejects_a_teacher_conflict(): void
    {
        $teacher = Teacher::create([
            'name' => 'Иванов Сергей Владимирович',
            'short_name' => 'ИВ',
            'color' => '#2563eb',
        ]);
        $subject = Subject::create([
            'name' => 'Информатика',
            'short_name' => 'ИНФ',
            'lesson_type' => 'Лекция',
        ]);
        $teacher->subjects()->attach($subject);

        $firstGroup = Group::create(['name' => 'П-21', 'speciality' => 'Программирование', 'course' => 2]);
        $secondGroup = Group::create(['name' => 'П-22', 'speciality' => 'Программирование', 'course' => 2]);

        $lesson = Lesson::create([
            'group_id' => $firstGroup->id,
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'day' => 'Понедельник',
            'week1_lesson' => 1,
            'lesson_type' => 'Лекция',
        ]);

        Lesson::create([
            'group_id' => $secondGroup->id,
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'day' => 'Вторник',
            'week1_lesson' => 2,
            'lesson_type' => 'Лекция',
        ]);

        $this->patch(route('lessons.move', $lesson), [
            'group_id' => $firstGroup->id,
            'day' => 'Вторник',
            'pair' => 2,
            'week' => 1,
        ])->assertSessionHasErrors('schedule');

        $this->assertDatabaseHas('lessons', ['id' => $lesson->id, 'day' => 'Понедельник']);
    }

    public function test_schedule_can_be_exported_as_ical(): void
    {
        $group = Group::create(['name' => 'П-21', 'speciality' => 'Программирование', 'course' => 2]);
        $teacher = Teacher::create(['name' => 'Иванов Сергей Владимирович', 'short_name' => 'ИВ', 'color' => '#2563eb']);
        $subject = Subject::create(['name' => 'Информатика', 'short_name' => 'ИНФ', 'lesson_type' => 'Лекция']);
        $teacher->subjects()->attach($subject);

        Lesson::create([
            'group_id' => $group->id,
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'day' => 'Понедельник',
            'week1_lesson' => 1,
            'week2_lesson' => 2,
            'lesson_type' => 'Лекция',
        ]);

        $this->get(route('schedule.export-ical', ['group_id' => $group->id]))
            ->assertOk()
            ->assertHeader('content-type', 'text/calendar; charset=UTF-8')
            ->assertSee('BEGIN:VCALENDAR', false)
            ->assertSee('RRULE:FREQ=WEEKLY;INTERVAL=2', false)
            ->assertSee('SUMMARY:Информатика — П-21', false);
    }
    public function test_lessons_can_be_created_for_multiple_pairs_on_both_weeks(): void
    {
        $group = Group::create(['name' => 'П-23', 'speciality' => 'Программирование', 'course' => 2]);
        $teacher = Teacher::create(['name' => 'Петров Пётр Петрович', 'short_name' => 'ПП', 'color' => '#16a34a']);
        $subject = Subject::create(['name' => 'Базы данных', 'short_name' => 'БД', 'lesson_type' => 'Практика']);
        $teacher->subjects()->attach($subject);

        $this->post(route('lessons.store'), [
            'group_id' => $group->id,
            'day' => ['Понедельник'],
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'week1_lesson' => [1, 2],
            'week2_lesson' => [2, 3],
            'lesson_type' => 'Практика',
        ])->assertRedirect(route('schedule.index'));

        $this->assertDatabaseCount('lessons', 3);
        $this->assertDatabaseHas('lessons', ['group_id' => $group->id, 'week1_lesson' => 1, 'week2_lesson' => null]);
        $this->assertDatabaseHas('lessons', ['group_id' => $group->id, 'week1_lesson' => 2, 'week2_lesson' => 2]);
        $this->assertDatabaseHas('lessons', ['group_id' => $group->id, 'week1_lesson' => null, 'week2_lesson' => 3]);
    }

    public function test_lesson_move_returns_json_without_redirecting(): void
    {
        $teacher = Teacher::create(['name' => 'Сидоров Иван Иванович', 'short_name' => 'СИ', 'color' => '#2563eb']);
        $subject = Subject::create(['name' => 'История', 'short_name' => 'ИСТ', 'lesson_type' => 'Лекция']);
        $teacher->subjects()->attach($subject);
        $group = Group::create(['name' => 'П-24', 'speciality' => 'Программирование', 'course' => 2]);

        $lesson = Lesson::create([
            'group_id' => $group->id,
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'day' => 'Понедельник',
            'week1_lesson' => 2,
            'lesson_type' => 'Лекция',
        ]);

        $this->withHeaders(['Accept' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest'])
            ->patch(route('lessons.move', $lesson), [
                'group_id' => $group->id,
                'day' => 'Вторник',
                'pair' => 1,
                'week' => '1',
            ])
            ->assertOk()
            ->assertJsonPath('lesson.week1', 1);

        $this->assertDatabaseHas('lessons', ['id' => $lesson->id, 'day' => 'Вторник', 'week1_lesson' => 1]);
    }

    public function test_lesson_move_uses_the_week_sent_by_the_browser(): void
    {
        $teacher = Teacher::create(['name' => 'Орлов Иван Иванович', 'short_name' => 'ОИ', 'color' => '#2563eb']);
        $subject = Subject::create(['name' => 'Алгебра', 'short_name' => 'АЛГ', 'lesson_type' => 'Лекция']);
        $teacher->subjects()->attach($subject);
        $group = Group::create(['name' => 'П-26', 'speciality' => 'Программирование', 'course' => 2]);

        $lesson = Lesson::create([
            'group_id' => $group->id,
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'day' => 'Понедельник',
            'week1_lesson' => 4,
            'week2_lesson' => 1,
            'lesson_type' => 'Лекция',
        ]);

        $this->withHeaders(['Accept' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest'])
            ->patch(route('lessons.move', $lesson), [
                'group_id' => (string) $group->id,
                'day' => 'Вторник',
                'pair' => '2',
                'week' => '1',
            ])
            ->assertOk()
            ->assertJsonPath('lesson.week1', 2)
            ->assertJsonPath('lesson.week2', 1);

        $this->assertDatabaseHas('lessons', [
            'id' => $lesson->id,
            'day' => 'Вторник',
            'week1_lesson' => 2,
            'week2_lesson' => 1,
        ]);
    }


    public function test_lesson_move_swaps_with_an_occupied_target_slot(): void
    {
        $firstTeacher = Teacher::create(['name' => 'Смирнов Алексей', 'short_name' => 'СА', 'color' => '#2563eb']);
        $secondTeacher = Teacher::create(['name' => 'Кузнецов Максим', 'short_name' => 'КМ', 'color' => '#16a34a']);
        $firstSubject = Subject::create(['name' => 'Программирование', 'short_name' => 'ПРОГ', 'lesson_type' => 'Лекция']);
        $secondSubject = Subject::create(['name' => 'Математика', 'short_name' => 'МАТ', 'lesson_type' => 'Лекция']);
        $firstTeacher->subjects()->attach($firstSubject);
        $secondTeacher->subjects()->attach($secondSubject);

        $firstGroup = Group::create(['name' => 'П-31', 'speciality' => 'Программирование', 'course' => 3]);
        $secondGroup = Group::create(['name' => 'П-32', 'speciality' => 'Программирование', 'course' => 3]);

        $source = Lesson::create([
            'group_id' => $firstGroup->id,
            'teacher_id' => $firstTeacher->id,
            'subject_id' => $firstSubject->id,
            'day' => 'Понедельник',
            'week1_lesson' => 4,
            'lesson_type' => 'Лекция',
        ]);

        $target = Lesson::create([
            'group_id' => $secondGroup->id,
            'teacher_id' => $secondTeacher->id,
            'subject_id' => $secondSubject->id,
            'day' => 'Вторник',
            'week1_lesson' => 2,
            'lesson_type' => 'Лекция',
        ]);

        $this->withHeaders(['Accept' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest'])
            ->patch(route('lessons.move', $source), [
                'group_id' => $secondGroup->id,
                'day' => 'Вторник',
                'pair' => 2,
                'week' => 1,
                'swap_lesson_id' => $target->id,
            ])
            ->assertOk()
            ->assertJsonPath('swapped', true);

        $this->assertDatabaseHas('lessons', [
            'id' => $source->id,
            'group_id' => $secondGroup->id,
            'day' => 'Вторник',
            'week1_lesson' => 2,
        ]);

        $this->assertDatabaseHas('lessons', [
            'id' => $target->id,
            'group_id' => $firstGroup->id,
            'day' => 'Понедельник',
            'week1_lesson' => 4,
        ]);
    }

    public function test_subject_can_be_dropped_to_create_a_lesson_without_redirect(): void
    {
        $group = Group::create(['name' => 'П-25', 'speciality' => 'Программирование', 'course' => 2]);
        $teacher = Teacher::create(['name' => 'Кузнецов Алексей Сергеевич', 'short_name' => 'КА', 'color' => '#16a34a']);
        $subject = Subject::create(['name' => 'Математика', 'short_name' => 'МАТ', 'lesson_type' => 'Лекция']);
        $teacher->subjects()->attach($subject);

        $this->withHeaders(['Accept' => 'application/json', 'X-Requested-With' => 'XMLHttpRequest'])
            ->post(route('lessons.store'), [
                'group_id' => $group->id,
                'day' => ['Среда'],
                'teacher_id' => $teacher->id,
                'subject_id' => $subject->id,
                'week1_lesson' => [2],
                'week2_lesson' => [],
                'lesson_type' => 'Лекция',
            ])
            ->assertOk()
            ->assertJsonPath('lesson.week1', 2)
            ->assertJsonPath('lesson.subject.id', $subject->id);

        $this->assertDatabaseHas('lessons', [
            'group_id' => $group->id,
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
            'day' => 'Среда',
            'week1_lesson' => 2,
        ]);
    }

}
