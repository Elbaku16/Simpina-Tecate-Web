#!/usr/bin/env bash
set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "$0")" && pwd)"
APP_PORT="${APP_PORT:-8000}"

if ! command -v php >/dev/null 2>&1; then
    echo "Falta PHP. Instálalo con: brew install php"
    exit 1
fi

if ! command -v mysqladmin >/dev/null 2>&1; then
    echo "Falta MySQL. Instálalo con: brew install mysql"
    exit 1
fi

if ! mysqladmin ping --silent >/dev/null 2>&1; then
    echo "Iniciando MySQL..."
    brew services start mysql

    for _ in {1..10}; do
        mysqladmin ping --silent >/dev/null 2>&1 && break
        sleep 1
    done
fi

if ! mysqladmin ping --silent >/dev/null 2>&1; then
    echo "MySQL no respondió. Prueba: brew services restart mysql"
    exit 1
fi

echo "SIMPINNA disponible en http://localhost:${APP_PORT}"
echo "Panel administrativo: http://localhost:${APP_PORT}/front-end/frames/admin/login.php"
exec php -S "localhost:${APP_PORT}" -t "$PROJECT_ROOT" "$PROJECT_ROOT/router.php"
