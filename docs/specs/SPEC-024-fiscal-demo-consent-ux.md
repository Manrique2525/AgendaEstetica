# SPEC-024 - Fiscal Demo & Consent UX

## Status

`DEFINITION / TECHNICAL DISCOVERY COMPLETE / READY FOR HUMAN DEVELOPMENT REVIEW`

Implementation is **not authorized** by this document. Production deployment,
real fiscal integration and reopening SPEC-022 remain out of scope.

## Purpose

Define a bounded product iteration with three goals:

1. Make the fixed academic Terms/Privacy banner compact and usable on phones.
2. Replace the simple invoice artifact with a complete synthetic fiscal-demo
   presentation and a reusable invoice data contract.
3. Provide a downloadable, well-formed CFDI 4.0-shaped test XML that is never
   represented as valid, signed, timbrado or SAT/PAC-certified.

## Boundaries

### In scope

- Responsive consent-banner presentation and dismissal lifecycle.
- Academic, synthetic invoice data and visual/PDF presentation design.
- CFDI 4.0 structural mapping, XML serialization and download design.
- Official SAT source and catalog mapping discovery.
- Tests, accessibility, responsive and truthfulness acceptance criteria.

### Out of scope

- Reopening or changing SPEC-022.
- Real RFC, customer, business or tax data.
- CFDI signing, CSD handling, TimbreFiscalDigital or PAC integration.
- SAT submission, validation service calls or production fiscal documents.
- Invoice persistence, fiscal API, migrations, orders, payments or checkout.
- Any deployment, production secret change or AlwaysData operation.

## Existing Authorities

- SPEC-022 owns the existing academic presentation boundary and keeps Digital
  Signature `PENDING / FUTURE WORK`.
- SPEC-012 remains the future order/invoice authority; this SPEC creates no
  order or invoice record.
- The backend remains authoritative for future real fiscal data and business
  rules. Vue must not become the authority for real fiscal calculations.
- The existing `DemoOrderData`, canonical payload version `2` and SHA-256
  integrity demonstration remain separate from CFDI signing.

## Consent UX Definition

### Mobile behavior

At approximately 320x568, 390x844 and 430x932 the banner must:

- remain fixed to the bottom viewport edge;
- use concise copy, compact heading and reduced vertical padding;
- keep readable text and minimum 44px touch targets;
- keep `Aceptar`, `Rechazar` and the Terms link visible without horizontal
  overflow;
- use a responsive action layout without appearing full-screen;
- leave the page substantially visible above the banner.

The banner should target less than one third of the 568px viewport at the
smallest target width, subject to readable content and browser text scaling.

### Decision behavior

- Initial state is `NO DECISION`.
- `Aceptar` immediately removes the banner.
- `Rechazar` immediately removes the banner.
- The footer and normal navigation continue to expose
  `/terminos-condiciones` after dismissal.
- This interaction is an academic UX decision, not legal consent and not a
  backend authorization.

### Persistence decision

Use versioned `sessionStorage` as the minimum useful lifecycle:

```text
yaris.academic-consent.v1 = accepted | rejected
```

This preserves the dismissed state across SPA navigation and refresh in the
same browser tab, but resets in a new browser session. It does not create a
cookie, backend record, legal-consent audit trail or cross-device identity.
The storage read/write must be guarded for SSR/test environments and malformed
values must fall back to `NO DECISION`. A future legal-consent requirement must
replace this academic key through a separately approved design.

## Fiscal Demo Contract

The implementation must introduce a reusable, UI-independent contract. Names
are illustrative and may follow repository conventions:

```text
InvoiceDraft
  issuer: IssuerFiscalProfile
  receiver: ReceiverFiscalProfile
  comprobante: ComprobanteMetadata
  concepts: InvoiceConcept[]
  taxes: InvoiceTaxSummary
  payment: PaymentInformation
  disclaimers: DemoDisclaimer
```

The visual invoice, PDF artifact and XML serializer must consume the same
fixture. No duplicate hardcoded totals or fiscal values may live in Vue
templates.

### Synthetic issuer

The fixture must use visibly synthetic data and must not imply a real taxpayer:

- RFC: implementation fixture selected and documented as synthetic;
- name/reason: `EMISOR DEMOSTRATIVO ACADEMICO` or equivalent;
- regime: catalog-backed demo value, selected during implementation;
- postal code/place of issue: synthetic demo value, clearly labelled.

No real Yaris RFC, address or taxpayer identity may be invented.

### Synthetic receiver

The fixture must account for, but clearly label as synthetic:

- RFC;
- fiscal name;
- `DomicilioFiscalReceptor` / postal code;
- `RegimenFiscalReceptor`;
- `UsoCFDI`.

Email and application contact information remain separate from SAT fiscal
fields and must not be presented as SAT-required.

### Comprobante metadata

The contract should map, when applicable:

`Version`, `Serie`, `Folio`, `Fecha`, `FormaPago`, `CondicionesDePago`,
`SubTotal`, `Descuento`, `Moneda`, `TipoCambio`, `Total`,
`TipoDeComprobante`, `Exportacion`, `MetodoPago` and `LugarExpedicion`.

Every catalog-backed field must link to its official SAT catalog source in the
implementation report. Unknown or not-applicable values must be omitted or
labelled, not guessed.

### Concepts and tax fixture

Each concept should map:

`ClaveProdServ`, `NoIdentificacion` when applicable, `Cantidad`, `ClaveUnidad`,
`Unidad` when applicable, `Descripcion`, `ValorUnitario`, `Importe`,
`Descuento` when applicable and `ObjetoImp`.

The academic fixture will use one explicit scenario: one synthetic taxable
service concept with a synthetic transferred IVA scenario, no retentions, and
totals derived from line data. The rate and all catalog keys must be confirmed
from official SAT catalogs before implementation. This is a demonstration
scenario, not tax advice and not a statement about Yaris's real tax treatment.

Totals must be derived:

```text
subtotal = sum(concept amounts)
discount = sum(concept discounts)
transferred taxes = sum(tax amounts)
withheld taxes = sum(retentions)
```

Currency, payment form and method must be visibly marked as demo values.

## Invoice Presentation

The future visual/PDF document should contain:

- `DOCUMENTO DEMOSTRATIVO` banner or watermark;
- `SIN VALIDEZ FISCAL`;
- `NO ES UN CFDI TIMBRADO`;
- `NO HA SIDO CERTIFICADO POR EL SAT NI POR UN PAC`;
- issuer and receiver blocks;
- document series/folio/date/place;
- payment information;
- concepts table with quantity, unit, description, unit price and amount;
- discounts, transferred/withheld taxes, subtotal, currency and total;
- academic fixture metadata;
- XML download action;
- PDF/download action generated from the same fixture.

The document must not display official-looking SAT QR validation links or
branding. UUID, `TimbreFiscalDigital`, `SelloSAT`, `SelloCFD`,
`NoCertificadoSAT`, CSD identifiers and PAC identity are unavailable and must
be shown as `NO DISPONIBLE - REQUIERE TIMBRADO REAL` only if educationally
needed.

## CFDI 4.0 XML Definition

The XML filename should be:

```text
cfdi-demo-sin-validez-fiscal.xml
```

It must be UTF-8, well-formed and generated from the same `InvoiceDraft` used
by the visual/PDF output. The document should use the official namespace:

```text
http://www.sat.gob.mx/cfd/4
```

and the official schema reference:

```text
http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd
```

The implementation should use `xsi:schemaLocation` with the CFDI namespace and
that XSD URL. A comment before the document element may identify the artifact:

```xml
<!-- DOCUMENTO DEMOSTRATIVO. SIN VALIDEZ FISCAL. NO TIMBRADO. -->
```

Do not add custom `demo="true"` attributes inside the SAT namespace.

The legal/fiscal discovery boundary also references CFF Articles 29 and 29-A
for the general obligation and required comprobante information. They are
context for the future real-fiscal design, not a claim that this academic demo
is compliant or tax advice.

Catalog-backed fields requiring an official catalog lookup include
`c_RegimenFiscal`, `c_UsoCFDI`, `c_FormaPago`, `c_MetodoPago`, `c_Moneda`,
`c_Exportacion`, `c_ClaveProdServ`, `c_ClaveUnidad`, `c_Impuesto`,
`c_TipoFactor`, `c_ObjetoImp` and `c_CodigoPostal`.

### XML validity boundary

The official XSD requires fields such as `Sello`, `NoCertificado` and
`Certificado`. This project must not manufacture those values. Therefore the
acceptance contract is explicit:

```text
XML well-formed: YES
CFDI 4.0 structural mapping: YES
SAT XSD validation without real signing material: NOT CLAIMED
SAT fiscal validity: NO
Timbrado: NO
PAC certification: NO
```

No `TimbreFiscalDigital`, UUID, fake seals, fake certificate, CSD private key,
cadena original or SAT verification QR may be included.

## Future Real Data Transition

The demo contract must be replaceable by future backend-authoritative data:

- issuer profile: real RFC, legal name, regime, postal code and controlled CSD
  references;
- receiver profile: real RFC, fiscal name, postal code, regime and CFDI use;
- order data: real services/products, quantity, prices, taxes and payment;
- signed/timbrado output: separate approved signing/PAC architecture.

Real fiscal data is sensitive. Future work must keep CSD private keys and
signing secrets server-side, never in Vue, the repository, browser storage or
logs. Actual signing/timbrado requires a new explicit architecture decision,
security review and business approval.

## Proposed Checkpoints

- **A - Consent UX:** compact responsive banner, session lifecycle, dismissal,
  accessibility and route-preservation tests.
- **B - Fiscal demo contract:** `InvoiceDraft`, synthetic fixture, catalog map,
  calculation rules and shared-source tests.
- **C - Invoice presentation:** complete visual/PDF document, disclaimers,
  derived totals and no-false-certification audit.
- **D - Demo XML:** UTF-8 serializer, official namespace/schema reference,
  download, well-formedness and shared-fixture tests.
- **E - Integrated audit:** responsive/accessibility/browser, PDF/XML parity,
  security/privacy, SAT-truthfulness and production-boundary review.

## Acceptance Criteria

### Consent UX

- AC-01: At 320, 390 and 430px widths the banner is fixed, compact, readable,
  touch-accessible and has no horizontal overflow.
- AC-02: `Aceptar` immediately hides the banner.
- AC-03: `Rechazar` immediately hides the banner.
- AC-04: The Terms link remains reachable through footer/navigation after
  dismissal.
- AC-05: Versioned session state preserves dismissal across same-tab route
  changes and refresh, but creates no backend consent record.

### Invoice demo

- AC-06: Issuer, receiver, concepts, payment, taxes and totals are represented
  by synthetic fixture data.
- AC-07: Totals are calculated from concepts and tax data, not duplicated UI
  constants.
- AC-08: The visual/PDF document contains the complete demo sections and the
  four explicit no-validity disclaimers.
- AC-09: The document never presents a UUID, timbre, seals, CSD or PAC as real.
- AC-10: The PDF and visual output consume the same `InvoiceDraft` source.

### XML

- AC-11: The XML is downloadable, UTF-8 and well-formed.
- AC-12: It uses the official CFDI 4.0 namespace and XSD reference.
- AC-13: It is generated from the same source data as visual/PDF output.
- AC-14: It contains no real secrets, signing material, fake timbre, fake UUID
  or SAT/PAC certification claim.
- AC-15: Tests verify escaping, totals parity, synthetic data and absence of
  `TimbreFiscalDigital`/fake seals.
- AC-16: Documentation distinguishes well-formed/structural XML from valid or
  timbrado CFDI.

### Governance

- AC-17: SPEC-022 and SPEC-023 remain unchanged and separate.
- AC-18: No migration, API, fiscal persistence, PAC or deployment is introduced
  by Definition/Discovery.
- AC-19: Digital Signature remains `PENDING / FUTURE WORK`.
- AC-20: Human approval is required before Checkpoint A implementation.

## Quality and Security Handoff

Future implementation must add frontend unit tests for consent and fiscal
calculations, XML parser/escaping tests, fixture parity tests, download tests,
responsive browser checks at 320x568, 390x844, 430x932, 768x1024 and 1440x900,
and checks that no real fiscal data, keys or certificates are logged or shipped
to the browser.

No implementation, dependency, migration or deployment is authorized by this
Definition/Discovery document.
