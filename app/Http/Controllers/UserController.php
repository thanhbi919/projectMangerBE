<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function store(Request $request)
    {
        // Xác thực dữ liệu đầu vào
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email', // Email phải là duy nhất
            'password' => 'required|string|min:6',
            'department_id' => 'nullable|exists:departments,id',
            'image' => 'nullable|string|max:2048', // Ảnh (tùy chọn), tối đa 2MB
            'phone_number' => 'nullable|string|max:20',
        ]);

        // Xử lý mật khẩu: mã hóa trước khi lưu
        $validatedData['password'] = Hash::make($validatedData['password']);

        // Tạo user
        $user = User::create($validatedData);

        // Trả về phản hồi JSON
        return response()->json([
            'message' => 'User created successfully.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'image' => $user->image ? asset('storage/'.$user->image) : null,
                'phone_number' => $user->phone_number,
                'department_id' => $user->department_id,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
        ], 201);
    }

    public function index()
    {
        $users = User::select('id', 'name', 'email', 'image', 'department_id', 'created_at', 'updated_at')
            ->whereNull('deleted_at')
            ->with('department:id,name')
            ->get();

        // Transform data
        $users = $users->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'image' => $user->image, // Chỉ trả về đường dẫn tương đối
                'phone_number' => $user->phone_number,
                'department' => $user->department,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ];
        });

        return response()->json($users);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,'.$id,
            'password' => 'nullable|string|min:6',
            'department_id' => 'nullable|exists:departments,id',
            'image' => 'nullable|string|max:2048',
            'phone_number' => 'nullable|string|max:20',
        ]);

        if ($request->has('password') && ! empty($validatedData['password'])) {
            $validatedData['password'] = Hash::make($validatedData['password']);
        } else {
            unset($validatedData['password']);
        }

        foreach ($validatedData as $key => $value) {
            if ($value !== null) {
                $user->$key = $value;
            }
        }

        $user->save();

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user, // Đảm bảo image chỉ là đường dẫn tương đối
        ]);
    }

    public function show($id)
    {
        $user = User::with('projects', 'department')->findOrFail($id);

        return response()->json([
            'message' => 'User retrieved successfully',
            'data' => [
                'name' => $user->name,
                'email' => $user->email,
                'image' => $user->image, // Trả về đường dẫn tương đối
                'phone_number' => $user->phone_number,
                'department' => $user->department,
                'department_id' => $user->department->id,
            ],
        ]);
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.',
        ], 200);
    }
}
