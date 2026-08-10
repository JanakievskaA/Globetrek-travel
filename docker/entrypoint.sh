#!/bin/sh
#
# First-run setup, made idempotent so `docker compose up` works on a clean
# machine and again on every restart afterwards.
#
# Everything that must outlive the container lives in /app/storage, which is a
# named volume: the SQLite file and .env (so the generated APP_KEY — and with
# it every existing session and encrypted cookie — stays valid across restarts).

set -e

PERSIST=/app/storage
ENV_FILE="$PERSIST/.env"

# .env on the volume, symlinked into place. Laravel only looks at /app/.env.
if [ ! -f "$ENV_FILE" ]; then
    echo "[entrypoint] creating .env from .env.example"
    cp /app/.env.example "$ENV_FILE"
fi
ln -sf "$ENV_FILE" /app/.env

if ! grep -q '^APP_KEY=base64:' "$ENV_FILE"; then
    echo "[entrypoint] generating application key"
    php artisan key:generate --force --no-interaction
fi

# Settings have to be written into .env, not merely exported: `artisan serve`
# forwards only a small whitelist of variables to the PHP server process it
# spawns, so a container-level DB_DATABASE reaches artisan but never a request.
set_env() {
    key="$1"
    value="$2"
    if grep -q "^${key}=" "$ENV_FILE"; then
        sed -i "s|^${key}=.*|${key}=${value}|" "$ENV_FILE"
    else
        printf '%s=%s\n' "$key" "$value" >> "$ENV_FILE"
    fi
}

set_env DB_CONNECTION "${DB_CONNECTION:-sqlite}"
set_env DB_DATABASE "${DB_DATABASE:-$PERSIST/database.sqlite}"
[ -n "${APP_URL:-}" ] && set_env APP_URL "$APP_URL"

# Stale caches from a previous image build would pin old config and views.
php artisan config:clear --no-interaction >/dev/null 2>&1 || true
php artisan view:clear --no-interaction >/dev/null 2>&1 || true

DATABASE="${DB_DATABASE:-$PERSIST/database.sqlite}"
FRESH=0
if [ ! -f "$DATABASE" ]; then
    echo "[entrypoint] creating SQLite database at $DATABASE"
    mkdir -p "$(dirname "$DATABASE")"
    touch "$DATABASE"
    FRESH=1
fi

if [ "$FRESH" = "1" ]; then
    echo "[entrypoint] migrating and seeding"
    php artisan migrate --seed --force --no-interaction
else
    echo "[entrypoint] applying any new migrations"
    php artisan migrate --force --no-interaction
fi

# Uploaded images are served through public/storage -> storage/app/public.
# public/ is image content rather than a volume, so this is re-made every boot.
# --relative keeps the link valid whichever root the project sits under, which
# matters because the same checkout is also mounted at /var/www/html by Sail.
php artisan storage:link --relative --force --no-interaction >/dev/null

echo "[entrypoint] ready"

exec "$@"
