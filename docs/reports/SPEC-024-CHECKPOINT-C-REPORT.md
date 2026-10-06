# SPEC-024 CHECKPOINT C REPORT

## Result

```text
SPEC-024: DEVELOPMENT IN PROGRESS
Checkpoint A: APPROVED / COMPLETE / IN MAIN / IN PRODUCTION
Checkpoint B: APPROVED / COMPLETE / IN MAIN / IN PRODUCTION
Checkpoint C: IMPLEMENTED / READY FOR HUMAN APPROVAL
Checkpoint D: NOT AUTHORIZED
XML: NOT IMPLEMENTED
Digital Signature: PENDING / FUTURE WORK
```

Checkpoint C implements only the complete academic invoice presentation and
print/PDF demonstration. It does not add XML, CFDI signing, PAC/CSD, real
fiscal data, fiscal persistence, backend/API work or deployment.

## Entry Point

The existing flow remains:

```text
/demo/pedido
-> Solicitar factura
-> Revisar demostracion
-> Confirmar demostracion
-> canonical DemoInvoiceDocument
```

The existing Demo Order integrity payload version `2` was not changed.

## Canonical Source

The invoice consumes `getDemoInvoiceDraft()` and
`calculateInvoiceTotals()`. No fiscal values are duplicated in the Vue
template. Screen values, totals, tax display and print output use the same
`InvoiceDraft` contract from Checkpoint B.

## Presented Data

### Document

- Version: `4.0`.
- Serie/Folio: deterministic `DEMO / DEMO-FISCAL-0001`.
- Fecha: deterministic fixture date.
- TipoDeComprobante: `I`.
- Exportacion: `01`.

### Issuer

- Name: `YARIS DEMOSTRACION ACADEMICA`.
- Synthetic RFC: `DEM010101AA0`.
- RegimenFiscal: `601`.
- LugarExpedicion: `01000`.

### Receiver

- Name: `CLIENTE DEMOSTRACION ACADEMICA`.
- Synthetic RFC: `DEMO010101AAA`.
- DomicilioFiscalReceptor: `01000`.
- RegimenFiscalReceptor: `612`.
- UsoCFDI: `S01`.

### Payment

- FormaPago: `03`.
- MetodoPago: `PUE`.
- Moneda: `MXN`.
- Conditions: academic scenario only.

### Concept and tax

- ClaveProdServ: `91101701`.
- Cantidad: `1`.
- ClaveUnidad: `E48`.
- Description: synthetic application/demo description.
- ValorUnitario/Importe: `$750.00`.
- ObjetoImp: `02`.
- Base: `$750.00`.
- Impuesto: `002`.
- TipoFactor: `Tasa`.
- TasaOCuota: `0.160000` / `16%`.
- Transferred tax: `$120.00`.
- Total: `$870.00 MXN`.

## Truthfulness

The document prominently displays:

```text
DOCUMENTO DEMOSTRATIVO
SIN VALIDEZ FISCAL
NO ES UN CFDI TIMBRADO
NO HA SIDO CERTIFICADO POR EL SAT NI POR UN PAC
```

It does not display or generate UUID, TimbreFiscalDigital, SelloSAT,
SelloCFD, CSD, PAC certification or SAT verification QR. Digital Signature
remains pending.

## Print/PDF Architecture

The invoice provides an `Imprimir / guardar PDF` action backed by browser
print CSS. Print rules hide the application shell and action, preserve the
invoice document and keep the disclaimer visible.

The static artifact at
`public/demo/factura-demostracion-sin-validez-fiscal.pdf` was regenerated from
the canonical invoice screen using transient Chrome print output. It is a
132603-byte, two-page academic artifact, not a runtime PDF dependency or a
fiscal document.

XML is deliberately not implemented; XML belongs to Checkpoint D.

## Tests and QA

- Screen invoice tests assert issuer, receiver, concept, totals and disclaimers.
- Print action test verifies `window.print()` and absence of downloads/fiscal
  certification artifacts.
- Frontend: `26 files / 104 tests` PASS.
- Backend: `208 tests / 1206 assertions` PASS.
- ESLint, TypeScript, build, Composer, Pint, PHPStan and audits: PASS.
- A transient frontend flake was stabilized: the invoice-demo integration test
  now flushes both pending promises and the Vue render tick after confirmation,
  matching the pattern used by the sibling demo tests. The file passes reliably
  across repeated runs.
- Browser/print QA remains required before formal C approval.

## Scope Audit

```text
Backend/API: UNCHANGED
Schema/migrations: UNCHANGED
XML/CFDI serializer: NOT IMPLEMENTED
PAC/CSD: NONE
Real fiscal data: NONE
New dependency: NONE
Demo Order payload v2: UNCHANGED
Checkpoint D: NOT AUTHORIZED
```

STOP. Submit Checkpoint C for explicit human approval. Do not start Checkpoint D.
