<?php

namespace Tests\Feature;

use App\Models\Conductor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConductorsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_conductores_index(): void
    {
        $user = User::factory()->create();
        Conductor::create([
            'nombre' => 'JUAN PEREZ',
            'dni' => '12345678',
            'estado' => 'activo',
        ]);

        $response = $this->actingAs($user)->get(route('conductores.index'));

        $response->assertStatus(200);
    }
}
