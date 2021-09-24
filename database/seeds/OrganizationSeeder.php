<?php

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Database\Seeder;
use App\OrganizationUser;
use App\User;

class OrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(App\Organisation::class, 1)->create()->each(function ($organization){
            $user = User::find($organization->user_id);
            $user->assignRole('organisation');
            OrganizationUser::create(['user_id' => $organization->user_id, 'organisation_id' => $organization->id]);
        });
    }
}
