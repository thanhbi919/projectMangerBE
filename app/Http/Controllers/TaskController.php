<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    /**
     * Store a newly created task.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
            'start_date' => 'nullable|date_format:Y-m-d',
            'due_date' => 'nullable|date_format:Y-m-d|after_or_equal:start_date',
            'project_id' => 'required|exists:projects,id',
            'assign_to' => 'nullable|exists:users,id',
            'priority_id' => 'required|exists:task_priorities,id',
            'type_id' => 'required|exists:task_types,id',
            //            'status_id' => 'required|exists:task_status,id',
        ]);

        // Validate if the user is part of the project
        if ($validated['assign_to']) {
            $project = Project::find($validated['project_id']);

            if (! $project->users()->where('users.id', $validated['assign_to'])->exists()) {
                return response()->json(['error' => 'Assigned user must be part of the project'], 422);
            }
        }

        // Create the task
        $task = Task::create($validated);

        return response()->json(['message' => 'Task created successfully', 'data' => $task], 200);
    }

    public function update(Request $request, Task $task)
    {
        // Validate input
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'start_date' => 'nullable|date|before_or_equal:due_date',
                'due_date' => 'nullable|date|after_or_equal:start_date',
                'project_id' => 'required|exists:projects,id',
                'assign_to' => 'nullable|exists:users,id',
                'priority_id' => 'required|exists:task_priorities,id',
                'type_id' => 'required|exists:task_types,id',
                'status_id' => 'nullable|exists:task_status,id',
                'spent_time' => 'nullable|integer|min:0',
                'estimated_time' => 'nullable|integer|min:0',
            ]);

        } catch (\Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], 422);
        }

        if ($validated['assign_to']) {
            $project = Project::find($validated['project_id']);

            if (! $project->users()->where('users.id', $validated['assign_to'])->exists()) {
                return response()->json(['error' => 'Assigned user must be part of the project'], 422);
            }
        }

        // Update task with validated data
        $task->update($validated);

        // Return response
        return response()->json([
            'message' => 'Task updated successfully',
            'data' => $task->load(['project', 'assignTo', 'priority', 'type', 'status']),
        ], 200);
    }

    public function index(Request $request)
    {
        // Lấy user hiện tại
        $user = auth()->user();

        //        dd($user);

        $projectIds = $user->projects()->pluck('projects.id')->toArray();

        $tasks = Task::with(['project', 'assignTo', 'priority', 'type', 'status', 'logs.user'])
            ->whereIn('project_id', $projectIds);

        // Áp dụng bộ lọc (filter)
        if ($request->has('project_id')) {
            $tasks->where('project_id', $request->input('project_id'));
        }

        if ($request->has('status_id')) {
            $tasks->where('status_id', $request->input('status_id'));
        }

        if ($request->has('priority_id')) {
            $tasks->where('priority_id', $request->input('priority_id'));
        }

        if ($request->has('type_id')) {
            $tasks->where('type_id', $request->input('type_id'));
        }

        if ($request->has('due_date')) {
            $tasks->whereDate('due_date', $request->input('due_date'));
        }

        if ($request->has('assign_to')) {
            $tasks->where('assign_to', $request->input('assign_to'));
        }

        // Phân trang
        $perPage = $request->input('per_page', 10);

        $task = $tasks->orderBy('project_id')->paginate($perPage);

        // Trả về danh sách task
        return response()->json($task, 200);
    }

    public function show($id)
    {
        $task = Task::with(['project', 'assignTo', 'priority', 'type', 'status', 'logs.user'])
            ->findOrFail($id);

        // Trả về dữ liệu task
        return response()->json($task, 200);
    }
}
