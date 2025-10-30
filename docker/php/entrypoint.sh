#!/usr/bin/env bash
set -euo pipefail

cd /var/www/html

# Variables por defecto (puedes pasar APP_ENV, DB_HOST, etc desde docker-compose)
: "${APP_ENV:=local}"
: "${DB_HOST:=db}"
: "${DB_PORT:=3306}"
: "${DB_RETRY_MAX:=15}"
: "${DB_RETRY_SLEEP:=3}"
: "${USER_ID:=1000}"
: "${GROUP_ID:=1000}"
: "${XDEBUG_ENABLE:=0}"

echo "Entrypoint: APP_ENV=$APP_ENV DB_HOST=$DB_HOST DB_PORT=$DB_PORT"

# Alinea UID/GID del www-data dentro del contenedor si es distinto (útil para permisos con host)
if id -u www-data >/dev/null 2>&1; then
  echo "Ajustando UID/GID de www-data a ${USER_ID}:${GROUP_ID} (si es necesario)..."
  usermod -u "${USER_ID}" www-data || true
  groupmod -g "${GROUP_ID}" www-data || true
fi

# Si no existe .env copiar ejemplo
if [ ! -f .env ]; then
  if [ -f .env.example ]; then
    cp .env.example .env
    echo "Se creó .env desde .env.example"
  else
    echo "No existe .env ni .env.example"
  fi
fi

# Instalar dependencias composer si falta composer.json o vendor
if [ -f composer.json ]; then
  echo "Composer: comprobando dependencias..."
  # preferir instalar sin --no-dev en dev
  if [ "$APP_ENV" = "production" ]; then
    composer install --no-dev --optimize-autoloader --no-interaction || true
  else
    composer install --no-interaction --prefer-dist || true
  fi
fi

# Activar o desactivar xdebug según variable de entorno
if [ "${XDEBUG_ENABLE}" = "1" ]; then
  echo "Xdebug habilitado por XDEBUG_ENABLE=1"
  # si se usó pecl para instalar, normalmente ya está habilitado; aquí puedes incluir zend_extension si necesario
  # ejemplo: phpenmod xdebug || true
else
  echo "Xdebug deshabilitado"
  # phpdismod xdebug || true
fi

# Espera a que la DB esté disponible (intento simple)
wait_for_db() {
  n=0
  echo "Esperando a que la DB ${DB_HOST}:${DB_PORT} responda..."
  while ! (nc -z "${DB_HOST}" "${DB_PORT}" >/dev/null 2>&1); do
    n=$((n+1))
    if [ "$n" -ge "$DB_RETRY_MAX" ]; then
      echo "DB no disponible después de ${DB_RETRY_MAX} intentos. Continuando y dejando que artisan maneje errores."
      return 1
    fi
    echo "DB no responde. Reintentando en ${DB_RETRY_SLEEP}s... ($n/${DB_RETRY_MAX})"
    sleep "${DB_RETRY_SLEEP}"
  done
  echo "DB responde."
  return 0
}

wait_for_db || true

# Generar APP_KEY si no existe en .env o en entorno
if [ -z "${APP_KEY:-}" ] || grep -q '^APP_KEY=$' .env 2>/dev/null || ! grep -q '^APP_KEY=' .env 2>/dev/null; then
  echo "Generando APP_KEY..."
  php artisan key:generate --ansi --force || true
else
  echo "APP_KEY ya presente"
fi

# Si existe el comando jwt:secret lo ejecutamos (si no, ignoramos)
if php artisan | grep -q 'jwt:secret'; then
  echo "Generando jwt:secret..."
  php artisan jwt:secret --force || true
fi

# Reintentar migraciones hasta un máximo (esto evita que el contenedor muera antes de que la DB esté lista)
migrate_with_retries() {
  n=0
  until php artisan migrate --force --no-interaction; do
    n=$((n+1))
    if [ "$n" -ge "$DB_RETRY_MAX" ]; then
      echo "No se pudo ejecutar migrate después de ${DB_RETRY_MAX} intentos. Salida con advertencia."
      return 1
    fi
    echo "Migración falló. Reintentando en ${DB_RETRY_SLEEP} segundos... ($n/${DB_RETRY_MAX})"
    sleep "${DB_RETRY_SLEEP}"
  done
  return 0
}

migrate_with_retries || echo "Advertencia: migraciones fallaron o DB no respondió — revisa manualmente."

# Crear storage symlink
php artisan storage:link || true

# Asegurar permisos mínimos en storage y cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache || true

# Ejecutar el comando pasado al contenedor (por defecto php-fpm)
exec "$@"
