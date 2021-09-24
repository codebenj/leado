<?php

use Illuminate\Database\Seeder;
use App\Role;
use App\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model as Eloquent;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Eloquent::unguard();

		//disable foreign key check for this connection before running seeders
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // truncate permissions table
        Permission::truncate();

        $allPermissions = [
            'dashboard', 'organisations', 'users', 'reports',
            'postcodes', 'notifications', 'activity_log', 'org_locator',
            'profile', 'admin_settings', 'roles_permissions', 'settings'
        ];

        foreach ($allPermissions as $allPermission) {
            try{
                $permission = Permission::create([
                    'name' => $allPermission
                ]);
            }catch(\Exception $ex){}
        }

        $defaultRoles = [
            'super admin' => [
                'dashboard', 'organisations', 'users', 'reports',
                'postcodes', 'notifications', 'activity_log', 'org_locator',
                'notifications', 'profile', 'admin_settings', 'roles_permissions',
                'settings'
            ],
            'administrator' => [
                'dashboard', 'organisations', 'users', 'reports',
                'postcodes', 'notifications', 'activity_log', 'org_locator',
                'notifications', 'profile', 'admin_settings', 'roles_permissions',
                'settings'
            ],
            'organisation' => [
                'dashboard', 'notifications',
                'notifications', 'profile',
            ],
            'user' => [
                'dashboard', 'organisations', 'users', 'reports',
                'postcodes', 'notifications', 'activity_log', 'org_locator',
                'notifications', 'profile', 'admin_settings', 'roles_permissions',
                'settings'
            ],
        ];

        // truncate roles table
        Role::truncate();

        // insert roles
        foreach ($defaultRoles as $key => $defaultRole) {
            $role = Role::create([
                'name' => $key
            ]);

            $role->givePermissionTo($defaultRole);

            //$role->syncPermissions($defaultRole);
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
