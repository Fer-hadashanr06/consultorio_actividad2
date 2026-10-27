<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->restrictOnDelete();
            $table->foreignId('odontologo_id')->constrained('odontologos')->restrictOnDelete();
            $table->dateTime('inicio');
            $table->dateTime('fin');
            $table->unsignedTinyInteger('bloques')->default(1);   // cantidad de bloques de 30 min
            $table->enum('estado', ['agendada', 'confirmada', 'reprogramada', 'atendida', 'no_asistida'])->default('agendada');
            $table->dateTime('inicio_original')->nullable();      // fecha original si se reprogramó
            $table->unsignedSmallInteger('reprogramaciones')->default(0);
            $table->string('motivo', 150)->nullable();
            $table->timestamps();

            $table->index(['odontologo_id', 'inicio']);
            $table->index(['paciente_id', 'inicio']);
            $table->index('estado');
        });

        // Reglas de dominio a nivel de base de datos (PostgreSQL)
        if (DB::getDriverName() == 'pgsql') {
            DB::statement('ALTER TABLE citas ADD CONSTRAINT citas_bloques_validos CHECK (bloques BETWEEN 1 AND 4)');
            // La cita dura exactamente N bloques de 30 minutos
            DB::statement("ALTER TABLE citas ADD CONSTRAINT citas_duracion_valida CHECK (fin = inicio + (bloques * interval '30 minutes'))");
            // Solo se agenda al inicio de un bloque: hh:00 o hh:30
            DB::statement('ALTER TABLE citas ADD CONSTRAINT citas_inicio_en_bloque CHECK (EXTRACT(MINUTE FROM inicio) IN (0, 30) AND EXTRACT(SECOND FROM inicio) = 0)');

            // Sin cruces: ni el odontólogo ni el paciente pueden tener dos citas activas que se traslapen
            DB::statement('CREATE EXTENSION IF NOT EXISTS btree_gist');
            DB::statement("ALTER TABLE citas ADD CONSTRAINT citas_sin_cruce_odontologo EXCLUDE USING gist (odontologo_id WITH =, tsrange(inicio, fin) WITH &&) WHERE (estado <> 'no_asistida')");
            DB::statement("ALTER TABLE citas ADD CONSTRAINT citas_sin_cruce_paciente EXCLUDE USING gist (paciente_id WITH =, tsrange(inicio, fin) WITH &&) WHERE (estado <> 'no_asistida')");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
