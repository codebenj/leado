<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Hash;
use DB;
use App\User;
use App\Role;
use Illuminate\Support\Facades\Redis;
use App\Notification;

class UserController extends Controller
{
    public function index() {
        $roleNames = [
            'administrator',
            'organisation',
            'user'
        ];

        $roles = Role::whereIn('name', $roleNames)->get()->pluck('id')->toArray();
        $users = User::whereIn('role_id', $roles)->with(['user_role'])->get();

        return response()->json([
            'success' => true,
            'data' => $users
        ]);
    }

    public function getUser($id) {
        $user = User::where('id', $id)->with(['user_role'])->first();

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }

    public function save(Request $request) {
        $rules = [
            'first_name' => 'required|max:255',
            'last_name' => 'required|max:255',
            'email' => 'required|email|unique:users,email' . ( isset($request->user_id) ? ",{$request->user_id}" : "" ),
        ];

        if ( isset($request->password) ) {
            $rules['password'] = 'required|string|min:6';
        }

        $request->validate($rules);

        $data = [
            'role_id' => $request->is_admin ? 2 : 4,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
        ];

        if(!empty($request->password) && strlen($request->password) > 0){
            $data['password'] = Hash::make($request->password);
        }

        $user = User::where('id', $request->user_id)->first();

        if ( isset($user) ) {
            $user->update($data);
        } else {
            $user = User::create($data);
        }


        if($request->is_admin){
            $user->removeRole('organisation');
            $user->assignRole('administrator');
        }else{
            $user->removeRole('administrator');
            $user->assignRole('user');
        }

        return response()->json([
            'success' => true,
            'message' => 'User successfully saved.',
            'data' => User::where('id', $user->id)->with(['user_role'])->first()
        ]);
    }

    public function delete(Request $request) {
        $user = User::where('id', $request->user_id)->first();
        $success = true;
        $message = '';

        if ( isset($user) ) {
            $user->delete();
            $message = 'User successfully deleted.';
        } else {
            $success = false;
            $message = 'User not found.';
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);
    }
}
