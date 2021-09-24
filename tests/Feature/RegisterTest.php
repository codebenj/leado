<?php

namespace Tests\Feature;

use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        $this->setupPermissions();
    }

    protected function setupPermissions()
    {
        Role::findOrCreate('administrator');
        Role::findOrCreate('organisation');

        $this->app->make(PermissionRegistrar::class)->registerPermissions();
    }

    /** @test */
    public function can_register()
    {
        $this->postJson('/api/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@test.app',
            'password' => 'secret',
            'password_confirmation' => 'secret',
        ])
        ->assertSuccessful()
        ->assertJsonStructure(['id', 'name', 'email']);
    }

    /** @test */
    public function can_not_register_with_existing_email()
    {
        $user = factory(User::class)->create();
        $user->assignRole('organisation');

        $this->postJson('/api/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => $user->email,
            'password' => 'secret',
            'password_confirmation' => 'secret',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
    }
}
