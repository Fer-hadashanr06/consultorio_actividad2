#!/usr/bin/env bash
# Ejecuta las 10 consultas de Tinker y guarda cada salida en docs/salidas/qXX.txt
# Uso (desde la carpeta del proyecto):  bash docs/ejecutar_consultas.sh
mkdir -p docs/salidas
for f in docs/consultas/q*.php; do
  n=$(basename "$f" .php)
  echo "=== $n ==="
  php artisan tinker --execute="$(cat "$f")" 2>&1 | grep -v "Aliasing" | tee "docs/salidas/$n.txt"
  echo
done
