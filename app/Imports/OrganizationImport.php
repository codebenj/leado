<?php

namespace App\Imports;

use App\Address;
use App\Country;
use App\User;
use App\Organisation;
use App\OrganizationUser;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Hash;

class OrganizationImport implements ToCollection
{
    public $error;

    public $saves;

    public function collection(Collection $collection){
        $counter = 0;
        $store_data = array();
        $errors = [];
        $emails = [];
        $is_file_valid = true;
        foreach($collection as $row){
            //checking columns
            if($counter == 0){
                $columns = array(
                    'Org Code', 'Company name', 'First Name', 'Last Name', 'Contact Number', 'Email', 'Password', 'Address', 'City', 'Suburb', 'Postcode', 'State', 'Country', 'Status'
                );
                $count = count($columns);
                for($i=0; $i<$count; $i++){
                    if(\strtolower($columns[$i]) != \strtolower($row[$i])){
                        $is_file_valid = false;
                        break;
                    }
                }
            }
            else{
                try{
                    $pos = strpos($row[4], '+');
                    $contact_number = ($pos === false) ? '+61'.$row[4] : $row[4];

                    $org_code = 0;
                    if(!empty($row[0])){
                        $org_code = $row[0];
                    }

                    $country = Country::where('name', $row[12])->first();
                    \DB::beginTransaction();

                    # Address
                    $address             = new Address;
                    $address->address    = $row[7] ?? '';
                    $address->state      = $row[11] ?? '';
                    $address->city       = $row[8] ?? '';
                    $address->suburb     = $row[9] ?? '';
                    $address->postcode   = $row[10] ?? '';
                    $address->country_id = $country->id;
                    $address->save();

                    # User
                    $user = new User;
                    $user->email      = $row[5];
                    $user->password   = Hash::make($row[6]);
                    $user->first_name = $row[2] ?? '';
                    $user->last_name  = $row[3] ?? '';
                    $user->role_id = 3; # Default;
                    $user->address_id = $address->id;
                    $user->save();

                    # Assign role
                    $user->assignRole('organisation');

                    $org_data = array(
                        'user_id' => $user->id,
                        'contact_number' => $contact_number,
                        'org_code' => $row[0] ?? '',
                        'is_suspended' => $row[13] ?? 0,
                        'address_id' => $address->id,
                        'name' => $row[1] ?? ''
                    );
                    $org = Organisation::create($org_data);

                    # Org User
                    $org_user = OrganizationUser::create([
                        'user_id' => $user->id,
                        'organisation_id' => $org->id,
                    ]);

                    $store_data[] = $org;

                    \DB::commit();
                }catch(\Exception $ex){
                    $error = $ex->getMessage();
                    $pos = strpos($error, 'users.users_email_unique');
                    if($pos === false){
                    }else{
                        //$errors[] = $row[5].' is already exist in the system.';
                        $emails[] = $row[5];
                    }
                    //$errors[] = $ex->getMessage();
                    \DB::rollback();
                }
            }

            if(!$is_file_valid){
                $errors[] = 'Oops! Please enter correct file.';
                break;
            }
            $counter++;
        }
        $temp_error = '';
        if(count($emails) > 0){
            foreach($emails as $email){
                $temp_error .= $email.', ';
            }
            $errors[] = rtrim($temp_error, ", ").' email are duplicate.';
        }
        $this->saves = $store_data;
        $this->error = $errors;
    }
}
