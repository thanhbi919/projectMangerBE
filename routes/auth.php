<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectStatusController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskLogController;
use App\Http\Controllers\TaskMetadataController;
use App\Http\Controllers\TypeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/api/auth/register', [RegisteredUserController::class, 'store'])
    ->middleware('guest')
    ->name('register');

Route::post('/api/auth/login', [AuthenticatedSessionController::class, 'store'])
    ->middleware('guest')
    ->name('login');

Route::post('/api/auth/forgot-password', [PasswordResetLinkController::class, 'store'])
    ->middleware('guest')
    ->name('password.email');

Route::post('/api/auth/reset-password', [NewPasswordController::class, 'store'])
    ->middleware('guest')
    ->name('password.store');

Route::get('/api/auth/verify-email/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['auth', 'signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::post('/api/auth/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
    ->middleware(['auth', 'throttle:6,1'])
    ->name('verification.send');

Route::post('/api/auth/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

//Route::get('/api/projects', [ProjectController::class, 'index'])->middleware('auth')->name(' projects.index');
//Route::get('/api/projects/{id}', [ProjectController::class, 'show'])->middleware('auth')->name('projects.show');
//Route::post('/api/projects', [ProjectController::class, 'store'])->middleware('auth')->name('projects.store');
//Route::delete('/api/projects/{id}', [ProjectController::class, 'destroy'])->middleware('auth')->name('projects.destroy');
//Route::put('/api/projects/{id}', [ProjectController::class, 'update'])->middleware('auth')->name('projects.update');

Route::apiResource('/api/projects', ProjectController::class)->middleware('auth');
Route::get('/api/project-status', [ProjectStatusController::class, 'index'])->middleware('auth')->name('project-status');
Route::get('/api/projects/{id}/members', [ProjectController::class, 'getProjectMembers'])->name('projects.members');

Route::get('/api/tasks/types', [TaskMetadataController::class, 'getTaskTypes'])->name('task.types');
Route::get('/api/tasks/statuses', [TaskMetadataController::class, 'getTaskStatuses'])->name('task.statuses');
Route::get('/api/tasks/priorities', [TaskMetadataController::class, 'getPriorities'])->name('priorities');
Route::apiResource('/api/tasks', TaskController::class)->middleware('auth');
Route::post('/api/tasks/log-work', [TaskLogController::class, 'logWork'])->middleware('auth')->name('log-work');


Route::get('/api/types', [TypeController::class, 'index'])->middleware('auth')->name('types.index');
Route::get('/api/users', [UserController::class, 'index'])->middleware('auth')->name('users.index');
Route::get('/api/roles', [RoleController::class, 'index'])->middleware('auth')->name('roles.index');
