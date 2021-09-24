<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\LeadWebForm;

class LeadWebFormTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function store()
    {
        $data = factory(LeadWebForm::class)->make()->toArray();
        $response = $this->postJson('/api/v1/webforms', $data);

        $response->assertSuccessful();
        $response->assertJsonStructure(['success', 'message', 'data']);
        $response->json();
    }
}
