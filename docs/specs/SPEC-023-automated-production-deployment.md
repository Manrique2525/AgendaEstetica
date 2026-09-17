# SPEC-023 - Automated GitHub → alwaysdata Production Deployment

## Status

`DEFINITION / NOT IMPLEMENTED`

This SPEC designs the automated production deployment pipeline for AgendaEstetica
on alwaysdata. No CI/CD workflow, GitHub Environment or deployment script is
implemented by this SPEC. Implementation is separately authorized and must be
based on this Definition plus a Technical Discovery approval.

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

A future workflow is to be defined as:

```text
.github/workflows/deploy-production.yml
```

Conceptual flow:

```text
push / merge to main
↓
CI
↓ Composer / Pint / PHPStan / Pest
↓ npm / lint / typecheck / Vitest / Vite build
↓
ALL PASS
↓
Deploy job
↓ SSH alwaysdata
↓ safe rsync
↓ composer install --no-dev
↓ php artisan migrate --force
↓ Laravel caches
↓ HTTP health check
↓ production complete
```

This workflow is **not** implemented by this SPEC.

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
- AC-06: The GitHub Actions workflow is designed but not implemented.
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
- AC-19: No implementation is performed by this SPEC.

## Out of Scope

Creating `.github/workflows/deploy-production.yml`, GitHub Environments,
secrets, rendering keys, alwaysdata SSH authorization, release directories,
rollback tooling and any CI/CD runner changes. Those belong to a separately
authorized implementation based on this Definition.

## State

```text
SPEC-023 - Automated GitHub → alwaysdata Production Deployment: DEFINITION / NOT IMPLEMENTED
Production branch: main (current governance)
alwaysdata production guide: docs/deployment/alwaysdata-production.md
```

This SPEC is a Definition. Do not implement CI/CD until separately authorized.