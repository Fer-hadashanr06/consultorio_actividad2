<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tratamientos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cita_id')->constrained('citas')->restrictOnDelete();
            $table->foreignId('historial_clinico_id')->constrained('historiales_clinicos')->restrictOnDelete();
            $table->string('descripcion', 150);
            $table->decimal('costo', 10, 2);                      // Bs.
            $table->timestamps();

            $table->index('cita_id');
            $table->index('historial_clinico_id');
        });

        if (DB::getDriverName() == 'pgsql') {
            DB::statement('ALTER TABLE tratamientos ADD CONSTRAINT tratamientos_costo_valido CHECK (costo >= 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tratamientos');
    }
};
