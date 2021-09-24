<?php

namespace App\Http\Controllers\Api\V1;

use App\Enquiries;
use App\Exports\StoreExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequest;
use App\ImportLog;
use App\Imports\StoreImport;
use App\Mail\EnquirerSent;
use App\Store;
use App\Rules\EmailString;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class StoreController extends Controller
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
        if ($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user') || $this->user->hasRole('organisation')) {
            $pageNo = isset($request->pageNo) ? $request->pageNo : 1;
            $pageSize = isset($request->pageSize) ? $request->pageSize : 20;
            $stores = Store::filter($request->all())->orderBy('distance', 'asc');

            $total = $stores->count();

            $data = $stores
                ->offset(($pageNo - 1) * $pageSize)
                ->limit($pageSize)
                ->get();

                // $data = $stores
                // ->offset(($pageNo - 1) * $pageSize)
                // ->limit($pageSize)
                // ->toSql();

            return response()->json([
                'success' => true,
                'message' =>  __('store.store_list'),
                'data' => [
                    'stores' => $data,
                    'total' => $total
                ]
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
    public function store(StoreRequest $request)
    {
        try{
            $store = Store::create($request->all());

            return response()->json([
                'success' => true,
                'message' => __('store.save_successfully'),
                'data' => $store,
            ]);
        }catch(\Exception $ex){
            return response()->json([
                'success' => false,
                'message' => __('messages.general_error_response'),
                'data' => []
            ]);
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
        if ($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user') || $this->user->hasRole('organisation')) {

            $data = Store::find($id);

            if($data){
                return response()->json([
                    'success' => true,
                    'message' =>  __('store.found'),
                    'data' => $data
                ]);
            }

            return response()->json([
                'success' => true,
                'message' =>  __('store.not_found'),
                'data' => $data
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
        if ($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user') || $this->user->hasRole('organisation')) {

            $data = Store::find($id);

            if($data){
                $data->fill($request->except(['id']))->save();

                return response()->json([
                    'success' => true,
                    'message' =>  __('store.update_successfully'),
                    'data' => $data
                ]);
            }

            return response()->json([
                'success' => true,
                'message' =>  __('store.not_found'),
                'data' => $data
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
    public function destroy($id)
    {
        if ($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user') || $this->user->hasRole('organisation')) {

            $data = Store::find($id);

            if($data){
                $data->delete();

                return response()->json([
                    'success' => true,
                    'message' =>  __('store.delete_successfully'),
                    'data' => $data
                ]);
            }

            return response()->json([
                'success' => true,
                'message' =>  __('store.not_found'),
                'data' => $data
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('messages.unauthorized'),
            'data' => []
        ], 401);
    }

    public function massDestroy(Request $request){
        if ($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user') || $this->user->hasRole('organisation')) {
            $data = Store::whereIn('id', $request->ids)->get();

            if($data){
                Store::whereIn('id', $request->ids)->delete();

                return response()->json([
                    'success' => true,
                    'message' =>  __('store.delete_successfully'),
                    'data' => $data
                ]);
            }

            return response()->json([
                'success' => true,
                'message' =>  __('store.not_found'),
                'data' => $data
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('messages.unauthorized'),
            'data' => []
        ], 401);
    }

    public function export(Request $request){
        if ($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user') || $this->user->hasRole('organisation')) {
            return (new StoreExport($request->ids))->download('stores.xlsx');
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
        ], 401);
    }

    public function sent(Request $request){
        if ($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user') || $this->user->hasRole('organisation')) {

            $request->validate([
                'emails' => ['required', 'string', new EmailString],
            ]);

            $email_enquirers = explode(',', $request->emails);
            $store_ids = $request->ids;
            $enquirers = [];

            foreach($email_enquirers as $email_enquirer){
                foreach($store_ids as $id){
                    $enquirers[] = Enquiries::create([
                        'store_id' => $id,
                        'email_enquirers' => $email_enquirer,
                        'message' => $request->messages
                    ]);
                }

                $stores = Store::whereIn('id', $store_ids)->get();
                Mail::to($email_enquirer)->queue(new EnquirerSent($stores, $request->messages));
            }

            return response()->json([
                'success' => true,
                'message' =>  __('store.sent_successfully'),
                'data' => $enquirers
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
        ], 401);
    }

    public function sentHistory(Request $request){
        if ($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user') || $this->user->hasRole('organisation')) {
            $pageNo = isset($request->pageNo) ? $request->pageNo : 1;
            $pageSize = isset($request->pageSize) ? $request->pageSize : 20;

            $histories = Enquiries::with(['store'])->filter($request->all());

            $total = $histories->count();

            $histories = $histories
                ->offset(($pageNo - 1) * $pageSize)
                ->limit($pageSize)
                ->get();

            return response()->json([
                'success' => true,
                'message' =>  __('store.sent_history'),
                'data' => [
                    'sents' => $histories,
                    'total' => $total
                ]
            ]);
        }
    }

    /**
     * Import from excel file .
     * column order: Org ID, Store, Add1, Suburb, State, postcode, Phone, LYSales, YTDSales, Pbook, Priority, Stock Kits
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function import(Request $request){
        if ($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user') || $this->user->hasRole('organisation')) {
            $import = new StoreImport;

            $file_name = $request->file('import_file')->getClientOriginalName();

            $path1 = $request->file('import_file')->store('temp');
            $path = storage_path('app').'/'.$path1;

            Excel::import($import, $path);

            if(count($import->saves) > 0){
                ImportLog::create(['from' => 'stores', 'file_name' => $file_name]);

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
     * Get logs information
     * @return \Illuminate\Http\Response
     */
    public function logs(Request $request){
        if ($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user') || $this->user->hasRole('organisation')) {
            $pageNo = $request->pageNo ?? 1;
            $pageSize = $request->pageSize ?? 20;

            $data = ImportLog::where('from', 'stores');

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
        if ($this->user->hasRole('super admin') || $this->user->hasRole('administrator') || $this->user->hasRole('user') || $this->user->hasRole('organisation')) {
            ImportLog::where('from', 'stores')->whereIn('id', $request->ids)->delete();

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
}
