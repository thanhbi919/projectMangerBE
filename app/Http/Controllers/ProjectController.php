<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type_id' => 'nullable|exists:types,id',
            'members' => 'required|array',
            'members.*.user_id' => 'required|exists:users,id',
            'members.*.role_id' => 'required|exists:roles,id',
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
            'name' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'type_id' => 'nullable|exists:types,id',
            'members' => 'required|array',
            'members.*.user_id' => 'required|exists:users,id',
            'members.*.role_id' => 'required|exists:roles,id',
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
                'description' => $project->description,
                'type_id' => $project->type_id,
                'members' => $project->users,
            ],
        ]);
    }

    public function index(Request $request)
    {
        $user = $request->user();

        $projects = $user->projects()->with(['users' => function ($query) {
            $query->select('users.id', 'users.name');
        }])->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $projects,
        ]);
    }
}
