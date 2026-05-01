<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
class AuthController extends Controller
{
    public function register(Request $request)
{
    $data = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:6|confirmed',
        'phone' => 'nullable|string',
    ]);

    $user = User::create([
        'name' => $data['name'],
        'email' => strtolower($data['email']),
        'password' => bcrypt($data['password']),
        'phone' => $data['phone'] ?? null,


        'role' => 'customer',

        'status' => 'active',
    ]);

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'message' => 'Register successful',
        'user' => $user,
        'token' => $token,
    ]);
}

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();

        $user = User::where('email', strtolower($data['email']))->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        if (($user->status ?? 'active') !== 'active') {
            return response()->json([
                'message' => 'Your account is inactive',
            ], 403);
        }

        $token = $user->createToken('pub-api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function me(): JsonResponse
    {
        return response()->json([
            'user' => Auth::user(),
        ]);
    }

 public function logout(): JsonResponse
{
    /** @var \App\Models\User|null $user */
    $user = Auth::user();

    if ($user !== null) {
        /** @var \Laravel\Sanctum\PersonalAccessToken|null $token */
        $token = $user->currentAccessToken();

        if ($token) {
            $token->delete();
        }
    }

    return response()->json([
        'message' => 'Logout successful',
    ]);
}
public function createUserByAdmin(Request $request): JsonResponse
{
    $authUser = Auth::user();

    if (!$authUser || $authUser->role !== 'admin') {
        return response()->json([
            'message' => 'Unauthorized'
        ], 403);
    }

    $data = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:6',
        'phone' => 'nullable|string',
        'role' => 'required|in:admin,staff',
        'status' => 'required|in:active,inactive',
    ]);

    $user = User::create([
        'name' => $data['name'],
        'email' => strtolower($data['email']),
        'password' => bcrypt($data['password']),
        'phone' => $data['phone'] ?? null,
        'role' => $data['role'],

        // ✅ FIX HERE
        'status' => $data['status'] ?? 'active',
    ]);

    return response()->json([
        'message' => 'User created successfully',
        'user' => $user,
    ]);
}
public function getUsers(): JsonResponse
{
    $authUser = Auth::user();

    if (!$authUser || $authUser->role !== 'admin') {
        return response()->json([
            'message' => 'Unauthorized'
        ], 403);
    }

    $users = User::select('name', 'email', 'role', 'status', 'phone')->get();

    return response()->json([
        'users' => $users
    ]);
}
public function updateUser(Request $request, $id)
{
    $user = User::findOrFail($id);

    $user->update([
        'name' => $request->name,
        'email' => strtolower($request->email),
        'phone' => $request->phone,
        'role' => $request->role,
        'status' => $request->status ?? 'active',
    ]);

    return response()->json([
        'message' => 'User updated successfully',
        'user' => $user
    ]);
}
public function deleteUser($id)
{
    $user = User::findOrFail($id);
    $user->delete();

    return response()->json([
        'message' => 'User deleted successfully'
    ]);
}
}
