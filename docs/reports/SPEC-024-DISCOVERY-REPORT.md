# SPEC-024 DEFINITION + TECHNICAL DISCOVERY REPORT

## Result

`SPEC-024 - Fiscal Demo & Consent UX` is defined as:

```text
DEFINITION / TECHNICAL DISCOVERY COMPLETE / READY FOR HUMAN DEVELOPMENT REVIEW
```

Implementation is not authorized. `main` and production were not changed by
this discovery.

## Repository Evidence

The current repository contains:

- `TermsConsentBanner.vue`: fixed banner, local component state only, no
  persistence; Accept/Reject currently retain an in-component status message
  instead of removing the banner.
- `PublicLayout.vue`: footer link to `/terminos-condiciones`.
- `TermsAndConditionsPage.vue`: provisional academic Terms/Privacy content.
- `DemoOrderPage.vue`: local-only order demo, synthetic fields, invoice
  checkbox, static PDF download and SHA-256 integrity display.
- `demoIntegrity.ts`: canonical payload version `2` with `requestInvoice`.
- `AcademicPhaseOnePage.vue`: truthful authentication, integrity, invoice and
  signature-pending presentation.
- `public/demo/factura-demostracion-sin-validez-fiscal.pdf`: static educational
  artifact; no runtime PDF generation.
- No fiscal model, invoice API, migration, XML generator, CFDI service, PAC or
  signing key exists.

## Consent Findings

The current banner uses a fixed bottom surface with `px-5 py-4`, verbose copy,
three stacked content/status lines and a mobile column of actions. It is
semantically accessible but can occupy too much of a 320px-height phone.

Recommended development boundary:

- shorten the mobile copy and heading;
- use compact responsive padding and a two-action touch-safe layout;
- preserve the Terms link in the banner and footer;
- remove the banner with `v-if` after either decision;
- use `sessionStorage` key `yaris.academic-consent.v1` only for same-tab UX;
- treat values as academic display state, never as legal consent.

No backend consent persistence is justified by the current requirement.

## Fiscal Findings

The current PDF and UI correctly disclaim fiscal validity but do not expose a
complete invoice-like structure. The next implementation needs one pure,
reusable fixture contract consumed by visual UI, PDF artifact and XML serializer.
The fixture must be synthetic and clearly labelled.

Recommended tax scenario for the academic fixture: one synthetic taxable
service, one transferred IVA scenario, no retentions, with all rates and keys
resolved from official SAT catalogs before coding. This is not a statement of
Yaris's real tax treatment or tax advice.

Totals must derive from concept data:

```text
subtotal - discounts + transferred taxes - withheld taxes = total
```

Future backend data can replace the fixture without rebuilding the UI if issuer,
receiver, concepts, taxes, payment and metadata are separate structures.

## Official SAT Sources

The official CFDI 4.0 XSD was retrieved during discovery:

- `https://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd`
- namespace: `http://www.sat.gob.mx/cfd/4`
- schema reference: `http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd`
- imported official namespaces include SAT catalogs and `tdCFDI` types.

The XSD confirms, among others, the required `Version=4.0`, issuer and receiver
fields, concepts, taxes, totals, `Exportacion` and `LugarExpedicion`. It also
requires signing/certificate attributes such as `Sello`, `NoCertificado` and
`Certificado`; the project must not fake those values.

CFF Articles 29 and 29-A are recorded as the legal context for a future real
CFDI architecture. They are not used to characterize this academic artifact as
tax-compliant or as tax advice.

Official SAT consultation URLs were used as the intended authority references:

- `https://www.sat.gob.mx/consultas/35025/formato-de-factura-electronica-(anexo-20)`
- `https://www.sat.gob.mx/consultas/42968/catalogos-de-comprobantes-fiscales-digitales-por-internet`
- `https://www.sat.gob.mx/consultas/92764/comprobante-fiscal-digital-por-internet`

Those HTML consultation requests returned gateway timeout responses during this
discovery session. No blog or third-party article was used as authority. Before
implementation, the official Anexo 20 and current catalog files must be
retrieved and version/date recorded in the implementation report.

## CFDI Mapping Handoff

The future contract should map:

- issuer: `Rfc`, `Nombre`, `RegimenFiscal`;
- receiver: `Rfc`, `Nombre`, `DomicilioFiscalReceptor`,
  `RegimenFiscalReceptor`, `UsoCFDI`;
- comprobante: `Version`, `Serie`, `Folio`, `Fecha`, `FormaPago`,
  `CondicionesDePago`, `SubTotal`, `Descuento`, `Moneda`, `TipoCambio`,
  `Total`, `TipoDeComprobante`, `Exportacion`, `MetodoPago`,
  `LugarExpedicion`;
- concepts: `ClaveProdServ`, `Cantidad`, `ClaveUnidad`, `Unidad`,
  `Descripcion`, `ValorUnitario`, `Importe`, `Descuento`, `ObjetoImp`;
- tax detail: `Base`, `Impuesto`, `TipoFactor`, `TasaOCuota`, `Importe`;
- totals: transferred and withheld tax aggregates.

Catalog sources must be recorded for regime, CFDI use, payment form, payment
method, currency, exportation, product/service, unit, tax, factor and taxable
object and postal code. In XSD/catalog terms this includes
`c_RegimenFiscal`, `c_UsoCFDI`, `c_FormaPago`, `c_MetodoPago`, `c_Moneda`,
`c_Exportacion`, `c_ClaveProdServ`, `c_ClaveUnidad`, `c_Impuesto`,
`c_TipoFactor`, `c_ObjetoImp` and `c_CodigoPostal`. Codes must not be guessed
in the implementation.

## XML Boundary

The XML should be generated from the shared fixture and named
`cfdi-demo-sin-validez-fiscal.xml`. It may be structurally shaped and use the
official namespace/schema reference, but it must not contain:

- `TimbreFiscalDigital`;
- UUID/Folio Fiscal presented as real;
- `SelloCFD` or `SelloSAT`;
- `NoCertificadoSAT`;
- real or fake CSD certificate material;
- PAC identity or SAT validation QR.

The acceptance boundary is:

```text
well-formed XML: YES
CFDI 4.0 structural mapping: YES
full XSD/fiscal validity: NOT CLAIMED
timbrado: NO
SAT/PAC certification: NO
```

Because signing attributes are required by the official XSD, full XSD-valid
output must not be manufactured by inserting fake values. A comment before the
root element is safer than an invented attribute in the SAT namespace:

```xml
<!-- DOCUMENTO DEMOSTRATIVO. SIN VALIDEZ FISCAL. NO TIMBRADO. -->
```

## Future Real-Fiscal Boundary

Real issuer/receiver/order data, CSD handling, signing, certificate storage,
PAC calls, fiscal persistence and privacy controls require a future approved
architecture. Private keys must remain server-side and never reach Vue,
browser storage, logs or the repository. SPEC-024 does not implement Digital
Signature.

## Proposed Checkpoints

- A: compact consent UX, session lifecycle, accessibility and route reachability.
- B: fiscal-demo data contract, synthetic fixtures, catalogs and calculations.
- C: complete visual/PDF invoice and disclaimer audit.
- D: shared-fixture XML generation, download and well-formedness tests.
- E: integrated responsive/browser/accessibility/security/SAT-truthfulness audit.

## Test Handoff

Future tests must cover:

- banner initial/accepted/rejected states at 320, 390 and 430px;
- session persistence and malformed-storage fallback;
- Terms footer reachability after dismissal;
- fiscal totals, rounding policy, tax aggregation and fixture mapping;
- invoice/PDF/XML parity;
- XML UTF-8, escaping, namespace and schema-location structure;
- no timbre, UUID, fake seals, certificates, PAC or real secrets;
- visual no-overflow and accessibility matrix through 1440px desktop.

## Scope Audit

```text
Application code changed: NONE
Backend/API: NONE
Database/migrations: NONE
Production/secrets: NONE
SAT/PAC calls: NONE
Real fiscal data: NONE
Digital Signature: PENDING / FUTURE WORK
SPEC-022: NOT REOPENED
SPEC-023: NOT MODIFIED
Deployment: NOT ATTEMPTED
```

STOP. Submit SPEC-024 for human development review. Do not start
Checkpoint A without explicit approval.
