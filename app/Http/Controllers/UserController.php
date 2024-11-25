<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index()
    {
        // Select users with their associated department name
        $users = User::select('id', 'name', 'email', 'image', 'department_id')
            ->with('department:id,name') // Eager load department with only its id and name
            ->get();

        // Transform the data to include the department name directly
        $users = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'image' => $user->image ? asset('storage/'.$user->image) : null, // Generate full URL
                'phone_number' => $user->phone_number,
                'department' => $user->department, // Use null-safe operator for optional relation
            ];
        });

        return response()->json($users);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id); // Find the user or return 404

        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,'.$id, // Allow same email for the current user
            'password' => 'nullable|string|min:6', // Password is optional
            'department_id' => 'nullable|exists:departments,id',
            'image' => 'nullable|image|max:2048', // Validate image file (for file upload)
            'phone_number' => 'nullable|string|max:20',
        ]);

        // Handle image upload if provided
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($user->image) {
                Storage::disk('public')->delete($user->image);
            }

            // Store the new image
            $validatedData['image'] = $request->file('image')->store('users/images', 'public');
        }

        // Update password only if provided
        if ($request->has('password') && ! empty($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        } else {
            unset($validatedData['password']); // Remove password if not provided
        }

        // Only update fields that are present in the request data
        foreach ($validatedData as $key => $value) {
            if ($value !== null) {
                $user->$key = $value;
            }
        }

        // Save the changes to the user
        $user->save();

        // Return response
        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user,
        ]);
    }

    public function show($id)
    {
        // Find the user by ID or return 404 if not found
        $user = User::with('projects', 'department')->findOrFail($id);

        // Return the user as JSON
        return response()->json([
            'message' => 'User retrieved successfully',
            'data' => [
                'name' => $user->name,
                'email' => $user->email,
                'image' => $user->image ? asset('storage/'.$user->image) : null, // Generate full URL
                'phone_number' => $user->phone_number,
                'department' => $user->department,
                'department_id' => $user->department->id,
            ],
        ]);
    }
}
