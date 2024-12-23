<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'title',
        'description',
        'type_id',
        'status_id',
        'start_date',
        'end_date',
    ];

    protected $dates = ['deleted_at'];


    public function type()
    {
        return $this->belongsTo(Type::class);
    }

    public function status()
    {
        return $this->belongsTo(ProjectStatus::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('role_id')->withTimestamps();
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

}
