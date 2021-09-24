<?php

namespace App\Http\Controllers\Api\V1;

use App\Address;
use App\Exports\UsersExport;
use App\Organisation;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Image;

class UserController extends Controller
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

            $users = User::getUsers($request->all());

            $total = $users->count();

            $data = $users
                    ->where('id', '<>', $this->user->id)
                    ->offset(($pageNo - 1) * $pageSize)
                    ->limit($pageSize)
                    ->get();

            if($total > 0){
                return response()->json([
                    'success' => true,
                    'message' =>  __('user.has_records'),
                    'data' => [
                        'users' => $data,
                        'total' => $total
                    ]
                ]);
            }

            return response()->json([
                'success' => true,
                'message' =>  __('user.no_records'),
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
        if($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user')){
            $rules = [
                'email' => 'required|unique:users',
                'first_name' => 'required',
                'last_name' => 'required',
                'password' => 'required|confirmed|min:6',
                'password_confirmation' => 'same:password'
            ];

            $request->validate($rules);

            $request->merge(['password' => Hash::make($request->password)]);

            $user = User::create($request->all());

            return response()->json([
                'success' => true,
                'message' =>  __('user.save_successfully'),
                'data' => $user
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('messages.unauthorized'),
            'data' => []
        ], 401);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user')){
            $user = User::find($id);

            if($user){
                return response()->json([
                    'success' => true,
                    'message' =>  __('user.has_record'),
                    'data' => $user
                ]);
            }

            return response()->json([
                'success' => true,
                'message' =>  __('user.no_record'),
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
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user')){
            $user = User::find($id);

            if($user){
                $rules = [
                    'email' => 'required|unique:users,email,'.$id,
                    'first_name' => 'required',
                    'last_name' => 'required',
                ];

                $request->validate($rules);

                $user->fill($request->all())->save();

                return response()->json([
                    'success' => true,
                    'message' =>  __('user.update_successfully'),
                    'data' => $user
                ]);
            }
            return response()->json([
                'success' => true,
                'message' =>  __('user.no_record'),
                'data' => []
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => __('messages.unauthorized'),
        ], 401);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user')){
            $user = User::find($id);

            if($user){
                $user->delete();

                return response()->json([
                    'success' => true,
                    'message' =>  __('user.delete_successfully'),
                    'data' => $user
                ]);
            }
            return response()->json([
                'success' => false,
                'message' =>  __('user.no_record'),
                'data' => []
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => __('messages.unauthorized'),
            'data' => []
        ], 401);
    }

    public function deleteUsers(Request $request){
        if($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user')){
            $users = User::whereIn('id', $request->ids)->delete();

            return response()->json([
                'success' => true,
                'message' =>  __('user.delete_successfully'),
                'data' => $users
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('messages.unauthorized'),
            'data' => []
        ], 401);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function uploadAvatar(Request $request){
        if($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user') || $this->user->hasRole('organisation')){
            if($request->hasFile('image')){
                $user = User::find($this->user->id);

                // $url = $request->file('image')->store('image', 'public');

                 # Upload file
                $original_file = $request->file('image');
                $base_path = "/images/";
                $image = Image::make($request->file('image'));

                # final image path
                $final_image = $base_path . 'AVATAR_' . time() . '.' . $original_file->getClientOriginalExtension();

                # Check if dir exists
                if(!\file_exists(public_path() . $base_path)) {
                    \mkdir(public_path() . $base_path);
                }

                # Save file
                $image->save(public_path() . $final_image, 75);

                $user->avatar = url($final_image);
                $user->save();

                return response()->json([
                    'success' => true,
                    'message' =>  __('user.avatar_uploaded'),
                    'data' => url($final_image)
                ]);
            }
            return response()->json([
                'success' => false,
                'message' =>  __('user.no_image'),
                'data' => []
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => __('messages.unauthorized'),
            'data' => []
        ], 401);
    }

    public function profile(Request $request){
        if($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user') || $this->user->hasRole('organisation')){
            $user = User::with(['organisation_user.organisation.address'])->find($this->user->id);

            // return response()->json([
            //     'success' => true,
            //     'data' => $this->user->id,
            //     'message' => __('user.profile_update')
            // ]);

            if(!$user){
                return response()->json([
                    'success' => false,
                    'data' => [],
                    'message' => __('user.no_record')
                ]);
            }

            $rules = [
                'email' => 'required|unique:users,email,'.$this->user->id,
                //  Removed temporarily due to issues with the current implementation on My Profile Page
                // 'company_name' => 'required',
                'first_name' => 'required',
                'last_name' => 'required'
            ];

            if(isset($request->password) && !empty($request->password)){
                $rules = [
                    'email' => 'required|unique:users,email,'.$this->user->id,
                    //  Removed temporarily due to issues with the current implementation on My Profile Page
                    // 'company_name' => 'required',
                    'first_name' => 'required',
                    'last_name' => 'required',
                    'password' => 'required|min:6|same:password_confirmation',
                    'password_confirmation' => 'min:6',
                ];
                // $rules['password'] = 'required|min:6|same:password_confirmation';
                // $rules['password_confirmation'] = 'min:6';

                // Added validation of rules for Password
                $request->validate($rules, [
                    'password.same' => 'Password and Confirm Password should match.',
                    'password_confirmation.same' => 'Confirm password and Password should match.'
                ]);

            } else {
                $request->validate($rules);
            }



            $address_data = array(
                'address' => $request->address ?? '',
                'state' => $request->state,
                'postcode' => $request->postcode,
                'country_id' => $request->country_id ?? 14,
            );

            //change to organization address
            if(!empty($user->organisation_user->organisation->address_id)){
                $address = Address::find($user->organisation_user->organisation->address_id);
                $address->fill($address_data)->save();
            }else{
                $address = Address::create($address_data);
            }

            //administrator role
            if($this->user->hasRole('organisation')){
                $user_data = array(
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                );
            }else{
                $user_data = array(
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'phone' => $request->phone,
                    'metadata' => ['company_name' => $request->company_name ?? "", 'timezone' => $user->metadata['timezone'] ?? 'Australia/Sydney'],
                    'address_id' => $address->id
                );
            }

            if(!empty($request->password) && strlen($request->password) > 0){
                $user_data['password'] = Hash::make($request->password);
            }

            $user->fill($user_data)->save();

            //include organization contact number since in admin organization edit it uses the organization contact number
            $orgaization_data = array(
                'contact_number' => $request->phone,
                'address_id' => $address->id,
                'name' => $request->company_name,
            );

            if(isset($user->organisation_user->id)){
                $orgaization = Organisation::find($user->organisation_user->id);
                $orgaization->fill($orgaization_data)->save();
            }

            return response()->json([
                'success' => true,
                'data' => $request->all(),
                'message' => __('user.profile_update')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('messages.unauthorized'),
            'data' => []
        ], 401);
    }

    public function getProfile(){
        if($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user') || $this->user->hasRole('organisation')){
            $user = User::with(['address', 'organisation_user.organisation', 'organisation_user.organisation.address'])->find($this->user->id);

            if(!$user){
                return response()->json([
                    'success' => false,
                    'data' => $user,
                    'message' => __('user.no_record')
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $user,
                'roles' => $this->user->getRoleNames(),
                'message' => __('user.has_record')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('messages.unauthorized'),
            'data' => []
        ], 401);
    }

    public function export(Request $request){
        $user = auth()->user();
        if($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user')){
            return (new UsersExport($request->ids))->download('users.xlsx');
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
        ], 401);
    }
}
