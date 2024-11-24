<?php

namespace App\Observers;

use App\Models\Task;

class TaskObserver
{
    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        //
    }

    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        //
    }

    /**
     * Handle the Task "deleted" event.
     */
    public function deleted(Task $task): void
    {
        //
    }

    /**
     * Handle the Task "restored" event.
     */
    public function restored(Task $task): void
    {
        //
    }

    /**
     * Handle the Task "force deleted" event.
     */
    public function forceDeleted(Task $task): void
    {
        //
    }

    public function saving(Task $task)
    {
        if ($task->estimated_time !== null && $task->spent_time !== null) {
            $task->remaining_time = max($task->estimated_time - $task->spent_time, 0);
        } else {
            $task->remaining_time = null; // Nếu không có dữ liệu, để trống
        }
    }
}
