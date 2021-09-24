<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\OrgLocator;
use App\Imports\OrgLocatorImport;
use Maatwebsite\Excel\Facades\Excel;

class OrgLocatorController extends Controller
{
    public function delete(Request $request){
        $org_locator = OrgLocator::find($request->id);
        $success = true;
        $message = '';

        if ($org_locator) {
            $org_locator->delete();
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

    public function import(Request $request){
        $import = new OrgLocatorImport;

        $path1 = $request->file('import_file')->store('temp');
        $path = storage_path('app').'/'.$path1;

        Excel::import($import, $path);

        return response()->json([
            'success' => true,
            'message' => 'Successfully imported all data',
            'data' => null
        ]);
    }

    public function all(){
        $org_locators = OrgLocator::all();

        return response()->json([
            'success' => true,
            'data' => $org_locators
        ]);
    }

    public function getOrgLocator($id){
        $org_locator = OrgLocator::find($id);

        return response()->json([
            'success' => true,
            'data' => $org_locator
        ]);
    }

    public function save(Request $request){
        $org_locator = OrgLocator::where('id', $request->id)->first();

        $rules = [
            'org_id' => 'required|numeric',
            'name' => 'required',
            'phone' => 'required',
            //'last_year_sales' => 'required|numeric',
            //'year_to_date_sales' => 'required|numeric',
            //'keeps_stock' => 'required',
            //'pricing_book' => 'required',
            //'priority' => 'required',
        ];

        $request->validate($rules);

        if( $org_locator === null){
            $request['metadata'] = [
                'address_search' => $request->address_search,
            ];
            $org_locator = OrgLocator::create($request->all());
        }else{
            $request->merge(['metadata' => ['address_search' => $request->address_search]]);
            $org_locator->update($request->all());
        }

        return response()->json([
            'success' => true,
            'message' => 'Org. Locator successfully saved.',
            'data' => $org_locator,
        ]);
    }
}
