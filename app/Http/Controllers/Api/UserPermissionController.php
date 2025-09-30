<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;

class UserPermissionController extends Controller
{
 public function sync(Request $request, User $user)
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string|exists:permissions,slug', // Validate that each item is a string and exists in the permissions table's 'slug' column
        ]);

        // Find the permission models from the given slugs
        $permissions = Permission::whereIn('slug', $request->permissions)->get();
        
        // Sync the user's permissions. This detaches any not in the list and attaches new ones.
        $user->permissions()->sync($permissions);

        // TODO in Step 7: Clear the user's cached permissions to reflect changes immediately.
        $user->clearPermissionCache();

        return response()->json([
            'message' => "Permissions for {$user->name} updated successfully.",
            'permissions' => $user->permissions->pluck('slug')
        ]);
    }
}
