#!/bin/bash
# Renews the Let's Encrypt cert and reloads nginx.
# Installed onto the droplet by scripts/deploy-production.sh on every release.

set -euo pipefail

LOG=/home/deploy/renew-certs.log
exec >> "$LOG" 2>&1

echo "=== $(date -u '+%Y-%m-%dT%H:%M:%SZ') renewal run starting ==="

cd /home/deploy/my-workouts-online

docker run --rm \
  -v my-workouts-online_certbot_certs:/etc/letsencrypt \
  -v my-workouts-online_certbot_webroot:/var/www/certbot \
  certbot/certbot:latest renew --no-random-sleep-on-renew

docker compose -f docker-compose.prod.yml exec -T web nginx -s reload

echo "=== $(date -u '+%Y-%m-%dT%H:%M:%SZ') renewal run finished ==="
