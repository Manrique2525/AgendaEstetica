# SPEC-022 DEFINITION + TECHNICAL DISCOVERY REPORT

## Decision

SPEC-022 is defined as an academic presentation scope for privacy/security,
authentication, integrity/signature and digital invoice. Its Definition and
Technical Discovery are `INTEGRATED / COMPLETED` on `main`; Checkpoints A and B
are completed and approved, and Checkpoint C is implemented on its feature
branch awaiting human approval.

## Repository Evidence

Checkpoints A-C contain only public Vue presentation routes/content,
provisional notices and secondary links. Checkpoint C contains no order,
customer-auth implementation, model, migration, asset or PDF. The SPEC-009
feature branch was not modified. Existing contracts inspected include:

- `AdminLoginPage.vue`, `useAuth.ts`, Sanctum auth and protected admin routes.
- `AdminLayout.vue` and existing admin navigation.
- `PublicBookingPage.vue`, Public Booking API and existing HTTP wrapper.
- Minimal `Customer` model, not an authentication principal.
- Router routes `/`, `/reservar`, `/admin/*` and SPA fallback.
- SPEC-009 public shell/footer, now available because SPEC-009 is closed/merged.

## Area Findings

### Privacy and security

Recommend one `/fase-1` overview section at
`#privacidad-seguridad`, marked `PENDIENTE DE REVISIÓN`. Verified claims are
limited to existing Sanctum, validation, rate limiting, idempotency, server
authority and data minimization. No certification, encryption-at-rest,
retention or legal-compliance claims are supported.

### Authentication

Admin authentication is `REAL / FUNCIONAL` through `/admin/login`. Customer
authentication is absent and should be a `/cliente/acceso` demonstration only.
WhatsApp opening is not authentication or number verification; no OTP, token,
cookie, session or insecure phone trust is allowed.

### Integrity and signature

Recommend `/demo/pedido` with local demo data, review/back/correction/review,
local demo submission, `FOLIO DE DEMOSTRACIÓN` using `DEMO-XXXXXX`, summary and
optional browser Web Crypto SHA-256. The hash is a real integrity fingerprint,
not a digital signature. Signing depends on the academic team proposal.

### Digital invoice

Recommend a demo-only `Solicitar factura` option. Do not collect real fiscal
data. A future static document may be labelled `DOCUMENTO DE PRUEBA — SIN
VALIDEZ FISCAL`; no CFDI, SAT, timbrado or realistic fiscal identifiers should
be implied. No PDF runtime dependency is needed.

## Minimal Routes and Navigation

Recommend `/fase-1`, `/cliente/acceso` and `/demo/pedido`. Use anchors for the
four areas instead of separate privacy/review/result routes. Keep academic
links secondary in the SPEC-009 footer; preserve primary business navigation.

## Persistence and Data

Use only visibly labelled `DEMO ACADÉMICA`/`DATOS DE PRUEBA` values. Keep state
local and create no Order, OrderItem, Customer, payment, inventory, signature
or fiscal records. SPEC-012 remains the real order authority.

## Dependencies and Blockers

No new dependencies, routes, assets or schema are required for the Definition.
Web Crypto is browser-native. SPEC-009 is now closed/merged, satisfying the
PublicLayout/footer dependency for future SPEC-022 Development. SPEC-010
and SPEC-012 are not blockers for demo-only planning. SPEC-021 remains reserved
and undefined; SPEC-008 remains Fake WhatsApp.

## Proposed Checkpoints

- A: overview, privacy/security statuses, notices and footer integration.
- B: customer-auth demonstration and real admin boundary.
- C: order review/correction, demo folio/summary and SHA-256.
- D: invoice-request demo and educational document.
- E: integrated browser, accessibility, responsive and real/demo/pending audit.

Checkpoints A and B are completed and approved. Checkpoint C is authorized and
implemented pending human approval. Checkpoints D-E are not authorized.

## Test Handoff

Checkpoint A tests cover the public route/title, one H1, four area statuses,
privacy anchor, provisional notice, footer integration, primary navigation
boundary and preservation of the booking form. Checkpoint B tests cover the
customer demo route/title, local-only continuation, no auth state or request,
admin reuse, WhatsApp semantics and `/fase-1` integration. Future tests should
cover review correction, demo folio, SHA-256/signature distinction, invoice
warning and no production persistence. Checkpoint C tests cover the isolated
edit/review/correction/confirmation flow, canonical serialization, known SHA
vector, reproducibility and changed-data digest behavior.

## Scope Audit

```text
Vue implementation: `/fase-1`, `/cliente/acceso`, `/demo/pedido`, notices and secondary links
PHP implementation: NONE
Routes: `/fase-1`, `/cliente/acceso`, `/demo/pedido`
Migrations/schema: NONE
Tests: Checkpoint A, B and C frontend coverage
Assets/PDF: NONE
Dependencies: NONE
SPEC-009 feature: NOT TOUCHED
SPEC-007: PAUSED / UNCHANGED
SPEC-008: FAKE WHATSAPP / UNCHANGED
```

STOP. Submit SPEC-022 Checkpoint C for explicit human approval.
