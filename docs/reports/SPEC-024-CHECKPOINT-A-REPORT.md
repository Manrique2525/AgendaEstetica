# SPEC-024 CHECKPOINT A REPORT

## Result

```text
SPEC-024: DEVELOPMENT IN PROGRESS
Checkpoint A: APPROVED / COMPLETE
Checkpoint B: AUTHORIZED NEXT AFTER A INTEGRATION
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

## Browser QA

Browser: Google Chrome `153.0.8010.48`, launched headlessly through transient
Playwright `1.63.0` outside the repository. No project dependency was added.

| Viewport | Banner width | Banner height | Occupancy | Overflow | Clipping | Verdict |
| --- | ---: | ---: | ---: | --- | --- | --- |
| 320x568 | 320px | 169.5px | 29.8% | No | No | PASS |
| 390x844 | 390px | 150.25px | 17.8% | No | No | PASS |
| 430x932 | 430px | 150.25px | 16.1% | No | No | PASS |
| 768x1024 | 768px | 102.25px | 10.0% | No | No | PASS |
| 1440x900 | 1440px | 102.25px | 11.4% | No | No | PASS |

All measurements used `getBoundingClientRect()` and actual document/body
scroll widths. The banner stayed fixed at the viewport bottom before and after
scrolling at 390x844. The page remained visible above it and the action row
fit without clipping at every target width.

Screenshots were kept as transient evidence outside the repository:

```text
/var/folders/4b/9ml3xz_s4h1g9plscls0vc2r0000gn/T/opencode/spec024-qa/evidence/
```

## Interaction QA

- Fresh session: banner visible, storage value absent.
- `Aceptar`: banner disappeared immediately; storage became `accepted`.
- Accept refresh: banner remained hidden.
- Accept SPA navigation and return: banner remained hidden.
- `Rechazar` in an isolated session: banner disappeared immediately; storage
  became `rejected`.
- Reject refresh: banner remained hidden.
- New isolated session: banner visible again with no stored decision.
- Footer Terms link remained present after dismissal and opened
  `/terminos-condiciones` successfully.
- Keyboard `Tab` reached `Leer términos`, `Rechazar`, `Aceptar` and the footer
  Terms link in order; visible focus rings were present.
- Keyboard `Space` activated `Rechazar` and persisted `rejected`.
- No keyboard trap was observed.

## Console and Request QA

- JavaScript console errors: `0`.
- Vue warnings: `0`.
- Page errors: `0`.
- Failed application requests: `0`.

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

Checkpoint A was explicitly approved by the human after the browser evidence
recorded above. Do not start Checkpoint B until A is integrated and production
verification completes.
