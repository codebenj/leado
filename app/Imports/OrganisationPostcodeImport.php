<?php

namespace App\Imports;

use App\Organisation;
use App\OrganizationPostcode;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class OrganisationPostcodeImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $collection)
    {
        $errors = [];
        $data = [];
        foreach ($collection as $key => $value) {
            try{
                $organisation = Organisation::where('org_code', $value[0])->first();

                if ($organisation) {
                    $organisation_postcode = OrganizationPostcode::where('organisation_id', $organisation->id)
                        ->where('postcode', $value[1])
                        ->first();

                    if (!$organisation_postcode) {
                        array_push($data, ['organisation_id' => $organisation->id, 'postcode' => $value[1], 'created_at' => now(), 'updated_at' => now()]);
                    }
                }else{
                    array_push($errors, "Organisation code $value[0] doesn't exit");
                }
            }catch(\Exception $ex){
                //add try to catch to continue event the entries are not correct..                
            }
        }

        if (count($data) > 0) {
            OrganizationPostcode::insert($data);
        }

        $this->error = $errors;
    }
}
