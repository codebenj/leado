<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Role;
use App\Permission;

class RoleController extends Controller
{
    public function index() {
        $roles = Role::all();

        return response()->json([
            'success' => true,
            'data' => $roles
        ]);
    }

    public function user() {
        $user = auth()->user();

        return response()->json([
            'success' => true,
            'data' => $user->getRoleNames()
        ]);
    }

    public function assign(Request $request) {
        $user = auth()->user();
        $role = Role::where('name', $request->name)->first();
        $success = true;
        $message = '';

        if ( isset($role) ) {
            
            $user->assignRole();    
            $message = "Role '{$request->name}' successfully assigned.";

        } else {

            $success = false;
            $message = $message;

        }

        return response()->json([
            'success' => $success,
            'message' => $message
        ]);
        
    }

    public function save(Request $request) {
        $request->validate([
            'name' => 'required'
        ]);

        $data = [
            'name' => $request->name
        ];

        $role = Role::where('id', $request->role_id)->first();

        if ( isset($role) ) {
            $role->update($data);
        } else {
            $role = Role::create($data);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role successfully saved.',
            'data' => $role
        ]);
    }

    public function delete(Request $request) {
        $role = Role::where('id', $request->role_id)->first();
        $success = true;
        $message = '';

        if ( isset($role) ) {

            $role->delete();
            $message = "Role successfully deleted.";

        } else {
            $success = false;
            $message = "Role not found.";
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);
    }
}
