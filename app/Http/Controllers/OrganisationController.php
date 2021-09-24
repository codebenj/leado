<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Organisation;
use App\User;
use Hash;

class OrganisationController extends Controller
{
    public function index () {
        $organisation = Organisation::with(['user'])->get();

        return response()->json([
            'success' => true,
            'data' => $organisation
        ]);
    }

    public function getOrg ($id) {
        $organisation = Organisation::where('id', $id)->with(['user'])->first();

        return response()->json([
            'success' => true,
            'data' => $organisation
        ]);
    }

    public function save (Request $request) {
        $organisation = Organisation::where('id', $request->org_id)->first();
        $isUpdate = isset($organisation) && isset($organisation->user->id);
        $user = User::where('id', ($isUpdate ? $organisation->user_id : ''))->first();

        $rules = [
            'first_name' => 'required|max:190',
            'last_name' => 'required|max:190',
            'org_code' => 'required|max:190',
            'state' => 'required|max:190',
            'contact_number' => 'required|max:190',
            'email' => 'required|email|unique:users,email' . ( $isUpdate ? ",{$user->id}" : "" )
        ];

        if ( isset($request->password) ) {
            $rules['password'] = 'required|string|min:6';
        }

        $request->validate($rules);

        $userData = [
            'role_id' => 3, // organisation role
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ];

        if ( isset($user) ) {
            $user->update($userData);
        } else {
            $user = User::create($userData);
        }

        $orgData = [
            'user_id' => $user->id,
            'contact_number' => $request->contact_number,
            'landline_contact' => $request->landline_contact,
            'state' => $request->state,
            'org_code' => $request->org_code,
            'is_suspended' => isset($request->is_suspended) && $request->is_suspended == '1' ? 1 : 0,
        ];

        if ( isset($organisation) ) {
            $organisation->update($orgData);
        } else {
            $organisation = Organisation::create($orgData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Organisation successfully saved.',
            'data' => Organisation::with(['user'])->where('id', ($isUpdate ? $request->org_id : $organisation->id ))->first()
        ]);
    }

    public function delete (Request $request) {
        $organisation = Organisation::where('id', $request->org_id)->first();
        $success = true;
        $message = '';

        if ( isset($organisation) ) {
            $user = User::where('id', $organisation->user_id)->delete();
            $organisation->delete();
            $message = 'Organisation successfully deleted.';
        } else {
            $success = false;
            $message = 'Organisation not found.';
        }

        return response()->json([
            'success' => $success,
            'message' => $message,
        ]);
    }
}
