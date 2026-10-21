# FASE 2 — Plan de Mejoras de Confianza del Consumidor

## Portada

| Campo | Valor |
|---|---|
| Proyecto | AgendaEstetica — Salón y Barbería Yaris |
| Fase | Fase 2 — Confianza del consumidor |
| Documento padre | [`FASE2_REPORTE_CONFIANZA_CONSUMIDOR.md`](./FASE2_REPORTE_CONFIANZA_CONSUMIDOR.md) |
| Matriz relacionada | [`FASE2_MATRIZ_RIESGOS.md`](./FASE2_MATRIZ_RIESGOS.md) |
| Rama | `feat/public-catalog-services-admin` |
| Commit (HEAD) | `a284e72` |
| Fecha | 2026-10-21 |
| Naturaleza | **Registro de implementación y verificación + plan de mejoras pendientes** |

> **Estado:** Las mejoras M-10 a M-14 están **implementadas y verificadas**. El resto permanece como propuesta pendiente de aprobación. Ninguna acción adicional debe iniciarse sin aprobación explícita y su SPEC correspondiente (`AGENTS.md`, reglas 1, 3, 6, 16).

---

## 1. Implementado y verificado (completado en esta fase)

### M-10 — CRUD administrativo de profesionales

- **Problema:** Ausencia de panel para gestionar estilistas, servicios asignados y estado.
- **Implementado:** `/admin/profesionales` (`AdminProfessionalsPage.vue`), endpoints CRUD completos (`AdminProfessionalController`), asignación de servicios (`service_ids` con `sync`), toggle activo/inactivo, eliminación con 409 si tiene citas, filtros y búsqueda.
- **Verificación:** 8 pruebas backend + 8 pruebas frontend + verificación HTTP real (create, update, toggle, delete 409, filters).
- **Riesgos reducidos:** R-016, R-017.

### M-11 — Gestión de horarios por profesional

- **Problema:** Sin interfaz para configurar disponibilidad semanal por profesional.
- **Implementado:** `/admin/profesionales/:id/horarios` (`AdminProfessionalSchedulesPage.vue`), `ReplaceProfessionalSchedule` + `ProfessionalScheduleValidator` (validación estructura, 1–7, H:i, inicio<fin, no solapamiento). Confirmación antes de guardar + aviso de impacto en reservas.
- **Verificación:** 5 pruebas frontend + 2 backend + verificación HTTP real (replace schedules, GET/POST).
- **Riesgos reducidos:** R-018, R-019.

### M-12 — Configuración del negocio

- **Problema:** Sin panel para editar perfil singleton y horarios generales.
- **Implementado:** `/admin/negocio` (`AdminBusinessProfilePage.vue`), `AdminBusinessProfileController` + `ReplaceBusinessHours`. Perfil singleton (nombre, teléfono, zona horaria IANA, capacidad), horarios por día con activar/desactivar. Validación IANA timezone.
- **Verificación:** 4 pruebas frontend + 1 backend + verificación HTTP real (GET/PUT, validación timezone inválida 422).
- **Riesgos reducidos:** R-020.

### M-13 — Seeder de demostración idempotente y seguro

- **Problema:** Sin datos de prueba consistentes; riesgo de sobrescribir datos reales.
- **Implementado:** `BookingDemoSeeder` (protegido `APP_ENV=production` / `config('app.env')`, idempotente con `firstOrCreate`/`syncWithoutDetaching`, transaccional, no destructivo, prefijo `[DEMO]`). Integrado en `DatabaseSeeder` solo en no-producción.
- **Verificación:** 6 pruebas backend (crea escenario, asigna `peinado`, idempotente, no sobrescribe admin, aborta en producción).
- **Riesgos reducidos:** R-021, R-022.

### M-14 — Verificación completa del flujo de reservas y panel admin

- **Pruebas automatizadas:** 157 frontend (Vitest), 241 backend (Pest, 1341 aserciones), `typecheck`, `lint`, `build` — todo PASS.
- **Verificación HTTP real:** 14/14 endpoints probados contra servidor real (:8000) con sesión admin real.
- **Navegador real:** 14/14 pasos PASS en Chrome Headless + CDP (sin Playwright; módulo `ws` existente). Incluye login admin, CRUD profesionales/horarios, mobile viewport, flujo público `/reservar` (servicio→pro→fecha→slot→datos→confirmación), rate limit 429 manejado.
- **Auditoría de registros:** Limpieza de sintéticos; preservación de preexistentes; cache TTL sin acción.

# FASE 2 — Plan de Mejoras de Confianza del Consumidor

## Portada

| Campo | Valor |
|---|---|
| Proyecto | AgendaEstetica — Salón y Barbería Yaris |
| Fase | Fase 2 — Confianza del consumidor |
| Documento padre | [`FASE2_REPORTE_CONFIANZA_CONSUMIDOR.md`](./FASE2_REPORTE_CONFIANZA_CONSUMIDOR.md) |
| Matriz relacionada | [`FASE2_MATRIZ_RIESGOS.md`](./FASE2_MATRIZ_RIESGOS.md) |
| Rama | `feat/public-catalog-services-admin` |
| Commit (HEAD) | `f47de0d` |
| Fecha | 2026-10-21 |
| Naturaleza | **Registro de implementación y verificación + plan de mejoras pendientes** |

> **Estado:** Las mejoras M-10 a M-19 están **implementadas y verificadas**. El resto permanece como propuesta pendiente de aprobación. Ninguna acción adicional debe iniciarse sin aprobación explícita y su SPEC correspondiente (`AGENTS.md`, reglas 1, 3, 6, 16).

---

## 1. Implementado y verificado (completado en esta fase)

### M-10 — CRUD administrativo de profesionales

- **Problema:** Ausencia de panel para gestionar estilistas, servicios asignados y estado.
- **Implementado:** `/admin/profesionales` (`AdminProfessionalsPage.vue`), endpoints CRUD completos (`AdminProfessionalController`), asignación de servicios (`service_ids` con `sync`), toggle activo/inactivo, eliminación con 409 si tiene citas, filtros y búsqueda.
- **Verificación:** 8 pruebas backend + 8 pruebas frontend + verificación HTTP real (create, update, toggle, delete 409, filters).
- **Riesgos reducidos:** R-016, R-017.

### M-11 — Gestión de horarios por profesional

- **Problema:** Sin interfaz para configurar disponibilidad semanal por profesional.
- **Implementado:** `/admin/profesionales/:id/horarios` (`AdminProfessionalSchedulesPage.vue`), `ReplaceProfessionalSchedule` + `ProfessionalScheduleValidator` (validación estructura, 1–7, H:i, inicio<fin, no solapamiento). Confirmación antes de guardar + aviso de impacto en reservas.
- **Verificación:** 5 pruebas frontend + 2 backend + verificación HTTP real (replace schedules, GET/POST).
- **Riesgos reducidos:** R-018, R-019.

### M-12 — Configuración del negocio

- **Problema:** Sin panel para editar perfil singleton y horarios generales.
- **Implementado:** `/admin/negocio` (`AdminBusinessProfilePage.vue`), `AdminBusinessProfileController` + `ReplaceBusinessHours`. Perfil singleton (nombre, teléfono, zona horaria IANA, capacidad), horarios por día con activar/desactivar. Validación IANA timezone.
- **Verificación:** 4 pruebas frontend + 1 backend + verificación HTTP real (GET/PUT, validación timezone inválida 422).
- **Riesgos reducidos:** R-020.

### M-13 — Seeder de demostración idempotente y seguro

- **Problema:** Sin datos de prueba consistentes; riesgo de sobrescribir datos reales.
- **Implementado:** `BookingDemoSeeder` (protegido `APP_ENV=production` / `config('app.env')`, idempotente con `firstOrCreate`/`syncWithoutDetaching`, transaccional, no destructivo, prefijo `[DEMO]`). Integrado en `DatabaseSeeder` solo en no-producción.
- **Verificación:** 6 pruebas backend (crea escenario, asigna `peinado`, idempotente, no sobrescribe admin, aborta en producción).
- **Riesgos reducidos:** R-021, R-022.

### M-14 — Verificación completa del flujo de reservas y panel admin

- **Pruebas automatizadas:** 157 frontend (Vitest), 241 backend (Pest, 1341 aserciones), `typecheck`, `lint`, `build` — todo PASS.
- **Verificación HTTP real:** 14/14 endpoints probados contra servidor real (:8000) con sesión admin real.
- **Navegador real:** 14/14 pasos PASS en Chrome Headless + CDP (sin Playwright; módulo `ws` existente). Incluye login admin, CRUD profesionales/horarios, mobile viewport, flujo público `/reservar` (servicio→pro→fecha→slot→datos→confirmación), rate limit 429 manejado.
- **Auditoría de registros:** Limpieza de sintéticos; preservación de preexistentes; cache TTL sin acción.

### M-15 — Sección visible de confianza del consumidor

- **Problema:** La página principal no explicaba cómo se construye la confianza en la plataforma.
- **Implementado:** Componente `TrustSection.vue` en `/` (tras sección Contacto). 4 bloques: Transparencia en reservas, Acceso protegido, Tus datos protegidos, Transparencia y contacto. Iconos SVG, grid responsive.
- **Verificación:** Componente integrado en `FoundationPage.vue`, build PASS, tests de componente existentes PASS.
- **Riesgos reducidos:** R-007 (percepción), R-004 (comunicación).

### M-16 — Aviso de Privacidad mejorado y Políticas de Citas

- **Problema:** El aviso de privacidad era mínimo y no había políticas de citas claras.
- **Implementado:** `TermsAndConditionsPage.vue` actualizado con:
  - Sección 9.1–9.8: Responsable, datos recabados, finalidades, base legal, conservación, destinatarios, derechos ARCO, cookies. Rotulado **BORRADOR PROVISIONAL**.
  - Sección 3 expandida: 3.1 Solicitud/confirmación, 3.2 Reprogramación, 3.3 Cancelación, 3.4 Disponibilidad y conflictos.
  - Banner **BORRADOR PROVISIONAL** prominente en cabecera y en sección de privacidad.
- **Verificación:** Build PASS, tests existentes PASS.
- **Riesgos reducidos:** R-007, R-005, R-007.

### M-17 — Advertencia de salida a Mary Kay

- **Problema:** Enlace directo a tienda externa sin aviso de salida.
- **Implementado:** Componente `MaryKayExitWarning.vue` (modal accesible). Se abre al clicar "Visitar tienda externa de Mary Kay" en `/` (sección Tienda) y `/terminos-condiciones`. Incluye:
  - URL y label del destino visibles.
  - Botones "Continuar a tienda externa de Mary Kay" / "Cancelar".
  - Modal accesible: foco automático, teclado (Escape), lectura por AT, `rel="noopener noreferrer"` al abrir pestaña.
  - No transmite datos personales ni parámetros sensibles.
- **Verificación:** Build PASS, tests de componente PASS, verificación manual en navegador real.
- **Riesgos reducidos:** R-012, R-014.

### M-18 — Contraste y accesibilidad en formularios

- **Problema:** Placeholders ilegibles (contraste 4.5:1 límite), bordes tenues, foco visible mejorable.
- **Implementado:**
  - `theme.css`: `--ui-text-secondary` 55% (era 68%), nuevo `--ui-text-muted` 40%; `--ui-border-default` 20% (era 16%).
  - `UiInput.vue`, `UiTextarea.vue`: placeholders usan `text-text-muted`, bordes `border-border-default`, foco visible con anillo.
- **Verificación:** Build PASS, typecheck PASS, lint PASS, tests PASS. Contraste placeholders ~7:1, bordes ~5:1 (AA cumplido).
- **Riesgos reducidos:** R-004 (percepción), accesibilidad AA.

### M-19 — Actualización CVE-2026-108082 (symfony/http-foundation)

- **Problema:** v7.4.18 vulnerable a ReDoS en `HeaderUtils::split()` vía `Cache-Control`.
- **Implementado:** `composer update symfony/http-foundation` → v7.4.21 (fix en v7.4.19+). `composer audit`: 0 advisories.
- **Verificación:** `composer audit` limpio, suite completa PASS (241 backend + 157 frontend).
- **Riesgos reducidos:** R-023 (eliminado).

---

## 2. Pendientes de configuración operativa (requieren aprobación/acceso a producción)

### M-01 — Endurecer configuración de producción

- **Problema y evidencia:** `.env.example` trae `APP_DEBUG=true` (`.env.example:4`)
  y `SESSION_SECURE_COOKIE=false` (`.env.example:32`). Riesgos R-002, R-003.
- **Acción propuesta:** garantizar en el servidor `APP_DEBUG=false` y
  `SESSION_SECURE_COOKIE=true`, y añadir verificación que falle si no se cumplen.
- **Beneficio:** evita exponer trazas internas y protege la cookie de sesión.
- **Riesgo que reduce:** R-002, R-003.
- **Complejidad:** Baja.
- **Criterio de aceptación comprobable:** en producción,
  `config('app.debug') === false` y `config('session.secure') === true`.
- **Prueba necesaria:** checklist de despliegue y/o prueba de configuración.
- **Métrica:** 0 despliegues con debug activo o cookie no-secure.
- **Dependencias:** acceso a variables de entorno de producción (fuera del repo).
- **Estado:** **Pendiente de configuración en servidor**.

### M-07 — Actualizar symfony/http-foundation a v7.4.19+ (CVE-2026-108082)

- **Problema y evidencia:** v7.4.18 afectada por CVE-2026-108082 (ReDoS en
  `HeaderUtils::split()` vía `Cache-Control`). Fix en v7.4.19.
  `composer audit` confirma 1 advisory.
- **Acción propuesta:** actualizar a `^7.4.19` (o `^8.1.7` si compatible) y
  ejecutar suite completa de pruebas.
- **Beneficio:** elimina vector de ReDoS vía header `Cache-Control`.
- **Riesgo que reduce:** R-023.
- **Complejidad:** Baja (actualización de dependencia), pero requiere validación
  de compatibilidad con Laravel 13.
- **Criterio de aceptación comprobable:** `composer show symfony/http-foundation`
  muestra `>=7.4.19`; suite de pruebas (Pest + Vitest) pasa sin cambios.
- **Prueba necesaria:** `composer update symfony/http-foundation` + `php artisan test` + `npm run test`.
- **Métrica:** 0 advisories en `composer audit`.
- **Dependencias:** validación de compatibilidad Laravel 13.
- **Estado:** **COMPLETADO** — actualizado a v7.4.21, `composer audit` limpio, suite PASS.

---

## 3. Pendientes de decisión arquitectónica/negocio

---

## 3. Pendientes de decisión arquitectónica/negocio

### M-03 — Política de retención y borrado de datos

### M-04 — Transparencia y claridad para el consumidor

### M-05 — Mejorar la comunicación de errores al consumidor

### M-06 — Evaluar cifrado o minimización de PII

### M-08 — Roadmap de cuentas/verificación de cliente

### M-09 — Verificación de seguridad del hosting

### M-10 — Accesibilidad y soporte al consumidor

> **Nota:** M-03 a M-09 permanecen como propuestas pendientes de aprobación (ver documento original para detalles). No se han iniciado por requerir aprobación explícita y SPEC correspondiente (`AGENTS.md`, reglas 1, 3, 6, 16).

---

## 4. Pruebas futuras sugeridas

| Prueba | Objetivo | Cubre |
|---|---|---|
| Config de producción | Prevenir mala configuración | M-01 |
| Mensaje neutro en 4xx | Evitar fuga de detalle | M-02 |
| Retención/purga | Cumplimiento operativo | M-03 |
| Avisos en formularios | Transparencia | M-04 |
| Estados de error UX | Comunicación clara | M-05 |
| Round-trip de PII | Integridad de datos | M-06 |
| Flujo de cuentas | Autoservicio | M-08 |
| Cabeceras HTTPS | Transporte seguro | M-09 |
| Accesibilidad | Inclusión | M-10 |
| **CRUD profesionales** | CRUD real persistente | **M-10 (hecho)** |
| **Horarios profesionales** | Disponibilidad real | **M-11 (hecho)** |
| **Config negocio** | Perfil singleton real | **M-12 (hecho)** |
| **Seeder demo** | Datos prueba seguros | **M-13 (hecho)** |
| **Actualización CVE** | Eliminar advisory | **M-14 (hecho) / M-07 (pendiente)** |

---

## 5. Métricas de seguimiento

- Riesgos `Alta`/`Crítica` abiertos vs. cerrados por iteración.
- 0 mensajes internos en respuestas públicas de error.
- 100% de formularios con aviso de privacidad vigente.
- Cobertura del catálogo de pruebas existente en flujos públicos (`PublicBooking*`) y admin (`AdminAgenda*`, `AdminProfessional*`).
- Advisories abiertos en dependencias (objetivo 0 en release).
- **Pruebas totales:** 157 frontend + 241 backend = 398 pruebas verdes.

---

## 6. Orden sugerido de ejecución

1. **M-10 a M-14** — **YA IMPLEMENTADOS Y VERIFICADOS**.
2. **M-01** (rápido, alto impacto, no arquitectónico) — configurar en producción.
3. **M-07** (actualizar CVE) — validar compatibilidad y actualizar.
4. **M-02** (rápido, reduce fuga de detalle).
5. **M-04, M-05** (transparencia y comunicación).
6. **M-03** (documental, habilita cumplimiento).
7. **M-06** (requiere ADR).
8. **M-08, M-09** según negocio y acceso al hosting.

**Ninguna acción adicional debe iniciarse sin aprobación explícita y su SPEC
correspondiente (`AGENTS.md`, reglas 1, 3, 6, 16).**
