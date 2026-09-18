# SPEC-024 CHECKPOINT B REPORT

## Result

```text
SPEC-024: DEVELOPMENT IN PROGRESS
Checkpoint A: APPROVED / COMPLETE / IN MAIN / IN PRODUCTION
Checkpoint B: IMPLEMENTED / READY FOR HUMAN APPROVAL
Checkpoint C: NOT AUTHORIZED
XML: NOT IMPLEMENTED
Digital Signature: PENDING / FUTURE WORK
```

Checkpoint B implements only a TypeScript fiscal-demo contract, one synthetic
fixture, deterministic fixed-point calculations and automated tests. It does
not change the UI, static PDF, XML, backend, API, database, or production.

## Official Sources

Retrieved `2026-09-18`:

- `https://www.sat.gob.mx/sitio_internet/cfd/4/cfdv40.xsd`
- `https://www.sat.gob.mx/sitio_internet/cfd/catalogos/catCFDI.xsd`
- `https://www.sat.gob.mx/sitio_internet/cfd/tipoDatos/tdCFDI/tdCFDI.xsd`

The CFDI 4.0 XSD confirms the namespace, document fields and the signing/
certificate attributes required by a real CFDI. The current catalog XSD
contains the selected enumeration values.

The SAT catalog consultation HTML endpoint returned HTTP 504 in this
environment. The selected `ClaveProdServ` was therefore verified as an
official catalog enumeration, but no human-readable catalog label is claimed
in the application. The fixture uses its own synthetic description and does
not present it as SAT text.

The XSD catalogs define individual value domains, but do not encode all
cross-field business relationships. This report therefore distinguishes
catalog membership from fiscal compatibility. The selected scenario is
internally coherent for academic purposes, not SAT-validated.

The final party contract makes person type explicit: the issuer is a synthetic
persona-moral-shaped fixture using regime `601`; the receiver is a synthetic
persona-fisica-shaped fixture using regime `612` and `UsoCFDI=S01`. This is an
academic compatibility scenario, not RFC registry or taxpayer-applicability
validation.

## Verified Catalog Values

| Field | Value | Evidence |
| --- | --- | --- |
| `ClaveProdServ` | `91101701` | present in `c_ClaveProdServ` enumeration |
| `ClaveUnidad` | `E48` | present in `c_ClaveUnidad` enumeration |
| Issuer `RegimenFiscal` | `601` | present in `c_RegimenFiscal` enumeration |
| Receiver `RegimenFiscalReceptor` | `612` | present in `c_RegimenFiscal` enumeration |
| `UsoCFDI` | `S01` | present in `c_UsoCFDI` enumeration |
| `FormaPago` | `03` | present in `c_FormaPago` enumeration |
| `MetodoPago` | `PUE` | present in `c_MetodoPago` enumeration |
| `Moneda` | `MXN` | present in `c_Moneda` enumeration |
| `TipoDeComprobante` | `I` | present in `c_TipoDeComprobante` enumeration |
| `Exportacion` | `01` | present in `c_Exportacion` enumeration |
| `ObjetoImp` | `02` | present in `c_ObjetoImp` enumeration |
| `Impuesto` | `002` | present in `c_Impuesto` enumeration |
| `TipoFactor` | `Tasa` | present in `c_TipoFactor` enumeration |
| Demo postal code | `01000` | present in `c_CodigoPostal` enumeration; public value, not real Yaris data |

These values prove catalog enumeration membership, not business tax treatment
or SAT registration of the synthetic identities.

## Postal and Compatibility Audit

- `LugarExpedicion` and `DomicilioFiscalReceptor` both use `01000` in the
  academic fixture. It is a current catalog value and is not asserted to be
  Yaris's real fiscal address or the receiver's real address.
- `FormaPago=03` and `MetodoPago=PUE` describe the selected academic
  single-payment transfer scenario; this is not a production payment rule.
- `ObjetoImp=02` with `Impuesto=002`, `TipoFactor=Tasa` and `0.160000` is an
  internally coherent transferred-tax demonstration.
- `TipoDeComprobante=I` and `Exportacion=01` describe a domestic income
  invoice-shaped scenario.
- The current XSD proves each code domain but does not prove receiver regime /
  `UsoCFDI` relationships or SAT taxpayer registry validity. The `612` +
  `S01` pair is retained as the documented academic compatibility scenario; a
  real CFDI must revalidate it against current SAT relationship rules and the
  receiver's actual fiscal profile.

## Contract

Implementation path:

```text
resources/js/data/fiscalDemo.ts
```

The contract separates:

- `IssuerFiscalProfile`;
- `ReceiverFiscalProfile`;
- `InvoiceMetadata`;
- `PaymentInformation`;
- `InvoiceConcept` and `InvoiceConceptTax`;
- `DemoFiscalDisclaimer`;
- `InvoiceDraft`;
- derived `InvoiceTotals`.

Future visual/PDF/XML consumers can use the same `InvoiceDraft` without moving
fiscal data into Vue templates. No persistence or API exists.

## Synthetic Fixture

The exported fixture is `getDemoInvoiceDraft()`.

- Issuer: `YARIS DEMOSTRACION ACADEMICA`, person type `moral`, regime `601`,
  synthetic 12-character RFC-like value `DEM010101AA0`.
- Receiver: `CLIENTE DEMOSTRACION ACADEMICA`, person type `fisica`, regime
  `612`, `UsoCFDI=S01`, synthetic 13-character RFC-like value
  `DEMO010101AAA`.
- RFC-like values are synthetic, not verified against the RFC registry and not
  for real invoicing.
- One synthetic service concept.
- One transferred-tax scenario.
- No retentions.
- Currency: `MXN`.
- Payment scenario: catalog-backed `03` / `PUE`, explicitly academic.
- No real client, business, tax, CSD, PAC or certificate data.
- Demo postal code: `01000`, valid catalog value, not real Yaris fiscal data.

Tax scenario:

```text
ESCENARIO FISCAL DEMOSTRATIVO
one synthetic taxable service
IVA-style transferred rate: 16% represented as 1600 basis points
retentions: 0
not Yaris tax configuration
not tax advice
```

The 16% value is the academic IVA scenario supported by the applicable general
IVA rule (LIVA Article 1, official legal source) and represented in the CFDI
tax shape as `Impuesto=002` + `TipoFactor=Tasa`. It is not a Yaris tax
configuration and does not make the fixture a valid CFDI.

## Calculations

Money uses integer cents. Tax uses integer basis points. The current fixture
derives:

```text
quantity = 1
unit price = 75000 cents
subtotal = 75000 cents
transferred tax = 12000 cents
retentions = 0 cents
total = 87000 cents
```

`calculateInvoiceTotals()` derives all values from concept data. Tests verify
determinism, quantity mutation and fixed-point tax rounding.

## Truthfulness and Security

- UUID/Folio Fiscal: absent.
- `TimbreFiscalDigital`: absent.
- `SelloSAT`: absent.
- `SelloCFD`: absent.
- `NoCertificadoSAT`: absent.
- CSD material: absent.
- PAC integration: absent.
- Digital Signature: remains pending.
- Demo disclaimers explicitly identify academic/no-validity status.
- Existing Demo Order canonical payload v2 was not changed.

## Scope Audit

```text
Frontend UI: UNCHANGED
Backend/API: UNCHANGED
Schema/migrations: UNCHANGED
Static PDF: UNCHANGED
XML: NOT IMPLEMENTED
SAT API/PAC: NONE
Real fiscal data: NONE
New dependency: NONE
Production: UNCHANGED
```

## Tests

`resources/js/data/fiscalDemo.test.ts` covers:

- synthetic issuer/receiver and catalog values;
- deterministic subtotal/tax/total calculation;
- quantity mutation;
- fixed-point rounding;
- absence of fiscal certification artifacts;
- complete demo disclaimers.

Checkpoint B does not start visual invoice work or XML generation. Submit this
report for explicit human approval before Checkpoint C.
