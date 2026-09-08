# SPEC-001 Checkpoint F Report

## Status

`IN PROGRESS - REMOTE CI PENDING`

Checkpoint F implementa el workflow de calidad y la auditoria final. La aceptación técnica permanece pendiente hasta verificar el run remoto del commit publicado.

## Workflow

Archivo:

```text
.github/workflows/quality.yml
```

Incluye jobs paralelos:

- `backend`
- `frontend`

Triggers:

- push a `main` y ramas `feat/**`;
- pull requests.

Permisos:

```yaml
contents: read
```

No contiene deployment ni secretos de producción.

## Backend CI

- Ubuntu latest.
- PHP 8.3.
- `shivammathur/setup-php@v2`.
- Composer install desde lockfile.
- MySQL 8.4 service.
- Database `agenda_estetica_test`.
- User CI dedicado.
- `php artisan key:generate` efímero.
- migrations sobre la DB test.
- Composer validate/audit.
- Pint check.
- Larastan.
- Pest mediante `php artisan test`.

## Frontend CI

- Ubuntu latest.
- Node 20.20.2.
- `actions/setup-node@v7`.
- `npm ci`.
- ESLint.
- `vue-tsc --noEmit`.
- Vitest.
- production build.
- npm audit.

## Local validation

Local gates passed before publication:

```text
composer validate --strict       PASS
composer audit                   PASS
vendor/bin/pint --test           PASS
vendor/bin/phpstan analyse       PASS
php artisan test                 PASS
npm run lint                     PASS
npm run typecheck                PASS
npm run test                     PASS
npm run build                    PASS
npm audit                        PASS
```

## Remaining verification

El workflow debe ejecutarse remotamente después del push del commit que lo contiene. No se declarará CI remoto como verde antes de inspeccionar ambos jobs.
