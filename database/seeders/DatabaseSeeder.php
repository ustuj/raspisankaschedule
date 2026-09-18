<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Group;
use App\Models\Lesson;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $group1 = Group::updateOrCreate(['name' => 'П-21'], ['speciality' => 'Программирование', 'course' => 2]);
        $group2 = Group::updateOrCreate(['name' => 'ИС-21'], ['speciality' => 'Информационные системы', 'course' => 2]);
        $group3 = Group::updateOrCreate(['name' => 'П-31'], ['speciality' => 'Программирование', 'course' => 3]);

        $teacher1 = Teacher::updateOrCreate(['short_name' => 'ИВ'], ['name' => 'Иванов Сергей Владимирович', 'color' => '#2563eb']);
        $teacher2 = Teacher::updateOrCreate(['short_name' => 'ПП'], ['name' => 'Петров Пётр Петрович', 'color' => '#16a34a']);
        $teacher3 = Teacher::updateOrCreate(['short_name' => 'КА'], ['name' => 'Кузнецов Алексей Сергеевич', 'color' => '#9333ea']);

        $subject1 = Subject::updateOrCreate(['short_name' => 'ИНФ'], ['name' => 'Информатика', 'lesson_type' => 'Лекция']);
        $subject2 = Subject::updateOrCreate(['short_name' => 'БД'], ['name' => 'Базы данных', 'lesson_type' => 'Практика']);
        $subject3 = Subject::updateOrCreate(['short_name' => 'ПРОГ'], ['name' => 'Программирование', 'lesson_type' => 'Лабораторная']);

        $teacher1->subjects()->syncWithoutDetaching([$subject1->id]);
        $teacher1->subjects()->syncWithoutDetaching([$subject2->id]);
        $teacher2->subjects()->syncWithoutDetaching([$subject2->id]);
        $teacher3->subjects()->syncWithoutDetaching([$subject3->id]);

        $room1 = Classroom::updateOrCreate(['number' => '305'], ['name' => 'Компьютерный класс', 'floor' => '3', 'capacity' => 24]);
        $room2 = Classroom::updateOrCreate(['number' => '214'], ['name' => 'Аудитория', 'floor' => '2', 'capacity' => 30]);
        $room3 = Classroom::updateOrCreate(['number' => '101'], ['name' => 'Лаборатория', 'floor' => '1', 'capacity' => 18]);

        $lessons = [
            ['group_id'=>$group1->id,'teacher_id'=>$teacher1->id,'subject_id'=>$subject1->id,'classroom_id'=>$room1->id,'day'=>'Понедельник','week1_lesson'=>1,'week2_lesson'=>1,'lesson_type'=>'Лекция','note'=>null],
            ['group_id'=>$group1->id,'teacher_id'=>$teacher2->id,'subject_id'=>$subject2->id,'classroom_id'=>$room2->id,'day'=>'Вторник','week1_lesson'=>2,'week2_lesson'=>3,'lesson_type'=>'Практика','note'=>'Пример заметки'],
            ['group_id'=>$group2->id,'teacher_id'=>$teacher3->id,'subject_id'=>$subject3->id,'classroom_id'=>$room3->id,'day'=>'Среда','week1_lesson'=>3,'week2_lesson'=>3,'lesson_type'=>'Лабораторная','note'=>null],
            ['group_id'=>$group3->id,'teacher_id'=>$teacher1->id,'subject_id'=>$subject2->id,'classroom_id'=>$room1->id,'day'=>'Четверг','week1_lesson'=>1,'week2_lesson'=>2,'lesson_type'=>'Практика','note'=>null],
        ];
        foreach ($lessons as $data) {
            Lesson::updateOrCreate([
                'group_id'=>$data['group_id'],'teacher_id'=>$data['teacher_id'],'subject_id'=>$data['subject_id'],
                'classroom_id'=>$data['classroom_id'],'day'=>$data['day'],'week1_lesson'=>$data['week1_lesson'],'week2_lesson'=>$data['week2_lesson'],
            ], $data);
        }

        User::updateOrCreate(['email'=>'admin@nsmk.local'], ['name'=>'Администратор НСМК','password'=>'admin123','role'=>'admin','teacher_id'=>null,'group_id'=>null]);
        User::updateOrCreate(['email'=>'teacher@nsmk.local'], ['name'=>$teacher1->name,'password'=>'teacher123','role'=>'teacher','teacher_id'=>$teacher1->id,'group_id'=>null]);
        User::updateOrCreate(['email'=>'student@nsmk.local'], ['name'=>'Студент П-21','password'=>'student123','role'=>'student','teacher_id'=>null,'group_id'=>$group1->id]);
    }
}
