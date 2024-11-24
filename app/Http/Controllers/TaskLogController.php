<?php

namespace App\Http\Controllers;

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

        return response()->json([
            'message' => 'Log work recorded successfully.',
            'data' => $log,
        ], 201);
    }
}
