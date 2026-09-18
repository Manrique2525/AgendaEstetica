# SPEC-024 CHECKPOINT A REPORT

## Result

```text
SPEC-024: DEVELOPMENT IN PROGRESS
Checkpoint A: IMPLEMENTED / READY FOR HUMAN APPROVAL
Checkpoint B: NOT AUTHORIZED
```

This checkpoint changes only the academic consent UX. No invoice, fiscal,
XML, backend, API, schema, migration, deployment or production work was done.

## Implementation

- Compact fixed bottom banner on phones with concise copy.
- `Aceptar` and `Rechazar` remain semantic buttons with minimum 44px height.
- `Leer términos` remains a normal internal router link.
- The banner renders only while the decision is `NO DECISION`.
- Either decision hides the banner immediately.
- The footer Terms/Privacy link remains outside the banner and available after
  dismissal.

## Session Lifecycle

Storage key:

```text
yaris.academic-consent.v1
```

Storage:

```text
sessionStorage only
```

Values:

```text
accepted
rejected
```

Behavior:

- missing value: banner visible;
- `accepted`: banner hidden;
- `rejected`: banner hidden;
- unsupported value: treated as `NO DECISION`, banner visible;
- read/write error: no application crash; write failure still hides the current
  banner, and a later load safely returns to `NO DECISION`;
- no cookies, localStorage, backend request, API call or legal-consent record.

## Accessibility and Responsive Contract

- fixed viewport-bottom positioning preserved;
- semantic region with labelled heading;
- semantic buttons for both decisions;
- semantic internal Terms link;
- visible focus styles preserved;
- keyboard order follows link, Reject, Accept;
- no modal behavior or keyboard trap;
- two-column action layout on narrow screens to avoid a tall button stack;
- no intentional horizontal overflow;
- public layout keeps bottom spacing on `/` so content is not permanently
  hidden while the banner is visible.

Browser tooling was unavailable in this environment, so visual viewport QA was
not executed. The responsive contract is covered by the component structure and
CSS classes, but no visual PASS is claimed for 320x568, 390x844, 430x932,
768x1024 or 1440x900.

## Tests

Added/updated tests cover:

- initial visible state;
- accepted pre-seeded state;
- rejected pre-seeded state;
- Accept persistence and immediate dismissal;
- Reject persistence and immediate dismissal;
- malformed storage value;
- storage read failure;
- storage write failure;
- same-tab remount lifecycle;
- existing Terms route/footer reachability;
- no duplicate banner widget on `/fase-1`.

## Quality

- Backend: `208 tests / 1200 assertions` PASS.
- Frontend: `24 files / 95 tests` PASS.
- ESLint: PASS.
- TypeScript: PASS.
- Build: PASS.
- Composer validate: PASS.
- Composer audit: PASS.
- Pint: PASS.
- PHPStan: PASS.
- npm audit: PASS, 0 vulnerabilities.
- `git diff --check`: PASS.

The backend assertion count differs from the prior 1202 baseline without any
backend change; all 208 tests pass and Checkpoint A is frontend-only.

## Scope Audit

```text
Consent UX: IMPLEMENTED
sessionStorage lifecycle: IMPLEMENTED
Terms route/footer: PRESERVED
Backend/API: UNCHANGED
Schema/migrations: UNCHANGED
Invoice/fiscal demo: NOT STARTED
PDF redesign: NOT STARTED
XML/CFDI: NOT STARTED
SAT/PAC: NOT STARTED
Digital Signature: PENDING / FUTURE WORK
main: UNCHANGED
Production: UNCHANGED
```

STOP. Submit Checkpoint A for explicit human approval. Do not start
Checkpoint B.
