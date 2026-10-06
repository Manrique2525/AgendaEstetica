# SPEC-024 Checkpoint C — FINAL VISUAL ACCEPTANCE REPORT

- **SPEC**: 024 — Factura de demostración fiscal (consent + UX)
- **Checkpoint**: C — Factura de demostración visual/PDF
- **Date**: 2026-10-05
- **Verifier**: OpenCode (control de calidad automatizado, sesión transitoria)
- **Branch**: `feat/spec-024-complete-invoice-demo`
- **PR**: #8 `SPEC-024 Checkpoint C: invoice demo visual/PDF` — **OPEN / MERGEABLE / CLEAN**, base `main`

> Este reporte documenta evidencia verificable por máquina y los hallazgos humanos. La **interpretación visual humana final** sigue **PENDIENTE/AWAITING** por parte del revisor humano sobre los PNG upright finales.

---

## 0. Revisiones del branch (en orden ascendente)

- `c58dd49` `feat: complete SPEC-024 checkpoint C invoice demo` (original de C, conservado)
- `492022e` `fix: stabilize invoice demo test race`
- `15c71d3` `fix: remediate source-map-js npm advisory`
- `e94c32b` `fix: wrap demo invoice totals rows to avoid 320px overflow` (QA sesión previa)
- `86ebe45` `fix: prevent invoice summary print splitting` (QA sesión previa)
- `ff1d59b` `fix: finalize SPEC-024 invoice print layout` (**código de esta pasada final**)
- `2ba7453` `docs: record SPEC-024 final acceptance evidence` (este reporte, trackeado)

### HEAD verificado (local == remoto == head del PR)

- Inicio de la pasada: `86ebe45785bb31a4f6803b663de8bfef3eae7381`
- Cierre de la pasada: `ff1d59bd18e79f170af822e0917b5e0764889ba9` (código final) + commit de este reporte.

- Commit original de C conservado intacto; todos los commits son aditivos.
- **No se añadieron dependencias de tooling** (Playwright en cache `npx` transitorio; scripts QA solo en `/tmp`).

---

## 1. Hallazgos humanos (revisión visual)

### 1a. Sesión previa (corregida en `86ebe45`)
El bloque IMPUESTOS / TOTALES quedaba dividido en el salto de página con `$120.00` huérfano. Corregido con `break-inside: avoid` en los hooks `invoice-summary` / `invoice-disclaimers` (Print-scope only).

### 1b. Pasada final (esta pasada)
- `sips-page1.png` se ve **BIEN** (referencia visual upright; NO rediseñar).
- `pdf-page1.png` / `pdf-page2.png` (renders Swift previos) aparecían **girados 180°** respecto a `sips-page1.png`.
- La factura demo generaba **2 páginas PDF** cuando visualmente cabe en 1 página A4; la página 2 quedaba con demasiado espacio vacío.

Clasificación: **CHECKPOINT C PRINT-SCOPE POLISH** — autorizada corrección mínima de CSS/layout de print y determinación empírica de orientación. No se autoriza rediseño visual.

## 2. Decisiones de diseño tomadas (print)

1. **1 página A4**: se priorizó que la factura demo quepa limpiamente en una sola hoja A4 (210×297 mm) mediante densidad print-only (`@page { size: A4 portrait; margin: 10mm 12mm }`), **sin** `transform: scale`, **sin** `zoom`, **sin** ocultar campos ni recortar contenido (body nunca baja de ~9–10pt: fuentes del documento intactas).
2. **Paginación de respaldo** (si el contenido creciera): bloques completos por página (`break-inside: avoid`), nunca una tarjeta partida ni un total huérfano.

## 3. Corrección aplicada (print layout ONLY, sin cambio funcional)

Archivo: `resources/js/components/public/DemoInvoiceDocument.vue` — solo bloques `@page` y `@media print`:

- `@page { size: A4 portrait; margin: 10mm 12mm }` → hoja A4 orientada en portrait (antes: `Letter` por defecto del navegador).
- `body > * { visibility:hidden; height:0; min-height:0; overflow:hidden }` → se colapsa y **recorta** el flujo de la app (el invoice escapa del clip: su containing block es el ICB, posición `absolute`), eliminando la **página fantasma vacía** que generaba Chromium.
- `html, body { height:auto }`; `header, footer { display:none }`.
- `.invoice-document { position:absolute; top/left/right:0; width:100%; max-width:none; padding:14px 20px; border:0; box-shadow:none }`.
- Densidad print-only: márgenes/gaps/paddings reducidos (header, grillas, tarjetas, dl, tabla conceptos, disclaimers).
- `.invoice-summary` / `.invoice-disclaimers` siguen con `break-inside: avoid`.
- `.invoice-document__print-action { display:none }`.

- **Datos funcionales modificados: NO** (ni `InvoiceDraft`, fixtures, emisor, receptor, conceptos, cálculos, totales, rutas, backend, API, DB, XML ni firma digital).

## 4. Resultado de paginación verificado (PDF regenerado 1 página)

| Página | Contenido |
|---|---|
| **1** | Todo el documento: Identidad, Emisor, Receptor, Metadata, Pago, Conceptos, **Impuestos** (Base $750.00 · 002/Tasa · 16.0000% · Trasladado $120.00), **Totales** (Subtotal $750.00 · Descuento $0.00 · Trasladados $120.00 · Total $870.00 MXN) y **disclaimers** (NO ES UN CFDI TIMBRADO / NO HA SIDO CERTIFICADO POR EL SAT NI POR UN PAC / SIN VALIDEZ FISCAL) |
| **2** | **NO EXISTE** (PDF = 1 página exacta) |

- PDFKit `pageCount`: **1**. Página 2 fantasma (0 caracteres / 0 tinta): **ELIMINADA**.
- Tinta página 1: y=67…782 dentro del MediaBox 594×842 pt (A4) → margen interno ~59px (< 73px disponibles). Article print h=988px ≤ contentHeightPx=1047px.
- «Resumen» (Impuestos+Totales, relativo): top 660 → bottom 884 (h 224), íntegro en página 1.
- Disclaimers: top 896 → bottom 974 (h 78), íntegros en página 1.
- Tax card dividido: **NO** · Totals card dividido: **NO** · `$120.00` huérfano: **NO** · Disclaimer partido: **NO**.
- Sin texto recortado (todo el texto canónico presente en página 1, 901 caracteres extraídos).

## 5. Determinación empírica de orientación PDF

Contexto: el revisor humano vio `sips-page1.png` upright y `pdf-page1/2.png` (renders Swift previos) girados 180°. Se determinó la causa sin asumir:

| Evidencia | Resultado |
|---|---|
| `/Rotate` en el binario del PDF | **ausente** → rotación 0 (portrait upright) |
| `MediaBox` binario | `0 0 594.95996 841.91998` → A4 portrait |
| PDFKit `page.rotation` | **0** en la única página |
| Render Swift **sin flip** vs `sips` (referencia upright) | diff medio por píxel = **55.92** |
| Render Swift **con flip** (y → -y) vs `sips` | diff medio = **68.67** |

**Conclusión**: el PDF está **upright / portrait** (no rotado). La inversión de los PNG previos provenía del transform de mi renderer (`translateBy + scaleBy(1,-1)`), no del PDF. Se corrigió `render.swift` (render **sin flip**) y se regeneraron los PNG finales upright.

## 6. QA de navegador responsive (regresión, sin cambios fuera de print)

Viewports: `320x568`, `390x844`, `430x932`, `768x1024`, `1440x900`.

| Viewport | pageOverflow | invoiceOverflow | clipping/overlap (issues) |
|---|---|---|---|
| 320x568 | false | false | 0 |
| 390x844 | false | false | 0 |
| 430x932 | false | false | 0 |
| 768x1024 | false | false | 0 |
| 1440x900 | false | false | 0 |

- Tabla de conceptos: scroll horizontal **contenido** en `.overflow-x-auto` en móviles (diseño aprobado, sin desbordamiento de página).
- Consola: **0** errors / **0** warnings / **0** pageerrors / **0** failedRequests.
- `visibility` de bloques print: warning, emisor, receptor, metadata, conceptos, impuestos, totales → `visible`; botón imprimir `display:none`; `header`/`footer` ocultos.
- `overflowHiddenAncestors`: 1 (`DIV#app`) — intencional (colapsa el flujo); **no recorta el invoice** (su containing block es el ICB; ver §3 y §4).
- Flujo: confirmación de demostración OK, back recarga a edición, H1 intacto.

## 7. QA PDF estructural y contenido

- PDF transitorio: `/tmp/spec024-c-final-qa.pdf` · **86581 bytes** · **1 página** (PDFKit `pageCount` = 1) · rotation 0 · MediaBox A4.
- Texto canónico (extracción Swift/PDFKit normalizada): **todos** los tokens presentes y **una sola vez** cada dato de total (Subtotal, Descuento, Trasladados, Total). `$120.00` aparece 2 veces en contextos legítimos (tax card Trasladado y Totales Impuestos trasladados) — coherente con 16% sobre $750.
- Importes extraídos: `$0.00`×1 (Descuento) · `$120.00`×2 (Trasladado + Impuestos trasladados) · `$750.00`×4 (Unitario, Importe, Base, Subtotal) · `$870.00`×1 (Total) — sin duplicados de total.
- Tokens prohibidos **AUSENTES** (demo honesta): `UUID`, `TimbreFiscalDigital`, `SelloSAT`, `SelloCFD`, `NoCertificadoSAT`, `CSD`, PAC, URL SAT de validación.
- Disclaimers completos: «NO ES UN CFDI TIMBRADO» / «NO HA SIDO CERTIFICADO POR EL SAT NI POR UN PAC» / «SIN VALIDEZ FISCAL».

## 8. Artefactos

Ruta limpia para revisión humana final: `/tmp/spec024-c-final-pdf-review/`

- `screen-reference.png` (638×1291) — captura browser upright del artículo en la app (por construcción).
- `pdf-page1.png` (1189×1683) — página 1 del PDF final, render Swift/PDFKit **sin flip** (upright).
- Página 2: **no aplica** (PDF de 1 página).

Soporte: `/tmp/spec024-c-browser-qa/` (capturas 5 viewports + `print-media-layout.png`), `/tmp/spec024-c-qa/report.json` (evidencia JSON completa), `/tmp/spec024-c-pdf-qa/` (render/dump/orient verifiers), `/tmp/spec024-c-final-qa.pdf` (PDF final).

---

## 9. Quality gates (locales) — pasada final

| Gate | Resultado |
|---|---|
| `composer validate --strict` | válido |
| `composer audit` | 0 advisories |
| `vendor/bin/pint --test` | passed |
| `vendor/bin/phpstan analyse` | passed (0 errores) |
| `php artisan test` | **PASS** — 208 tests / 1202 assertions |
| `npm run lint` | PASS |
| `npm run typecheck` | PASS |
| `npm run test` | **PASS** — 26 files / 104 tests |
| `npm run build` | PASS |
| `npm audit` | 0 vulnerabilities |
| `git diff --check` | limpio |

## 10. Remote Quality

| Run | Event | Workflow/Jobs | Conclusión |
|---|---|---|---|
| 37405992561 | pull_request | Quality | **success** (SHA `86ebe45`) |
| 37405987610 | push | Quality: **Backend quality success** · **Frontend quality success** | **success** (SHA `86ebe45`) |
| 37408366023 | pull_request | Quality: **Backend quality success** · **Frontend quality success** | **success** (SHA `ff1d59b`, código final) |
| 37408362206 | push | Quality: **Backend quality success** · **Frontend quality success** | **success** (SHA `ff1d59b`, código final) |
| 37408535584 | pull_request | Quality: **Backend quality success** · **Frontend quality success** | **success** (SHA `2ba7453`, HEAD final) |
| 37408532339 | push | Quality: **Backend quality success** · **Frontend quality success** | **success** (SHA `2ba7453`, HEAD final) |

Sin despliegue (`deploy-production.yml` solo actúa ante push/merge a `main`).

---

## FINAL PRINT POLISH REPORT

### A. GIT EVIDENCE
1. Branch de trabajo: **OPEN** `feat/spec-024-complete-invoice-demo`
2. Base: `main`
3. PR: **#8 OPEN / MERGEABLE / CLEAN** (verificado al inicio y al cierre de la pasada)
4. Commits de la pasada (aditivos, **sin amend/rebase/force-push**): `ff1d59b` (código) + commit de documentación (este reporte)
5. HEAD verificado local == origin == head del PR al iniciar: `86ebe45785bb31a4f6803b663de8bfef3eae7381`
6. HEAD final (código) tras commits de la pasada: `ff1d59bd18e79f170af822e0917b5e0764889ba9`
7. Árbol de trabajo al iniciar: limpio salvo el reporte (untracked)
8. Reporte de aceptación ahora **trackeado en el repo** (no queda untracked)
9. Sin merge a `main`, sin commit no pusheado — push verificado con Remote Quality en el SHA final

### B. PRINT DESIGN
10. Decisión: **1 página A4 portrait** para la factura demo
11. Mecanismo: CSS print-only + `@page { size: A4 portrait; margin: 10mm 12mm }`
12. Sin `transform: scale` / `zoom` (render nativo, no comprimido)
13. Sin ocultar campos ni recortar contenido (901/901 caracteres en página 1)
14. Body nunca degrada por debajo de ~9–10pt (fuentes intactas)
15. Paginación de respaldo respetada: `break-inside: avoid` en resumen y disclaimers
16. Fuerza: solo archivo `DemoInvoiceDocument.vue`, bloques print/`@page`
17. Sin cambio de datos funcionales ni fixtures
18. `break-before` forzado: NO (resumen fluye naturalmente dentro de la página 1)

### C. PDF ORIENTATION
19. `/Rotate` en binario: **ausente** → rotation 0
20. `MediaBox` binario: `0 0 594.96 841.92` = A4 portrait
21. PDFKit `rotation`: **0** (única página)
22. Render **sin flip** vs sips: diff 55.92
23. Render **con flip**: diff 68.67 → peor (invertido)
24. Conclusión: PDF **upright / portrait** (no rotado)
25. Causa de la inversión de PNG previos: transform `translateBy + scaleBy(1,-1)` en mi `render.swift` (renderer, no el PDF)
26. Corrección: `render.swift` ahora renderiza **sin flip**
27. PNG finales de revisión: **upright** (por construcción y por comparación con sips)

### D. PAGE 1
28. Identidad de factura presente y legible
29. Emisor presente y completo (nominal / RFC sintético / régimen 601 / Yer 01000 / versión 4.0 / Serie-Folio DEMO / DEMO-FISCAL-0001)
30. Receptor presente y completo (nominal / RFC sintético DEM010101AAA / 01000 / 612 / Uso CFDI S01)
31. Metadata presente (fecha 2026-01-15T10:00:00 · Tipo I / 01)
32. Pago presente (03 · PUE · MXN)
33. Conceptos presente (91701101 / E48 · Servicio de corte y tinte DEMOSTRATIVO · ctd 1 · $750.00 · importe $750.00)
34. **Impuestos completo** en página 1 (Base $750.00 · 002/Tasa · 16.0000% · Trasladado $120.00)
35. **Totales completo** en página 1 (Subtotal $750.00 · Descuento $0.00 · Trasladados $120.00 · Total $870.00 MXN)
36. Disclaimers completos en página 1 (3 líneas fiscales)
37. Botón imprimir oculto en print (`display:none`)
38. Sin `$120.00` huérfano en límite de página
39. Tinta página 1: y=67…782 (dentro del A4)
40. Altura print del artículo: 988px ≤ contentHeightPx 1047px

### E. PAGE 2
41. PDF pageCount: **1**
42. Página fantasma 2 (0 chars / 0 ink): **ELIMINADA** vía `overflow:hidden` en `body > *`
43. No existe contenido en página 2
44. Sin tarjetas parciales ni totales duplicados

### F. CANONICAL
45. Emisor canónico: True
46. Receptor canónico: True
47. Concepto canónico: True
48. Impuesto canónico: True
49. Totales canónicos: True
50. Disclaimers canónicos: True
51. `$870.00` Total aparece 1 sola vez
52. `$0.00` Descuento (1 vez, contexto legítimo)
53. `$120.00` (2 veces, contextos legítimos; sin huérfano)
54. `$750.00` (4 veces, unitario/importe/base/subtotal — coherente)
55. Sin inventar datos reales del negocio ni precios ficticios del cliente (dataset demo aprobado)

### G. TRUTHFULNESS
56. «NO ES UN CFDI TIMBRADO» presente
57. «NO HA SIDO CERTIFICADO POR EL SAT NI POR UN PAC» presente
58. «SIN VALIDEZ FISCAL» presente
59. `UUID` **ausente**
60. `TimbreFiscalDigital` **ausente**
61. `SelloSAT` **ausente**
62. `SelloCFD` **ausente**
63. `NoCertificadoSAT` **ausente**
64. PAC / URL SAT de validación **ausentes**
65. No se sugiere timbrado, certificación ni validez fiscal real

### H. RESPONSIVE
66. 320x568: **PASS** (sin overflow, 0 issues)
67. 390x844: **PASS**
68. 430x932: **PASS**
69. 768x1024: **PASS**
70. 1440x900: **PASS**
71. Console errors: **0** · warnings **0** · pageerrors **0** · failedRequests **0**
72. Tabla de conceptos móvil: scroll **contenido** (sin desbordar la página)
73. Flujo back a edición: scroll preservado (reloadedEdit), H1 intacto
74. Sin regresiones visuales de pantalla (los cambios solo viven en `@media print` / `@page`)

### I. PDF QA
75. Generado con `preferCSSPageSize` (A4 @page del documento)
76. Viewport de medición print: 794×1123 (A4 @96dpi)
77. `pageCount` verificado con Swift/PDFKit: **1**
78. Texto por página extraído (901 caracteres, todo en página 1)
79. Sin texto recortado (tinta dentro del MediaBox)
80. Disclaimers sin romper (bloque íntegro en página 1)
81. Resumen sin romper (bloque íntegro en página 1)
82. Bytes PDF: 86581 (PDF de 1 página compacto)
83. MediaBox y tamaño A4 coherentes entre binario y PDFKit
84. Sin dependencias de poppler/mutool/qpdf (verificación vía Swift/PDFKit+sips)

### J. ARTIFACTS
85. `/tmp/spec024-c-final-pdf-review/screen-reference.png` (638×1291, upright)
86. `/tmp/spec024-c-final-pdf-review/pdf-page1.png` (1189×1683, upright)
87. Página 2: **no aplica** (PDF de 1 página)
88. `/tmp/spec024-c-final-qa.pdf` (el PDF concreto revisado)
89. `/tmp/spec024-c-qa/report.json` (evidencia maquinal completa)
90. Capturas de 5 viewports + `print-media-layout.png` en `/tmp/spec024-c-browser-qa/`
91. Verificadores Swift (`render.swift` corregido, `orient.swift`, `dump.swift`, `count.swift`)

### K. SECURITY
92. `npm audit`: **0 vulnerabilities**
93. `composer audit`: **0 advisories**
94. `league/commonmark`: **2.10.3** (baseline, sin cambio)
95. `source-map-js`: **1.2.2** (remediado en `15c71d3`, sin cambio)
96. `brace-expansion`: **5.0.12** (sin cambio)
97. Sin secretos ni llaves en repo/documentos
98. Sin dependencias de tooling añadidas al proyecto

### L. QUALITY (local + remote)
99. `pint` passed · `phpstan` 0 errores · `composer validate --strict` válido
100. `php artisan test` **208 passed / 1202 assertions**
101. `npm run test` **26 files / 104 passed**
102. `npm run lint` PASS · `npm run typecheck` PASS · `npm run build` PASS
103. `git diff --check` limpio
104. Remote Quality previo (SHA `86ebe45`): push `37405987610` (Backend/Frontend quality success) · pull_request `37405992561` success
105. Remote Quality código final (SHA `ff1d59b`): push `37408362206` (Backend/Frontend quality success) · pull_request `37408366023` success
106. Remote Quality HEAD final (SHA `2ba7453`): push `37408532339` (Backend/Frontend quality success) · pull_request `37408535584` success

### M. BOUNDARY / VERDICT
107. Alcance respetado: **QA/FIX ONLY** — print CSS mínimo; sin merge PR #8, sin deploy, sin Checkpoint D, sin XML, sin firma digital
108. Estado: **PDF VISUAL HUMAN REVIEW: AWAITING FINAL HUMAN RECHECK** sobre `pdf-page1.png` (upright) y `screen-reference.png`

## VERDICT

Checkpoint C:
**AWAITING FINAL HUMAN PDF VISUAL RECHECK**

(PDF VISUAL HUMAN REVIEW: **AWAITING FINAL HUMAN RECHECK** — la lista de comprobación maquinal quedó en 1 página A4 upright con la jerarquía 1-página requerida; la aprobación visual final corresponde al revisor humano sobre `pdf-page1.png` / `screen-reference.png` en `/tmp/spec024-c-final-pdf-review/`.)

```
STOP. DO NOT MERGE PR #8. DO NOT DEPLOY. DO NOT START CHECKPOINT D. DO NOT GENERATE XML.
```