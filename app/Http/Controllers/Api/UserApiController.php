<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * UserApiController
 *
 * REST API for managing hospital system users (doctors, staff, receptionists, admins).
 */
class UserApiController extends Controller
{
    /**
     * Get users list with optional search and role/status filters.
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $s = '%' . $request->search . '%';
            $query->where(fn($q) => $q->where('name', 'like', $s)
                ->orWhere('username', 'like', $s)
                ->orWhere('phone', 'like', $s));
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->orderBy('name')->paginate(20);

        return response()->json([
            'status' => 'success',
            'data'   => $users
        ]);
    }

    /**
     * Get specific user details.
     */
    public function show(User $user)
    {
        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'         => $user->id,
                'name'       => $user->name,
                'username'   => $user->username,
                'email'      => $user->email,
                'phone'      => $user->phone,
                'role'       => $user->role,
                'role_label' => $user->role_label,
                'is_active'  => $user->is_active,
                'avatar_url' => $user->avatar_url,
                'created_at' => $user->created_at,
            ]
        ]);
    }

    /**
     * Create a new user (admin only).
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string|max:50|unique:users',
            'name'     => 'required|string|max:100',
            'email'    => 'nullable|email|unique:users',
            'phone'    => 'required|string|max:20|unique:users',
            'role'     => 'required|in:admin,doctor,receptionist,staff,coach,treasurer,member',
            'password' => 'required|string|min:8',
        ]);

        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);

        AuditLog::record('user_api_created', $user, [], ['username' => $user->username, 'role' => $user->role]);

        return response()->json([
            'status'  => 'success',
            'message' => 'User created successfully.',
            'data'    => $user
        ], 201);
    }

    /**
     * Update user details (admin only).
     */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'      => 'sometimes|string|max:100',
            'email'     => 'sometimes|nullable|email|unique:users,email,' . $user->id,
            'phone'     => 'sometimes|string|max:20|unique:users,phone,' . $user->id,
            'role'      => 'sometimes|in:admin,doctor,receptionist,staff,coach,treasurer,member',
            'is_active' => 'sometimes|boolean',
        ]);

        $old = $user->only(array_keys($data));
        $user->update($data);

        AuditLog::record('user_api_updated', $user, $old, $data);

        return response()->json([
            'status'  => 'success',
            'message' => 'User updated successfully.',
            'data'    => $user->fresh()
        ]);
    }

    /**
     * Delete a user (admin only).
     */
    public function destroy(User $user)
    {
        if ($user->id === auth('sanctum')->id()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'You cannot delete your own profile.'
            ], 422);
        }

        AuditLog::record('user_api_deleted', auth('sanctum')->user(), [], [
            'deleted_name'     => $user->name,
            'deleted_username' => $user->username
        ]);

        $user->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'User profile deleted successfully.'
        ]);
    }
}
