<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskLog;
use App\Models\TaskStatus;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function overview()
    {
        // Tổng số dự án (chỉ tính các dự án chưa bị xóa mềm)
        $totalProjects = Project::whereNull('deleted_at')->count();

        // Tổng số task
        $totalTasks = Task::count();

        // Tổng số user
        $totalUsers = User::count();

        // Tổng số user theo department
        $usersByDepartment = User::selectRaw('department_id, COUNT(*) as count')
            ->with('department:id,name') // Eager load department name
            ->groupBy('department_id')
            ->get()
            ->map(function ($item) {
                return [
                    'department' => $item->department ? $item->department->name : 'No Department',
                    'count' => $item->count,
                ];
            });

        // Tổng số giờ log work
        $totalLogWork = TaskLog::sum('logged_time');

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_projects' => $totalProjects,
                'total_tasks' => $totalTasks,
                'total_users' => $totalUsers,
                'users_by_department' => $usersByDepartment,
                'total_log_work_hours' => $this->convertMinutesToTime($totalLogWork),
            ],
        ]);
    }

    // Hàm chuyển đổi phút thành giờ và phút
    private function convertMinutesToTime($minutes)
    {
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        return "{$hours}h {$remainingMinutes}m";
    }

    public function projectDistribution()
    {
        // Thống kê số lượng dự án theo trạng thái
        $projectsByStatus = Project::selectRaw('status_id, COUNT(*) as count')
            ->whereNull('deleted_at') // Chỉ tính các dự án chưa bị xóa mềm
            ->groupBy('status_id')
            ->with('status:id,name') // Eager load trạng thái
            ->get()
            ->map(function ($item) {
                return [
                    'status' => $item->status ? $item->status->name : 'Unknown Status',
                    'count' => $item->count,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $projectsByStatus,
        ]);
    }

    public function taskDistribution()
    {
        $allStatuses = TaskStatus::all(['id', 'name']);

        // Thống kê số lượng task theo trạng thái
        $tasksByStatus = Task::selectRaw('status_id, COUNT(*) as count')
            ->groupBy('status_id')
            ->get()
            ->keyBy('status_id'); // Chuyển kết quả thành key-value theo status_id
        // Tạo danh sách đầy đủ trạng thái, bao gồm những trạng thái có count = 0
        $result = $allStatuses->map(function ($status) use ($tasksByStatus) {
            return [
                'status' => $status->name,
                'count' => $tasksByStatus->get($status->id)->count ?? 0,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $result,
        ]);
    }


    public function pmOverview()
    {
        $user = Auth()->user();

        // Lấy danh sách dự án mà PM đang quản lý
        $projects = $user->projects()->withCount(['tasks', 'users'])->get();

        // Tổng hợp thống kê
        $totalProjects = $projects->count();
        $totalTasks = $projects->sum('tasks_count');
        $totalMembers = $projects->sum('users_count');

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_projects' => $totalProjects,
                'total_tasks' => $totalTasks,
                'total_members' => $totalMembers,
            ],
        ]);
    }

    public function taskProgress()
    {
        $user = Auth()->user();

        // Lấy danh sách các trạng thái từ bảng `task_status`
        $allStatuses = \App\Models\TaskStatus::all(['id', 'name']);

        // Lấy danh sách dự án mà PM đang quản lý
        $projects = $user->projects()->with(['tasks.status'])->get();

        // Xử lý dữ liệu
        $data = $projects->map(function ($project) use ($allStatuses) {
            // Thống kê task theo trạng thái
            $tasksByStatus = $project->tasks->groupBy('status_id')->map->count();

            // Gán `count = 0` cho trạng thái không có task
            $statusCounts = $allStatuses->mapWithKeys(function ($status) use ($tasksByStatus) {
                return [$status->name => $tasksByStatus->get($status->id, 0)];
            });

            // Tính phần trăm hoàn thành
            $totalTasks = $statusCounts->sum();
            $completedTasks = $statusCounts->get('Completed', 0);
            $completionPercentage = $totalTasks > 0 ? ($completedTasks / $totalTasks) * 100 : 0;

            return [
                'project_name' => $project->title,
                'tasks' => $statusCounts,
                'completion_percentage' => round($completionPercentage, 2),
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    public function taskByMember(Request $request)
    {
        $user = $request->user();


        $projectId = $request->input('project_id');

        $query = Task::query()
            ->with(['assignTo', 'status']) // Eager load bảng task_status
            ->whereHas('project', function ($q) use ($user, $projectId) {
                $q->whereIn('id', $user->projects->pluck('id'));
                if ($projectId) {
                    $q->where('id', $projectId);
                }
            });

        // Group dữ liệu theo thành viên
        $tasks = $query->get()->groupBy('assign_to');

        $result = $tasks->map(function ($userTasks, $userId) {
            $open = $userTasks->where('status_id', '1')->count();
            $inProgress = $userTasks->where('status_id', '2')->count();
            $pending = $userTasks->where('status_id', '3')->count();
            $done = $userTasks->where('status_id', '4')->count();

            $overdue = $userTasks->where('due_date', '<', now())->count();

            return [
                'member_id' => $userId,
                'member_name' => $userTasks->first()->assignTo->name ?? 'N/A',
                'total_tasks' => $userTasks->count(),
                'open' => $open,
                'in_progress' => $inProgress,
                'pending' => $pending,
                'done' => $done,
                'overdue_tasks' => $overdue,
            ];
        })->values();

        return response()->json(['status' => 'success', 'data' => $result], 200);
    }

    public function myTasks(Request $request)
    {
        $user = $request->user();

        // Lấy tất cả task mà user được assign
        $tasks = Task::with('status','project') // Load trạng thái task
        ->where('assign_to', $user->id) // Chỉ lấy task của user hiện tại
        ->get();
//        dd($tasks);

        // Tính toán dữ liệu thống kê
        $openTasks = $tasks->where('status_id', '1')->count();
        $inProgressTasks = $tasks->where('status_id', '2')->count();
        $pendingTasks = $tasks->where('status_id', '3')->count();
        $doneTasks = $tasks->where('status_id', '4')->count();
        $overdueTasks = $tasks->where('due_date', '<', now())->count();
        // Chuẩn bị dữ liệu trả về
        $data = [
            'project'=>$tasks,
            'member_id' => $user->id,
            'member_name' => $user->name,
            'total_tasks' => $tasks->count(),
            'open' => $openTasks,
            'in_progress' => $inProgressTasks,
            'pending' => $pendingTasks,
            'done' => $doneTasks,
            'overdue_tasks' => $overdueTasks,
        ];

        return response()->json([
            'status' => 'success',
            'data' => [$data], // Đặt trong mảng để giống cấu trúc mẫu
        ]);
    }




}

