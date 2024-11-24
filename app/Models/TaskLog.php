<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'log_date',
        'logged_time',
    ];

    // Quan hệ với Task
    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    // Quan hệ với User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::created(function ($log) {
            $log->task->updateTimeSpent();
        });
    }
}
