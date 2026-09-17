# SPEC-022 CHECKPOINT D IMPLEMENTATION REPORT

## Result

`SPEC-022: DEVELOPMENT IN PROGRESS`

`Checkpoint A: COMPLETED / APPROVED`

`Checkpoint B: COMPLETED / APPROVED`

`Checkpoint C: COMPLETED / APPROVED`

`Checkpoint D: IMPLEMENTED / READY FOR HUMAN APPROVAL`

Checkpoint D is implemented on `feat/spec-022-academic-phase-1`. It is not
self-approved and Checkpoint E was not started.

## Implementation

- Added `Solicitar factura` as a local-only checkbox in `/demo/pedido`.
- Clearly qualified the option as `DEMOSTRACIÓN`.
- Added no fiscal-data fields.
- Included `requestInvoice` in the canonical integrity payload.
- Removed the previously misplaced Terms/Privacy control from `/demo/pedido`.
- Restored the approved Demo Order canonical payload version `2`; the landing
  Terms/Privacy decision is outside the Demo Order integrity payload.
- Preserved SHA-256 stability and changed-preference digest behavior.
- Added one local-only Accept/Reject Terms and Privacy interaction at the
  bottom of `/fase-1`; it is not persisted as legal consent.
- Added invoice preference to edit, review and final demo summary states.
- Added a static educational PDF at
  `public/demo/factura-demostracion-sin-validez-fiscal.pdf`.
- Added a download link only when the demo invoice option is selected.
- Updated `/fase-1#factura-digital` to `DEMOSTRACIÓN` with real invoicing
  remaining `PENDIENTE DE INTEGRACIÓN`.
- Corrective human-review pass: privacy/terms responsibility is now explicit
  and independent Phase 1 capabilities remain sibling sections.
- Added independent `Mary Kay a domicilio` external-store item with exact URL
  `https://www.marykay.com.mx/yaris` and external-store disclosure.

## Invoice Boundary

```text
Solicitar factura demo: IMPLEMENTED
Static educational PDF: IMPLEMENTED
Real invoice: NOT IMPLEMENTED
CFDI: NOT IMPLEMENTED
SAT: NOT INTEGRATED
PAC: NOT INTEGRATED
Fiscal data: NOT COLLECTED
Invoice persistence: NONE
Invoice API: NONE
Payment: NOT IMPLEMENTED
```

The invoice checkbox is a demo preference only. The landing Accept/Reject
interaction is a frontend academic demonstration, not production legal-consent
persistence, a digital signature, identity verification or a fiscal request.

## Static PDF

- Repository path: `public/demo/factura-demostracion-sin-validez-fiscal.pdf`.
- URL: `/demo/factura-demostracion-sin-validez-fiscal.pdf`.
- Size: `113422` bytes.
- Header: `%PDF-`.
- HTTP: `200 OK`.
- Content-Type: `application/pdf`.
- Runtime PDF generation: NO.
- New PDF dependency: NONE.
- Generation method: temporary HTML rendered with external Playwright Chromium
  `1.63.0` / Chromium `153.0.8010.12`, then copied as a static artifact.

The document contains the exact warning:

`DOCUMENTO DE PRUEBA — SIN VALIDEZ FISCAL`

It also states that it is not a CFDI, is not SAT-timbrado, has no fiscal
validity and is for academic use only. It contains no RFC, fiscal UUID, SAT
seal, PAC, fiscal QR, certificate or official SAT branding.

## Scope Audit

```text
Backend: UNCHANGED
Schema: UNCHANGED
Invoice model/table/API: NOT IMPLEMENTED
Order infrastructure: NOT IMPLEMENTED
Product catalog: NOT IMPLEMENTED
Inventory: NOT IMPLEMENTED
Payment: NOT IMPLEMENTED
Customer auth: NOT IMPLEMENTED
Digital signature: NOT IMPLEMENTED / PENDING INTEGRATION
AppointmentRequest: NOT IMPLEMENTED
New npm dependencies: NONE
New Composer dependencies: NONE
Database writes: ZERO
```

## Automated Testing

Changed/added frontend test files:

- `resources/js/pages/public/DemoOrderPage.test.ts`
- `resources/js/pages/public/AcademicPhaseOnePage.test.ts`
- `resources/js/utils/demoIntegrity.test.ts`

Focused Checkpoint D tests: `3 files / 14 tests PASS`.

The tests cover the checkbox, review/correction, final requested state,
download link, no fiscal fields, static warning, no invoice request, and
invoice-preference SHA change.

Full quality results:

- Backend: `208 tests / 1202 assertions PASS`
- Frontend: `22 files / 83 tests PASS`
- `composer validate --strict`: PASS
- `composer audit`: PASS, no advisories
- `vendor/bin/pint --test`: PASS
- `vendor/bin/phpstan analyse`: PASS
- `npm run lint`: PASS
- `npm run typecheck`: PASS
- `npm run test`: PASS
- `npm run build`: PASS
- `npm audit`: PASS, 0 vulnerabilities
- `git diff --check`: PASS

The final local backend rerun encountered the pre-existing shared MySQL test
database singleton collision (`business_profiles_singleton_key_unique`), with
no backend files changed in this correction. Fresh remote Quality is the
authoritative backend verification for the final feature HEAD.

## Browser and Database QA

Real headless Chromium QA was executed outside the repository with Playwright
`1.63.0` and Chromium `153.0.8010.12`.

Passed:

- `/demo/pedido`: `390x844`, `768x1024`, `1440x900`.
- `/fase-1` smoke: `390x844`, `1440x900`.
- Corrective `/fase-1` hierarchy QA: `390x844`, `768x1024`, `1440x900`.
- Landing Terms Accept/Reject QA: PASS; initial state undecided.
- Mary Kay exact external href/target/rel QA: PASS.
- `/cliente/acceso` smoke: `390x844`.
- `/reservar` smoke: `390x844`.
- No-invoice review/final flow.
- Invoice-request, correction/toggle, re-review and final flow.
- Static PDF download: `factura-demostracion-sin-validez-fiscal.pdf`, 113422 bytes.
- Static PDF HTTP: `200`, `application/pdf`, `%PDF-` header.
- No invoice/order/payment/fiscal/auth requests; only asset/font requests.
- localStorage/sessionStorage and pre-existing cookies unchanged.
- Database before/after: customers `2`, users `0`, appointments `2`.
- No orders or invoices tables; no database writes.
- No console errors, Vue warnings, page errors or critical failed requests.
- No horizontal overflow.

`pdftotext` was unavailable in the environment. The final checked-in PDF was
directly inspected with the available PDF reader, which parsed and confirmed
the required warning, CFDI/timbrado/SAT disclaimers and academic-use wording.

Screenshots and raw evidence are outside the repository at
`/tmp/spec022-browser-qa/`.

STOP. Submit Checkpoint D for explicit human approval. Do not start
Checkpoint E.
