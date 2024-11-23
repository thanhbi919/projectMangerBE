<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskType extends Model
{
    use HasFactory;

    protected $table = 'task_types';


    protected $fillable = ['name'];

    // Relationships
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}

