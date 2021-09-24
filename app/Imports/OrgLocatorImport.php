<?php

namespace App\Imports;

use App\OrgLocator;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class OrgLocatorImport implements ToCollection
{
    public $error;
    public $saves;

    public function collection(Collection $collection){
        $counter = 0;
        $store_data = array();
        $errors = [];
        $is_file_valid = true;

        foreach($collection as $row){
            //checking columns
            if($counter == 0){
                $columns = array(
                    //'Org ID', 'store', 'Add1', 'suburb', 'state', 'postcode', 'phone', 'LYSales', 'YTDSales', 'PBook', 'Priority'
                    'Org ID', 'Org', 'Address', 'Suburb', 'State', 'Postcode', 'Phone', 'LYSales', 'YTDSales', 'PBook', 'Priority'
                );
                for($i=0; $i<11; $i++){
                    if(\strtolower($columns[$i]) != \strtolower($row[$i])){
                        $is_file_valid = false;
                        break;
                    }
                }
            }
            else{
                try{
                    $pos = strpos($row[6], '+');
                    $phone = ($pos === false) ? '+61'.$row[6] : $row[6];

                    $org_id = 0;
                    if(!empty($row[0])){
                        $org_id = $row[0];
                    }

                    $data = array(
                        'name' => $row[1],
                        'org_id' => $org_id,
                        'street_address' => $row[2],
                        'suburb' => $row[3],
                        'postcode' => $row[5],
                        'state' => $row[4],
                        'phone' => str_replace(' ', '',$phone),
                        'last_year_sales' => $row[7],
                        'year_to_date_sales' => $row[8],
                        //'keeps_stock' => $row[11],
                        'pricing_book' => $row[9],
                        'priority' => $row[10],
                    );
                    //check if store is exist

                    // $store = OrgLocator::where(['org_id' => $org_id, 'name' => $row[1], 'street_address' => $row[2], 'suburb' => $row[3],
                    //     'postcode' => $row[5], 'state' => $row[4]])->first();

                    //use just org id
                    $store = false;
                    if(! empty($org_id)){
                        $store = OrgLocator::where(['org_id' => $org_id])->first();
                    }else if(! empty($row[1]) && ! empty($org_id)){
                        $store = OrgLocator::where(['org_id' => $org_id, 'name' => $row[1]])->first();
                    }else if(empty($org_id) && ! empty($row[1])){
                        $store = OrgLocator::where(['name' => $row[1]])->first();
                    }

                    if($store){
                        $store_data[] = $store->fill($data)->save();
                    }else{
                        $store_data[] = OrgLocator::create($data);
                    }
                }catch(\Exception $ex){
                    $errors[] = $ex->getMessage();
                }
            }
            if(! $is_file_valid){
                $errors = "<p>File must be:</p><p>CSV, XLS, XLSX file</p><br><p>Column headings (MUST be in this order):</p><p>Org ID, Org, Address, Suburb, State, Postcode, Phone, LYSales, YTDSales, PBook, Priority</p>";
                break;
            }
            $counter++;
        }
        $this->saves = $store_data;
        $this->error = $errors;
    }
}
