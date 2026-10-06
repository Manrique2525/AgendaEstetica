# SPEC-024 CHECKPOINT D REPORT

## Result

```text
SPEC-024: DEVELOPMENT IN PROGRESS
Checkpoint A: APPROVED / COMPLETE / PRODUCTION
Checkpoint B: APPROVED / COMPLETE / PRODUCTION
Checkpoint C: APPROVED / COMPLETE / PRODUCTION
Checkpoint D: IMPLEMENTED / READY FOR HUMAN APPROVAL
CFDI/XML: DEMO SERIALIZER IMPLEMENTED / NOT VALID / NOT TIMBRADO
Digital Signature: PENDING / FUTURE WORK
Production: DEPLOYED THROUGH CHECKPOINT C ONLY
```

Checkpoint D implements a demonstrative, well-formed CFDI 4.0-style XML that is
generated client-side from the SAME canonical `InvoiceDraft` used by the visual
and PDF output, plus a clearly labelled download action. It never claims SAT
validity, timbrado, PAC certification or any signing material.

## Baseline

- Production `main` SHA used as branch base:
  `8f7be61ca390b6fe9edc93c4281b07019758f430` (Merge pull request #8, Checkpoint C).
- Worktree clean before branching.
- Feature branch: `feat/spec-024-demo-cfdi-xml`.

## Official SAT References (recheck, Section 19)

Re-retrieved `2026-10-05` (same official sources as the approved Discovery):

- `https://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd` — HTTP 200, 50 607 bytes,
  sha256 `2489b5b5…`, target namespace `http://www.sat.gob.mx/cfd/4`.
- `https://www.sat.gob.mx/sitio_internet/cfd/catalogos/catCFDI.xsd` — HTTP 200, 5 984 046 bytes,
  sha256 `6c58936c…`, target namespace `http://www.sat.gob.mx/sitio_internet/cfd/catalogos`.
- `https://www.sat.gob.mx/sitio_internet/cfd/tipoDatos/tdCFDI/tdCFDI.xsd` — HTTP 200, 7 315 bytes,
  sha256 `b3b81fe4…`, target namespace `http://www.sat.gob.mx/sitio_internet/cfd/tipoDatos/tdCFDI`.

The namespaces and the `cfdv40.xsd` location match the approved Discovery:
`http://www.sat.gob.mx/cfd/4` with
`http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd`.
No material invalidation of the approved Discovery was found. No XSD is stored
in the repository; the references are documented only.

## Scope audit

Implemented (Checkpoint D only):

- `resources/js/data/cfdiDemoXml.ts` — pure serializer `serializeDemoCfdiXml(draft)`.
- `resources/js/data/cfdiDemoXml.test.ts` — serializer unit tests.
- `resources/js/components/public/DemoInvoiceDocument.vue` — layered demo-XML action
  (`invoice-document__xml-action`) hidden in print.
- `resources/js/components/public/DemoInvoiceDocument.test.ts` — download-action test.
- `docs/specs/SPEC-024-fiscal-demo-consent-ux.md` — status sections updated to D.
- `docs/reports/SPEC-024-CHECKPOINT-D-REPORT.md` — this report.

Not implemented (boundary):

- No backend changes, no API, no persistence, no migration.
- No CFDI XML processing on the server; download is fully client-side.
- No `Sello`, `Certificado`, `NoCertificado`, `NoCertificadoSAT`, UUID,
  `TimbreFiscalDigital` (or its complement), `SelloSAT`, `SelloCFD`, CSD,
  cadena original, private keys, PAC data or SAT verification QR.
- No change to SPEC-022, to the Demo Order payload v2, its SHA-256 integrity
  semantics, or the review/correction behavior.
- No new dependency; no network request is added by the flow.

## Serializer design

- Single source of truth: `serializeDemoCfdiXml(getDemoInvoiceDraft())` consumes
  the same `InvoiceDraft` as the visual invoice and PDF. No duplicated fixture
  and no hardcoded totals.
- Totals and taxes are derived at serialization time from the draft contract
  via `calculateInvoiceTotals` / `calculateTaxCents`; the demo summary is not a
  UI constant.
- Determinism: identical input always produces byte-identical output. No random
  IDs, no timestamps injected at runtime; `Fecha` comes from the deterministic
  fixture value `2026-01-15T10:00:00`.
- Money serialization uses integer fixed-point (cents to 2 decimals,
  e.g. `750.00`, `120.00`, `870.00`) and basis points to 6 decimals
  (`1600` → `0.160000`) via integer math, without float artifacts.
- Escaping of `&`, `<`, `>`, `"`, `'` on every value (attributes and text).
- No `DOCTYPE`, no external entities, no XXE, no remote loading.

## Filename and declaration

- Repository-approved name: `cfdi-demo-sin-validez-fiscal.xml`
  (`docs/specs/SPEC-024-fiscal-demo-consent-ux.md`, XML definition section).
- UTF-8; leading `<?xml version="1.0" encoding="UTF-8"?>` and a leading comment:

```xml
<!--
DOCUMENTO DEMOSTRATIVO.
SIN VALIDEZ FISCAL.
NO ES UN CFDI TIMBRADO.
NO HA SIDO CERTIFICADO POR EL SAT NI POR UN PAC.
-->
```

## Generated XML (summary of the canonical fixtures)

- Root `cfdi:Comprobante`, `Version="4.0"`,
  namespace `http://www.sat.gob.mx/cfd/4`,
  `xsi:schemaLocation="http://www.sat.gob.mx/cfd/4 http://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd"`.
- Document: `Serie="DEMO"`, `Folio="DEMO-FISCAL-0001"`,
  `Fecha="2026-01-15T10:00:00"`, `FormaPago="03"`, `MetodoPago="PUE"`,
  `Moneda="MXN"`, `TipoDeComprobante="I"`, `Exportacion="01"`,
  `LugarExpedicion="01000"`, `SubTotal="750.00"`, `Total="870.00"`.
- Emisor: `Rfc="DEM010101AA0"`, `Nombre="YARIS DEMOSTRACION ACADEMICA"`,
  `RegimenFiscal="601"`.
- Receptor: `Rfc="DEMO010101AAA"`, `Nombre="CLIENTE DEMOSTRACION ACADEMICA"`,
  `DomicilioFiscalReceptor="01000"`, `RegimenFiscalReceptor="612"`,
  `UsoCFDI="S01"`.
- Concepto: `ClaveProdServ="91101701"`, `Cantidad="1"`, `ClaveUnidad="E48"`,
  `Descripcion="Servicio de corte y tinte DEMOSTRATIVO"`,
  `ValorUnitario="750.00"`, `Importe="750.00"`, `ObjetoImp="02"`.
- Impuestos (per-concept and aggregated root): `Base="750.00"`,
  `Impuesto="002"`, `TipoFactor="Tasa"`, `TasaOCuota="0.160000"`,
  `Importe="120.00"`; root `TotalImpuestosTrasladados="120.00"`.
- No retentions, no discounts (`discountCents = 0`), no invented optional
  attributes; `demo="true"` is not emitted inside the SAT namespace.

## XML QA (local WebKit/parser + browser)

- Downloaded file `/tmp/spec024-d-downloaded.xml`: 1 466 bytes, declaration
  present, namespace and schemaLocation present, all canonical tokens present,
  forbidden tokens absent.
- Well-formedness (Python `xml.etree.ElementTree` on the transient artifact
  `/tmp/spec024-d-cfdi-demo.xml`): parse OK; root
  `{http://www.sat.gob.mx/cfd/4}Comprobante`; no `<!DOCTYPE`.
- Browser download (Playwright headless Chrome): suggested filename
  `cfdi-demo-sin-validez-fiscal.xml`; download initiates without navigation
  (URL unchanged after click); Blob type `application/xml;charset=utf-8`;
  object URL revoked ~1 s after download; 0 console errors, 0 Vue warnings.

## UI / download action

- The final invoice header now shows `Descargar XML demostrativo`
  (class `invoice-document__xml-action`) next to `Imprimir / guardar PDF`, with a
  nearby disclosure `XML demostrativo · SIN VALIDEZ FISCAL`.
- The action is layered (not the default action) and never presents itself as a
  real CFDI download label.
- No backend persistence or API; the serializer runs in the browser.

## Print regression

- Print of the demo invoice `/tmp/spec024-d-print.pdf`: 1 page, rotation 0,
  A4 media box (0, 0, 594.96, 841.92) pt; no blank second page.
- The XML action button and its disclosure are `display:none` in print
  (`invoice-document__actions`, `invoice-document__xml-action`).
- Extracted text keeps `$750.00`, `$120.00`, `$870.00 MXN`, `SIN VALIDEZ FISCAL`
  and `DEM010101AA0`; it does not contain the literal "Descargar" (button hidden).
  Column-width glyph wrapping of long synthetic RFCs in the extracted text is a
  PDFKit text-extraction artifact; the on-screen and printed regions contain the
  full values (confirmed via the visual/photo screenshots and DOM assertions).

## Responsive / browser QA

At 320, 390, 430, 768 and 1440 px: no horizontal page overflow, invoice and XML
button visible, button inside the viewport, touch target >= 44 px high
(44–56 px measured), no console errors, no Vue warnings, no overlap between the
XML button, print button, disclosure text or the invoice title.

## Tests

- `resources/js/data/cfdiDemoXml.test.ts` (9 tests): declaration/comment/root,
  well-formedness + namespaces, no DOCTYPE/entities, canonical parity
  (`750.00`/`120.00`/`870.00`/`DEM010101AA0`/`DEMO010101AAA`/`91101701`/`E48`/
  `0.160000`), escaping round-trip (`&<>"'`), determinism, derived totals from
  the draft contract, absence of invented signing/timbrado artifacts and
  approved filename.
- `resources/js/components/public/DemoInvoiceDocument.test.ts`: download action
  test (Blob type, anchor click, URL revocation) plus the pre-existing print/no
  artifact assertions.
- Frontend suite: 27 files / 114 tests PASS.
- Backend suite: `php artisan test` 208 tests / 1202 assertions PASS.

## Quality gates

- `composer validate --strict` PASS · `composer audit` 0 advisories.
- `vendor/bin/pint --test` PASS · `phpstan analyse` 0 errors.
- `npm run typecheck` PASS · `npm run lint` PASS · `npm run build` PASS ·
  `npm audit` 0 vulnerabilities.
- `git diff --check` clean.

## XML validity boundary (truthfulness)

```text
XML well-formed: YES
CFDI 4.0 structural mapping: YES
SAT XSD validation without real signing material: NOT CLAIMED
SAT fiscal validity: NO
Timbrado: NO
PAC certification: NO
Digital Signature: PENDING / FUTURE WORK
```

No invented `TimbreFiscalDigital`, UUID, fake seals, fake certificate, CSD
private key, cadena original or SAT verification QR is produced or shipped.

## Transient QA artifacts (not committed)

- `/tmp/spec024-d-cfdi-demo.xml` (serializer output)
- `/tmp/spec024-d-downloaded.xml` (browser download)
- `/tmp/spec024-d-print.pdf` (print regression)
- `/tmp/spec024-d-qa/` (Playwright harnesses and screenshots)

## Remote Quality

Verified on the exact final branch SHA:

- SHA `cee3deab73ac58827b7e6aa36958ba1798791beb` (branch `feat/spec-024-demo-cfdi-xml`).
- GitHub Actions run `37410606338` (Quality, event `push`): conclusion `success`.
- Jobs: `Frontend quality` success · `Backend quality` success.
- Overall: SUCCESS.

## Checkpoint D status

```text
Checkpoint D: IMPLEMENTED / READY FOR HUMAN APPROVAL
Digital Signature: PENDING / FUTURE WORK
Production: THROUGH CHECKPOINT C ONLY
Remote Quality: PASSED ON FINAL BRANCH SHA (run 37410606338)
```

STOP. Submit Checkpoint D for explicit human approval. Do not merge
`feat/spec-024-demo-cfdi-xml`, do not deploy it, do not start Checkpoint E or
any real signing/timbrado/PAC/CSD work.