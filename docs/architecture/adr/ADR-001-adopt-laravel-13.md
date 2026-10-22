# ADR-001 - Adopt Laravel 13

## Title

Adopt Laravel 13 for the application foundation.

## Status

`ACCEPTED`

## Date

2026-09-07

## Context

The repository contains governance documentation but no application code. SPEC-001 was initially defined against Laravel 12. Laravel 12 was released on 2025-02-24, and its bug-fix window ended on 2026-08-13. Laravel 13 was released on 2026-03-17 and provides the longer maintenance window appropriate for a new application.

The local environment reports PHP 8.3.19, which satisfies the Laravel 13 minimum requirement. Changing the framework major before writing application code has minimal migration cost.

## Options Considered

### A. Maintain Laravel 12

Pros:

- Keeps the existing SPEC wording unchanged.
- Avoids changing the initial framework reference.

Cons:

- Starts a new project after the Laravel 12 bug-fix window has ended.
- Creates an avoidable upgrade near the MVP.
- Shortens the supported maintenance window for the new application.

### B. Adopt Laravel 13 before implementation

Pros:

- Starts the new project on the maintained major version.
- Requires no application migration because no application code exists.
- Extends the expected bug-fix and security-support horizon.
- Aligns the skeleton, packages and tooling from the beginning.

Cons:

- Requires resolving a new compatible dependency set.
- Requires updating the Foundation specification and related documentation.

## Decision

Choose option B: adopt Laravel 13 as the approved backend framework for AgendaEstetica.

The project remains on PHP 8.3+ and does not adopt PHP 8.4-only features during Foundation. Laravel 13 does not authorize unrelated Laravel ecosystem features such as Laravel AI SDK, JSON:API or vector search.

## Rationale

- The project is new and has zero application code.
- PHP 8.3.19 is already compatible.
- Laravel 12 no longer receives bug fixes.
- Laravel 13 provides a longer maintenance window.
- The decision avoids an upgrade immediately after the MVP.
- The existing modular monolith, Sanctum SPA, database queue and Vue architecture remain unchanged.

## Consequences

- All normative framework references change from Laravel 12 to Laravel 13.
- Dependency compatibility must be resolved and recorded in lockfiles during implementation.
- Laravel 13 becomes the baseline for the root application skeleton.
- No new business scope is introduced.
- No PHP minimum increase is required.
- Future Laravel-specific features still require their own scope and approval.

## Compatibility

- PHP: 8.3+ remains the project minimum.
- MySQL: 8.4 LTS is the approved database target.
- Sanctum: 4.x remains the approved SPA authentication package.
- Pest: 4.x remains required because Pest 5 requires PHP 8.4.
- Larastan: `larastan/larastan` 3.x is the approved package line, with the exact compatible version resolved during implementation.
- Vue, Vite, Tailwind, TypeScript and frontend testing remain separate Foundation decisions.

## Migration Impact

There is no application migration. The implementation will create a Laravel 13 skeleton in a temporary location and copy it selectively to the repository root, preserving `.git/`, `AGENTS.md` and `docs/`.

No business migrations, models, controllers, Vue components, queues or integrations are created by this ADR.
