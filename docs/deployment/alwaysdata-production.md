# ALWAYSDATA PRODUCTION DEPLOYMENT

AgendaEstetica / Salón y Barbería Yaris

This document records the **real production deployment** completed on
`alwaysdata`. It is the source of truth for the current production topology
and the baseline for the automated deployment work defined in
`docs/specs/SPEC-023-automated-production-deployment.md`.

No secret value (`APP_KEY`, `DB_PASSWORD`, private SSH keys) is documented in
this repository. Secrets live exclusively in the server environment and in the
alwaysdata panel / GitHub Environments.

## 1. Hosting

| Item | Value |
| --- | --- |
| Provider | alwaysdata |
| Account | `manrique` |
| Production URL | `https://manrique.alwaysdata.net` |

## 2. SSH

| Item | Value |
| --- | --- |
| SSH user | `manrique` |
| SSH host | `ssh-manrique.alwaysdata.net` |
| SSH port | `22` |
| HOME | `/home/manrique` |
| Shell | `bash` |
| Authentication | SSH key |

SSH authentication with a dedicated ED25519 key against
`ssh-manrique.alwaysdata.net` was verified during the initial deployment.

Rules:

- **Never** document the content of a private key.
- **Never** include passwords.
- Use **independent keys per function**:
  - `human/admin deployment key` — interactive administration.
  - `CI/CD deployment key` — GitHub Actions deployment (see SPEC-023).

### First-time SSH login note (alwaysdata)

On alwaysdata, SSH key authentication only works after a first connection with
the account password from the panel/console. Document this operational detail
only; the password itself is never stored.

## 3. Application deployment path

| Item | Path |
| --- | --- |
| Application root | `/home/manrique/apps/agenda-estetica` |
| Laravel public directory | `/home/manrique/apps/agenda-estetica/public` |

### WARNING — alwaysdata Root directory is RELATIVE to the account HOME

In alwaysdata Admin → **Web → Sites** the **Root directory** field is joined
under the account home automatically.

Correct value to enter in the panel:

```text
apps/agenda-estetica/public
```

Do **NOT** enter:

```text
/home/manrique/apps/agenda-estetica/public
```

An absolute path causes alwaysdata to double the prefix and produce the
incorrect effective path:

```text
/home/manrique/home/manrique/apps/agenda-estetica/public
```

This exact misconfiguration was encountered during the initial deployment and
fixed by using the relative value.

## 4. alwaysdata Web Site

Panel: **Web → Sites**.

| Setting | Value |
| --- | --- |
| Address | `manrique.alwaysdata.net` |
| Type | PHP |
| Root directory | `apps/agenda-estetica/public` |
| Effective DocumentRoot | `/home/manrique/apps/agenda-estetica/public` |
| PHP | 8.3 |
| HTTPS | enabled |

The effective `DocumentRoot` can be verified in the generated vhost config:

```text
/home/manrique/admin/config/apache/sites.conf
```

Expected line: `DocumentRoot "/home/manrique/apps/agenda-estetica/public/"`.

## 5. PHP / runtime

Deployment verified with:

- PHP 8.3.x
- Composer 2.x

The project **must follow `composer.lock`**; productions installs with:

```text
composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader
```

Never run `composer update` during a deployment.

Vendor directory may be installed server-side from `composer.lock` and must be
excluded from file synchronization.

## 6. Database

| Item | Value |
| --- | --- |
| Engine | MariaDB 11.x |
| Laravel driver | `mysql` |
| Host | `mysql-manrique.alwaysdata.net` |
| Port | `3306` |
| Database | `manrique_agenda_estetica` |
| User | `manrique` |
| Permissions | all rights on `manrique_agenda_estetica` |

`DB_PASSWORD` is **never** documented. It exists only as a production secret in
the server `.env` (and, if automation is implemented later, in the GitHub
Environment secret store).

## 7. Production .env

The production `.env` is created **only on the server**. The local `.env` is
**never** uploaded.

Confirmed values:

```text
APP_ENV=production
APP_DEBUG=false
APP_URL=https://manrique.alwaysdata.net

DB_CONNECTION=mysql
DB_HOST=mysql-manrique.alwaysdata.net
DB_PORT=3306
DB_DATABASE=manrique_agenda_estetica
DB_USERNAME=manrique

SESSION_SECURE_COOKIE=true
```

- `APP_KEY`: configured; the value is **never** documented.
- `DB_PASSWORD`: configured; the value is **never** documented.

Preserve the drivers defined by the current project configuration; do not
replace them arbitrarily:

```text
SESSION_DRIVER
CACHE_STORE
QUEUE_CONNECTION
FILESYSTEM_DISK
```

## 8. Frontend

Vue/Vite production assets are built **before** deployment. Expected result:

```text
public/build/
```

Rules:

- `node_modules/` is **never** deployed.
- No Vite development server runs in production.
- `public/hot` must **not** exist.

## 9. File synchronization

The first deployment used a safe synchronization into:

```text
/home/manrique/apps/agenda-estetica
```

Never synchronized:

```text
.git/
.env
node_modules/
vendor/
runtime logs
development/test artifacts
```

The first deployment intentionally did **not** use `rsync --delete`.

Future CI/CD may evaluate `--delete` only with explicit exclusions that
protect persistent server-only files (see SPEC-023 rsync rules).

## 10. Permissions

Writable directories:

```text
storage/
bootstrap/cache/
```

Safe command:

```text
chmod -R u+rwX storage bootstrap/cache
```

Never use `chmod 777`.

## 11. Database migrations

Normal production deployment:

```text
php artisan migrate --force
```

Never:

```text
php artisan migrate:fresh
php artisan migrate:refresh
php artisan db:wipe
```

Seeders are not run automatically and development data is not imported.

## 12. Laravel optimization

After a successful deployment:

```text
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 13. Storage

`storage:link` is currently **not required**: the deployment audit found no
active `Storage::` usage in the application. Do not create unnecessary links.

## 14. Queue

`QUEUE_CONNECTION` may currently be `database`, but no permanent production
queue worker is required by active functionality.

Do not start random background workers with:

```text
nohup
screen
tmux
```

If a worker is required in the future, alwaysdata **Advanced → Services** must
be evaluated.

## 15. Scheduler

No production Laravel scheduled commands are currently required.

If scheduled tasks are required later, configure alwaysdata
**Advanced → Scheduled tasks** with a conceptually minute-based command:

```text
cd /home/manrique/apps/agenda-estetica && php artisan schedule:run
```

Do not configure scheduled tasks until actually required.

## 16. Mail

Current production setting:

```text
MAIL_MAILER=log
```

Real email delivery is **not** currently active. This is a pending production
integration if application features eventually require actual emails.

## 17. Security

Production must always maintain:

```text
APP_ENV=production
APP_DEBUG=false
```

Plus:

- HTTPS enabled.
- No exposed `.env`.
- No publicly accessible Composer files.
- No publicly accessible Laravel logs.
- No private SSH keys in the repository.
- No DB passwords in the repository.
- No `APP_KEY` in the repository.
- No localhost URLs.
- No Vite development server.
- No secrets in documentation.

## 18. Smoke tests

Production verification suite (performed during the initial deployment):

| Check | Expected |
| --- | --- |
| `/` | 200 |
| `/fase-1` | 200 |
| `/demo/pedido` | 200 |
| `/cliente/acceso` | 200 |
| `/reservar` | 200 |
| `/api/v1/health` | 200 JSON |
| unknown API route | 404 JSON |
| protected admin endpoint (anonymous) | 401 JSON |
| static academic invoice PDF | 200 `application/pdf` |
| Vite JS/CSS assets | 200, correct MIME |

Sensitive resources (`.env`, `composer.json`, logs, `.git`, cached config)
must never expose their actual file contents.

## 19. First production deployment reference

| Item | Value |
| --- | --- |
| Branch | `feat/spec-022-academic-phase-1` |
| Commit | `4830ab4a` |

This records the initial deployed baseline **only**. It does not mean future
production deployments should keep deploying from that feature branch.
Production deployments originate from the approved production branch defined
by the Git governance (see AGENTS.md and SPEC-023).

## 20. Initial production deployment result

All checks passed:

- Quality gates: PASS.
- Backend Pest: 208 tests / 1202 assertions.
- Frontend Vitest: 82 tests / 22 files.
- PHPStan: PASS.
- Pint: PASS.
- ESLint: PASS.
- Typecheck: PASS.
- `npm audit`: PASS.
- `composer audit`: PASS.
- Production build: PASS.
- MariaDB compatibility audit: PASS.
- Migrations: 13/13 PASS.
- HTTP smoke suite: PASS.
- HTTPS: PASS.