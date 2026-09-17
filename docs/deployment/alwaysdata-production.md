# ALWAYSDATA PRODUCTION DEPLOYMENT

AgendaEstetica / Salón y Barbería Yaris

This document records the **real production deployment** completed on
`alwaysdata`. It is the source of truth for the current production topology
and the baseline for the automated deployment work defined in
`docs/specs/SPEC-023-automated-production-deployment.md`.

No secret value (`APP_KEY`, `DB_PASSWORD`, private SSH keys) is documented in
this repository. Secrets live exclusively in the server environment and in the
alwaysdata panel / GitHub Environments.

## Identity migration note

```text
Initial production identity: manrique
Current production identity: yaris
Changed: 2026-09-17 (alwaysdata account/site rename)
```

The alwaysdata account/site identity was renamed from `manrique` to `yaris`.
alwaysdata automatically renamed the account-prefixed technical resources
(SSH user/host, HOME, MySQL host/database/user, site address and effective
DocumentRoot). This document describes the **current post-rename** values.
Section 19 preserves the initial deployment baseline and Section 20 records the
results obtained under the initial identity.

## 1. Hosting

| Item | Value |
| --- | --- |
| Provider | alwaysdata |
| Account | `yaris` |
| Production URL | `https://yaris.alwaysdata.net` |

## 2. SSH

| Item | Value |
| --- | --- |
| SSH user | `yaris` |
| SSH host | `ssh-yaris.alwaysdata.net` |
| SSH port | `22` |
| HOME | `/home/yaris` |
| Shell | `bash` |
| Authentication | SSH key |

SSH authentication with a dedicated ED25519 key against
`ssh-yaris.alwaysdata.net` was verified after the account rename. The host
fingerprint presented by the renamed endpoint matches the alwaysdata host key
verified during the initial deployment (same SSH server, renamed identity).

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
| Application root | `/home/yaris/apps/agenda-estetica` |
| Laravel public directory | `/home/yaris/apps/agenda-estetica/public` |

### WARNING — alwaysdata Root directory is RELATIVE to the account HOME

In alwaysdata Admin → **Web → Sites** the **Root directory** field is joined
under the account home automatically.

Correct value to enter in the panel:

```text
apps/agenda-estetica/public
```

Do **NOT** enter:

```text
/home/yaris/apps/agenda-estetica/public
```

An absolute path causes alwaysdata to double the prefix and produce the
incorrect effective path:

```text
/home/yaris/home/yaris/apps/agenda-estetica/public
```

This misconfiguration was encountered during the initial deployment and fixed
by using the relative value. The relative value is **reused unchanged** after
the rename because HOME changed from `/home/manrique` to `/home/yaris` but the
relative location under HOME stayed `apps/agenda-estetica/public`.

## 4. alwaysdata Web Site

Panel: **Web → Sites**.

| Setting | Value |
| --- | --- |
| Address | `yaris.alwaysdata.net` |
| Type | PHP |
| Root directory | `apps/agenda-estetica/public` |
| Effective DocumentRoot | `/home/yaris/apps/agenda-estetica/public` |
| PHP | 8.3 |
| HTTPS | enabled |

The effective `DocumentRoot` can be verified in the generated vhost config:

```text
/home/yaris/admin/config/apache/sites.conf
```

Expected line: `DocumentRoot "/home/yaris/apps/agenda-estetica/public/"`.

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
| Host | `mysql-yaris.alwaysdata.net` |
| Port | `3306` |
| Database | `yaris_agenda_estetica` |
| User | `yaris` |
| Permissions | all rights on `yaris_agenda_estetica` |

The database, its name and its user were renamed automatically by alwaysdata
from `manrique_agenda_estetica`/`manrique` to `yaris_agenda_estetica`/`yaris`.
The stored password was preserved by alwaysdata and is **never** documented. It
exists only as a production secret in the server `.env` (and, if automation is
implemented later, in the GitHub Environment secret store).

## 7. Production .env

The production `.env` is created **only on the server**. The local `.env` is
**never** uploaded.

Confirmed values (post-rename):

```text
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yaris.alwaysdata.net

DB_CONNECTION=mysql
DB_HOST=mysql-yaris.alwaysdata.net
DB_PORT=3306
DB_DATABASE=yaris_agenda_estetica
DB_USERNAME=yaris

SANCTUM_STATEFUL_DOMAINS=yaris.alwaysdata.net
SESSION_SECURE_COOKIE=true
```

- `APP_KEY`: configured; the value is **never** documented. **Never** rotate it
  during an identity/hostname migration.
- `DB_PASSWORD`: configured; the value is **never** documented. The rename
  preserved the password; it was **not** regenerated.

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
/home/yaris/apps/agenda-estetica
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
cd /home/yaris/apps/agenda-estetica && php artisan schedule:run
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

Production verification suite (performed after the initial deployment and
re-verified after the account rename):

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

Sensitive resources (`.env`, `composer.json`, `composer.lock`, logs, `.git`,
cached config) must never expose their actual file contents.

## 19. First production deployment reference

| Item | Value |
| --- | --- |
| Branch | `feat/spec-022-academic-phase-1` |
| Commit | `4830ab4a` |
| Deployed under identity | `manrique` (pre-rename) |

This records the initial deployed baseline **only**. It does not mean future
production deployments should keep deploying from that feature branch.
Production deployments originate from the approved production branch defined
by the Git governance (see AGENTS.md and SPEC-023). After the rename the same
application data continued to be served by the same code under the new `yaris`
identity without an application release.

## 20. Initial production deployment result

All checks passed under the initial identity:

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

After the 2026-09-17 rename the same smoke suite passed again under
`https://yaris.alwaysdata.net`, the existing production database connected
successfully with the preserved `APP_KEY` and `DB_PASSWORD`, and no pending
migrations appeared (13/13 `Ran`, batch 1, unchanged).