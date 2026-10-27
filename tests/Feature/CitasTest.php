<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\Odontologo;
use App\Models\Paciente;
use Carbon\Carbon;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitasTest extends TestCase
{
    use RefreshDatabase;

    private Odontologo $odontologo;
    private Paciente $paciente;
    private Carbon $lunes;

    protected function setUp(): void
    {
        parent::setUp();

        $this->odontologo = Odontologo::factory()->create(['especialidad' => 'ortodoncia']);
        // Atiende los lunes de 08:00 a 12:00
        $this->odontologo->horarios()->create(['dia_semana' => 1, 'hora_inicio' => '08:00', 'hora_fin' => '12:00']);

        $this->paciente = Paciente::factory()->create();
        $this->lunes = now()->next(Carbon::MONDAY)->setTime(9, 0, 0);
    }

    private function nuevaCita(Carbon $inicio, ?Paciente $paciente = null, string $estado = 'agendada'): Cita
    {
        return Cita::create([
            'paciente_id' => ($paciente ?? $this->paciente)->id,
            'odontologo_id' => $this->odontologo->id,
            'inicio' => $inicio,
            'estado' => $estado,
        ]);
    }

    public function test_agenda_un_bloque_de_30_minutos_dentro_del_horario(): void
    {
        $cita = $this->nuevaCita($this->lunes);

        $this->assertEquals('agendada', $cita->estado);
        $this->assertEquals(30, $cita->inicio->diffInMinutes($cita->fin));
    }

    public function test_rechaza_cita_fuera_del_horario(): void
    {
        $this->expectException(DomainException::class);
        $this->nuevaCita($this->lunes->copy()->setTime(13, 0));
    }

    public function test_rechaza_cita_en_dia_sin_atencion(): void
    {
        $this->expectException(DomainException::class);
        $this->nuevaCita($this->lunes->copy()->addDay());
    }

    public function test_rechaza_inicio_que_no_es_bloque_de_30_minutos(): void
    {
        $this->expectException(DomainException::class);
        $this->nuevaCita($this->lunes->copy()->setTime(9, 15));
    }

    public function test_rechaza_cruce_de_horario_del_odontologo(): void
    {
        $this->nuevaCita($this->lunes);

        $this->expectException(DomainException::class);
        $this->nuevaCita($this->lunes, Paciente::factory()->create());
    }

    public function test_confirma_y_reprograma_una_cita(): void
    {
        $cita = $this->nuevaCita($this->lunes);

        $cita->confirmar();
        $this->assertEquals('confirmada', $cita->fresh()->estado);

        $nueva = $this->lunes->copy()->addWeek()->setTime(10, 30);
        $cita->reprogramar($nueva);
        $cita = $cita->fresh();

        $this->assertEquals('reprogramada', $cita->estado);
        $this->assertEquals(1, $cita->reprogramaciones);
        $this->assertEquals($this->lunes->toDateTimeString(), $cita->inicio_original->toDateTimeString());
        $this->assertEquals($nueva->toDateTimeString(), $cita->inicio->toDateTimeString());
    }

    public function test_reprogramar_a_un_horario_invalido_deja_la_cita_como_estaba(): void
    {
        $cita = $this->nuevaCita($this->lunes);

        try {
            $cita->reprogramar($this->lunes->copy()->setTime(15, 0));
            $this->fail('Debio lanzar DomainException');
        } catch (DomainException $e) {
            $this->assertEquals($this->lunes->toDateTimeString(), $cita->fresh()->inicio->toDateTimeString());
            $this->assertEquals('agendada', $cita->fresh()->estado);
        }
    }

    public function test_no_asistida_libera_el_bloque(): void
    {
        $pasado = now()->previous(Carbon::MONDAY)->setTime(9, 0, 0);
        $cita = $this->nuevaCita($pasado);

        $cita->marcarNoAsistida();
        $this->assertEquals('no_asistida', $cita->fresh()->estado);

        // El mismo bloque vuelve a quedar disponible
        $otra = $this->nuevaCita($pasado, Paciente::factory()->create());
        $this->assertNotNull($otra->id);
    }

    public function test_no_se_puede_marcar_no_asistida_una_cita_futura(): void
    {
        $cita = $this->nuevaCita($this->lunes);

        $this->expectException(DomainException::class);
        $cita->marcarNoAsistida();
    }

    public function test_estado_de_cuenta_suma_tratamientos_y_pagos(): void
    {
        $cita = $this->nuevaCita($this->lunes, null, 'confirmada');
        $cita->registrarTratamiento('Endodoncia', 600);
        $cita->registrarTratamiento('Control', 200);
        $this->paciente->registrarPago(500);

        $cuenta = $this->paciente->estadoDeCuenta();

        $this->assertEquals('atendida', $cita->fresh()->estado);
        $this->assertEquals(800.0, $cuenta['total_tratamientos']);
        $this->assertEquals(500.0, $cuenta['total_pagos']);
        $this->assertEquals(300.0, $cuenta['saldo']);
        $this->assertCount(2, $cuenta['tratamientos']);
    }
}
