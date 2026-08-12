#!/bin/sh
# Bootstraps the dev container so the host only needs Docker: installs PHP
# dependencies, prepares .env, waits for MySQL, then migrates (and seeds a
# fresh database) before handing off to php-fpm.
set -e

cd /var/www/html

log() {
    echo "[dev-entrypoint] $*"
}

if [ ! -f .env ]; then
    log "Creating .env from .env.example"
    cp .env.example .env
fi

# Reinstall when vendor/ is missing or composer.lock changed since the last install.
LOCK_HASH_FILE="vendor/.composer-lock-hash"
CURRENT_LOCK_HASH="$(md5sum composer.lock 2>/dev/null | cut -d' ' -f1)"

if [ ! -f vendor/autoload.php ] || [ "$(cat "$LOCK_HASH_FILE" 2>/dev/null)" != "$CURRENT_LOCK_HASH" ]; then
    log "Installing PHP dependencies (this takes a minute on first run)"
    composer install --no-interaction --prefer-dist
    echo "$CURRENT_LOCK_HASH" > "$LOCK_HASH_FILE"
fi

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views \
    storage/logs bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache 2>/dev/null || true

if ! grep -qE '^APP_KEY=.+' .env; then
    log "Generating application key"
    php artisan key:generate --force
fi

# depends_on marks MySQL healthy as soon as it answers pings, which can precede the
# database/user actually being created on a fresh volume. Connect through artisan so
# credentials come from .env rather than the container environment.
log "Waiting for MySQL"
attempt=0
until php artisan db:show >/dev/null 2>&1; do
    attempt=$((attempt + 1))
    if [ "$attempt" -ge 60 ]; then
        log "MySQL did not become available; skipping migrations"
        exec docker-php-entrypoint "$@"
    fi
    sleep 2
done

# A missing migrations table means this is a fresh database, so seed it once.
if php artisan migrate:status >/dev/null 2>&1; then
    php artisan migrate --force
else
    log "Fresh database detected: migrating and seeding"
    php artisan migrate --force --seed
fi

log "Ready"

exec docker-php-entrypoint "$@"
