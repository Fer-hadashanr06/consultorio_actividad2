<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\HorarioAtencion;
use App\Models\Odontologo;
use App\Models\Paciente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WebTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create());
    }

    // Próximo lunes (siempre futuro) a la hora indicada
    private function lunes(string $hora = '09:00'): string
    {
        return now()->next('Monday')->toDateString() . ' ' . $hora;
    }

    private function datosPaciente(array $cambios = []): array
    {
        return array_merge([
            'ci' => '7654321 TJ',
            'nombre' => 'Rosa',
            'apellido' => 'Vargas',
            'fecha_nacimiento' => '1995-04-12',
            'telefono' => '77712345',
            'email' => 'rosa.vargas@example.com',
            'direccion' => 'Calle Bolivar 123',
        ], $cambios);
    }

    public function test_las_pantallas_principales_cargan(): void
    {
        Cita::factory()->create();

        $this->get('/')->assertOk()->assertSee('Agendar');
        $this->get('/citas')->assertOk()->assertSee('Citas');
        $this->get('/citas/create')->assertOk();
        $this->get('/pacientes')->assertOk();
        $this->get('/pacientes/create')->assertOk();
        $this->get('/odontologos')->assertOk();
        $this->get('/odontologos/create')->assertOk();
    }

    public function test_registra_un_paciente_y_crea_su_historial(): void
    {
        $this->post('/pacientes', $this->datosPaciente())->assertRedirect();

        $paciente = Paciente::where('ci', '7654321 TJ')->first();
        $this->assertNotNull($paciente);
        $this->assertNotNull($paciente->historial);
        $this->get('/pacientes/' . $paciente->id)->assertOk()->assertSee('Rosa');
    }

    public function test_rechaza_ci_duplicado_y_datos_invalidos(): void
    {
        Paciente::factory()->create(['ci' => '7654321 TJ']);

        $this->post('/pacientes', $this->datosPaciente())->assertSessionHasErrors('ci');
        $this->post('/pacientes', $this->datosPaciente(['ci' => '1111111 TJ', 'email' => 'no-es-correo']))->assertSessionHasErrors('email');
        $this->post('/pacientes', $this->datosPaciente(['ci' => '2222222 TJ', 'fecha_nacimiento' => now()->addDay()->toDateString()]))->assertSessionHasErrors('fecha_nacimiento');
        $this->assertEquals(1, Paciente::count());
    }

    public function test_los_mensajes_de_validacion_estan_en_espanol(): void
    {
        $this->post('/pacientes', ['ci' => '999 TJ'])->assertSessionHasErrors([
            'nombre' => 'El campo nombre es obligatorio.',
            'fecha_nacimiento' => 'El campo fecha de nacimiento es obligatorio.',
        ]);
    }

    public function test_editar_paciente_permite_conservar_su_propio_ci(): void
    {
        $paciente = Paciente::factory()->create(['ci' => '7654321 TJ']);

        $this->put('/pacientes/' . $paciente->id, $this->datosPaciente(['nombre' => 'Rosita']))->assertRedirect();

        $this->assertEquals('Rosita', $paciente->fresh()->nombre);
    }

    public function test_no_elimina_un_paciente_con_citas(): void
    {
        $cita = Cita::factory()->create();

        $this->delete('/pacientes/' . $cita->paciente_id)->assertSessionHasErrors('regla');

        $this->assertNotNull(Paciente::find($cita->paciente_id));
    }

    public function test_elimina_un_paciente_sin_citas(): void
    {
        $paciente = Paciente::factory()->create();

        $this->delete('/pacientes/' . $paciente->id)->assertRedirect('/pacientes');

        $this->assertNull(Paciente::find($paciente->id));
    }

    public function test_odontologo_con_horario_y_tramo_duplicado(): void
    {
        $this->post('/odontologos', [
            'nombre' => 'Ana',
            'apellido' => 'Rojas',
            'email' => 'ana.rojas@example.com',
            'especialidad' => 'ortodoncia',
            'activo' => 1,
        ])->assertRedirect();

        $odontologo = Odontologo::where('email', 'ana.rojas@example.com')->firstOrFail();

        $this->assertTrue((bool) $odontologo->activo);

        $tramo = [
            'dia_semana' => 1,
            'hora_inicio' => '08:00',
            'hora_fin' => '12:00',
        ];

        // Registra un horario válido.
        $this->post('/odontologos/' . $odontologo->id . '/horarios', $tramo)
            ->assertSessionHasNoErrors();

        // Rechaza el horario duplicado.
        $this->post('/odontologos/' . $odontologo->id . '/horarios', $tramo)
            ->assertSessionHasErrors('regla');

        // Rechaza un horario cuya hora de fin es anterior a la de inicio.
        $respuesta = $this->post(
            '/odontologos/' . $odontologo->id . '/horarios',
            [
                'dia_semana' => 2,
                'hora_inicio' => '12:00',
                'hora_fin' => '08:00',
            ]
        );

        $respuesta->assertSessionHasErrors('hora_fin');

        $this->assertEquals(1, $odontologo->horarios()->count());

        $horario = $odontologo->horarios()->firstOrFail();

        $this->delete('/horarios/' . $horario->id)->assertRedirect();

        $this->assertEquals(0, $odontologo->horarios()->count());
    }

    public function test_especialidad_invalida_y_email_repetido_en_odontologo(): void
    {
        $existente = Odontologo::factory()->create();

        $this->post('/odontologos', ['nombre' => 'X', 'apellido' => 'Y', 'email' => $existente->email, 'especialidad' => 'cirugia'])
            ->assertSessionHasErrors(['email', 'especialidad']);
    }

    public function test_pantalla_de_nueva_cita_muestra_los_bloques_libres(): void
    {
        $odontologo = Odontologo::factory()->conHorarioCompleto()->create();

        $this->get('/citas/create?odontologo_id=' . $odontologo->id . '&fecha=' . now()->next('Monday')->toDateString())
            ->assertOk()
            ->assertSee('08:00')
            ->assertSee('17:30');
    }

    public function test_agenda_una_cita_valida(): void
    {
        $odontologo = Odontologo::factory()->conHorarioCompleto()->create();
        $paciente = Paciente::factory()->create();

        $this->post('/citas', [
            'paciente_id' => $paciente->id, 'odontologo_id' => $odontologo->id,
            'fecha' => now()->next('Monday')->toDateString(), 'hora' => '09:00', 'bloques' => 2, 'motivo' => 'Control',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $cita = Cita::first();
        $this->assertEquals('09:00', $cita->inicio->format('H:i'));
        $this->assertEquals('10:00', $cita->fin->format('H:i'));
        $this->get('/citas/' . $cita->id)->assertOk()->assertSee('agendada');
    }

    public function test_las_reglas_de_negocio_se_muestran_como_errores(): void
    {
        $odontologo = Odontologo::factory()->conHorarioCompleto()->create();
        $paciente = Paciente::factory()->create();
        $otro = Paciente::factory()->create();
        $base = ['paciente_id' => $paciente->id, 'odontologo_id' => $odontologo->id, 'fecha' => now()->next('Monday')->toDateString(), 'bloques' => 1];

        // Fuera del horario de atención
        $this->post('/citas', $base + ['hora' => '20:00'])->assertSessionHasErrors('regla');
        // A mitad de bloque
        $this->post('/citas', $base + ['hora' => '09:15'])->assertSessionHasErrors('regla');
        // Cruce del odontólogo
        $this->post('/citas', $base + ['hora' => '09:00'])->assertSessionHasNoErrors();
        $this->post('/citas', array_merge($base, ['paciente_id' => $otro->id, 'hora' => '09:00']))->assertSessionHasErrors('regla');
        // Validación del request (paciente inexistente, bloques fuera de rango)
        $this->post('/citas', array_merge($base, ['paciente_id' => 99999, 'hora' => '10:00', 'bloques' => 9]))->assertSessionHasErrors(['paciente_id', 'bloques']);

        $this->assertEquals(1, Cita::count());
    }

    public function test_ciclo_de_vida_confirmar_reprogramar_y_no_asistida(): void
    {
        $cita = Cita::factory()->create();

        $this->post('/citas/' . $cita->id . '/confirmar')->assertRedirect();
        $this->assertEquals('confirmada', $cita->fresh()->estado);
        $this->post('/citas/' . $cita->id . '/confirmar')->assertSessionHasErrors('regla');

        $this->put('/citas/' . $cita->id . '/reprogramar', ['fecha' => now()->next('Tuesday')->toDateString(), 'hora' => '10:00'])->assertSessionHasNoErrors();
        $cita->refresh();
        $this->assertEquals('reprogramada', $cita->estado);
        $this->assertEquals(1, $cita->reprogramaciones);

        // Una cita futura no se puede marcar como no asistida
        $this->post('/citas/' . $cita->id . '/no-asistida')->assertSessionHasErrors('regla');

        // Una cita que ya pasó sí, y libera el bloque
        $pasada = Cita::factory()->noAsistida()->create(['estado' => 'agendada']);
        $this->post('/citas/' . $pasada->id . '/no-asistida')->assertRedirect();
        $this->assertEquals('no_asistida', $pasada->fresh()->estado);
    }

    public function test_registra_tratamiento_y_pago_y_actualiza_el_saldo(): void
    {
        $cita = Cita::factory()->confirmada()->create();
        $paciente = $cita->paciente;

        $this->post('/citas/' . $cita->id . '/tratamientos', ['descripcion' => 'Limpieza dental', 'costo' => 150])->assertSessionHasNoErrors();
        $this->assertEquals('atendida', $cita->fresh()->estado);
        $this->post('/citas/' . $cita->id . '/tratamientos', ['descripcion' => 'Prueba', 'costo' => -5])->assertSessionHasErrors('costo');

        $this->post('/pacientes/' . $paciente->id . '/pagos', ['monto' => 100, 'metodo' => 'efectivo', 'cita_id' => $cita->id])->assertSessionHasNoErrors();
        $this->post('/pacientes/' . $paciente->id . '/pagos', ['monto' => 0, 'metodo' => 'efectivo'])->assertSessionHasErrors('monto');
        $this->post('/pacientes/' . $paciente->id . '/pagos', ['monto' => 10, 'metodo' => 'bitcoin'])->assertSessionHasErrors('metodo');

        $this->assertEquals(50.0, $paciente->fresh()->estadoDeCuenta()['saldo']);
        $this->get('/pacientes/' . $paciente->id)->assertOk()->assertSee('50.00');
    }

    public function test_no_registra_tratamiento_en_una_cita_agendada(): void
    {
        $cita = Cita::factory()->create();

        $this->post('/citas/' . $cita->id . '/tratamientos', ['descripcion' => 'Limpieza', 'costo' => 100])->assertSessionHasErrors('regla');
        $this->assertEquals(0, $cita->tratamientos()->count());
    }
}
