<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    // La portada es pública; la lista de citas exige sesión
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->get('/')->assertOk()->assertSee('Iniciar sesión');
        $this->get('/citas')->assertRedirect('/login');
        $this->actingAs(User::factory()->create())->get('/citas')->assertOk();
    }
}
