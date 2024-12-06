<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskHistory;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

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
            'status_id' => 'required|exists:task_status,id',
            'estimated_time' => 'nullable|integer|min:0',
            'created_by' => 'nullable|exists:users,id',
        ]);

        // Validate if the user is part of the project
        if ($validated['assign_to']) {
            $project = Project::find($validated['project_id']);

            if (! $project->users()->where('users.id', $validated['assign_to'])->exists()) {
                return response()->json(['error' => 'Assigned user must be part of the project'], 422);
            }
        }

        if ($validated['created_by']) {
            $project = Project::find($validated['project_id']);

            if (! $project->users()->where('users.id', $validated['created_by'])->exists()) {
                return response()->json(['error' => 'User hasn\'t authorization'], 422);
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
            'data' => $task->load(['project', 'assignTo', 'priority', 'type', 'status', 'creator']),
        ], 200);
    }

    public function index(Request $request)
    {
        // Lấy user hiện tại
        $user = auth()->user();

        // Kiểm tra nếu user thuộc department "admin"
        if ($user->department && strtolower($user->department->name) === 'admin') {
            // Admin: lấy tất cả task
            $tasks = Task::with(['project', 'assignTo', 'creator', 'priority', 'type', 'status', 'logs.user', 'histories.user']);
        } else {
            // Người dùng thường: chỉ lấy các task trong dự án mà họ tham gia
            $projectIds = $user->projects()->pluck('projects.id')->toArray();
            $tasks = Task::with(['project', 'assignTo', 'creator', 'priority', 'type', 'status', 'logs.user', 'histories.user'])
                ->whereIn('project_id', $projectIds);
        }

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
        $perPage = $request->input('per_page', 50);
        $paginatedTasks = $tasks->orderBy('project_id')->paginate($perPage);

        // Trả về danh sách task
        return response()->json($paginatedTasks, 200);
    }


    public function show($id)
    {
        $task = Task::with(['project', 'assignTo', 'priority', 'type', 'status', 'logs.user'])
            ->findOrFail($id);

        // Trả về dữ liệu task
        return response()->json($task, 200);
    }

    public function destroy($id)
    {
        // Tìm task theo ID
        $task = Task::find($id);

        if (! $task) {
            return response()->json([
                'message' => 'Task not found.',
            ], Response::HTTP_NOT_FOUND);
        }

        // Xóa task
        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully.',
        ], Response::HTTP_OK);
    }

    public function updateStatus(Request $request, $id): JsonResponse
    {
        // Xác thực dữ liệu đầu vào
        $validated = $request->validate([
            'status_id' => 'required|integer|exists:task_status,id', // Giả sử bạn có bảng 'task_statuses'
        ]);

        try {
            // Tìm task theo ID
            $task = Task::findOrFail($id);

            // Lưu trạng thái cũ trước khi cập nhật
            $oldStatusId = $task->status_id;

            // Cập nhật trạng thái mới
            $task->status_id = $validated['status_id'];
            $task->save(); // Lưu thay đổi

            TaskHistory::create([
                'task_id' => $task->id,
                'user_id' => auth()->id(), // Giả sử bạn sử dụng middleware auth
                'action' => 'update_status',
                'old_value' => json_encode(['status_id' => $oldStatusId]),
                'new_value' => json_encode(['status_id' => $validated['status_id']]),
                'description' => 'Task status updated.',
            ]);

            return response()->json([
                'message' => 'Task status updated successfully.',
                'data' => $task,
            ], Response::HTTP_OK);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation error.'], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update task status.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}
