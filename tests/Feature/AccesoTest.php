<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\Odontologo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccesoTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_portada_y_el_login_son_publicos(): void
    {
        $this->get('/')->assertOk()->assertSee('Qué estudia la odontología')->assertSee('Iniciar sesión');
        $this->get('/login')->assertOk()->assertSee('Entrar');
    }

    public function test_las_pantallas_privadas_piden_iniciar_sesion(): void
    {
        foreach (['/panel', '/citas', '/citas/create', '/pacientes', '/pacientes/create', '/odontologos', '/odontologos/create'] as $ruta) {
            $this->get($ruta)->assertRedirect('/login');
        }
        $this->post('/odontologos', [])->assertRedirect('/login');
        $this->post('/pacientes', [])->assertRedirect('/login');
    }

    public function test_inicia_sesion_y_vuelve_a_la_pantalla_pedida(): void
    {
        $usuario = User::factory()->create(['password' => 'secreto123']);

        $this->get('/pacientes');
        $this->post('/login', ['email' => $usuario->email, 'password' => 'secreto123'])->assertRedirect('/pacientes');
        $this->assertAuthenticatedAs($usuario);
    }

    public function test_rechaza_credenciales_incorrectas(): void
    {
        $usuario = User::factory()->create(['password' => 'secreto123']);

        $this->post('/login', ['email' => $usuario->email, 'password' => 'otra'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_panel_y_cierre_de_sesion(): void
    {
        Cita::factory()->create();
        $this->actingAs(User::factory()->create(['name' => 'Ana Rojas']));

        $this->get('/panel')->assertOk()->assertSee('Hola, Ana')->assertSee('Registrar odontólogo');
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
        $this->get('/panel')->assertRedirect('/login');
    }

    public function test_con_sesion_se_puede_registrar_un_odontologo(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post('/odontologos', [
            'nombre' => 'Lucía', 'apellido' => 'Pérez', 'email' => 'lucia@example.com',
            'especialidad' => 'endodoncia', 'activo' => 1,
        ])->assertRedirect();

        $this->assertNotNull(Odontologo::where('email', 'lucia@example.com')->first());
    }
}
