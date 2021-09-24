<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Role;
use App\Permission;

class PermissionController extends Controller
{
    public function index() {
        $permissions = Permission::get()->pluck('name');

        return response()->json([
            'success' => true,
            'data' => $permissions
        ]);
    }

    public function user() {
        $user = auth()->user();
        
        return response()->json([
            'success' => true,
            'data' => auth()->user()->getAllPermissions()->pluck('name')
        ]);
    }

    public function save(Request $request) {
        $request->validate([
            'name' => 'required'
        ]);

        $message = 'Permission successfully saved.';
        $data = [
            'name' => $request->name
        ];

        $permission = Permission::where('name', $request->name)->first();

        if ( isset($permission) ) {
            $permission->update($data);
        } else {
            $permission = Permission::create($data);
        }

        $role = Role::where('id', $request->role_id)->first();

        if ( isset($role) ) {
            $role->givePermissionTo($permission);
            $message = 'Permission successfully saved and assigned.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $permission
        ]);
    }

    public function revoke(Request $request) {
        $role = Role::where('id', $request->role_id)->first();
        $permission = Permission::where('id', $request->permission_id)->first();
        $success = true;
        $message = '';

        if ( isset($role) && isset($permission) ) {
            // revoke 
            $role->revokePermissionTo($permission);
            $message = 'Permission successfully revoked.';

        } else {
            $success = false; 
            $message = 'Role or permission not found.';
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);
    }
}
