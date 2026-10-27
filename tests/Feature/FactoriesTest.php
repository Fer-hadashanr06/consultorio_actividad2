<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\HistorialClinico;
use App\Models\HorarioAtencion;
use App\Models\Odontologo;
use App\Models\Pago;
use App\Models\Paciente;
use App\Models\Tratamiento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FactoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_odontologo_con_horario_completo_tiene_10_tramos(): void
    {
        $odontologo = Odontologo::factory()->conHorarioCompleto()->create();

        $this->assertCount(10, $odontologo->horarios);
    }

    public function test_horario_factory_crea_un_tramo_valido(): void
    {
        $horario = HorarioAtencion::factory()->create();

        $this->assertTrue($horario->hora_fin > $horario->hora_inicio);
        $this->assertNotNull($horario->odontologo);
    }

    public function test_paciente_nuevo_recibe_un_historial_automatico(): void
    {
        $paciente = Paciente::factory()->create();

        $this->assertNotNull($paciente->historial);
    }

    public function test_historial_factory_crea_un_solo_historial_por_paciente(): void
    {
        $historial = HistorialClinico::factory()->create();

        $this->assertEquals(1, HistorialClinico::where('paciente_id', $historial->paciente_id)->count());
    }

    public function test_cita_factory_respeta_las_reglas_de_agenda(): void
    {
        $cita = Cita::factory()->create();

        $this->assertEquals('agendada', $cita->estado);
        $this->assertEquals(30, $cita->inicio->diffInMinutes($cita->fin));
        $this->assertTrue($cita->odontologo->atiendeEn($cita->inicio, $cita->fin));
    }

    public function test_estados_de_cita_del_factory(): void
    {
        $this->assertEquals('confirmada', Cita::factory()->confirmada()->create()->estado);
        $this->assertEquals('atendida', Cita::factory()->atendida()->create()->estado);
        $this->assertEquals('no_asistida', Cita::factory()->noAsistida()->create()->estado);

        $reprogramada = Cita::factory()->reprogramada()->create();
        $this->assertEquals(1, $reprogramada->reprogramaciones);
        $this->assertNotNull($reprogramada->inicio_original);
    }

    public function test_tratamiento_queda_en_el_historial_del_paciente_de_la_cita(): void
    {
        $tratamiento = Tratamiento::factory()->create();

        $this->assertEquals($tratamiento->cita->paciente->historial->id, $tratamiento->historial_clinico_id);
        $this->assertGreaterThan(0, $tratamiento->costo);
    }

    public function test_pago_factory_y_relaciones_nuevas(): void
    {
        $cita = Cita::factory()->atendida()->create();
        $pago = Pago::factory()->for($cita->paciente)->create(['cita_id' => $cita->id]);

        $this->assertEquals($cita->paciente_id, $pago->paciente_id);
        $this->assertTrue($cita->pagos->contains($pago));

        $tratamiento = Tratamiento::factory()->create(['cita_id' => $cita->id, 'historial_clinico_id' => $cita->paciente->historial->id]);
        $this->assertTrue($cita->odontologo->tratamientos->contains($tratamiento));
    }
}
