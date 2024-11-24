<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;


    protected $table = 'tasks';

    protected $fillable = [
        'title',
        'description',
        'start_date',
        'due_date',
        'project_id',
        'assign_to',
        'priority_id',
        'type_id',
        'status_id',
        'spent_time',
        'estimated_time',
        'remaining_time',
    ];

    // Relationships
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignTo()
    {
        return $this->belongsTo(User::class, 'assign_to');
    }

    public function priority()
    {
        return $this->belongsTo(TaskPriority::class, 'priority_id');
    }

    public function type()
    {
        return $this->belongsTo(TaskType::class, 'type_id');
    }

    public function status()
    {
        return $this->belongsTo(TaskStatus::class, 'status_id');
    }

    public function logs()
    {
        return $this->hasMany(TaskLog::class);
    }

    public function updateTimeSpent()
    {
        $this->spent_time = $this->logs()->sum('logged_time');

        $this->save(); // Lưu lại
    }
}

