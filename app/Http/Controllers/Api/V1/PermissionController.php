<?php

namespace App\Http\Controllers\Api\V1;

use Spatie\Permission\Models\Permission;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $permissions = Permission::pluck('name')->toArray();

        return response()->json([
            'success' => true,
            'data' => $permissions
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name',
        ]);

        try {
            \DB::beginTransaction();
            $data = [
                'name' => $request->name,
            ];
    
            $permission = Permission::create($data);

            \DB::commit();

            return response()->json([
                'success' => true,
                'data' => $permission,
                'message' => __('messages.permission_create_response')
            ]);
        } catch(\Exception $e) {
            \Log::error($e->getMessage());
            \Log::error($e->getTraceAsString());

            \DB::rollback();

            return response()->json([
                'success' => false,
                'message' => __('messages.general_error_response')
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $request->validate([
            'id' => 'required|integer|exists:permissions,id'
        ]);

        $permission = Permission::find($request->id);

        return response()->json([
            'success' => true,
            'data' => $permission
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string'
        ]);

        $permission = Permission::find($id);

        if(!$permission) {
            return response()->json([
                'success' => false,
                'message' => __('messages.permission_not_found_response')
            ], 400);
        }

        $permission->name = $request->name;
        $permission->save();

        return response()->json([
            'success' => true,
            'data' => $permission,
            'message' => __('messages.permission_update_response')
        ]);
    }

    /**
     * Revoke permission.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function revoke(Request $request) {
        $request->validate([
            'role_id' => 'required|integer|exists:roles,id',
            'permission_id' => 'required|integer|exists:permissions,id',
        ]);

        $role = Role::find($request->role_id);
        $permission = Permission::find($request->permission_id);
        $permission->revokePermissionTo($role);

        return response()->json([
            'success' => true,
            'data' => $permission,
            'message' => __('messages.permission_revoke_success_response')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $name)
    {
        $permission = Permission::where('name', $name)->first();

        if ( isset($permission) ) {
            $permission->delete();
        }

        return response()->json([
            'success' => true,
            'message' => __('messages.permission_delete_response')
        ]);
    }
}
