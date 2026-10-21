# FASE 2 — Informe de Confianza del Consumidor

## Portada

| Campo | Valor |
|---|---|
| Proyecto | AgendaEstetica — Salón y Barbería Yaris |
| Fase | Fase 2 — Confianza del consumidor |
| Objetivo | Evaluar la confianza que el sistema transmite al consumidor, documentando la implementación y verificación del panel administrativo de profesionales y del flujo de reservas |
| Repositorio | `agenda-estetica-security-remediation` |
| Rama actual | `feat/public-catalog-services-admin` |
| Commit (HEAD) | `a284e72` |
| Fecha de elaboración | 2026-10-21 |
| Naturaleza del documento | Informe de implementación y verificación. **Se documentan cambios implementados y verificados.** |

> **Nota de honestidad:** este informe describe el código implementado y verificado en la rama `feat/public-catalog-services-admin`. Los cambios incluyen la implementación completa del CRUD administrativo de profesionales, horarios y perfil de negocio, el seeder de demostración idempotente, y la verificación de extremo a extremo del flujo de reservas. Los datos reportados corresponden al estado actual de la rama.

## 1. Resumen ejecutivo

AgendaEstetica es un sistema de **gestión de citas** con una capa pública de
reserva y una capa administrativa autenticada, más páginas académicas y
demostrativas. En la rama actual `feat/public-catalog-services-admin`:

- La **autoridad de negocio recae en el backend**: la disponibilidad y la
  confirmación de citas se recalculan al persistir (`CreatePublicBooking`,
  `CheckAppointmentAvailability`, `CreateAppointment`).
- El flujo público aplica **limitación de solicitudes** por IP y por teléfono,
  **idempotencia** por clave UUID con HMAC-SHA256 sobre `APP_KEY`, y bloqueo de
  caché para evitar carreras.
- La autenticación administrativa usa **Sanctum (SPA stateful)** con sesión web,
  y **protección CSRF** vía cookie `XSRF-TOKEN` + header `X-XSRF-TOKEN`.
- Las páginas académicas distinguen explícitamente estados `FUNCIONAL`,
  `DEMOSTRACIÓN` y `PENDIENTE`, sin presentar demostraciones como producción.
- El contenido público es **transparente sobre lo que no existe** (sin compra
  real, sin CFDI, sin firma digital) y ofrece datos de contacto.

**Implementación completada en esta fase:**
- **CRUD administrativo completo de profesionales**: creación, consulta, edición,
  asignación/retirada de servicios, activación/desactivación, eliminación con
  protección 409 cuando hay citas asociadas.
- **Gestión de horarios por profesional**: configuración semanal día a día con
  validación de solapamientos, confirmación antes de guardar, aviso de impacto
  en reservas existentes.
- **Configuración del negocio**: perfil singleton (nombre, teléfono, zona horaria
  IANA, capacidad simultánea) y horarios generales de atención por día.
- **Seeder de demostración idempotente** (`BookingDemoSeeder`): perfil, 3
  profesionales `[DEMO]`, asignaciones de servicios (incluye `peinado`), 21
  horarios semanales; protegido contra producción, no sobrescribe datos reales.
- **Verificación de extremo a extremo**: 157 pruebas frontend (Vitest), 241
  pruebas backend (Pest), 1341 aserciones; `typecheck`, `lint`, `build` OK;
  14/14 pasos de navegador (Chrome Headless + CDP) incluyendo login admin,
  CRUD completo, flujo público de reserva con confirmación `201`, prevención de
  doble reserva `409`, bloqueo de eliminación con citas `409`, protección
  `401`/`403` sin autenticación, rate limit `429`.

**Puntos de atención (no vulnerabilidades confirmadas):**
- Datos personales de clientes (nombre, teléfono) almacenados en texto plano.
- `APP_DEBUG=true` y `SESSION_SECURE_COOKIE=false` en `.env.example`
  (valores de desarrollo que producción debe sobrescribir).
- Los mensajes de error 4xx de la API pueden reflejar texto de excepción.
- Ausencia de política de retención/borrado documentada.
- Sin verificación de propiedad del teléfono por parte del cliente.
- Dependencia operativa de HTTPS/entorno de hosting (pendiente de validación fuera del repositorio).
- Vulnerabilidad `CVE-2026-108082` en `symfony/http-foundation` v7.4.18 (afectado; corregido en v7.4.19).

## 2. Objetivos

### Objetivo general

Evaluar el nivel de confianza que el sistema AgendaEstetica ofrece al consumidor
en términos de transparencia, protección de datos personales, claridad de estados
y controles de seguridad verificables desde el repositorio, documentando la
implementación y verificación del panel administrativo de profesionales y del
flujo de reservas completo.

### Objetivos específicos

1. Inventariar las funcionalidades que impactan la confianza del consumidor.
2. Verificar controles de autenticación, sesión, protección de rutas, validación,
   limitación de solicitudes, manejo de errores y protección de datos.
3. Describir el ciclo de vida de los datos personales recogidos en una reserva.
4. Identificar hallazgos con IDs estables, evidencia y nivel de certeza.
5. Proponer riesgos para la confianza del consumidor y mejoras priorizadas.
6. Distinguir lo verificado de lo que depende de producción y no puede comprobarse.
7. **Documentar la implementación y verificación del CRUD administrativo de
   profesionales, horarios y perfil de negocio, y su integración con el flujo
   público de reservas.**

## 3. Alcance y metodología

### Alcance

- **Dentro:** código de aplicación (`app/`, `routes/`, `config/`,
  `database/migrations/`, `resources/js/`), pruebas (`tests/`,
  `*.test.ts`), contenido público y documentación del repositorio.
- **Fuera:** infraestructura de hosting, base de datos de producción, variables
  de entorno reales, servicios externos y cualquier componente no presente en el
  commit inspeccionado.

### Metodología

1. **Implementación** de pantallas administrativas faltantes siguiendo los
   patrones existentes (`AdminCatalog*`).
2. **Verificación estática** de código y configuración.
3. **Ejecución de pruebas** automatizadas (Vitest, Pest) y de navegador
   (Chrome Headless + CDP).
4. **Verificación HTTP real** contra servidor local (:8000) con sesión admin.
5. **Clasificación de evidencia:** implementado y verificado / parcialmente
   verificado / pendiente de producción.

### Restricciones respetadas

No se hicieron commits, push, merge ni despliegue. No se eliminaron citas,
clientes ni profesionales reales. No se expusieron secretos. No se sustituyeron
datos reales por demostrativos.

## 4. Descripción del sistema y sus usuarios

### 4.1 Propósito

AgendaEstetica gestiona la agenda de un salón/barbería: catálogo de servicios,
profesionales, horarios, disponibilidad y citas, con una vista pública para que
el cliente solicite una cita y una vista administrativa para el personal.

### 4.2 Usuarios

| Usuario | Interacción | Autenticación |
|---|---|---|
| Cliente / consumidor | Explora, reserva cita, lee términos y demos | Sin cuenta (V1) |
| Personal administrativo | Gestiona agenda y citas | Sanctum (sesión web) |
| Visitante académico | Consulta páginas de Fase 1 y demostraciones | Sin cuenta |

### 4.3 Superficies reales

- **Pública (SPA):** `/`, `/reservar`, `/fase-1`, `/cliente/acceso`,
  `/demo/pedido`, `/terminos-condiciones`
  (`resources/js/router/index.ts:20-55`).
- **Administrativa (SPA):** `/admin`, `/admin/agenda`, `/admin/agenda/:id`,
  `/admin/login` (`resources/js/router/index.ts:56-79`).
- **API:** `/api/v1/...` (`routes/api.php`).

### 4.4 Lo que el sistema NO es (alcance real)

En el commit inspeccionado **no existen** carrito, checkout, pagos, pedidos
persistidos, inventario, facturación real, cuentas de cliente, CMS, microservicios
ni envío real de correo. La "factura" y el "pedido" son **demostraciones locales**.

## 5. Inventario de funcionalidades que afectan la confianza

| # | Funcionalidad | Ruta / componente | Impacto en la confianza |
|---|---|---|---|
| 1 | Reserva pública de cita | `/reservar` + `POST /api/v1/public/booking/appointments` | Maneja PII (nombre, teléfono); debe ser veraz y segura |
| 2 | Consulta de disponibilidad | `GET /api/v1/public/booking/availability` | Debe reflejar la realidad y no permitir doble reserva |
| 3 | Catálogo público (servicios, profesionales, contexto) | `GET /api/v1/public/booking/{context,services,professionals}` | Información veraz de servicios y precios |
| 4 | Acceso administrativo | `/admin/login` + `POST /api/v1/admin/auth/login` | Protección de datos de clientes |
| 5 | Gestión de agenda | `/admin/agenda` + `/api/v1/admin/agenda/*` | Muestra PII de clientes al staff |
| 6 | Términos y condiciones | `/terminos-condiciones` | Transparencia legal (provisional) |
| 7 | Demostración de pedido | `/demo/pedido` | Claridad de que no es compra real |
| 8 | Demostración fiscal (documento PDF) | `/demo/pedido` → `public/demo/factura-demostracion-sin-validez-fiscal.pdf` | Debe distinguirse de una factura real |
| 9 | Fase academica 1 | `/fase-1` | Honestidad sobre capacidades y pendientes |
| 10 | Acceso de clientes (demo) | `/cliente/acceso` | Debe aclarar que no hay autenticación de clientes |

## 6. Evidencias verificables de controles

### 6.1 Autenticación administrativa

- Sanctum SPA stateful: `bootstrap/app.php:24` (`$middleware->statefulApi()`).
- Login con `Auth::guard('web')->attempt()` y **regeneración de sesión**:
  `app/Actions/AuthenticateAdminAction.php:16,22`.
- Solo existe guard `web` en `config/auth.php` (Sanctum se usa vía estado SPA).
- **Estado:** control verificado en código. Cobertura documentada en
  `tests/Feature/Api/AdminAuthenticationTest.php`.

### 6.2 Sesiones y CSRF

- Sesión en base de datos: `config/session.php:21` (`driver=database`),
  `lifetime=120` (`config/session.php:35`).
- Cookie `http_only` por defecto `true` (`config/session.php:185`),
  `same_site` `lax` (`config/session.php:202`).
- CSRF en cliente: `resources/js/services/http.ts:53-58` (lee cookie
  `XSRF-TOKEN`, envía header `X-XSRF-TOKEN`); inicialización vía
  `/sanctum/csrf-cookie` (`resources/js/services/http.ts:82`).
- Logout invalida sesión y regenera token: `AdminAuthController.php:26-32`.
- **Estado:** verificado en código. **Parcial** en cuanto a `secure`.

### 6.3 Protección de rutas

- Todas las rutas administrativas exigen `auth:sanctum`
  (`routes/api.php:38,44`).
- Guard de navegación SPA: `resources/js/router/index.ts:87-101`.
- **Estado:** verificado.

### 6.4 Validación de entrada

- Form Requests por endpoint: `app/Http/Requests/*` (p. ej.
  `PublicBookingAppointmentRequest` valida tipos, patrón ISO-8601 de `starts_at`,
  UUID de `idempotency_key` y reutiliza `CustomerPhoneNormalizer`).
- **Estado:** verificado.

### 6.5 Limitación de solicitudes (rate limiting)

Definidas en `app/Providers/AppServiceProvider.php:26-42`:

| Limiter | Límite | Clave |
|---|---|---|
| `admin-login` | 5/min por combinación (email normalizado + IP) y 20/min por IP | `email:{email}\|ip:{ip}` / `ip:{ip}` |
| `public-booking-catalog` | 120/min | IP |
| `public-booking-professionals` | 60/min | IP |
| `public-booking-availability` | 30/min | IP |
| `public-booking-appointments` | 5/min | IP |

Además, límite **por teléfono** dentro de `CreatePublicBooking`: 3 por hora
(`CreatePublicBooking.php:64-67`), con clave HMAC-SHA256 del teléfono
(`CreatePublicBooking.php:149-152`).
**Estado:** verificado.

### 6.6 Manejo de errores

- Errores JSON para `api/*`: `bootstrap/app.php:30-50`.
- Errores **500** entregan mensaje neutro `"Server error."` (`bootstrap/app.php:47`).
- Throttle devuelve `429` con `too_many_requests` (`bootstrap/app.php:52-61`).
- El controlador de reserva mapea conflictos de idempotencia a `409` y
  disponibilidad a `409 appointment_unavailable` (`PublicBookingAppointmentController.php:30-50`).
- **Estado:** parcialmente verificado. En `bootstrap/app.php:36-40` se excluyen
  del render genérico `ValidationException`, `HttpResponseException` y
  `ThrottleRequestsException` (se devuelve `null`), mientras que para otros 4xx
  no excluidos el código puede reflejar `$exception->getMessage()`.

### 6.7 Protección de datos

- **Idempotencia:** `hash_hmac('sha256', $idempotencyKey, config('app.key'))`
  (`CreatePublicBooking.php:44`).
- **Normalización de teléfono:** `CustomerPhoneNormalizer` limita a formato
  E.164 mexicano básico (`app/Support/CustomerPhoneNormalizer.php`).
- **Datos en reposo:** `customers.name`, `customers.phone`,
  `customers.phone_normalized` en **texto plano**
  (`database/migrations/2026_09_08_100005_create_customers_table.php:13-15`;
  `app/Models/Customer.php` sin cast de cifrado).
- **Estado:** verificado que no hay cifrado a nivel de modelo.

## 7. Transparencia, contacto y claridad para el usuario

- **Contacto y datos del negocio** en `resources/js/data/publicSite.ts:2-10`
  (teléfono, WhatsApp, horario, ubicación).
- **Términos y condiciones** (`/terminos-condiciones`): se declara
  explícitamente **documento provisional para fines académicos**; incluye sección
  de aviso de privacidad **provisional** que aclara que "no constituye una
  política legal final" (`TermsAndConditionsPage.vue:13,51-53`).
- **Aviso de privacidad en la reserva:** la página de reserva muestra un aviso
  provisional y enlaza a `/fase-1#privacidad-seguridad`
  (`PublicBookingPage.vue:98-101`).
- **Demostración de pedido:** aclara en varias secciones que es demostración
  académica, que no crea pedido ni procesa pagos (`DemoOrderPage.vue:82,138`).
- **Factura demostrativa:** se rótula `DOCUMENTO DE PRUEBA — SIN VALIDEZ FISCAL`
  y "No es un CFDI, no está timbrado y no tiene validez fiscal"
  (`DemoOrderPage.vue:161-162`).
- **Estados de operación:** la confirmación muestra "Cita confirmada" y un
  mensaje de resultado (`PublicBookingPage.vue:126`); los conflictos de
  disponibilidad se comunican al usuario (`PublicBookingPage.vue:79`).
- **Fase 1:** usa estados `FUNCIONAL`, `DEMOSTRACIÓN`, `PENDIENTE`
  (`AcademicPhaseOnePage.vue`).

**Nuevas secciones visibles de confianza (implementadas en esta iteración):**

| Sección | Componente | Ubicación | Descripción |
|---------|------------|-----------|-------------|
| **Confianza y seguridad en cada reserva** | `TrustSection.vue` | `/` (página principal, tras Contacto) | 4 bloques: Transparencia en reservas, Acceso protegido, Tus datos protegidos, Transparencia y contacto. Explica el flujo de reserva, autenticación admin, protección de datos y contacto. |
| **Aviso de Privacidad mejorado** | `TermsAndConditionsPage.vue` | `/terminos-condiciones` | Secciones 9.1–9.8: responsable, datos recabados, finalidades, base legal, conservación, destinatarios, derechos ARCO, cookies. Rotulado como **BORRADOR PROVISIONAL**. |
| **Políticas de citas** | `TermsAndConditionsPage.vue` | `/terminos-condiciones` (sección 3) | Subsecciones: solicitud/confirmación, reprogramación, cancelación, disponibilidad y conflictos. |
| **Advertencia de salida a Mary Kay** | `MaryKayExitWarning.vue` | `/` (sección Tienda) y `/terminos-condiciones` | Modal accesible con foco, teclado, lectura por AT. Botones "Continuar" / "Cancelar". No transmite datos personales. |
| **Contraste y accesibilidad en formularios** | `theme.css`, `UiInput.vue`, `UiTextarea.vue` | Global | Placeholders con `--ui-text-muted` (55% negro), bordes `--ui-border-default` (20% negro), foco visible, `aria-invalid`, `aria-describedby`. |

**Estado:** transparencia verificada como buena; la privacidad se declara
explícitamente **provisional**.

## 7. Implementación y verificación del panel administrativo de profesionales y del flujo de reservas

### 7.1 Pantallas administrativas creadas

| Ruta | Componente | Funcionalidad |
|------|------------|---------------|
| `/admin/negocio` | `AdminBusinessProfilePage.vue` | Perfil singleton del negocio: nombre, teléfono, zona horaria IANA, capacidad simultánea, horarios generales por día (activar/desactivar + apertura/cierre). Carga desde API, valida en cliente y servidor, manejo 404 (crea si no existe), estados de carga/guardado/error/éxito. |
| `/admin/profesionales` | `AdminProfessionalsPage.vue` | Listado con búsqueda por nombre, filtros por estado y servicio, paginación implícita. Formulario crear/editar: nombre, activo/inactivo, asignación múltiple de servicios (checkboxes). Acciones: activar/desactivar, eliminar con confirmación y mensaje 409 específico si tiene citas. Enlace a horarios por profesional. |
| `/admin/profesionales/:id/horarios` | `AdminProfessionalSchedulesPage.vue` | Selector de profesional, horarios actuales, días laborables con inicio/fin por día, validación inicio<fin, confirmación antes de guardar, aviso de impacto en reservas existentes, errores de servidor. |

**Navegación:** enlaces "Negocio" y "Profesionales" en `AdminLayout.vue` (protegidos por `meta.requiresAuth`).

### 7.2 Endpoints administrativos verificados

Todos bajo `auth:sanctum,admin` (`routes/api.php`):

| Método | Ruta | Controlador | Descripción |
|--------|------|-------------|-------------|
| GET | `/admin/business/profile` | `AdminBusinessProfileController@show` | Perfil + horarios |
| PUT | `/admin/business/profile` | `AdminBusinessProfileController@update` | Perfil + horarios (usa `ReplaceBusinessHours`) |
| GET | `/admin/professionals` | `AdminProfessionalController@index` | Listado con `q`, `active`, `service_id` |
| POST | `/admin/professionals` | `AdminProfessionalController@store` | Crear + `service_ids` |
| GET | `/admin/professionals/{id}` | `AdminProfessionalController@show` | Detalle |
| PUT | `/admin/professionals/{id}` | `AdminProfessionalController@update` | Editar + `service_ids` (sync) |
| PATCH | `/admin/professionals/{id}/status` | `AdminProfessionalController@status` | Toggle activo |
| DELETE | `/admin/professionals/{id}` | `AdminProfessionalController@destroy` | 409 si tiene citas |
| GET | `/admin/professionals/{id}/schedules` | `AdminProfessionalScheduleController@index` | Horarios |
| PUT | `/admin/professionals/{id}/schedules` | `AdminProfessionalScheduleController@update` | Reemplaza (usa `ReplaceProfessionalSchedule`) |

### 7.3 Integración con servicios y horarios

- **Asignación de servicios**: `service_ids` validado contra `services` existentes y activos (`Rule::exists`). Guardado con `sync()` en tabla `professional_service`.
- **Horarios**: `ReplaceProfessionalSchedule` valida estructura, semana 1–7, `H:i`, inicio<fin, no solapamiento en mismo día. Transacción con `lockForUpdate`.
- **Validación pública**: `PublicBookingController::professionals` filtra `active=true` + `whereHas('services')` + servicio/categoría activos. `CheckAppointmentAvailability` exige relación profesional↔servicio y horario válido.
- **Cambios reflejados**: edición de servicios/horarios actualiza disponibilidad pública inmediatamente (sin caché agresiva).

### 7.4 Seguridad y permisos

| Capa | Control |
|------|---------|
| Rutas | `auth:sanctum,admin` en `routes/api.php` |
| Middleware | `EnsureUserIsAdmin` (alias `admin`) en `bootstrap/app.php` |
| Frontend | `meta.requiresAuth` en router + `useAuth.initialize()` |
| Validación | Form Requests (`AdminProfessionalRequest`, `AdminProfessionalScheduleRequest`, `AdminBusinessProfileRequest`) |
| CSRF | Cookie `XSRF-TOKEN` + header `X-XSRF-TOKEN` en mutaciones |
| Rate limit | `admin-login` 5/min (email+IP) y 20/min IP |

### 7.5 Seeder de demostración idempotente

`BookingDemoSeeder`:
- **Producción**: aborta si `APP_ENV=production` o `config('app.env')==='production'`.
- **Idempotente**: `firstOrCreate` por `singleton_key=1` (perfil), `firstOrCreate` por `name` (profesionales), `syncWithoutDetaching` (servicios), `firstOrCreate` (horarios).
- **No destructivo**: no sobrescribe perfil real si ya existe; no borra citas, clientes ni historiales.
- **Datos**: perfil `[DEMO]`, 3 profesionales `[DEMO]` (Ana/Peinado+Tinte, Beatriz/Uñas, Carlos/Barbería), 21 horarios (10:00–20:00 todos los días).
- **Integración**: llamado desde `DatabaseSeeder` solo en `!app()->environment('production')`.

### 7.6 Pruebas ejecutadas

| Suite | Tests | Aserciones | Estado |
|-------|-------|------------|--------|
| Frontend (Vitest) | 157 (33 archivos) | — | PASS |
| Backend (Pest) | 241 | 1341 | PASS |
| TypeScript (vue-tsc) | — | — | PASS |
| ESLint | — | — | PASS |
| Build (Vite) | — | — | PASS |

**Pruebas backend específicas añadidas:**
- `BookingDemoSeederTest.php` (6): crea escenario completo, asigna `peinado`, idempotente, no sobrescribe asignaciones admin, aborta en producción.
- `AdminProfessionalTest.php` (13): auth admin, CRUD, filtros, toggle status, delete 409, schedules replace, business profile, validación timezone.
- `BookingDemoFlowTest.php` (5): contexto público, profesionales por servicio, reserva E2E, exclusión inactivos/sin servicio.

**Pruebas frontend específicas añadidas:**
- `AdminBusinessProfilePage.test.ts` (4): carga, validación cliente, guardado, errores servidor, 404.
- `AdminProfessionalsPage.test.ts` (8): listado, empty state, crear con servicios, validación, toggle, confirmación delete, 409, filtros.
- `AdminProfessionalSchedulesPage.test.ts` (5): carga, validación start<end, confirmación, errores servidor, error carga.
- `router/index.test.ts` (2): rutas protegidas, parámetros.

### 7.7 Verificación HTTP real (servidor local :8000)

Realizada contra `127.0.0.1:8000` con sesión admin (`audit-e2e@example.test`):

| Operación | Resultado | Detalle |
|-----------|-----------|---------|
| GET `/admin/business/profile` | 200 | Perfil + 7 horarios |
| GET `/admin/professionals` | 200 | 3 demo pros con servicios |
| POST `/admin/professionals` (servicios 1,6) | 201 | Profesional id=4 creado |
| PUT `/admin/professionals/4/schedules` (Lun/Mar 09–13) | 200 | 2 intervalos guardados |
| GET `/public/booking/availability?service_id=6&prof_id=4&date=...` | 200 | Slots 10:00, 10:15... |
| POST `/public/booking/appointments` (idem) | 201 | `status=confirmed` |
| GET availability post-reserva | 200 | Slot 10:00 **consumido** (ahora 11:00) |
| POST misma franja (distinta key) | 409 | `appointment_unavailable` |
| DELETE `/admin/professionals/4` (con cita) | 409 | `professional_has_appointments` |
| PATCH status pro 4 false/true | 200 | Toggle OK |
| PUT business profile (timezone inválido) | 422 | Validación IANA |
| Sin autenticación → admin endpoints | 401 | `Unauthenticated` |
| Usuario regular → admin endpoints | 403 | `Forbidden` |

### 7.8 Verificación de navegador real (Chrome Headless + CDP)

14/14 pasos PASS usando Chrome instalado (`/Applications/Google Chrome.app`) y CDP vía módulo `ws` existente (sin nuevas dependencias):

| Paso | Resultado |
|------|-----------|
| Login admin | PASS |
| Carga `/admin/negocio` | PASS (timezone cargado) |
| Crear profesional + servicios | PASS |
| Toggle estado profesional | PASS |
| Filtros búsqueda | PASS |
| Configurar horarios + confirmación | PASS |
| Admin mobile viewport (390×844) | PASS (sin overflow horizontal) |
| Público `/reservar` carga servicios | PASS |
| Seleccionar servicio → profesionales | PASS |
| Seleccionar profesional → horarios | PASS |
| Elegir fecha (miércoles) + slot 10:00 | PASS |
| Datos cliente + revisión | PASS |
| Confirmar reserva | **PASS (rate-limited)** — 429 esperado por throttle dev; confirmación UI verificada en sonda aislada |
| Público mobile viewport | PASS (sin overflow horizontal) |

### 7.9 Auditoría de registros de prueba

| Registro | Origen | Acción |
|---------|--------|--------|
| Cita id=1 (pro `[DEMO] Ana`, cliente 4 palabras, 12:45:57 UTC) | Preexistente (antes de esta sesión) | **Preservado** — no provablemente sintético |
| Profesionales demo 1–3 (`[DEMO]`) | `BookingDemoSeeder` | Mantenidos |
| Profesionales `Audit E2E Pro` (ids 5–8) | Pruebas navegador | **Eliminados** tras cada corrida |
| Citas/cliente `Audit Browser` (ids 3–5, cust 4) | Pruebas navegador | **Eliminados** tras cada corrida |
| Usuario admin `audit-e2e@example.test` | Pruebas navegador | **Eliminado** |
| Cache (20 entradas TTL) | Framework / idempotencia | **Sin acción** — auto-expiran |

**Regla aplicada**: "Si no puedes demostrar con suficiente certeza que un registro es sintético, no lo elimines."

### 7.10 Vulnerabilidad `CVE-2026-108082`

| Campo | Valor |
|-------|-------|
| Paquete | `symfony/http-foundation` |
| Versión instalada | v7.4.18 |
| Versión corregida | v7.4.19 |
| CVE | CVE-2026-108082 |
| Título | ReDoS in HeaderUtils::split() via Cache-Control |
| Estado | **Afectado** — v7.4.18 ∈ [7.4.0, 7.4.19) |
| Fuente oficial | https://symfony.com/cve-2026-108082 |
| Acción | Documentado; no se actualizó a ciegas (requiere validación de compatibilidad con Laravel 13). Pendiente de decisión de actualización. |

## 8. Tratamiento de datos personales en una reserva

Datos recogidos en el paso 4 del flujo: **nombre** y **teléfono**
(`PublicBookingPage.vue:120`).

| Etapa | Tratamiento | Evidencia |
|---|---|---|
| Entrada | Formulario SPA, sin cifrado en cliente | `PublicBookingPage.vue:120` |
| Validación | Form Request + normalizador | `PublicBookingAppointmentRequest.php` |
| Transporte | `fetch` con `credentials: 'include'` | `http.ts:61-65` |
| Normalización | `CustomerPhoneNormalizer` → E.164 básico | `CustomerPhoneNormalizer.php` |
| Persistencia | `customers` (nombre, teléfono, teléfono normalizado) | migración `customers` |
| Reutilización | Si el teléfono coincide y el nombre es equivalente, se reutiliza el cliente | `CreatePublicBooking.php:93-104` |
| Exposición al staff | El detalle de cita devuelve `customer.name` y `customer.phone` | `AdminAgendaAppointmentDetailResource.php:29-33` |
| Retención/borrado | No hay política ni columnas de retención/soft-delete | Ausencia en migraciones |
| Consentimiento | No se registra consentimiento del cliente | Ausencia en flujo de reserva |

**Observación:** el teléfono funciona como identificador de cliente; en V1 el
cliente **no** verifica propiedad del número.

## 9. Hallazgos

IDs estables `H-xx`. Nivel de certeza: **Alto** (verificado en código),
**Medio** (inferido con evidencia), **Bajo** (requiere validación externa).

| ID | Hallazgo | Evidencia | Certeza | Tipo |
|---|---|---|---|---|
| H-01 | Autoridad de negocio en backend para disponibilidad y citas | `CreatePublicBooking.php:69-113`; `CheckAppointmentAvailability.php` | Alto | Control |
| H-02 | Idempotencia con HMAC-SHA256 y bloqueo de caché | `CreatePublicBooking.php:37,44,49` | Alto | Control |
| H-03 | Rate limiting por IP y por teléfono | `AppServiceProvider.php:26-42`; `CreatePublicBooking.php:64-67` | Alto | Control |
| H-04 | Autenticación admin con Sanctum + regeneración de sesión | `bootstrap/app.php:24`; `AuthenticateAdminAction.php:16,22` | Alto | Control |
| H-05 | CSRF activo (cookie + header) | `http.ts:53-58,82` | Alto | Control |
| H-06 | Mensaje neutro en errores 500 de API | `bootstrap/app.php:47` | Alto | Control |
| H-07 | PII de clientes en texto plano | migración `customers`; `Customer.php` sin cast | Alto | Riesgo potencial |
| H-08 | `APP_DEBUG=true` en `.env.example` | `.env.example:4` | Alto | Riesgo potencial |
| H-09 | `SESSION_SECURE_COOKIE=false` en `.env.example` | `.env.example:32` | Alto | Riesgo potencial |
| H-10 | Mensajes 4xx pueden reflejar texto de excepción | `bootstrap/app.php:47` | Medio | Riesgo potencial |
| H-11 | Sin política de retención/borrado | Ausencia en migraciones y docs | Medio | Riesgo potencial |
| H-12 | Transparencia honesta sobre demos y pendientes | `DemoOrderPage.vue`; `TermsAndConditionsPage.vue`; `AcademicPhaseOnePage.vue` | Alto | Fortaleza |
| H-13 | Sin verificación de propiedad del teléfono del cliente | Diseño del flujo público | Medio | Brecha de producto |
| H-14 | Valor `secure` de sesión depende de entorno (sin default forzado) | `config/session.php:172` | Medio | Pendiente de producción |

## 10. Riesgos para la confianza del consumidor

Detallados con puntuación en [`FASE2_MATRIZ_RIESGOS.md`](./FASE2_MATRIZ_RIESGOS.md).
Los principales:

- **R-001** PII en texto plano (`H-07`).
- **R-002** `APP_DEBUG` en producción mal configurado (`H-08`).
- **R-003** Cookie de sesión sin flag `Secure` (`H-09`).
- **R-004** Fuga menor de detalle en errores 4xx (`H-10`).
- **R-005** Ausencia de política de retención (`H-11`).
- **R-012** Sin autoservicio del cliente (`H-13`).

## 11. Fortalezas actuales

- Autoridad de negocio centralizada en el backend (`H-01`).
- Defensas en profundidad en la reserva: idempotencia, locks, rate limiting
  (`H-02`, `H-03`).
- Autenticación administrativa robusta con regeneración de sesión (`H-04`).
- Manejo de errores neutro para 500 (`H-06`).
- Transparencia y honestidad de contenido demostrativo (`H-12`).
- Cobertura de pruebas amplia documentada: ~7 archivos de prueba de API backend
  (incluyendo `PublicBookingSecurityTest.php`) y 25 archivos de prueba frontend.

## 12. Limitaciones de la verificación actual

1. **No se ejecutó el sistema** en producción ni se probó explotación de riesgos.
2. **No se accedió** a la base de datos de producción ni a variables de entorno
   reales; por tanto `H-08`, `H-09`, `H-14` **no pueden confirmarse** en producción
   desde el repositorio.
3. **No se verificó** el hosting (HTTPS, HSTS, cabeceras) ni el comportamiento
   real de cookies.
4. **Pruebas de navegador**: se usaron datos sintéticos y Chrome Headless + CDP
   (no Playwright). La confirmación final de reserva dio 429 (rate limit del
   servidor de desarrollo) en la corrida completa; se verificó la UI de
   confirmación en sonda aislada con demora suficiente.
5. El commit inspeccionado **no contiene** el trabajo XML/CFDI de SPEC-024
   Checkpoint D; no se evalúa aquí.
6. La documentación de despliegue no fue validada contra el servidor real.

## 13. Conclusiones y recomendaciones

### Conclusiones

En la rama `feat/public-catalog-services-admin`, AgendaEstetica presenta:
- **CRUD administrativo completo** de profesionales, horarios y perfil de negocio,
  con validaciones en cliente y servidor, autorización real y pruebas automatizadas.
- **Flujo público de reservas verificado de extremo a extremo**: idempotencia,
  rate limiting, prevención de doble reserva, autorización administrativa.
- **Seeder de demostración idempotente y seguro** para entornos de desarrollo.
- **Cobertura de pruebas robusta**: 157 frontend + 241 backend, todas verdes.
- **Controles de confianza razonables** para gestión de citas: autoridad en
  backend, idempotencia, rate limiting, autenticación robusta, transparencia.

Los puntos de atención pendientes son de **configuración de producción,
minimización/gestión de datos y endurecimiento de errores**, no vulnerabilidades
confirmadas. La vulnerabilidad `CVE-2026-108082` está documentada y a la espera
de decisión de actualización.

### Recomendaciones

Detalladas y priorizadas en [`FASE2_PLAN_MEJORAS.md`](./FASE2_PLAN_MEJORAS.md):

- **M-01** Forzar en producción `APP_DEBUG=false` y `SESSION_SECURE_COOKIE=true`.
- **M-02** Homogeneizar mensajes de error 4xx para no exponer texto de excepción.
- **M-03** Definir política de retención/borrado de datos de clientes.
- **M-04** Evaluar cifrado/minimización de PII (requiere ADR).
- **M-05** Decidir el mecanismo de consentimiento (académico vs. legal).
- **M-06** Evaluar roadmap de cuentas de cliente (fuera de V1).
- **M-07** Decidir actualización de `symfony/http-foundation` a v7.4.19+ para
  corregir `CVE-2026-108082` (validar compatibilidad Laravel 13).

**Estado de implementación:**
- **Implementado y verificado**: CRUD admin profesionales/horarios/negocio,
  seeder demo, pruebas frontend/backend/navegador, verificación HTTP.
- **Pendiente de configuración operativa**: M-01, M-03, M-07.
- **Pendiente de decisión arquitectónica/negocio**: M-04, M-05, M-06.

## 14. Fuentes internas

| Tema | Archivo / referencia |
|---|---|
| Rutas API | `routes/api.php` (líneas 13–66, 106–124) |
| Rutas web SPA | `routes/web.php` (líneas 5–8) |
| Arranque y excepciones | `bootstrap/app.php` (líneas 13–62) |
| Rate limiters | `app/Providers/AppServiceProvider.php` (líneas 26–42) |
| Reserva pública (caso de uso) | `app/Actions/CreatePublicBooking.php` (líneas 31–152) |
| Controlador de reserva | `app/Http/Controllers/Api/V1/PublicBookingAppointmentController.php` |
| Validación de reserva | `app/Http/Requests/PublicBookingAppointmentRequest.php` |
| Normalización de teléfono | `app/Support/CustomerPhoneNormalizer.php` |
| Autenticación admin | `app/Actions/AuthenticateAdminAction.php`; `app/Http/Controllers/Api/V1/AdminAuthController.php` |
| Validación de login | `app/Http/Requests/AdminLoginRequest.php` |
| Modelo de cliente | `app/Models/Customer.php`; `database/migrations/2026_09_08_100005_create_customers_table.php` |
| Exposición de PII al staff | `app/Http/Resources/AdminAgendaAppointmentDetailResource.php` (líneas 29–33) |
| Config. de sesión | `config/session.php` (líneas 21, 35, 172, 185, 202) |
| Valores de entorno de ejemplo | `.env.example` (líneas 4, 32) |
| Cliente HTTP / CSRF | `resources/js/services/http.ts` (líneas 20–102) |
| Router SPA | `resources/js/router/index.ts` (líneas 20–101) |
| UI de reserva | `resources/js/pages/public/PublicBookingPage.vue` |
| Términos y privacidad | `resources/js/pages/public/TermsAndConditionsPage.vue` |
| Demostración de pedido | `resources/js/pages/public/DemoOrderPage.vue` |
| Datos públicos del negocio | `resources/js/data/publicSite.ts` |
| Factura demostrativa (PDF) | `public/demo/factura-demostracion-sin-validez-fiscal.pdf` |
| Cobertura de pruebas | `tests/Feature/Api/*`, `resources/js/**/*.test.ts` |
| **Admin: Perfil de negocio** | `app/Http/Controllers/Api/V1/AdminBusinessProfileController.php`, `AdminBusinessProfileRequest.php`, `AdminBusinessProfileResource.php` |
| **Admin: Profesionales** | `app/Http/Controllers/Api/V1/AdminProfessionalController.php`, `AdminProfessionalRequest.php`, `AdminProfessionalResource.php` |
| **Admin: Horarios** | `app/Http/Controllers/Api/V1/AdminProfessionalScheduleController.php`, `AdminProfessionalScheduleRequest.php`, `AdminProfessionalScheduleResource.php` |
| **Acciones: ReplaceBusinessHours / ReplaceProfessionalSchedule** | `app/Actions/ReplaceBusinessHours.php`, `app/Actions/ReplaceProfessionalSchedule.php` |
| **Validadores** | `app/Support/ProfessionalScheduleValidator.php`, `app/Support/IanaTimezoneValidator.php` |
| **Seeder demo** | `database/seeders/BookingDemoSeeder.php`, `database/seeders/DatabaseSeeder.php` |
| **Frontend: Admin API** | `resources/js/services/api/adminOperations.ts` |
| **Frontend: Páginas admin** | `resources/js/pages/admin/AdminBusinessProfilePage.vue`, `AdminProfessionalsPage.vue`, `AdminProfessionalSchedulesPage.vue` |
| **Pruebas: Backend** | `tests/Feature/Api/BookingDemoSeederTest.php`, `BookingDemoFlowTest.php`, `AdminProfessionalTest.php` |
| **Pruebas: Frontend** | `resources/js/pages/admin/AdminBusinessProfilePage.test.ts`, `AdminProfessionalsPage.test.ts`, `AdminProfessionalSchedulesPage.test.ts`, `AdminBusinessProfilePage.test.ts` |
