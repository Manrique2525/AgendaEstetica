# SPEC-022 - Academic Phase 1 Presentation

## Status

`DEVELOPMENT IN PROGRESS / CHECKPOINT D IMPLEMENTED / READY FOR HUMAN APPROVAL`

Definition and Discovery are integrated into `main`. Checkpoints A, B and C are
completed and approved. Checkpoint D is implemented on its feature branch,
pending human approval; Checkpoint E and implementation beyond D are not
authorized.

## Purpose

Define a truthful academic presentation layer for exactly four areas:

1. Privacidad y seguridad.
2. Autenticación.
3. Integridad y firma digital.
4. Factura digital.

Every area must distinguish `FUNCIONAL`, `DEMOSTRACIÓN`, `PENDIENTE DE
INTEGRACIÓN` and `PENDIENTE DE REVISIÓN`. A demonstration must never be shown
as production functionality.

## Roadmap and Epic

- Epic: `EPIC-STOREFRONT-ACADEMIC-PHASE-1`, coordination only.
- SPEC-021: reserved for Appointment Request; not defined here.
- SPEC-022: this Definition and Technical Discovery.
- SPEC-009: public shell/footer dependency; it is now closed/merged.
- SPEC-010 and SPEC-012: future real catalog/order authorities, not required
  for a demo-only academic presentation.
- SPEC-008: Fake WhatsApp, unchanged and not a customer-auth mechanism.

## Authorities and Actors

- Existing `/admin/login`, Sanctum API and protected admin routes are the real
  administrator authentication authority.
- SPEC-003 owns Customer identity and phone normalization.
- SPEC-004 owns Appointment lifecycle and persistence.
- SPEC-006 owns guest booking and immediate confirmed Appointment creation.
- SPEC-009 owns the public shell/footer until closure/merge.
- SPEC-012 owns future real Cart/Checkout/Order persistence and invoice state.

Actors are visitors/students, future Customers, the Yaris administrator and an
academic demonstrator. No new actor authentication is created here.

## Canonical Status Matrix

| Area | Status | Boundary |
| --- | --- | --- |
| Privacy/security | `PENDIENTE DE REVISIÓN` | Provisional explanation only |
| Admin auth | `FUNCIONAL` | Existing Sanctum login |
| Customer auth | `DEMOSTRACIÓN / VERIFICACIÓN PENDIENTE` | No session, OTP or token |
| Order review/correction | `DEMOSTRACIÓN ACADÉMICA` | Local demo state |
| Demo folio/summary | `DEMOSTRACIÓN` | `DEMO-XXXXXX`, non-production |
| SHA-256 integrity | `DEMOSTRACIÓN TÉCNICA FUNCIONAL` | Web Crypto hash of demo data |
| Digital signature | `NO IMPLEMENTADA / DEMOSTRACIÓN` | Explanation only |
| Invoice request | `DEMOSTRACIÓN` | No fiscal request |
| Invoice example | `DOCUMENTO DE PRUEBA` | No fiscal validity |

## Minimal Presentation Routes

- `/fase-1`: overview of the four areas, with anchors
  `#privacidad-seguridad`, `#autenticacion`, `#integridad-firma` and
  `#factura-digital`.
- `/cliente/acceso`: customer-access demonstration only; no real authentication.
- `/demo/pedido`: isolated client-only academic flow with review, correction,
  demo folio, summary and SHA-256 integrity presentation.

No separate privacy, review or result route is required initially. The primary
business navigation remains Inicio, Tienda, Servicios and Contacto; academic
access belongs in a secondary footer/link after SPEC-009 is merged.

## Privacy and Security

The page may state only verified facts: admin Sanctum protection, Public Booking
validation/rate limiting/idempotency, server-side Appointment authority,
minimal Customer fields and public-data minimization. All legal/security text
must say `PENDIENTE DE REVISIÓN`.

Do not claim encryption at rest, ISO/PCI certification, formal legal
compliance, retention periods, automatic deletion or end-to-end encryption.
Future notices may sit beside `/reservar`, `/cliente/acceso` and `/demo/pedido`
without changing form semantics.

## Authentication

The existing `/admin/login` is real and must be reused. It may be presented as
“Administración Yaris - acceso real” without exposing credentials.

Customer authentication is absent. `/cliente/acceso` shows a local-only phone
field and future WhatsApp verification concept labelled `DEMOSTRACIÓN` and
`VERIFICACIÓN DE WHATSAPP PENDIENTE`.

Opening WhatsApp is not authentication, registration or proof of number
ownership. No password, OTP, token, auth cookie, customer endpoint or trusted
phone shortcut is authorized.

## Academic Order Integrity

The Checkpoint C demo flow uses only `DEMO ACADÉMICA`/`DATOS DE PRUEBA` values:

```text
demo inputs -> review -> correction/back -> review -> demo submission
  -> FOLIO DE DEMOSTRACIÓN -> summary
```

Submission must say that it was registered locally and is not a real order. It
must not create `Order`, `OrderItem`, Customer production data, payment state,
inventory movement or production folios. A display-only `DEMO-XXXXXX` folio is
appropriate. Real order ownership remains SPEC-012.

## Hash and Digital Signature

Checkpoint C canonicalizes its local demo data and uses browser-native Web
Crypto SHA-256, displayed as `Huella de integridad (SHA-256)`. This is a hash/
integrity fingerprint, not a digital signature. A signature requires additional
cryptographic/signing infrastructure, none of which exists or is approved.

Final signature architecture depends on the academic team proposal. A checkbox
“Acepto” must never be called a signature; prefer no checkbox, or use
“Confirmo que revisé los datos” with an explicit non-signature explanation.

## Digital Invoice

`Solicitar factura` is demo-only and must not create a fiscal request. Do not
collect real RFC, tax address, tax regime, CFDI use or email without future
approval.

A later static sample may say exactly:

```text
DOCUMENTO DE PRUEBA — SIN VALIDEZ FISCAL
No es CFDI. No está timbrado. No tiene validez ante el SAT.
Contenido únicamente académico/demostrativo.
```

Do not include realistic UUID, SAT seal, cadena original, QR, PAC data, real
RFC or certificate identifiers. No PDF runtime dependency is authorized.

## Security and Privacy Boundary

- No new production PII, tracking, analytics, cookies or consent banner.
- Demo screens discourage real confidential data.
- No production-order identifiers, secrets, signing keys or tax credentials.
- Future CMS-authored HTML requires sanitization and authorization.
- SPEC-009 must be merged before footer integration development.

## Functional Requirements

- FR-01: Provide one overview of the four areas and truthful statuses.
- FR-02: Mark privacy/security content `PENDIENTE DE REVISIÓN`.
- FR-03: Link real admin access to `/admin/login`.
- FR-04: Present Customer access only as demo/pending verification.
- FR-05: Never treat WhatsApp opening as authentication or verification.
- FR-06: Provide local demo order review and correction.
- FR-07: Provide demo submission, folio and summary with no real-order claim.
- FR-08: Provide browser SHA-256 integrity demonstration for confirmed demo data.
- FR-09: Distinguish hash from digital signature and leave signature pending.
- FR-10: Provide demo invoice request, static educational document and
  no-fiscal-validity labeling.
- FR-11: Preserve SPEC-003, SPEC-004, SPEC-006 and SPEC-012 authority.

## Non-Functional Requirements

- NFR-01: Reuse Yaris public/admin shell and existing HTTP/auth boundaries after
  SPEC-009 merge.
- NFR-02: Use Yaris visual language with text labels for statuses.
- NFR-03: Keep flows semantic, labelled, keyboard-accessible and focus-visible.
- NFR-04: Do not rely on color alone for status.
- NFR-05: Keep demo state local and non-production.
- NFR-06: Minimize PII and protect sensitive identifiers.
- NFR-07: Add no auth, payment, PDF, signature, CMS or provider dependency.
- NFR-08: Keep admin authorization authoritative.
- NFR-09: Keep academic links secondary to business navigation.
- NFR-10: Keep future SPEC-012 integration possible through shaped data.

## Acceptance Criteria

- AC-01: Exactly the four required academic areas are defined.
- AC-02: Each area has a visible truthful status.
- AC-03: No unsupported legal/security/compliance claim is made.
- AC-04: Admin auth is identified as real and reused.
- AC-05: Customer auth is demo-only and verification-pending.
- AC-06: No fake login, session, OTP or token exists.
- AC-07: WhatsApp is explicitly not verification.
- AC-08: Demo review supports back/correction/re-review.
- AC-09: Demo data and folio are visibly non-production.
- AC-10: Demo submission never claims a real/paid/fulfilled order.
- AC-11: SHA-256 is labelled integrity hash, never signature.
- AC-12: Signature remains pending team integration.
- AC-13: “Acepto” is never described as a digital signature.
- AC-14: Invoice option is demo-only and collects no unnecessary fiscal data.
- AC-15: Sample invoice warning excludes CFDI/timbrado/SAT validity.
- AC-16: Realistic fiscal identifiers and runtime PDF dependencies are excluded.
- AC-17: SPEC-009 footer dependency is explicit.
- AC-18: SPEC-012, SPEC-021 and SPEC-008 boundaries are preserved.
- AC-19: No implementation occurs beyond the currently authorized checkpoint.
- AC-20: Human review is required before advancing to another checkpoint.

## Proposed Development Checkpoints

1. Privacy/security overview, statuses, notices and footer integration.
2. Customer-access demo and real admin-auth boundary.
3. Demo order review/correction, folio/summary and SHA-256.
4. Invoice-request demo and static educational document.
5. Integrated academic browser/accessibility/responsive audit.

Checkpoints A, B, C and D are authorized for development. Checkpoints A, B and
C are completed and approved; Checkpoint D is implemented pending human
approval. Checkpoint E remains unauthorized.

## State

```text
SPEC-022: DEVELOPMENT IN PROGRESS / CHECKPOINT D IMPLEMENTED / READY FOR HUMAN APPROVAL
Development: AUTHORIZED FOR CHECKPOINT D ONLY
Checkpoint A: COMPLETED / APPROVED
Checkpoint B: COMPLETED / APPROVED
Checkpoint C: COMPLETED / APPROVED
SPEC-009: CLOSED / MERGED / DEPENDENCY SATISFIED
SPEC-010 / SPEC-012: NOT REQUIRED FOR DEMO IMPLEMENTATION
SPEC-021: RESERVED / NOT AUTHORIZED / UNDEFINED
Checkpoint E: NOT AUTHORIZED
SPEC-007: PAUSED / UNCHANGED
SPEC-008: FAKE WHATSAPP / UNCHANGED / NOT AUTHORIZED
```

STOP. Submit SPEC-022 Checkpoint D for explicit human approval. Do not
auto-advance to Checkpoint E.
