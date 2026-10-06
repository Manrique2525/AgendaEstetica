# SPEC-023 - Automated GitHub → alwaysdata Production Deployment

## Status

`DEFINITION / IMPLEMENTED (pending activation in GitHub)`

This SPEC designs the automated production deployment pipeline for AgendaEstetica
on alwaysdata. The workflow is implemented at
`.github/workflows/deploy-production.yml` and documented in
`docs/deployment/alwaysdata-production.md`.

What remains is **human activation on GitHub**: creating the `production`
Environment with the required secrets and authorizing the dedicated CI SSH key
on alwaysdata (see "Activation checklist" at the end of this SPEC). Until that
is done, the pipeline is not production-active; merging this SPEC alone must
not block already approved integrations.

## Purpose

Automate future AgendaEstetica deployments so that code correctly integrated
into the official production branch can be deployed automatically to alwaysdata,
traceable to the exact Git SHA.

This is **not** interpreted as "every push of every branch goes to production".

The pipeline rule is:

```text
feature branch
→ Pull Request / approved integration
→ production branch
→ CI quality gate
→ deploy alwaysdata
```

## Roadmap, Epic and numbering

- Roadmap item: `20. Production`.
- This SPEC allocates number `SPEC-023` to production deployment automation.
- SPEC-021 remains reserved for Appointment Request.
- SPEC-022 remains the Academic Phase 1 Presentation.
- The complete deployment guide lives in
  `docs/deployment/alwaysdata-production.md`.

## Production branch

The repository Git governance (AGENTS.md) defines `main` as the stable branch
and the target of approved SPEC merges. **Production branch = `main`.**

Pipeline trigger:

```text
push / merge to main
→ production deploy
```

This SPEC does not change the branch strategy. If a different production branch
is ever defined, this SPEC must be updated first.

## Git / Production invariants

Permanent rules established by this SPEC:

1. Every code change must be versioned in Git.
2. Application changes are never made manually on production.
3. Git is the source of truth for deployable code.
4. Never develop directly inside `/home/yaris/apps/agenda-estetica`.
5. Changes are developed locally on a branch.
6. The flow is: tests → commit → push → PR/integration → production branch.
7. Once an approved change enters the production branch via push/merge, the
   pipeline synchronizes it to alwaysdata automatically.
8. Production must not contain code that does not exist in Git.
9. No "server-only" application changes are allowed.
10. Server-only exceptions that are **not** committed to Git:
    `.env`, secrets, runtime logs, cache, persistent storage and alwaysdata's
    own configuration.

## CI quality gate

Before any automatic deployment, **all** quality gates defined by the project
must pass. Commands reflect the current repository tooling
(`package.json`, `composer.json`, `docs/testing/TEST_PLAN.md`):

### Backend

```text
composer validate --strict
composer audit
vendor/bin/pint --test
vendor/bin/phpstan analyse
php artisan test
```

### Frontend

```text
npm ci
npm run lint
npm run typecheck
npm run test
npm run build
npm audit
```

### Repository

```text
git diff --check
```

If any gate fails, **deployment must not run**. Do not assume scripts that do
not exist; a missing script is reported, never simulated. If project tooling
changes, the gate commands are updated from the project sources.

## GitHub Actions design

Implemented workflow:

```text
.github/workflows/deploy-production.yml
```

Trigger: `push` to `main` only (includes approved PR merges) plus a manual
`workflow_dispatch` emergency/retry. The workflow never deploys feature, fix or
docs branches and `pull_request` events never deploy.

Jobs:

```text
backend  → quality gate (composer validate --strict, composer audit,
           pint --test, phpstan analyse, php artisan test, git diff --check)
frontend → quality gate (npm ci, lint, typecheck, test, build, npm audit)
           and uploads public/build as an artifact
deploy   → needs backend + frontend
           → GitHub Environment production
           → SSH alwaysdata (dedicated key + verified known_hosts)
           → safe rsync (exclusions below, no --delete)
           → composer install --no-dev + check-platform-reqs
           → php artisan about environment verification
           → php artisan migrate --force
           → php artisan optimize + view:cache
           → chmod storage bootstrap/cache
           → HTTP health checks with bounded retries
           → deployment record append (storage/logs/deployment.log)
```

If any quality gate fails, the deploy job never starts.

## GitHub Environment

The pipeline must use a dedicated GitHub Environment:

```text
production
```

Required configuration / secrets:

```text
ALWAYSDATA_HOST
ALWAYSDATA_USER
ALWAYSDATA_PORT
ALWAYSDATA_SSH_KEY
ALWAYSDATA_KNOWN_HOSTS
```

`ALWAYSDATA_KNOWN_HOSTS` holds the verified alwaysdata host key line for the
SSH host (format: `ssh-yaris.alwaysdata.net ssh-ed25519 AAA...`). SSH host
verification stays enabled: the workflow requires this secret and fails fast if
it is missing.

Optional overrides (documented production defaults are used otherwise):

```text
ALWAYSDATA_APP_ROOT   default /home/yaris/apps/agenda-estetica
ALWAYSDATA_BASE_URL   default https://yaris.alwaysdata.net
```

Recommended values (verified after the 2026-09-17 account rename):

```text
ALWAYSDATA_HOST=ssh-yaris.alwaysdata.net
ALWAYSDATA_USER=yaris
ALWAYSDATA_PORT=22
```

`ALWAYSDATA_SSH_KEY` is a **secret only**.

`DB_PASSWORD` and `APP_KEY` are **not** stored in GitHub unless future design
requires it; they already live exclusively in the server `.env`.

## Dedicated CI SSH key

The pipeline must use a dedicated ED25519 deployment key for GitHub Actions:

```text
ssh-keygen -t ed25519
-f ~/.ssh/id_ed25519_alwaysdata_github
-C "github-actions-agenda-estetica"
```

- Public key: installed/authorized in alwaysdata.
- Private key: **GitHub Environment secret only**.
- Never in the repository.
- Never in logs.
- Never in documentation.

The personal/admin SSH key is **not** reused by CI/CD.

## SSH host verification

The future workflow must **not** use `StrictHostKeyChecking=no`.

Required behavior:

- Obtain the legitimate alwaysdata host key/fingerprint.
- Store and use a verified `known_hosts` entry.
- Keep host verification enabled.

## rsync rules

Deployment destination:

```text
/home/yaris/apps/agenda-estetica/
```

Never overwrite or delete:

```text
.env
storage persistent data
server-only runtime data
```

Never deploy:

```text
.git/
node_modules/
tests artifacts
development .env
local secrets
```

`public/build` **must** be deployed. `vendor` may be installed server-side from
`composer.lock`.

`--delete` behavior must be evaluated with explicit exclusions so that
server-only files always survive. It is not assumed to be enabled.

The implemented workflow does **not** enable `--delete`: obsolete server-side
files remain after a deploy. That consequence is accepted in the first
implementation; enabling `--delete` requires the same exclusion safety review
documented here.

## Database safety

Automated deployment may run:

```text
php artisan migrate --force
```

Only after successful application synchronization and environment verification.

Never automatically run:

```text
php artisan migrate:fresh
php artisan migrate:refresh
php artisan db:wipe
php artisan db:seed
```

Migrations must be backward-conscious and production-safe. A migration failure
means the deployment is **FAILED** and must be reported.

## Concurrency

The pipeline must prevent simultaneous production deployments:

```yaml
concurrency:
  group: agenda-estetica-production
  cancel-in-progress: false
```

Only one production deployment at a time.

## Health check / post-deploy

After deployment:

```text
curl -fsS https://yaris.alwaysdata.net/api/v1/health
```

Must return HTTP 200 JSON. Additionally at least:

```text
/
protected endpoint unauthenticated behavior
critical frontend asset availability
```

If the health check fails, the deployment is marked **FAILED** and is not
reported as success.

## Rollback / failure design

The current rsync-in-place deployment has **limited rollback guarantees**. It
must not be presented as atomic until one of the following is evaluated before
calling CI/CD production-grade:

- A: release directories + current symlink.
- B: deployment artifact with previous release retained.
- C: safe Git-SHA artifact restoration.

The first automated deployment implementation must explicitly document that
atomic rollback does not exist if it does not.

## Production deployment record

Every successful automated deployment records at minimum:

```text
timestamp
Git branch
Git SHA
actor
CI run
test result
migration result
health-check result
deployment result
```

The deployed SHA must always be identifiable.

## Manual deployment policy

Manual production deployment remains an emergency fallback. If used:

- deploy only a clean Git SHA;
- document the SHA;
- run normal quality gates;
- never deploy uncommitted code;
- never bypass migrations or security rules;
- never create server-only application code changes.

## Acceptance Criteria

- AC-01: The SPEC establishes Git as the source of truth for deployable code.
- AC-02: The production branch is defined from the existing Git governance.
- AC-03: Deployment only happens after an approved integration into the
  production branch.
- AC-04: All real project quality gates are listed with exact commands.
- AC-05: A failing quality gate blocks deployment.
- AC-06: The GitHub Actions workflow is implemented at
  `.github/workflows/deploy-production.yml`.
- AC-07: A dedicated GitHub Environment named `production` is required.
- AC-08: CI/CD uses a dedicated ED25519 SSH key, never the personal key.
- AC-09: SSH host verification is enabled with a verified `known_hosts`.
- AC-10: rsync exclusions protect server-only files and secrets.
- AC-11: Database safety rules forbid destructive commands and seeding.
- AC-12: Concurrency prevents simultaneous production deployments.
- AC-13: Health checks run after deploy and failure marks the deploy failed.
- AC-14: Rollback limitations are documented explicitly.
- AC-15: The deployment record captures SHA, actor, run and results.
- AC-16: Manual emergency deployment rules are defined.
- AC-17: The alwaysdata production guide is cross-linked.
- AC-18: No secrets are documented.
- AC-19: Implementation is materialized by this SPEC state and documented with
  the workflow; the remaining human activation steps are listed.

## Out of Scope

Creating the GitHub Environment `production`, storing secrets, rendering/using
the private CI key, alwaysdata SSH authorization of the CI public key, enabling
release-directory/atomic rollback tooling and any CI/CD runner configuration.
Those are human activation steps (see `Activation checklist` below) that must
happen on GitHub and on alwaysdata, not in this repository's code.

## Activation checklist (human, before the pipeline is production-safe)

Executed by a maintainer, not by code:

1. Generate the dedicated ED25519 CI key (never reuse the admin key):
   ```text
   ssh-keygen -t ed25519 -f ~/.ssh/id_ed25519_alwaysdata_github -C "github-actions-agenda-estetica"
   ```
2. Install the **public** key on alwaysdata (panel `Administration → SSH keys`,
   or `ssh-copy-id` once with the account password from the panel/console).
   `ALWAYSDATA_KNOWN_HOSTS` requires the verified alwaysdata fingerprint:
   ```text
   ssh-keygen -lfF ssh-yaris.alwaysdata.net
   ```
   Store the resulting host-line
   (`ssh-yaris.alwaysdata.net ssh-ed25519 AAA...`, ED25519 fingerprint
   `SHA256:5i/vJYokzNsnXAeHkwzEm+3kxPQWwsRzwXFPQ7oOvNI`) as the secret.
3. In GitHub: Settings → Environments → create `production`.
4. Add Environment secrets:
   `ALWAYSDATA_HOST=ssh-yaris.alwaysdata.net`,
   `ALWAYSDATA_USER=yaris`, `ALWAYSDATA_PORT=22`,
   `ALWAYSDATA_SSH_KEY=` (private key), `ALWAYSDATA_KNOWN_HOSTS=` (host line).
   Optional: `ALWAYSDATA_APP_ROOT`, `ALWAYSDATA_BASE_URL`.
5. Run `workflow_dispatch` on a clean main and confirm SSH connectivity +
   health checks pass; then the pipeline is active.

## State

```text
SPEC-023 - Automated GitHub → alwaysdata Production Deployment: DEFINITION / IMPLEMENTED (pending activation)
Production branch: main (current governance)
alwaysdata production guide: docs/deployment/alwaysdata-production.md
Deploy workflow: .github/workflows/deploy-production.yml
```

This SPEC defines and implements the pipeline. Do not enable it in GitHub until
the `Activation checklist` above is complete.