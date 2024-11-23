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
    ];

    // Relationships
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assign_to');
    }

    public function priority()
    {
        return $this->belongsTo(Priority::class);
    }

    public function type()
    {
        return $this->belongsTo(TaskType::class);
    }

    public function status()
    {
        return $this->belongsTo(TaskStatus::class, 'status_id');
    }
}

