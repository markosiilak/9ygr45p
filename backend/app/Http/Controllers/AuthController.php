<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // Assign customer role by default
        $customerRole = Role::where('slug', 'customer')->first();
        if ($customerRole) {
            $user->roles()->attach($customerRole);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user->load('roles')),
            'token' => $token,
        ], 201);
    }

    /**
     * Login user and create token.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => new UserResource($user->load('roles')),
            'token' => $token,
        ]);
    }

    /**
     * Logout user (revoke token).
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * Get authenticated user.
     */
    public function user(Request $request)
    {
        return new UserResource($request->user()->load('roles'));
    }

    /**
     * Set roles for a user.
     * Requires authentication and manage-users permission (admin only).
     */
    public function setRoles(Request $request, $userId)
    {
        // Check if user has permission to manage users (admin only)
        if (!$request->user()->hasPermission('manage-users')) {
            return response()->json(['message' => 'Insufficient permissions. Only admins can set user roles.'], 403);
        }

        $validated = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'required|string|exists:roles,slug',
        ]);

        $user = User::findOrFail($userId);

        // Get role IDs from slugs
        $roleSlugs = $validated['roles'];
        $roles = Role::whereIn('slug', $roleSlugs)->get();

        if ($roles->count() !== count($roleSlugs)) {
            return response()->json([
                'message' => 'One or more roles not found.',
                'errors' => ['roles' => ['Invalid role slugs provided.']]
            ], 422);
        }

        // Sync roles (replace all existing roles with new ones)
        $user->roles()->sync($roles->pluck('id'));

        return response()->json([
            'message' => 'User roles updated successfully.',
            'user' => new UserResource($user->load('roles')),
        ]);
    }

    /**
     * Get all users with their roles.
     * Requires authentication and manage-users permission (admin only).
     */
    public function index(Request $request)
    {
        // Check if user has permission to manage users (admin only)
        if (!$request->user()->hasPermission('manage-users')) {
            return response()->json(['message' => 'Insufficient permissions. Only admins can view all users.'], 403);
        }

        $users = User::with('roles')->get();

        return response()->json([
            'users' => UserResource::collection($users),
        ]);
    }

    /**
     * Get all available roles.
     * Requires authentication and manage-users permission (admin only).
     */
    public function getRoles(Request $request)
    {
        // Check if user has permission to manage users (admin only)
        if (!$request->user()->hasPermission('manage-users')) {
            return response()->json(['message' => 'Insufficient permissions. Only admins can view roles.'], 403);
        }

        $roles = Role::with('permissions')->get();

        return response()->json([
            'roles' => $roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'slug' => $role->slug,
                    'description' => $role->description,
                    'permissions' => $role->permissions->map(function ($permission) {
                        return [
                            'id' => $permission->id,
                            'name' => $permission->name,
                            'slug' => $permission->slug,
                        ];
                    }),
                ];
            }),
        ]);
    }
}
