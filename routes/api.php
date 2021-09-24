<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'prefix' => 'v1',
    'middleware' => 'auth:api',
    'namespace' => 'Api\V1'
], function () {

    Route::group(['middleware' => ['role:organisation|administrator|super user|user']], function() {
        Route::post('leads/update_response', 'LeadController@updateLeadEscalationStatus')->name('leads.response');
        Route::get('leads/response_structure', 'LeadController@leadEscalationResponseStructure')->name('leads.structure');
        Route::get('brand-logo', 'SettingController@brandLogo');

        // api/v1/leads/dashboard
        Route::get('leads/getDashboard', 'LeadController@dashboard')->name('leads.dashboard');
        // api/v1/leads/{id}/history
        Route::get('leads/{id}/history', 'LeadController@leadHistory')->name('leads.history');

        // api/v1/leads/comments
        Route::get('leads/comments/{leadId}', 'LeadController@comments');

        // api/v1/leads/staffComment
        Route::get('leads/staffComment/{leadId}', 'LeadController@staffComment');

        // api/v1/leads/add_lead_comment/{leadId}
        Route::post('leads/add_lead_comment/{leadId}', 'LeadController@saveLeadComment');

        // api/v1/leads/delete_lead_comment/{leadId}
        Route::post('leads/delete_lead_comment/{leadId}', 'LeadController@deleteLeadComment');

        // ASSIGN USER TO LEAD
        // api/v1/leads/fetchUsers
        Route::get('leads/fetchUsers', 'LeadController@fetchUsers');

        // api/v1/leads/fetchAssignedUsers/{id}
        Route::get('leads/fetch_assigned_users/{id}', 'LeadController@fetchAssignedUsers');

        // api/v1/leads/assign_user_to_lead/{id}
        Route::post('leads/assign_user_to_lead/{id}', 'LeadController@assignUserToLead');

        //  api/v1/leads/actual-estimations
        Route::post('leads/actual-estimations', 'LeadController@updateLeadEscalationMeters')
        ->name('leads.updateEscalationMeters');

        // api/v1/leads/{id}/send_message
        Route::post('leads/{id}/send_message', 'LeadController@sendInquirerMessage')
            ->name('leads.send_message');

        // api/v1/leads/{id}/send_enquirer_details
        Route::post('leads/{id}/send_enquirer_details', 'LeadController@sendInquirerDetails')
            ->name('leads.send_message');

        Route::post('leads/{id}/save/sale', 'LeadController@saveSale')->name('leads.save.sale');

        // api/v1/leads/orgs-icon-action
        Route::post('leads/orgs-icon-action', 'LeadController@orgsIconAction')->name('leads.icon.action');

        // api/v1/leads/name-validate
        Route::post('leads/name-validate', 'LeadController@leadNameValidate')->name('leads.name.validate');


    });

    Route::group(['middleware' => ['role:user|administrator']], function() {
        // api/v1/leads/{id}/reassign
        Route::post('leads/{id}/reassign', 'LeadController@reassignLead')->name('leads.reassign');

        Route::get('activities/view/email/{activity_id}', 'ActivityController@getEmailData')->name('activity.view.email');

        Route::resource('activities', 'ActivityController', [
            // api/v1/activities/*
            'only' => ['index']
        ]);

        Route::resource('roles', 'RoleController', [
            // api/v1/roles/*
            'only' => ['index', 'show', 'store', 'update',  'destroy']
        ]);

        // api/v1/permissions/revoke
        Route::post('permissions/revoke', 'PermissionController@revoke')->name('permissions.revoke');

        Route::resource('permissions', 'PermissionController', [
            // api/v1/permissions/*
            'only' => ['index', 'show', 'store', 'update',  'destroy']
        ]);
    });

    Route::resource('leads', 'LeadController', [
        // api/v1/leads/*
        'only' => ['index', 'show', 'store', 'update',  'destroy']
    ]);

    // api/v1/organisations/availability/status
    Route::post('organisations/availability/status', 'OrganizationController@availabilityStatus');

    // api/v1/organisations/status/update/{orgId}
    Route::post('organisations/status/update/{orgId}', 'OrganizationController@updateStatus');

    // api/v1/organisations/comments
    Route::get('organisations/comments/{orgId}', 'OrganizationController@comments');

    // api/v1/organisations/add_org_comment/{orgId}
    Route::post('organisations/add_org_comment/{orgId}', 'OrganizationController@saveOrgComment');

    // api/v1/organizations/delete_org_comment/{orgId}
    Route::post('organizations/delete_org_comment/{orgId}', 'OrganizationController@deleteOrgComment');

    // api/v1/organizations/countries
    Route::get('organizations/countries', 'OrganizationController@countries')->name('org.countries');

    // api/v1/organisations/import
    Route::post('organisations/import', 'OrganizationController@importOrganizations');

    // api/v1/organisation/leads/reassigned
    Route::get('organisation/{id}/leads/reassigned', 'OrganizationController@getReassignedLeads');

    // api/v1/organisation/postcode/import
    Route::post('organisation/postcode/import', 'OrganizationController@import');

    // api/v1/organisation/logs
    Route::get('organisations/logs', 'OrganizationController@organizationLogs');

    // api/v1/organisation/postcode/logs
    Route::get('organisation/postcode/logs', 'OrganizationController@logs');

    // api/v1/organisation/postcode/deleteLogs
    Route::post('organisation/postcode/deleteLogs', 'OrganizationController@deleteLogs');

    // api/v1/organisations/all
    Route::get('organizations/all', 'OrganizationController@all');

    // api/v1/organizations/delete
    Route::post('organizations/delete', 'OrganizationController@delete');

    // api/v1/organizations/export
    Route::post('organizations/export', 'OrganizationController@export');

    // api/v1/organizations/postcodes
    Route::get('organizations/postcodes', 'OrganizationController@getPostCodesAll');

    // api/v1/organizations/ids
    Route::get('organizations/ids', 'OrganizationController@getOrganizationByIds');

    // api/v1/organizations/get/by/postcodes
    Route::get('organizations/postcode/get/by/lead_id/{lead_id}', 'OrganizationController@getOrganizationByLeadId');

    // api/v1/organizations/customers/postcodes/id
    Route::get('organizations/customers/postcodes/{id}', 'OrganizationController@getOrganizationCustomersPostcodes');

    // api/v1/organizations/postcodes/get/{id}
    Route::get('organizations/postcodes/get/{id}', 'OrganizationController@postcodes');

    // api/v1/organizations/id/send_message
    Route::post( 'organizations/{id}/send_message', 'OrganizationController@sendMessageOrgProfile' );

    // api/v1/organizations/id/fetch_messages
    Route::get( 'organizations/{id}/fetch_messages', 'OrganizationController@fetchMessageOrgProfile' );

    // api/v1/organizations/lead/update/id
    Route::post('organizations/lead/update/{id}', 'OrganizationController@sendLeadUpdate');

    // api/v1/organizations/assigned/leads/{id}
    Route::get('organizations/assigned/leads/{id}', 'OrganizationController@leadAssigned');

    Route::get('organizations/leadStats/{org_id}', 'OrganizationController@leadStats');


    Route::resource('organizations', 'OrganizationController', [
        // api/v1/organizations/*
        'only' => ['index', 'show', 'store', 'update',  'destroy']
    ]);

    // api/v1/org-locator/import
    Route::post('org-locator/import', 'OrgLocatorController@import');

    // api/v1/org-locator/delete
    Route::post('org-locator/delete', 'OrgLocatorController@delete');

    // api/v1/org-locator/delete-all
    Route::post('org-locator/delete-all', 'OrgLocatorController@deleteAll');

    // api/v1/org-locator/logs
    Route::get('org-locator/logs', 'OrgLocatorController@logs');

    // api/v1/org-locator/export
    Route::post('org-locator/export', 'OrgLocatorController@export');

    // api/v1/org-locator/logs/delete
    Route::post('org-locator/logs/delete', 'OrgLocatorController@deleteLogs');

    Route::resource('org-locator', 'OrgLocatorController', [
        // api/v1/org-locator/*
        'only' => ['index', 'show', 'store', 'update',  'destroy']
    ]);


    // api/v1/notifications
    Route::get('notifications/notification', 'NotificationController@getNotification');
    Route::get('notifications/read/{id}', 'NotificationController@readNotification');
    Route::get('notifications/read-all', 'NotificationController@readAllNotifications');
    Route::get('notifications/testsms', 'NotificationController@testSms');

    Route::resource('notifications', 'NotificationController', [
        // api/v1/notifications/*
        'only' => ['index'],
    ]);

    Route::resource('scrum/tasks', 'TaskController', [
        // api/v1/scrum/tasks/*
        'only' => ['index', 'show', 'store', 'update',  'destroy']
    ]);

    // api/v1/scrum/task/delete
    Route::post('scrum/task/delete', 'TaskController@deleteTask');

    // api/v1/scrum/update/task_status
    Route::post('scrum/update/task_status', 'TaskController@updateStatus');


    // api/v1/leads/fetchAssignedUsers/{id}
    Route::get('scrum/fetch_assigned_users/{id}', 'TaskController@fetchAssignedUsers');

    // api/v1/leads/assign_user_to_lead/{id}
    Route::post('scrum/assign_user_to_task/{id}', 'TaskController@assignUserToTask');

    Route::resource('scrum/status', 'StatusController', [
        // api/v1/scrum/status/*
        'only' => ['index']
    ]);


    //Route::post('notifications/custom/send', 'NotificationController@customNotificationSend');
    Route::post('custom/notifications/send', 'CustomNotificationController@customNotificationSend');

    Route::resource('custom/notifications', 'CustomNotificationController', [
        // api/v1/custom/notifications/*
        'only' => ['index', 'show', 'store', 'update',  'destroy']
    ]);

    // api/v1/users/upload_avatar
    Route::post('users/upload_avatar', 'UserController@uploadAvatar');

    // api/v1/users/profile
    Route::post('users/profile', 'UserController@profile');

    // api/v1/users/profile
    Route::get('users/profile', 'UserController@getProfile');

    // api/v1/users/delete
    Route::post('users/delete', 'UserController@deleteUsers');

    // api/v1/users/export
    Route::post('users/export', 'UserController@export');

    Route::resource('users', 'UserController', [
        // api/v1/users/*
        'only' => ['index', 'show', 'store', 'update',  'destroy']
    ]);

    // api/v1/lead/jobs/sales-total/{lead_id}
    Route::get('lead/jobs/sales-total/{lead_id}', 'LeadJobsController@getLeadJobsTotal');

    Route::resource('lead/jobs', 'LeadJobsController', [
        // api/v1/lead/jobs/*
        'only' => ['index', 'show', 'store', 'update',  'destroy']
    ]);

    Route::get('customers', 'CustomerController@index');

    Route::post('customers/export', 'CustomerController@export');

    Route::resource('stores', 'StoreController', [
        // api/v1/stores/*
        'only' => ['index', 'show', 'store', 'update',  'destroy']
    ]);

    Route::post('store/delete/mass', 'StoreController@massDestroy');
    Route::post('store/export', 'StoreController@export');
    Route::post('store/sent', 'StoreController@sent');
    Route::get('store/sent/history', 'StoreController@sentHistory');
    Route::post('store/import', 'StoreController@import');
    Route::get('store/import/logs', 'StoreController@logs');
    Route::post('store/import/logs/delete', 'StoreController@deleteLogs');


    Route::group(['prefix' => 'admin'], function () {
        // api/v1/admin/setting/upload_logo
        Route::post('setting/upload_logo', 'SettingController@uploadLogo')->name('settings.upload_logo');

        // api/v1/admin/setting/admin_email_receivers
        Route::post('setting/admin_email_receivers', 'SettingController@saveEmailReceivers')->name('settings.email_receivers');

        // api/v1/admin/setting/get/{key}
        Route::get('setting/get/{key}', 'SettingController@getSettingsByKey')->name('settings.key');

        Route::resource('setting', 'SettingController', [
            // api/v1/admin/setting
            'only' => ['index', 'show', 'store', 'update',  'destroy']
        ]);

        Route::group(['prefix' => 'time-setting'], function() {

            Route::get('/', 'TimeSettingController@index');           // api/v1/admin/time-setting
            Route::post('/save', 'TimeSettingController@save');       // api/v1/admin/time-setting/save
            Route::post('/delete/{id}', 'TimeSettingController@delete');       // api/v1/admin/time-setting/delete
            Route::post('/change-status/{id}', 'TimeSettingController@changeStatus');       // api/v1/admin/time-setting/save
        });

        // api/v1/admin/reports/medium-breakdown
        Route::get('reports/medium-breakdown', 'ReportController@mediumBreakdown');
        Route::get('reports/organisation-breakdown', 'ReportController@organisationBreakdown');
        Route::get('reports/leads-won-breakdown', 'ReportController@leadsWonBreakdown');
        Route::get('reports/lead/{lead_id}/stat', 'ReportController@leadStats');
        Route::get('reports/organization/{org_id}/stat', 'ReportController@organizationStats');

        Route::post('reports/export/advertising-medium-breakdown', 'ReportController@exportAdvertisingMediumBreakdown');
        Route::post('reports/export/organisation-status-breakdown', 'ReportController@exportOrganisationStatusBreakdown');
        Route::post('reports/export/lead-won-breakdown', 'ReportController@exportLeadWonBreakdown');
    });
});

Route::group(['middleware' => 'auth:api'], function () {
    Route::post('logout', 'Auth\LoginController@logout');
    Route::get('/roles', 'RoleController@index');

    Route::group(['prefix' => 'role'], function () {
        Route::get('/all', 'RoleController@index');                 // api/role/all
        Route::post('/assign', 'RoleController@assign');            // api/role/assign
        Route::post('/save', 'RoleController@save');                // api/role/save
        Route::post('/delete', 'RoleController@delete');            // api/role/delete
    });

    Route::group(['prefix' => 'permission'], function () {
        Route::get('/all', 'PermissionController@index');           // api/permission/all
        Route::post('/save', 'PermissionController@save');          // api/permission/save
        Route::post('/revoke', 'PermissionController@revoke');      // api/permission/delete
    });

    Route::group(['prefix' => 'user'], function () {
        Route::get('/', 'Auth\UserController@current');             // api/user
        Route::get('/roles', 'RoleController@user');                // api/user/roles
        Route::get('/permissions', 'PermissionController@user');    // api/user/permissions

        // menu routes
        Route::get('/menu', 'MenuController@getMenu');              // api/user/menu
    });

    Route::group(['prefix' => 'admin'], function () {
        Route::get('/organisations', 'OrganisationController@index');           // api/admin/organisations
    });

    Route::patch('settings/profile', 'Settings\ProfileController@update');
    Route::patch('settings/password', 'Settings\PasswordController@update');

    // Administrator Routes
    Route::group(['prefix' => 'admin'], function () {
        Route::group(['prefix' => 'organisation'], function () {
            Route::get('/get/{id}', 'OrganisationController@getOrg');       // api/admin/organisation/get/1
            Route::get('/all', 'OrganisationController@index');             // api/admin/organisation/all
            Route::post('/save', 'OrganisationController@save');            // api/admin/organisation/save
            Route::post('/delete', 'OrganisationController@delete');        // api/admin/organisation/delete
        });

        Route::group(['prefix' => 'user'], function () {
            Route::get('/get/{id}', 'UserController@getUser');      // api/admin/user/get/1
            Route::get('/all', 'UserController@index');             // api/admin/user/all
            Route::post('/save', 'UserController@save');            // api/admin/user/save
            Route::post('/delete', 'UserController@delete');        // api/admin/user/delete
        });

        Route::group(['prefix' => 'settings'], function () {
            Route::get('/get/{id}', 'AdminSettingsController@getSetting');      // api/admin/user/get/1
            Route::get('/all', 'AdminSettingsController@index');                // api/admin/user/all
            Route::post('/save', 'AdminSettingsController@save');               // api/admin/user/save
            Route::post('/delete', 'AdminSettingsController@delete');           // api/admin/user/delete
        });
    });

    // Org. Locator
    Route::group(['prefix' => 'org-locator'], function () {
        Route::post('/save', 'OrgLocatorController@save');            // api/org-locator/save
        Route::get('/all', 'OrgLocatorController@all');            // api/org-locator/all
        Route::get('/get/{id}', 'OrgLocatorController@getOrgLocator');      // api/org-locator/get/{id}
        Route::post('/delete', 'OrgLocatorController@delete');      // api/org-locator/delete
        Route::post('/import', 'OrgLocatorController@import');            // api/org-locator/import
    });
});

Route::group(['middleware' => 'guest:api'], function () {
    Route::post('login', 'Auth\LoginController@login');
    Route::post('register', 'Auth\RegisterController@register');

    Route::post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail');
    Route::post('password/reset', 'Auth\ResetPasswordController@reset');

    Route::post('email/verify/{user}', 'Auth\VerificationController@verify')->name('verification.verify');
    Route::post('email/resend', 'Auth\VerificationController@resend');

    Route::post('oauth/{driver}', 'Auth\OAuthController@redirectToProvider');
    Route::get('oauth/{driver}/callback', 'Auth\OAuthController@handleProviderCallback')->name('oauth.callback');

    Route::resource('/v1/webforms', 'Api\V1\LeadWebFormsController', [
        // api/v1/webforms/*
        'only' => ['index', 'show', 'store', 'update',  'destroy']
    ]);
});
