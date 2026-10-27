<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\HorarioAtencionController;
use App\Http\Controllers\InicioController;
use App\Http\Controllers\OdontologoController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\TratamientoController;
use Illuminate\Support\Facades\Route;

// ---- Zona pública: información general del consultorio e inicio de sesión ----
Route::get('/', [InicioController::class, 'index'])->name('inicio');
Route::get('/login', [AuthController::class, 'mostrarLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:8,1')->name('login.enviar');

// ---- Zona privada: solo con sesión iniciada ----
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/panel', [PanelController::class, 'index'])->name('panel');

    // Pacientes (con su historial y estado de cuenta) y sus pagos
    Route::resource('pacientes', PacienteController::class);
    Route::post('pacientes/{paciente}/pagos', [PagoController::class, 'store'])->name('pagos.store');

    // Odontólogos y su horario de atención
    Route::resource('odontologos', OdontologoController::class);
    Route::post('odontologos/{odontologo}/horarios', [HorarioAtencionController::class, 'store'])->name('horarios.store');
    Route::delete('horarios/{horario}', [HorarioAtencionController::class, 'destroy'])->name('horarios.destroy');

    // Citas y su ciclo de vida
    Route::resource('citas', CitaController::class)->only(['index', 'create', 'store', 'show']);
    Route::post('citas/{cita}/confirmar', [CitaController::class, 'confirmar'])->name('citas.confirmar');
    Route::put('citas/{cita}/reprogramar', [CitaController::class, 'reprogramar'])->name('citas.reprogramar');
    Route::post('citas/{cita}/no-asistida', [CitaController::class, 'noAsistida'])->name('citas.no-asistida');
    Route::post('citas/{cita}/tratamientos', [TratamientoController::class, 'store'])->name('tratamientos.store');
});
