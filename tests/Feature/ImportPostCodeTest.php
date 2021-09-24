<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use App\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportPostCodeTest extends TestCase
{
    protected $administrator_user;

    public function setUp(): void
    {
        parent::setUp();

        app()['cache']->forget('spatie.permission.cache');
        Role::findOrCreate('administrator');
        $this->app->make(PermissionRegistrar::class)->registerPermissions();

        $this->administrator_user = User::find(1);
        $this->administrator_user->assignRole('administrator');
    }

    /** @test */
    public function import_administrator_success(){
        $name = 'testingPostcodeImport.csv';
        //physical path
        $path = storage_path('test_assets/testingPostcodeImport.csv');
        $file = new UploadedFile($path, $name);
        //on post need file name "import_file"
        $response = $this->actingAs($this->administrator_user)
            ->postJson('/api/v1/organisation/postcode/import', ['import_file' => $file]);

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }

    /** @test */
    public function logs_administrator_success(){
        $response = $this->actingAs($this->administrator_user)
            ->getJson('/api/v1/organisation/postcode/logs');

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }
}
