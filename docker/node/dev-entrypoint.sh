#!/bin/sh
# Keeps the Vite container's node_modules in sync with package-lock.json so the host only
# needs Docker. Mirrors docker/php/dev-entrypoint.sh, which does the same for composer.lock.
set -e

cd /var/www/html

log() {
    echo "[node-entrypoint] $*"
}

# Reinstall when node_modules is empty (fresh volume) or package-lock.json changed since
# the last install. The marker lives in the volume, so wiping it forces a clean install.
LOCK_HASH_FILE="node_modules/.package-lock-hash"
CURRENT_LOCK_HASH="$(md5sum package-lock.json 2>/dev/null | cut -d' ' -f1)"

if [ ! -d node_modules/.bin ] || [ "$(cat "$LOCK_HASH_FILE" 2>/dev/null)" != "$CURRENT_LOCK_HASH" ]; then
    log "Installing npm dependencies (this takes a minute on first run)"
    npm ci
    echo "$CURRENT_LOCK_HASH" > "$LOCK_HASH_FILE"
fi

log "Ready"

exec "$@"
