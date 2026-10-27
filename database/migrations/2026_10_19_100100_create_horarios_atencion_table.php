<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horarios_atencion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('odontologo_id')->constrained('odontologos')->cascadeOnDelete();
            $table->unsignedTinyInteger('dia_semana');   // 1 = lunes ... 7 = domingo (ISO)
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->timestamps();

            // Un odontólogo puede tener varios tramos por día (mañana y tarde)
            $table->unique(['odontologo_id', 'dia_semana', 'hora_inicio']);
        });

        if (DB::getDriverName() == 'pgsql') {
            DB::statement('ALTER TABLE horarios_atencion ADD CONSTRAINT horarios_dia_valido CHECK (dia_semana BETWEEN 1 AND 7)');
            DB::statement('ALTER TABLE horarios_atencion ADD CONSTRAINT horarios_rango_valido CHECK (hora_fin > hora_inicio)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios_atencion');
    }
};
