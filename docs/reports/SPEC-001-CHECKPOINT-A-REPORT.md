# SPEC-001 Checkpoint A Report

## Status

`COMPLETED`

Checkpoint A estableció el skeleton Laravel 13, el boot básico y la validación de migrations contra MySQL 8.4 LTS. El bloqueo inicial del entorno queda conservado en la sección histórica Checkpoint A.1.

## Repository

- Branch: `feat/spec-001-project-foundation`
- Base: `133b549 docs: align foundation with Laravel 13`
- Laravel resolved: `13.30.1`
- PHP: `8.3.19`
- Composer: `2.8.6`

## Initialization strategy

Se generó un skeleton temporal fuera del repositorio mediante Composer con la restricción `13.*`:

```text
composer create-project laravel/laravel <temporary-directory> "13.*" --no-interaction
```

El skeleton reportó:

```text
Laravel Framework 13.30.1
```

Después se inspeccionó y se copió selectivamente al root.

## Protected assets

Se preservaron:

- `.git/`
- `AGENTS.md`
- `docs/`

No se copiaron desde el temporal:

- `.git/`
- `.env`
- `vendor/`
- `node_modules/`
- `database/database.sqlite`
- `AGENTS.md`
- `docs/`

La base SQLite generada accidentalmente por la copia recursiva inicial fue eliminada antes de continuar.

## Files introduced

- Laravel application directories: `app/`, `bootstrap/`, `config/`, `database/`, `public/`, `resources/`, `routes/`, `storage/`, `tests/`
- `artisan`
- `composer.json`
- `composer.lock`
- `package.json`
- `phpunit.xml`
- `vite.config.js`
- `.editorconfig`
- `.gitattributes`
- `.gitignore`
- `.env.example`
- `README.md`

No Vue functionality, API routes, Sanctum, queue jobs, scheduler tasks or business models were added.

## Environment configuration

`.env.example` was adjusted to include only the technical Foundation baseline:

- `APP_TIMEZONE=UTC`
- `DB_CONNECTION=mysql`
- blank MySQL host, database, username and password values
- `SESSION_DRIVER=database`
- `CACHE_STORE=database`
- `QUEUE_CONNECTION=database`
- `FILESYSTEM_DISK=public`

Redis and AWS storage variables from the generic skeleton were removed. No real credentials were added.

`config/app.php` now reads the technical timezone from `APP_TIMEZONE` with UTC fallback.

## Composer

`composer validate --strict` passed.

`composer install --no-interaction --prefer-dist` passed using the generated lockfile. The resolved framework is Laravel 13.30.1.

No manual Sanctum, Pest plugin or Larastan packages were added in this checkpoint.

## Technical migrations

The Laravel 13 skeleton contains:

- `users`
- `password_reset_tokens`
- `sessions`
- `cache`
- `cache_locks`
- `jobs`
- `job_batches`
- `failed_jobs`

No business migrations were created.

## MySQL verification

The local environment did not provide usable MySQL credentials. The migration attempt produced:

```text
SQLSTATE[HY000] [1045] Access denied for user ''@'localhost' (using password: NO)
```

No migration completed and no SQLite fallback was used.

Required follow-up:

```text
DATABASE ENVIRONMENT BLOCKER
```

A valid MySQL 8.4 LTS server connection must be configured before declaring database migrations validated.

## Boot verification

Passed:

```text
php artisan --version
php artisan about
```

`php artisan about` confirmed:

- Laravel `13.30.1`
- PHP `8.3.19`
- Composer `2.8.6`
- timezone `UTC`
- database driver `mysql`
- cache driver `database`
- queue driver `database`
- session driver `database`

## Security audit

- `.env` is ignored and was not staged.
- `APP_KEY` exists only in the local ignored `.env`.
- `vendor/` is ignored and was not staged.
- `database/database.sqlite` was removed.
- No API tokens or production credentials were added.

## Scope audit

- Vue functionality: not implemented.
- Sanctum: not implemented.
- API v1: not implemented.
- Business models: not implemented.
- WhatsApp: not implemented.
- Appointments: not implemented.
- Ecommerce: not implemented.
- CI: not implemented.

## Checkpoint A.1 - Database Environment Resolution

### Previous blocker

Checkpoint A initially could not validate migrations because no usable MySQL 8.4 LTS credentials were available.

### MySQL version selected

MySQL 8.4 LTS was selected and documented in `ADR-002-adopt-mysql-8-4-lts.md`. The existing MySQL 9.2 installation was not modified or migrated.

### Environment case

Initial environment: `CASE C`.

Another MySQL server/version existed, but MySQL 8.4 was not installed or usable.

### Installation performed

```text
MySQL 8.4.11 installed through Homebrew as mysql@8.4.
```

It remains keg-only and was not force-linked. A separate datadir and local port `3307` were used to avoid the existing MySQL 9.2 datadir and port.

### Service status

MySQL 8.4 is running as a dedicated local process using:

```text
datadir: /opt/homebrew/var/mysql@8.4
port: 3307
socket: /tmp/mysql84.sock
```

The existing MySQL 9.2 service remains untouched.

### Database configuration

- Database: `agenda_estetica`
- User: dedicated non-root project user
- Credentials: configured only in local ignored `.env`
- Charset: `utf8mb4`
- MySQL server: `8.4.11`

Passwords are intentionally not recorded in this report.

### Migration result

Laravel connected successfully using the dedicated project user and executed all Foundation migrations:

```text
0001_01_01_000000_create_users_table  [1] Ran
0001_01_01_000001_create_cache_table  [1] Ran
0001_01_01_000002_create_jobs_table   [1] Ran
```

No business tables or records were created.

### Blocker resolution

The MySQL environment blocker is resolved for local Checkpoint A validation.

## SPEC status

SPEC-001 is `APPROVED FOR DEVELOPMENT`.

The overall SPEC is not complete. The roadmap reflects `IN PROGRESS`.

Checkpoint A is now `COMPLETED`.

## Next checkpoint

Checkpoint B must not start until this checkpoint is reviewed explicitly.
