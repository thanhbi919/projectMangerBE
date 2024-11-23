<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'title' => 'required|string|max:50',
            'description' => 'string:max:100',
            'type_id' => 'nullable|exists:types,id',
            'members' => 'required|array',
            'members.*.user_id' => 'required|exists:users,id',
            'members.*.role_id' => 'required|exists:roles,id',
            'status_id' => 'required|exists:project_status,id',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after:start_date',
        ]);

        if (! $validated) {
            return response()->json([
                'status' => 'error',
                'message' => $validated->errors(),
            ], 422);
        }

        $project = Project::create([
            'title' => $request->title,
            'name' => $request->name,
            'type_id' => $request->type_id,
            'description' => $request->description,
        ]);

        foreach ($request->members as $member) {
            $project->users()->attach($member['user_id'], ['role_id' => $member['role_id']]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Project created successfully with members and roles',
            'data' => $project,
        ], 201);

    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'title' => 'required|string|max:50',
            'description' => 'string:max:100',
            'type_id' => 'nullable|exists:types,id',
            'members' => 'required|array',
            'members.*.user_id' => 'required|exists:users,id',
            'members.*.role_id' => 'required|exists:roles,id',
            'status_id' => 'required|exists:project_status,id',
            'start_date' => 'required|date_format:Y-m-d',
            'end_date' => 'required|date_format:Y-m-d|after:start_date',
        ]);
        if (! $validated) {
            return response()->json([
                'status' => 'error',
                'message' => $validated->errors(),
            ], 422);
        }

        $project = Project::findOrFail($id);
        $project->update([
            'title' => $request->title,
            'name' => $request->name,
            'description' => $request->description,
            'type_id' => $request->type_id,
            'status_id' => $request->status_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
        ]);

        if ($request->has('members')) {
            // Clear existing members
            $project->users()->detach();

            //Attach new members with roles
            foreach ($request->members as $member) {
                $project->users()->attach($member['user_id'], ['role_id' => $member['role_id']]);
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Project updated successfully with members and roles',
            'data' => $project->load('users'),
        ]);

    }

    public function destroy($id)
    {
        $project = Project::findOrFail($id);
        $project->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Project deleted successfully with members and roles',
        ]);
    }

    public function show($id)
    {
        $project = Project::with(['users' => function ($query) {
            $query->select('users.id', 'users.name') // Lấy các trường cần thiết từ bảng users
                ->withPivot('role_id');
        }])->findOrFail($id);

        $project->users = $project->users->map(function ($user) {
            return [
                'user_id' => $user->id,
                'role_id' => $user->pivot->role_id,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $project->id,
                'title' => $project->title,
                'status_id' => $project->status_id,
                'description' => $project->description,
                'type_id' => $project->type_id,
                'members' => $project->users,
                'start_date' => $project->start_date,
                'end_date' => $project->end_date,

            ],
        ]);
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $projects = $user->projects()->with(['users' => function ($query) {
            $query->select('users.id', 'users.name');
        }])->get();

        return response()->json([
            'status' => 'success',
            'data' => $projects,
        ]);
    }
}
