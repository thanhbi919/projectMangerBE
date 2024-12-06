<?php

namespace App\Http\Controllers;

use App\Models\TaskHistory;
use App\Models\TaskLog;
use Illuminate\Http\Request;

class TaskLogController extends Controller
{
    public function logWork(Request $request)
    {
        $validated = $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'user_id' => 'required|exists:users,id',
            'log_date' => 'required|date',
            'logged_time' => 'required|integer|min:1',
        ]);

        // Tạo log work
        $log = TaskLog::create($validated);
        TaskHistory::create([
            'task_id' => $validated['task_id'],
            'user_id' => $validated['user_id'],
            'action' => 'log_work',
            'old_value' => null,
            'new_value' => json_encode([
                'log_date' => $validated['log_date'],
                'logged_time' => $validated['logged_time'],
            ]),
            'description' => 'User logged work for the task.',
        ]);

        return response()->json([
            'message' => 'Log work recorded successfully.',
            'data' => $log,
        ], 201);
    }
}
