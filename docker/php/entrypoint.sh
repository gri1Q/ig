#!/usr/bin/env bash
set -euo pipefail

# --- Функции wait ---
wait_for_port() {
  host="$1"
  port="$2"
  timeout=${3:-60}
  echo "Waiting for $host:$port (timeout ${timeout}s)..."
  for i in $(seq 1 $timeout); do
    if (echo > /dev/tcp/"$host"/"$port") >/dev/null 2>&1; then
      echo "$host:$port is available"
      return 0
    fi
    sleep 1
  done
  echo "Timed out waiting for $host:$port"
  return 1
}

# --- Переменные по умолчанию ---
: "${DB_HOST:=db}"
: "${DB_PORT:=3306}"
: "${REDIS_HOST:=redis}"
: "${REDIS_PORT:=6379}"
: "${APP_ENV:=local}"
: "${APP_KEY:=}"

# --- Ждем сервисы ---
wait_for_port "$DB_HOST" "$DB_PORT" 120 || echo "Warning: DB port wait timed out"
wait_for_port "$REDIS_HOST" "$REDIS_PORT" 60 || echo "Warning: Redis port wait timed out"

cd /var/www/html

# --- Устанавливаем зависимости (если нет vendor) ---
if [ ! -d vendor ] || [ ! -f vendor/autoload.php ]; then
  echo "Installing composer dependencies..."
  # Если нужно: в CI/prod можно использовать --no-dev
  if [ "$APP_ENV" = "production" ]; then
    composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev
  else
    composer install --no-interaction --prefer-dist --optimize-autoloader
  fi

  # дать права на vendor чтобы www-data мог читать
  chown -R www-data:www-data vendor || true
else
  echo "Vendor already exists — пропускаем composer install"
fi

# --- Генерация APP_KEY если нужно ---
if [ -z "${APP_KEY}" ]; then
  echo "APP_KEY пустой — генерируем..."
  php artisan key:generate --force || true
else
  echo "APP_KEY задан — пропускаем генерацию"
fi

# --- Права на storage/cache ---
echo "Устанавливаем права на storage и bootstrap/cache..."
mkdir -p storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R 775 storage bootstrap/cache || true

# --- Запуск миграций и сидов (только в локальной среде или если нужно) ---
# чтобы не выполнять миграции на каждом рестарте, можно использовать маркер .initialized
if [ "$APP_ENV" = "local" ]; then
  if [ ! -f .initialized ]; then
    echo "Запуск миграций..."
    php artisan migrate --force --no-interaction || { echo "Migrations failed"; }

    echo "Запуск сидов..."
    php artisan db:seed --force --no-interaction || echo "Seeds failed (ignored)"

    # Создаём маркер успешной инициализации
    touch .initialized
    chown www-data:www-data .initialized || true
  else
    echo ".initialized найден — пропускаем миграции и сиды"
  fi
else
  echo "APP_ENV != local — пропускаем автоматический запуск миграций/сидов"
fi

# --- Запускаем основной процесс (php-fpm) ---
exec "$@"
