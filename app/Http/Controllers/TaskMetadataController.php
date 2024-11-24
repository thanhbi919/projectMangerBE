<?php

namespace App\Http\Controllers;

use App\Models\TaskPriority;
use App\Models\TaskStatus;
use App\Models\TaskType;
use Illuminate\Http\JsonResponse;

class TaskMetadataController extends Controller
{
    /**
     * Get list of task types.
     */
    public function getTaskTypes(): JsonResponse
    {
        $types = TaskType::all(['id', 'name']);

        return response()->json($types, 200);
    }

    /**
     * Get list of task statuses.
     */
    public function getTaskStatuses(): JsonResponse
    {
        $statuses = TaskStatus::all(['id', 'name']);

        return response()->json($statuses, 200);
    }

    /**
     * Get list of priorities.
     */
    public function getPriorities(): JsonResponse
    {
        $priorities = TaskPriority::all(['id', 'name']);

        return response()->json($priorities, 200);
    }
}
