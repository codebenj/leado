<?php

namespace App\Http\Controllers\Api\V1;

use App\Customer;
use App\Exports\CustomersExport;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request){
        $user = auth()->user();

        if($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user') || $user->hasRole('user') || $user->hasRole('organisation')) {
            $pageNo = $request->pageNo ?? 1;
            $pageSize = $request->pageSize ?? 20;

            $req = $request->all();

            $customers = Customer::with(['address', 'lead'])->filter($req);

            $totalLeads = $customers->count();

            $customers = $customers->offset(($pageNo - 1) * $pageSize)
                ->limit($pageSize)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'customers' => $customers,
                    'total' => $totalLeads,
                ],
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => __('messages.unauthorize_response'),
            'data' => [],
        ], 400);
    }

    public function export(Request $request)
    {
        $user = auth()->user();

        if ($user->hasRole('super admin') || $user->hasRole('administrator') || $user->hasRole('user')) {
            return (new CustomersExport($request->ids))->download('customers.xlsx');
        }

        return response()->json([
            'success' => false,
            'message' => __('auth.unauthorized'),
        ], 401);
    }
}
