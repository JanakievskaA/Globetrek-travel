#!/usr/bin/env bash
#
# Runs once per container start, before Apache. Everything here exists because
# a Render instance begins life with an empty writable filesystem: no database,
# no storage symlink, no caches.
set -euo pipefail

log() { echo "entrypoint: $*" >&2; }

# --- Port -------------------------------------------------------------------
# Render assigns the port and passes it as $PORT; the image is built listening
# on 80. Rewriting the config beats relying on Apache's ${VAR} expansion, which
# depends on the variable being exported into httpd's own environment.
PORT="${PORT:-10000}"
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:[0-9]\+>/<VirtualHost *:${PORT}>/" \
    /etc/apache2/sites-available/000-default.conf

# Silences the "could not reliably determine the server's fully qualified
# domain name" warning that would otherwise open every deploy log.
echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf
a2enconf servername > /dev/null

# --- Environment ------------------------------------------------------------
# Render publishes the service's public URL. Without it Laravel builds absolute
# links against localhost, which breaks anything that leaves a page body.
export APP_URL="${APP_URL:-${RENDER_EXTERNAL_URL:-http://localhost:${PORT}}}"

# A missing key would only surface as a 500 on the first request, which is a
# miserable way to find out. Booting on a throwaway key is better, but it is
# regenerated every deploy, so it logs out every signed-in visitor.
if [ -z "${APP_KEY:-}" ]; then
    export APP_KEY="base64:$(head -c 32 /dev/urandom | base64)"
    log "APP_KEY is not set — generated a throwaway key for this boot."
    log "Set APP_KEY in the Render dashboard to keep sessions across deploys."
fi

# --- Database ---------------------------------------------------------------
# The SQLite file is deliberately not in the image: it is gitignored, and this
# instance's disk is wiped on every deploy anyway. It gets built from the
# migrations and seeders instead, which makes each deploy a clean demo site.
DB_PATH="${DB_DATABASE:-/var/www/html/database/database.sqlite}"
if [ ! -f "$DB_PATH" ]; then
    log "No database at ${DB_PATH} — creating one."
    install -o www-data -g www-data -m 664 /dev/null "$DB_PATH"
fi

php artisan migrate --force --no-interaction

# Seed only into an empty catalogue. The seeders are not all idempotent: the
# category, destination, tour, user and page-section ones are updateOrCreate,
# but the review and booking seeders build rows from factories, so a second run
# against a database that survived doubles every review and every booking.
#
# Asked over PDO rather than through artisan because the table does not exist
# yet on a first boot, and a bare query is cheaper than booting the framework.
TOUR_COUNT="$(DB_PROBE_PATH="$DB_PATH" php -r '
    try {
        $pdo = new PDO("sqlite:" . getenv("DB_PROBE_PATH"));
        echo (int) $pdo->query("SELECT COUNT(*) FROM tours")->fetchColumn();
    } catch (Throwable $e) {
        echo 0;
    }
' 2>/dev/null || echo 0)"

if [ "$TOUR_COUNT" -eq 0 ] 2>/dev/null; then
    log "Empty database — seeding the demo catalogue."
    php artisan db:seed --force --no-interaction
else
    log "Database already holds ${TOUR_COUNT} tours — skipping the seeders."
fi

# --- Caches -----------------------------------------------------------------
# public/storage is gitignored, so uploaded images are unreachable until the
# symlink is recreated here. --force because a warm restart already has one.
php artisan storage:link --force

php artisan config:cache
php artisan route:cache
php artisan view:cache

# The commands above ran as root, so the caches and the log file they touched
# are root-owned. Apache runs as www-data and needs to write to all of them.
chown -R www-data:www-data storage bootstrap/cache "$(dirname "$DB_PATH")"

log "Ready on port ${PORT} — ${APP_URL}"

exec "$@"
