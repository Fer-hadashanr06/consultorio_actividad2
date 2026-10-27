#!/usr/bin/env bash
# Crea una rama NUEVA con el historial incremental de este proyecto (base, migraciones, modelos,
# factories, seeders, interfaz web, pruebas, consultas y documentos). No borra ni modifica la rama actual.
#
# Uso (desde la carpeta del proyecto, con git ya configurado con tu nombre y correo):
#   bash docs/historial_git.sh                  # crea la rama "actividad2-consultorio"
#   git push -u origin actividad2-consultorio   # la sube a GitHub
set -e

RAMA="${1:-actividad2-consultorio}"
git checkout --orphan "$RAMA"
git rm -rf --cached . -q

paso() {            # paso "mensaje" ruta1 ruta2 ...
  mensaje="$1"; shift
  for ruta in "$@"; do
    if [ -e "$ruta" ]; then git add -- "$ruta"; fi
  done
  git commit -q -m "$mensaje"
  echo "OK  $mensaje"
}

paso "chore: proyecto base Laravel 13 configurado para PostgreSQL" \
  .editorconfig .gitattributes .gitignore .npmrc .env.example artisan composer.json composer.lock \
  package.json phpunit.xml vite.config.js bootstrap config public resources/css resources/js routes/console.php \
  storage app/Providers app/Http/Controllers/Controller.php app/Models/User.php \
  database/.gitignore database/migrations/0001_01_01_000000_create_users_table.php \
  database/migrations/0001_01_01_000001_create_cache_table.php \
  database/migrations/0001_01_01_000002_create_jobs_table.php database/factories/UserFactory.php \
  tests/TestCase.php tests/Unit AGENTS.md CLAUDE.md

paso "feat: migraciones de las 7 tablas con claves, UNIQUE, CHECK, EXCLUDE e indices" database/migrations
paso "feat: modelos Eloquent con relaciones y reglas de negocio de la agenda" app/Models
paso "feat: factories para todos los modelos" database/factories
paso "feat: seeder con datos reproducibles del consultorio" database/seeders
paso "feat: form requests con validacion y mensajes en espanol" app/Http/Requests lang
paso "feat: controllers y rutas de pacientes, odontologos, citas, tratamientos y pagos" app/Http/Controllers app/Providers routes
paso "feat: vistas Blade de la interfaz web" resources/views
paso "test: pruebas de reglas de citas, factories e interfaz web" tests
paso "docs: 10 consultas de Tinker con scripts y salidas" docs/consultas docs/ejecutar_consultas.sh docs/salidas docs/salida_migraciones.txt docs/salida_seeder.txt docs/salida_tests.txt docs/salida_migrate_seed.txt
paso "docs: README e informe de la actividad" README.md docs/Actividad2_Consultorio.docx docs/historial_git.sh

# Cualquier archivo que haya quedado fuera de los pasos anteriores
if [ -n "$(git status --porcelain)" ]; then
  git add -A
  git commit -q -m "chore: archivos restantes del proyecto"
  echo "OK  chore: archivos restantes del proyecto"
fi

echo
git log --oneline
echo
echo "Listo. Ahora sube la rama:  git push -u origin $RAMA"
