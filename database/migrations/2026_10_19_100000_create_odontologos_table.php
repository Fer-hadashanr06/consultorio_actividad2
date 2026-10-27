<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('odontologos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 60);
            $table->string('apellido', 60);
            $table->string('email')->unique();
            // enum en PostgreSQL = columna con restricción CHECK
            $table->enum('especialidad', ['ortodoncia', 'endodoncia', 'odontopediatria']);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->index('especialidad');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('odontologos');
    }
};
