# Dependency Security Remediation Report

## Scope

This branch remediates only dependency advisories blocking the project quality
gates. It does not modify application code, Checkpoint C, production, secrets,
the deployment workflow or database schema.

Baseline:

```text
origin/main: c021b13190597359f5ff333ce207630a4eaf9f16
Security branch: fix/security-dependency-advisories-2026-10
```

## Composer

- Previous `league/commonmark`: `2.10.1`.
- Dependency path: `laravel/framework v13.30.1 -> league/commonmark ^2.8.1`.
- New `league/commonmark`: `2.10.3`.
- Accompanying targeted lock update: `symfony/polyfill-php80 v1.37.0 -> v1.43.0`.
- `composer.json` changed: NO.
- `composer.lock` changed: YES.
- GHSA-97jj-33gv-5xf9: resolved.
- GHSA-3q6v-r5mr-hxv8: resolved.
- Final `composer audit`: PASS, no advisories.

## npm

- Previous `brace-expansion`: `5.0.9`.
- Dependency path: `eslint -> minimatch@10.2.6 -> brace-expansion@5.0.9`.
- New `brace-expansion`: `5.0.12`.
- `package.json` changed: NO.
- `package-lock.json` changed: YES, only the targeted package record.
- npm override introduced: NO.
- GHSA-q2hr-2g5m-vwhr: resolved.
- GHSA-qhr7-859c-m2p7: resolved.
- GHSA-6j4f-fj2g-mc7p: resolved.
- Final `npm audit`: PASS, 0 vulnerabilities.

## Quality

- Composer validate: PASS.
- Composer audit: PASS.
- Pint: PASS.
- PHPStan: PASS.
- ESLint: PASS.
- TypeScript: PASS.
- Frontend tests: PASS.
- Frontend build: PASS.
- npm audit: PASS.
- `git diff --check`: PASS.

The local backend test command was attempted twice and could not connect to the
machine-local MySQL test database: user `agenda_estetica_test` was rejected on
ports 3306 and 3307. No source or test failure was observed. Remote Quality is
required to authorize the security branch because this local environment lacks
the configured test database credentials.

## Preservation and Safety

- Original Checkpoint C worktree remains untouched and uncommitted.
- No C application files are in this branch.
- No production files, secrets, deployment workflow or database files changed.
- No `npm audit fix`, `npm audit fix --force`, blanket Composer update or force
  push was used.
- Production was not changed.
