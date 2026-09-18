<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/', fn () => redirect()->route('schedule.index'))->name('home');
    Route::get('/schedule', [LessonController::class, 'index'])->name('schedule.index');
    Route::get('/schedule/print', [LessonController::class, 'print'])->name('schedule.print');
    Route::get('/schedule/export/ical', [LessonController::class, 'exportIcal'])->name('schedule.export-ical');

    Route::patch('/lessons/{lesson}/move', [LessonController::class, 'move'])->name('lessons.move');
    Route::patch('/lessons/{lesson}/note', [LessonController::class, 'updateNote'])->name('lessons.note');
    Route::resource('lessons', LessonController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);

    Route::middleware('role:admin')->group(function (): void {
        Route::resource('groups', GroupController::class)->except(['show']);
        Route::resource('teachers', TeacherController::class)->except(['show']);
        Route::resource('subjects', SubjectController::class)->except(['show']);
        Route::resource('classrooms', ClassroomController::class)->except(['show']);
        Route::resource('users', UserController::class)->except(['show']);
    });
});
