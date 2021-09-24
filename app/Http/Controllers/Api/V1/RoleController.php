<?php

namespace App\Http\Controllers\Api\V1;

use App\User;
use App\Role;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->user = auth()->user();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        if($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user')){
            $pageNo = isset($request->pageNo) ? $request->pageNo : 1;
            $pageSize = isset($request->pageSize) ? $request->pageSize : 20;
            $roles = Role::with('permissions')
                ->where(function ($q) use($request){
                    if(isset($request['search']) && !empty($request['search'])){
                        $q->orWhere('name', 'like' , '%'.$request['search'].'%');
                    }
                });

            $total = $roles->count();

            $data = $roles
                    ->offset(($pageNo - 1) * $pageSize)
                    ->limit($pageSize)
                    ->get();

            if($total > 0){
                return response()->json([
                    'success' => true,
                    'message' =>  __('role.has_records'),
                    'data' => [
                        'roles' => $data,
                        'total' => $total
                    ]
                ]);
            }

            return response()->json([
                'success' => true,
                'message' =>  __('role.no_records'),
                'data' => []
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('messages.unauthorized'),
            'data' => []
        ], 401);
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
            'name' => 'required|string|unique:roles,name',
        ]);

        try {
            \DB::beginTransaction();

            $data = [
                'name' => $request->name,
                'guard' => 'api'
            ];

            $role = Role::create($data);
            $role->syncPermissions($request->permissions);

            \DB::commit();

            return response()->json([
                'success' => true,
                'data' => $role,
                'message' => __('messages.role_create_response')
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
            'id' => 'required|integer|exists:roles,id'
        ]);

        $role = Role::find($request->id);

        // assign permissions
        foreach ($request->permissions as $permission) {
            $role->givePermissionTo($permission);
        }

        return response()->json([
            'success' => true,
            'data' => $role
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
            'name' => 'required|string|unique:roles,name,' .$id
        ]);

        $role = Role::find($id);
        // revoke all
        $role->syncPermissions([]);
        // assign new permissions
        $role->syncPermissions($request->permissions);

        if(!$role) {
            return response()->json([
                'success' => false,
                'message' => __('messages.role_not_found_response')
            ], 400);
        }

        // # It should not allow to update role name if it is used by users.
        // $role_used_count = User::where('role_id', $role->id)->count();
        // if($role_used_count > 0) {
        //     return response()->json([
        //         'success' => false,
        //         'message' => __('messages.role_has_been_used_response')
        //     ], 400);
        // }

        # update role
        $role->name = $request->name;
        $role->save();

        return response()->json([
            'success' => true,
            'data' => $role,
            'message' => __('messages.role_update_response')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $role = Role::find($id);

        # It should not allow to delete role name if it is used by users.
        $role_used_count = User::where('role_id', $role->id)->count();

        if($role_used_count > 0) {
            return response()->json([
                'success' => false,
                'message' => __('messages.role_has_been_used_response')
            ], 400);
        }

        $role->delete();

        return response()->json([
            'success' => true,
            'message' => __('messages.role_delete_response')
        ]);
    }
}
