<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Lesson;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Day5AccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_can_log_in(): void
    {
        $user = User::create(['name'=>'Admin','email'=>'admin@test.local','password'=>'secret123','role'=>'admin']);
        $this->post(route('login.store'), ['email'=>$user->email,'password'=>'secret123'])->assertRedirect(route('schedule.index'));
    }

    public function test_student_sees_only_own_group_by_default_and_can_open_all_schedule(): void
    {
        $own = Group::create(['name'=>'П-21','speciality'=>'ПО','course'=>2]);
        Group::create(['name'=>'П-22','speciality'=>'ПО','course'=>2]);
        $student = User::create(['name'=>'Student','email'=>'student@test.local','password'=>'secret123','role'=>'student','group_id'=>$own->id]);
        $this->actingAs($student)->get(route('schedule.index'))->assertOk()->assertSee('П-21')->assertDontSee('П-22');
        $this->actingAs($student)->get(route('schedule.index',['view'=>'all']))->assertOk()->assertSee('П-21')->assertSee('П-22');
    }

    public function test_student_cannot_change_a_lesson(): void
    {
        $group = Group::create(['name'=>'П-21','speciality'=>'ПО','course'=>2]);
        $teacher = Teacher::create(['name'=>'Teacher','short_name'=>'Т','color'=>'#2563eb']);
        $subject = Subject::create(['name'=>'Информатика','short_name'=>'ИНФ','lesson_type'=>'Лекция']);
        $teacher->subjects()->attach($subject);
        $lesson = Lesson::create(['group_id'=>$group->id,'teacher_id'=>$teacher->id,'subject_id'=>$subject->id,'day'=>'Понедельник','week1_lesson'=>1,'lesson_type'=>'Лекция']);
        $student = User::create(['name'=>'Student','email'=>'student@test.local','password'=>'secret123','role'=>'student','group_id'=>$group->id]);
        $this->actingAs($student)->patch(route('lessons.note',$lesson), ['note'=>'x'])->assertForbidden();
    }

    public function test_teacher_can_edit_own_note_but_not_another_teachers_lesson(): void
    {
        $group = Group::create(['name'=>'П-21','speciality'=>'ПО','course'=>2]);
        $teacher = Teacher::create(['name'=>'Teacher One','short_name'=>'Т1','color'=>'#2563eb']);
        $other = Teacher::create(['name'=>'Teacher Two','short_name'=>'Т2','color'=>'#16a34a']);
        $subject = Subject::create(['name'=>'Информатика','short_name'=>'ИНФ','lesson_type'=>'Лекция']);
        $teacher->subjects()->attach($subject); $other->subjects()->attach($subject);
        $ownLesson = Lesson::create(['group_id'=>$group->id,'teacher_id'=>$teacher->id,'subject_id'=>$subject->id,'day'=>'Понедельник','week1_lesson'=>1,'lesson_type'=>'Лекция']);
        $otherLesson = Lesson::create(['group_id'=>$group->id,'teacher_id'=>$other->id,'subject_id'=>$subject->id,'day'=>'Вторник','week1_lesson'=>1,'lesson_type'=>'Лекция']);
        $account = User::create(['name'=>'Teacher','email'=>'teacher@test.local','password'=>'secret123','role'=>'teacher','teacher_id'=>$teacher->id]);
        $this->actingAs($account)->patch(route('lessons.note',$ownLesson), ['note'=>'перенесено'])->assertOk();
        $this->assertDatabaseHas('lessons',['id'=>$ownLesson->id,'note'=>'перенесено']);
        $this->actingAs($account)->patch(route('lessons.note',$otherLesson), ['note'=>'no'])->assertForbidden();
    }


    public function test_creating_teacher_user_creates_linked_teacher_profile(): void
    {
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@test.local', 'password' => 'secret123', 'role' => 'admin']);

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Смирнов Алексей Петрович',
            'email' => 'teacher2@test.local',
            'role' => 'teacher',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ])->assertRedirect(route('users.index'));

        $user = User::where('email', 'teacher2@test.local')->firstOrFail();
        $this->assertNotNull($user->teacher_id);
        $this->assertDatabaseHas('teachers', [
            'id' => $user->teacher_id,
            'name' => 'Смирнов Алексей Петрович',
        ]);
    }


    public function test_teacher_profile_cannot_be_deleted_while_linked_to_user(): void
    {
        $teacher = Teacher::create(['name' => 'Орлов Иван Петрович', 'short_name' => 'ОП', 'color' => '#2563eb']);
        $admin = User::create(['name' => 'Admin', 'email' => 'admin@test.local', 'password' => 'secret123', 'role' => 'admin']);
        User::create(['name' => $teacher->name, 'email' => 'teacher-linked@test.local', 'password' => 'secret123', 'role' => 'teacher', 'teacher_id' => $teacher->id]);

        $this->actingAs($admin)->delete(route('teachers.destroy', $teacher))
            ->assertSessionHas('error', 'Нельзя удалить преподавателя, пока к нему привязана учётная запись. Сначала удалите или измените пользователя.');

        $this->assertDatabaseHas('teachers', ['id' => $teacher->id]);
    }

    public function test_group_course_cannot_be_greater_than_four(): void
    {
        $admin = User::create(['name'=>'Admin','email'=>'admin@test.local','password'=>'secret123','role'=>'admin']);
        $this->actingAs($admin)->post(route('groups.store'), ['name'=>'П-99','speciality'=>'ПО','course'=>5])->assertSessionHasErrors('course');
    }
}
