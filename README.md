# Sistema de citas para un consultorio odontológico

Caso 3 · Laravel + PostgreSQL. Tablas: `odontologos`, `horarios_atencion`, `pacientes`, `historiales_clinicos`, `citas`, `tratamientos`, `pagos`.

## Puesta en marcha

```bash
composer install
cp .env.example .env
php artisan key:generate
# crear la base de datos "consultorio" en PostgreSQL y ajustar DB_* en .env
php artisan migrate:fresh --seed
php artisan tinker
```

Las 10 consultas de Tinker están en `docs/consultas/q01.php` … `q10.php`.
Para ejecutar una: `php artisan tinker --execute="$(cat docs/consultas/q01.php)"`
Para ejecutar las 10 y guardar la salida de cada una en `docs/salidas/`: `bash docs/ejecutar_consultas.sh`

| Consulta | Qué demuestra |
|---|---|
| q01 | Relaciones `hasMany` / `belongsTo`: odontólogos con sus horarios |
| q02 | Agenda de un odontólogo y bloques libres de 30 minutos |
| q03 | Integridad: unicidad (CI, emails, historial 1 a 1, tramo horario duplicado) |
| q04 | Integridad: claves foráneas (restrict, cascade, set null) |
| q05 | Integridad: reglas de negocio de la agenda (horario, bloques, cruces, odontólogo inactivo) |
| q06 | Integridad: CHECK y EXCLUDE de PostgreSQL, saltándose el modelo con `DB::table()` |
| q07 | Ciclo de vida de la cita: crear, confirmar, reprogramar, no asistida |
| q08 | Collections: `groupBy`, `countBy`, `filter`, `map`, `sortByDesc`, `pluck`, `sum` |
| q09 | Agregaciones: `count`, `sum`, `avg`, `max` y `having` |
| q10 | Estado de cuenta (`hasManyThrough`), saldos pendientes con `withSum` y Collections |

## Interfaz y acceso

- **Sitio público** (`/`): información general de la odontología (partes del diente, datos, especialidades, cuidado diario, preguntas frecuentes) e inicio de sesión.
- **Panel** (`/panel`): resumen del día. Con sesión iniciada se pueden registrar citas, ver pacientes y registrar más odontólogos (con su horario).
- Todas las rutas de citas, pacientes, odontólogos, horarios y pagos exigen sesión (`middleware auth`).
- Usuario creado por el seeder: `admin@consultorio.com` / `admin12345` (cambiar la contraseña antes de usar en producción).
- Estilos en `public/css/consultorio.css` (no requiere `npm run build`).

## Factories

| Factory | Qué genera |
|---|---|
| `OdontologoFactory` | Odontólogo con especialidad; estados `conHorarioCompleto()` e `inactivo()` |
| `HorarioAtencionFactory` | Tramo de atención de lunes a viernes (mañana o tarde) |
| `PacienteFactory` | Paciente con CI boliviana (7 dígitos + extensión), teléfono y dirección |
| `HistorialClinicoFactory` | Historial 1 a 1 con un paciente, con antecedentes variados |
| `CitaFactory` | Cita de un bloque dentro del horario; estados `confirmada()`, `atendida()`, `noAsistida()`, `reprogramada()` |
| `TratamientoFactory` | Tratamiento de una cita atendida, anotado en el historial de su paciente |
| `PagoFactory` | Pago de un paciente (monto, fecha y método) |

El seeder usa las factories para odontólogos, horarios, pacientes y pagos. Las citas y los tratamientos se crean con los métodos del modelo (`Cita::create`, `registrarTratamiento`) para que pasen por las reglas de negocio; sus factories se verifican en `tests/Feature/FactoriesTest.php`.

Pruebas de las reglas y de las factories: `php artisan test` (usan SQLite en memoria; las restricciones extra de PostgreSQL se omiten ahí).

## Cómo cumple cada requisito del enunciado

| Requisito | Dónde se cumple |
|---|---|
| Varios odontólogos con especialidad (ortodoncia, endodoncia, odontopediatría) | `odontologos.especialidad` (enum con CHECK) |
| Horario de atención por día de la semana | `horarios_atencion` (día 1–7 + tramos hora_inicio/hora_fin, varios tramos por día) |
| Pacientes con datos personales | `pacientes` (CI única, nombre, fecha de nacimiento, teléfono, email, dirección) |
| Historial clínico con tratamientos de cada cita y su costo | `historiales_clinicos` (1 a 1 con el paciente, se crea solo) + `tratamientos` (cita, historial, descripción, costo) |
| Citas en bloques de 30 minutos | `Cita::BLOQUE_MINUTOS`; inicio en hh:00 o hh:30; `fin` se calcula como inicio + N bloques (1 a 4); CHECK en BD |
| Solo dentro del horario del odontólogo | `Odontologo::atiendeEn()` se valida en `Cita::validarAgenda()` |
| Sin cruces | Validación en el modelo (odontólogo y paciente) + restricciones `EXCLUDE USING gist` en PostgreSQL |
| Confirmar | `Cita::confirmar()` (agendada/reprogramada → confirmada) |
| Reprogramar | `Cita::reprogramar($nuevoInicio)` revalida todo, guarda `inicio_original` y cuenta `reprogramaciones` |
| Marcar como no asistida | `Cita::marcarNoAsistida()` (solo si la cita ya ocurrió); libera el bloque |
| Estado de cuenta por paciente | `Paciente::estadoDeCuenta()` → detalle, `total_tratamientos`, `total_pagos` y `saldo` |

## Estados de una cita

`agendada` → `confirmada` → `atendida` (al registrar el primer tratamiento).
`agendada`/`confirmada` → `reprogramada` (vuelve a confirmarse con `confirmar()`).
`agendada`/`confirmada`/`reprogramada` → `no_asistida` (el bloque queda libre).

Las reglas lanzan `DomainException` con un mensaje claro, igual que en el proyecto anterior.

## Interfaz web (controllers, vistas y form requests)

Para verla: `php artisan serve` y abrir http://127.0.0.1:8000 (la página de inicio es una portada con las especialidades y el botón para agendar).

| Pantalla | Ruta | Controller |
|---|---|---|
| Portada (presentación y acceso a agendar) | `/` | `InicioController@index` |
| Citas (filtros por fecha, odontólogo y estado) | `/citas` | `CitaController@index` |
| Nueva cita (elegir odontólogo y fecha, luego un bloque libre) | `/citas/create` | `CitaController@create`, `@store` |
| Detalle de la cita: confirmar, reprogramar, no asistida, tratamientos | `/citas/{cita}` | `CitaController`, `TratamientoController` |
| Pacientes (búsqueda, alta, edición, baja) | `/pacientes` | `PacienteController` |
| Ficha del paciente con estado de cuenta y registro de pagos | `/pacientes/{paciente}` | `PacienteController@show`, `PagoController@store` |
| Odontólogos y su horario de atención | `/odontologos` | `OdontologoController`, `HorarioAtencionController` |

Form requests (`app/Http/Requests`): `StorePacienteRequest`, `UpdatePacienteRequest`, `StoreOdontologoRequest`, `UpdateOdontologoRequest`, `StoreHorarioRequest`, `StoreCitaRequest`, `ReprogramarCitaRequest`, `StoreTratamientoRequest` y `StorePagoRequest`. Validan los datos del formulario antes de llegar al modelo; las reglas de negocio del modelo (`DomainException`) y las claves foráneas de la base de datos (`QueryException`) se muestran como mensajes en pantalla. Los textos de validación están en `lang/es/validation.php`.

Pruebas de la interfaz: `php artisan test --filter=WebTest`
