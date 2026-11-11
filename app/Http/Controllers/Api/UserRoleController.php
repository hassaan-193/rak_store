<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;

class UserRoleController extends Controller
{
    // Get all users with roles
    public function index()
    {
        return response()->json(User::with('roles')->get());
    }

    // Get a single user's roles
    public function show($id)
    {
        $user = User::with('roles')->findOrFail($id);
        return response()->json($user);
    }

    // Assign roles to a user (can be one or multiple)
    public function assignRoles(Request $request, $id)
    {
        $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);

        $user = User::findOrFail($id);

        $user->syncRoles($request->roles); // This replaces existing roles

        return response()->json([
            'message' => 'Roles assigned successfully',
            'data' => $user->load('roles'),
        ]);
    }
}
