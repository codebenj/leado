<?php

namespace App\Http\Controllers\Api\V1;

use App\Exports\OrgLocatorExport;
use App\Http\Controllers\Controller;
use App\ImportLog;
use App\OrgLocator;
use App\Imports\OrgLocatorImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class OrgLocatorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user')){
            $pageNo = $request->pageNo ?? 1;
            $pageSize = $request->pageSize ?? 20;
            $org_locator = OrgLocator::getOrgLocator($request->all());
            $total = $org_locator->count();
            $org_locator = $org_locator
                            ->offset(($pageNo - 1) * $pageSize)
                            ->limit($pageSize)
                            ->get();

            if($org_locator){
                return response()->json([
                    'success' => true,
                    'message' => __('orglocator.has_records'),
                    'data' => ['org_locator' => $org_locator, 'total' => $total]
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => __('orglocator.no_records'),
                'data' => []
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
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
        $user = auth()->user();

        if($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user')){
            $rules = [
                'org_id' => 'required|numeric',
                'name' => 'required',
                'street_address' => 'required',
                'suburb' => 'required',
                'postcode' => 'required',
                'phone' => 'required',
                'last_year_sales' => 'numeric',
                'year_to_date_sales' => 'numeric',
            ];

            $request->validate($rules);

            $org_locator = OrgLocator::create($request->all());

            return response()->json([
                'success' => true,
                'message' => __('orglocator.save_successfully'),
                'data' => true
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
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
        $user = auth()->user();

        if($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user')){
            $org_locator = OrgLocator::find($id);

            if($org_locator){
                return response()->json([
                    'success' => true,
                    'message' =>  __('orglocator.has_records'),
                    'data' => $org_locator
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => __('orglocator.no_records'),
                'data' => []
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
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
        $user = auth()->user();

        if($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user')){
            $org_locator = OrgLocator::where('id', $id)->first();
            $message = __('orglocator.no_records');

            if($org_locator){
                $org_locator->update($request->all());
                $message =  __('orglocator.update_successfully');
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $org_locator
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
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
        $user = auth()->user();

        if($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user')){
            $org_locator = OrgLocator::find($id);
            $success = true;
            $message = '';

            if ($org_locator) {
                $org_locator->delete();
                $message =  __('orglocator.delete_successfully');
            } else {
                $success = false;
                $message = __('orglocator.no_records');
            }

            return response()->json([
                'success' => $success,
                'message' => $message,
                'data' => $org_locator
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
        ], 401);
    }

    /**
     * Import from excel file .
     * column order: Org ID, Store, Add1, Suburb, State, postcode, Phone, LYSales, YTDSales, Pbook, Priority, Stock Kits
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request){
        $user = auth()->user();
        if($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user')){
            $import = new OrgLocatorImport;

            $file_name = $request->file('import_file')->getClientOriginalName();

            $path1 = $request->file('import_file')->store('temp');
            $path = storage_path('app').'/'.$path1;

            Excel::import($import, $path);

            if(count($import->saves) > 0){
                ImportLog::create(['from' => 'org-locator', 'file_name' => $file_name]);

                return response()->json([
                    'success' => true,
                    'message' => __('messages.org_locator_import'),
                    'data' => $import->saves,
                ]);
            }else{
                return response()->json([
                    'success' => false,
                    'message' => __('messages.org_locator_import_failed'),
                    'error' => $import->error,
                    //'data' => []
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
        ], 401);
    }

    /**
     * Delete all Org. Locator
     */
    public function deleteAll(){
        $user = auth()->user();
        if($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user')){
            $data = OrgLocator::where('id', 'like', '%%')->delete();

            return response()->json([
                'success' => true,
                'message' => __('orglocator.delete_successfully'),
                'data' => $data,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
        ], 401);
    }

    /**
     * Delete Org. Locator
     * @param  array  $ids
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request){
        $user = auth()->user();

        if($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user')){
            if(isset($request->ids)){
                $ids = $request->ids;
                $data = OrgLocator::whereIn('id', $ids)->delete();

                return response()->json([
                    'success' => true,
                    'message' => __('orglocator.delete_successfully'),
                    'data' => $data,
                ]);
            }
            return response()->json([
                'success' => false,
                'message' => __('orglocator.ids_empty'),
                'data' => [],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
        ], 401);
    }

    /**
     * Get logs information
     * @return \Illuminate\Http\Response
     */
    public function logs(Request $request){
        $user = auth()->user();

        if($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user')){
            $pageNo = $request->pageNo ?? 1;
            $pageSize = $request->pageSize ?? 20;

            $data = ImportLog::where('from', 'org-locator');

            $total = $data->count();

            $data = $data->offset(($pageNo - 1) * $pageSize)
                ->limit($pageSize)
                ->get();

            return response()->json([
                'success' => true,
                'message' => __('messages.logs_org_locator'),
                'data' => ['data' => $data, 'total' => $total]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
        ], 401);
    }

    public function deleteLogs(Request $request){
        $user = auth()->user();

        if($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user')){
            ImportLog::where('from', 'org-locator')->whereIn('id', $request->ids)->delete();

            return response()->json([
                'success' => true,
                'message' => __('messages.logs_deleted')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
        ], 401);
    }

    public function export(Request $request){
        $user = auth()->user();
        if($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user')){
            return (new OrgLocatorExport($request->ids))->download('ogrlocator.xlsx');
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
        ], 401);
    }
}
