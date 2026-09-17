# SPEC-009 CHECKPOINT A REPORT

## Status

- SPEC-009: `DEVELOPMENT IN PROGRESS`.
- Definition: `COMPLETED / APPROVED`.
- Technical Discovery: `COMPLETED / APPROVED`.
- Development authorization: Checkpoint A only.
- Checkpoint A: `IMPLEMENTED / READY FOR HUMAN REVIEW`.
- Checkpoint B: `NOT AUTHORIZED`.
- Branch: `feat/spec-009-cms-landing`.

## Scope Delivered

Checkpoint A implements only the reusable public shell:

- Centralized typed public data in `resources/js/data/publicSite.ts`.
- Shared PublicLayout header and desktop navigation.
- Root-safe links for `/`, `/#tienda`, `/#servicios` and `/#contacto`.
- Native `/reservar` CTA without changing Public Booking.
- Shared footer with approved identity, WhatsApp and location.
- Skip link targeting `#main-content`.
- Local accessible mobile menu with `aria-expanded`, `aria-controls`, Escape
  close and link close behavior.
- Empty `#tienda` and `#servicios` integration targets only.

The current Foundation technical content remains otherwise intact for
Checkpoint B. No full hero, catalog categories, services section or contact
section was implemented.

## Verification

- Focused shell/data tests: `4 tests PASS`.
- Frontend suite: `17 files / 59 tests PASS`.
- Backend suite: `208 tests / 1202 assertions PASS`.
- `npm run typecheck`: PASS.
- `npm run lint`: PASS.
- `npm run build`: PASS.
- `npm audit`: PASS, 0 vulnerabilities.
- `composer validate --strict`: PASS.
- `composer audit`: PASS.
- `vendor/bin/pint --test`: PASS.
- `vendor/bin/phpstan analyse`: PASS.
- `git diff --check`: PASS.

## Scope Audit

- New routes: none.
- Ecommerce/catalog/product/order code: none.
- Booking behavior/API changes: none.
- AppointmentRequest: none.
- Generic CMS: none.
- Migrations/schema/backend changes: none.
- New dependencies/assets: none.
- SPEC-007: unchanged.
- SPEC-008: unchanged.

## Decision

Checkpoint A is ready for human review. Do not start Checkpoint B, SPEC-010,
SPEC-021, SPEC-022 or any other unauthorized scope.
