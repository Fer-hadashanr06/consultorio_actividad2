<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->restrictOnDelete();
            $table->foreignId('cita_id')->nullable()->constrained('citas')->nullOnDelete();
            $table->decimal('monto', 10, 2);                      // Bs.
            $table->date('fecha_pago')->useCurrent();
            $table->enum('metodo', ['efectivo', 'tarjeta', 'transferencia'])->default('efectivo');
            $table->timestamps();

            $table->index('paciente_id');
        });

        if (DB::getDriverName() == 'pgsql') {
            DB::statement('ALTER TABLE pagos ADD CONSTRAINT pagos_monto_positivo CHECK (monto > 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
