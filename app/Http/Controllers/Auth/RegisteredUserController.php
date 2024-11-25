<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
                'department_id' => 'required|exists:departments,id',
                'image' => 'nullable|image|max:2048', // Validate image file
                'phone_number' => 'nullable|string|max:20',
            ]);

            if ($request->hasFile('image')) {
                $validatedData['image'] = $request->file('image')->store('users/images', 'public');
            }
            $validatedData['password'] = Hash::make($validatedData['password']);

            $user = User::create($validatedData);

            event(new Registered($user));

        } catch (\Exception $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ]);
        }

        return response()->json(['message' => 'User registered successfully.', 'user' => $user]);
    }
}
