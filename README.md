# My Workouts Online

![tests](https://github.com/mwazovzky/my-workouts-online/actions/workflows/tests.yml/badge.svg?branch=main)
![code style](https://github.com/mwazovzky/my-workouts-online/actions/workflows/code-style.yml/badge.svg?branch=main)
![lint](https://github.com/mwazovzky/my-workouts-online/actions/workflows/lint.yml/badge.svg?branch=main)
[![backend coverage](https://codecov.io/github/mwazovzky/my-workouts-online/graph/badge.svg?flag=backend)](https://app.codecov.io/github/mwazovzky/my-workouts-online)
[![frontend coverage](https://codecov.io/github/mwazovzky/my-workouts-online/graph/badge.svg?flag=frontend)](https://app.codecov.io/github/mwazovzky/my-workouts-online)

> Product docs, features & architecture: [docs/](docs/README.md)

## Local development

### Run with Docker Compose (recommended)

**Prerequisites:** Docker + Docker Compose. Nothing else — PHP, Composer and Node all live in
containers, so no host toolchain is needed.

```bash
docker compose up -d --build
```

That's the whole setup. On start the `app` container bootstraps itself: creates `.env` from
`.env.example` if missing, runs `composer install` (re-running whenever `composer.lock` changes),
generates `APP_KEY`, waits for MySQL, then migrates — seeding as well when the database is fresh.
The `node` container does the same for `npm ci` whenever `package-lock.json` changes, so pulling a
branch with new dependencies needs no manual install — just restart. Follow along with
`docker compose logs -f app` or `docker compose logs -f node`.

Then open:

- App: `http://localhost:8080` (from `APP_PORT`)
- Vite dev server: `http://localhost:5173`

This mirrors the production compose topology (nginx + php-fpm + mysql) while keeping code
bind-mounted for live edits. `vendor/` is bind-mounted too, so IDE autocompletion works on the host.

### Running commands

Backend commands run in the `app` container, frontend commands in the `node` container:

```bash
docker compose exec app php artisan migrate      # Artisan
docker compose exec app composer install         # Composer
docker compose exec app php artisan test         # Backend tests
docker compose exec app ./vendor/bin/pint        # PHP formatting

docker compose exec node npm run test:frontend   # Frontend tests
docker compose exec node npm run lint:fix        # ESLint
docker compose exec node npm run format          # Prettier
```

Shell aliases such as `alias art='docker compose exec app php artisan'` make this less verbose.

The `app` service deliberately has no `env_file`: the bind-mounted `.env` is read by Laravel
directly. Injecting those values as container environment variables would shadow the `<env>`
overrides in `phpunit.xml`, making the test suite run against the dev MySQL database instead of
in-memory SQLite.

### Run with Artisan

Requires PHP 8.4 and Composer installed on the host:

```bash
composer install
php artisan serve
```

## Documentation

- [Product overview](docs/product.md)
- [Feature docs](docs/README.md)
- [Architecture](docs/architecture.md)
- [Pages & routes](docs/pages-and-routes.md)
- [Deployment runbook](docs/deployment.md)

## Production

Deployment and redeploy instructions live in [docs/deployment.md](docs/deployment.md).

### Linting / formatting

**Backend (PHP):**
```bash
./vendor/bin/pint
```

**Frontend (JavaScript/Vue):**
```bash
npm run lint        # Check for issues
npm run lint:fix    # Auto-fix issues
npm run format      # Format code with Prettier
```

## Database diagram

https://dbdiagram.io/d/workouts-68a1ae421d75ee360ae77ad8
