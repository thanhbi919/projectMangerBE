<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Priority extends Model
{
    use HasFactory;
    protected $table = 'task_priorities';

    protected $fillable = ['name'];

    // Relationships
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}

