<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskLog;
use App\Models\TaskStatus;
use App\Models\User;

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
}
