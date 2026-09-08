# SPEC-001 - Project Foundation

## SPEC ID

`SPEC-001`

## Title

Project Foundation

## Status

`APPROVED FOR DEVELOPMENT`

Esta especificación fue aprobada explícitamente para desarrollo. La aprobación no autoriza funcionalidades fuera del checkpoint activo ni cambia el alcance de la SPEC.

## Objective

Definir una base técnica reproducible para un modular monolith Laravel 13 con una SPA Vue 3, API versionada, autenticación administrativa, persistencia MySQL, colas database, scheduler, almacenamiento, pruebas y quality gates.

La Foundation debe dejar preparado el sistema sin implementar dominios funcionales del salón, agenda, ecommerce, CMS o WhatsApp.

## Context

El repositorio está vacío de código de aplicación y contiene únicamente la gobernanza inicial. La arquitectura aprobada establece Laravel y Vue en un solo repositorio, con Laravel como backend autoritativo y Vue como cliente de la API.

La Foundation debe resolver primero las decisiones de ejecución, configuración, seguridad y calidad para que las SPEC posteriores no inventen convenciones distintas. No debe anticipar tablas ni flujos de negocio que pertenecen a otras SPEC.

## Dependencies

### Prerequisites

- `AGENTS.md` y los documentos de contexto, arquitectura, dominio y testing existentes.
- PHP 8.3 o superior.
- Composer.
- Node.js y npm en versiones compatibles con el toolchain elegido.
- MySQL 8.4 LTS para desarrollo y pruebas de integración que requieran persistencia.
- Un entorno local que pueda ejecutar PHP, el servidor de desarrollo de Vite y, cuando corresponda, un worker y el scheduler.

### Approved stack

- Laravel 13.
- PHP 8.3+.
- MySQL 8.4 LTS.
- Vue 3.
- TypeScript.
- Vite.
- Tailwind CSS.
- Laravel Sanctum.
- Pest.

### Approved additional dependencies

- `vue-router`: necesario para separar rutas públicas y administrativas en la SPA.
- `vitest` y `@vue/test-utils`: aprobados para pruebas unitarias y de componentes frontend de Foundation.
- `pestphp/pest-plugin-laravel`: integración Pest aprobada para Laravel.
- `larastan/larastan`: aprobado como integración Laravel sobre PHPStan. La implementación seleccionará una versión 3.x compatible con Laravel 13 y PHP 8.3+.
- `vue-tsc`: necesario para typecheck de archivos Vue SFC.
- `vite`, `laravel-vite-plugin` y `@vitejs/plugin-vue`: líneas aprobadas para Vite 8 y Vue 3.
- `tailwindcss` y `@tailwindcss/vite`: línea 4.3 aprobada para la integración moderna con Vite.

No se incorpora Axios, Pinia, Vuex, un cliente de estado global, una librería UI, moment.js, Lodash, paquetes de repositories, paquetes de permisos, dashboards de queues, clientes Redis ni SDKs de WhatsApp. El cliente HTTP inicial usará `fetch` encapsulado en un servicio pequeño y testeable.

### Approved dependency plan

```text
laravel/framework ^13
laravel/sanctum ^4
pestphp/pest ^4
pestphp/pest-plugin-laravel ^4
laravel/pint ^1
larastan/larastan ^3

vue ^3.5
vue-router ^4.6
vite ^8
laravel-vite-plugin ^3
@vitejs/plugin-vue ^6
typescript ^5.9
vue-tsc ^3
tailwindcss ^4.3
@tailwindcss/vite ^4.3
vitest ^4
@vue/test-utils ^2
jsdom
```

ESLint y sus dependencias directas de configuración se resolverán en líneas estables compatibles con TypeScript 5.9, Vue 3 y el parser flat utilizado. No se fijan patch versions aquí; los lockfiles registrarán las versiones exactas.

## In Scope

- Crear la aplicación base Laravel 13.
- Definir configuración de entorno y `.env.example` sin secretos.
- Configurar MySQL, locale y timezone técnica configurable.
- Integrar Vue 3, TypeScript, Vite y Tailwind CSS.
- Establecer la estructura frontend mínima para SPA, layouts, páginas futuras, componentes UI y servicios.
- Establecer `vue-router` y el fallback de rutas SPA.
- Establecer la separación conceptual entre aplicación frontend y API `/api/v1`.
- Definir e implementar únicamente endpoints técnicos mínimos de Foundation.
- Preparar autenticación administrativa SPA con Laravel Sanctum.
- Preparar rutas de login, logout y usuario autenticado para `users`, sin implementar `customers`.
- Configurar las migraciones framework estrictamente necesarias para la Foundation.
- Configurar database queue, `jobs` y `failed_jobs`.
- Preparar la estrategia de Laravel Scheduler sin lógica de recordatorios.
- Configurar Laravel Storage con el disco público local.
- Establecer logging base, seguridad, pruebas y quality gates.
- Definir un workflow inicial de GitHub Actions sin despliegue.
- Documentar el entorno de desarrollo y el plan de implementación.

## Out of Scope

SPEC-001 no implementa:

- clientes;
- servicios del salón;
- profesionales;
- turnos de profesionales;
- agenda;
- citas;
- availability engine;
- reprogramaciones;
- anticipos;
- productos;
- inventario;
- carrito;
- checkout;
- pedidos;
- reviews;
- favorites;
- CMS;
- landing page funcional;
- WhatsApp;
- Meta Cloud API;
- recordatorios;
- pagos reales;
- facturación;
- reportes;
- reglas de negocio de dominios posteriores;
- datos demo de servicios, clientes, profesionales o productos;
- microservicios o un segundo repositorio;
- Docker obligatorio;
- Inertia, Nuxt, Next.js u otro framework backend/frontend;
- Redis obligatorio, RabbitMQ, Kafka o Kubernetes;
- despliegue a producción;
- Playwright; se incorporará en una fase futura de testing E2E cuando existan flujos completos.

## Architecture Decisions

### AD-001: Laravel backend plus Vue SPA

Laravel será el backend y servirá la aplicación frontend Vue desde el mismo repositorio y despliegue inicial. Vue consumirá una API bajo `/api/v1`.

La SPA tendrá un entry point único. El servidor deberá devolver el shell frontend para rutas de navegación que no sean `/api/*`, `/sanctum/*` o rutas de recursos backend. El servidor nunca debe devolver el shell SPA para una ruta API inexistente: esas rutas deben responder con el error HTTP correspondiente.

La SPA se organizará conceptualmente en:

- superficie pública futura;
- superficie administrativa futura;
- infraestructura compartida.

SPEC-001 no crea páginas funcionales de esas superficies.

### AD-002: Same-origin Sanctum SPA authentication

La autenticación administrativa inicial usará Sanctum en modo SPA con sesión y cookies HTTP, no tokens personales enviados por el frontend.

Flujo previsto:

1. El frontend solicita `/sanctum/csrf-cookie`.
2. El frontend envía credenciales a `/api/v1/admin/auth/login` con cookies y protección CSRF.
3. Las solicitudes administrativas posteriores incluyen las cookies de sesión.
4. El backend protege las rutas con middleware de autenticación.
5. Logout invalida la sesión y regenera/limpia el contexto CSRF según las capacidades estándar de Laravel.

La configuración de dominios stateful, sesión, cookie segura y `SameSite` debe corresponder al host real. No se habilitará una arquitectura cross-origin como requisito de Foundation. Si se necesitara posteriormente, requerirá una decisión específica de CORS, dominios y despliegue.

### AD-003: API versioning and response contract

Las rutas se agruparán conceptualmente así:

```text
/api/v1/public/*
/api/v1/admin/*
/api/v1/webhooks/*
```

La Foundation puede exponer solamente un health check técnico y autenticación administrativa mínima. No debe crear endpoints de negocio.

Contrato inicial propuesto:

```json
{
  "data": {},
  "meta": {}
}
```

`meta` será opcional y no se enviará vacío de forma obligatoria. Las respuestas sin cuerpo, como logout exitoso, usarán el código HTTP apropiado sin forzar un objeto `data` innecesario.

Errores:

```json
{
  "message": "Human-readable message",
  "code": "stable_machine_code",
  "errors": {
    "field": ["Validation message"]
  }
}
```

`errors` será opcional. No se expondrán stack traces, consultas SQL, secretos ni detalles internos. Los códigos de error estables deben agregarse solo cuando exista un consumidor real; no se diseñará una taxonomía exhaustiva para dominios futuros.

Convenciones HTTP iniciales:

- `200` para lectura o acción exitosa con respuesta.
- `201` para creación futura de recursos.
- `204` para operaciones exitosas sin cuerpo cuando corresponda.
- `401` cuando no existe autenticación válida.
- `403` cuando el usuario autenticado no puede realizar la operación.
- `404` cuando el recurso no existe o no debe revelarse.
- `409` para conflictos de estado o concurrencia cuando una operación los defina.
- `422` para validación de entrada.
- `429` para rate limiting.
- `500` o `503` para fallos no controlados o indisponibilidad, sin filtrar implementación.

La paginación futura usará una forma documentada por endpoint, preferentemente:

```json
{
  "data": [],
  "meta": {
    "current_page": 1,
    "per_page": 20,
    "last_page": 1,
    "total": 0
  }
}
```

No se implementará paginación hasta existir un recurso que la necesite.

### AD-004: Eloquent without generic repositories

Eloquent será utilizado directamente en casos simples. Actions y Services se introducirán cuando exista una operación de aplicación o una regla que lo justifique. No se creará un Repository Pattern genérico.

### AD-005: Database queue

La conexión inicial de cola será `database`. Foundation creará la infraestructura framework para `jobs` y `failed_jobs`, y dejará documentado el worker persistente de producción. No se crearán jobs de citas, WhatsApp ni recordatorios. Redis queda explícitamente fuera de SPEC-001 y solo podrá evaluarse mediante una SPEC y decisión posterior basadas en necesidad real.

### AD-006: Technical configuration versus business settings

`.env` y los archivos de configuración contendrán infraestructura, entorno y secretos: credenciales de base de datos, claves de sesión, hosts y tokens.

Una futura entidad de configuración de negocio contendrá valores editables por administración, como horarios, mensajes o parámetros operativos aprobados. No se mezclará esta entidad con `config()` ni se creará en Foundation.

### AD-007: Backend enums

Los estados y otros vocabularios cerrados futuros usarán PHP Backed Enums y persistencia como strings cuando corresponda. No se crean enums de citas, pedidos o dominios futuros en esta SPEC. No se usará MySQL ENUM para estados de negocio.

## Backend Foundation

### Application initialization

La implementación posterior deberá:

- usar Laravel 13 con PHP mínimo 8.3;
- configurar `APP_ENV`, `APP_DEBUG`, `APP_URL`, `APP_KEY` y logging por entorno;
- configurar la conexión MySQL mediante variables de entorno;
- conservar configuración cacheable en entornos de despliegue;
- mantener las rutas y responsabilidades separadas entre web, API, Sanctum y consola;
- no agregar modelos, migraciones o servicios funcionales de dominios futuros.

La aplicación Laravel 13 se creará primero en una ubicación temporal fuera del repositorio y se copiará selectivamente al root. Deben preservarse `.git/`, `AGENTS.md` y `docs/`. No deben copiarse automáticamente `.env`, `vendor/`, `node_modules/` ni una base SQLite temporal. `.gitignore`, `README`, manifests y configuraciones root deben inspeccionarse antes de sobrescribirse.

### Proposed backend structure

La estructura inicial debe permanecer pequeña. Solo se crearán directorios cuando tengan archivos o una responsabilidad implementada.

```text
app/
├── Actions/       # Solo acciones de Foundation que realmente existan
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Resources/
├── Providers/
└── Support/       # Helpers transversales justificados

routes/
├── api.php
├── web.php
└── console.php
```

Los módulos de negocio posteriores podrán organizarse dentro de esta arquitectura modular sin obligar a crear una abstracción vacía en Foundation.

## Frontend Foundation

### Toolchain

La implementación futura integrará Vue 3.5 con TypeScript 5.9, Vite 8 y Tailwind CSS 4.3. Vite será el único bundler frontend y el entry point se ubicará en `resources/js`.

### Proposed structure

```text
resources/js/
├── app/
│   └── bootstrap.ts
├── components/
│   └── ui/
├── layouts/
├── pages/
│   ├── public/
│   └── admin/
├── router/
├── services/
│   └── api/
├── composables/
├── types/
└── utils/
```

No se crearán cientos de carpetas vacías ni páginas funcionales. Los primeros archivos deben limitarse al bootstrap, router, cliente HTTP, tipos de respuesta, layouts base y cualquier componente mínimo necesario para demostrar la integración.

### Routing

`vue-router` 4.x será el router frontend. Las rutas se separarán por superficies pública y administrativa, aunque Foundation solo necesita registrar shells/layouts o rutas técnicas mínimas.

El guard de rutas no sustituye la autorización backend. Puede consultar el usuario autenticado para navegación, pero cada endpoint administrativo seguirá protegido en Laravel.

### State

No se incorpora Pinia en Foundation. El estado local de componentes, composables pequeños y el estado de sesión obtenido desde `/api/v1/admin/auth/me` son suficientes para la base. Si una SPEC posterior demuestra estado global complejo, deberá justificar la dependencia y registrar el impacto.

### HTTP client

Se recomienda un wrapper pequeño sobre `fetch` con:

- `baseURL` configurable para `/api/v1`;
- `credentials: 'include'` para Sanctum;
- headers JSON y `Accept`;
- manejo uniforme de respuestas exitosas y errores;
- soporte de CSRF bootstrap cuando se autentique;
- tipos TypeScript para el envelope de API.

No debe duplicarse la lógica HTTP en páginas o componentes.

### UI and layouts

Foundation solo prepara layouts y componentes técnicos mínimos. El diseño visual definitivo se definirá en la etapa de UX y design system. No se construye una landing, agenda, catálogo ni dashboard funcional.

## Authentication

### Scope

La autenticación aplica únicamente a usuarios internos administrativos. `users` no representa clientes; el dominio `customers` queda fuera de esta SPEC.

### Endpoints técnicos previstos

Estos endpoints forman parte de la infraestructura de autenticación y no de un módulo de negocio:

```text
GET  /sanctum/csrf-cookie
POST /api/v1/admin/auth/login
POST /api/v1/admin/auth/logout
GET  /api/v1/admin/auth/me
```

El login debe validar credenciales y regenerar la sesión usando las capacidades estándar de Laravel. Logout debe invalidar la sesión. `me` debe responder con el usuario autenticado mínimo, sin secretos ni campos innecesarios.

### Authorization

Inicialmente solo se requiere autenticación administrativa básica. No se implementan roles, permisos complejos, equipos ni políticas de negocio. La estructura debe permitir agregar autorización posteriormente sin asumir que todo usuario autenticado puede operar cualquier dominio.

### Protection

Las rutas administrativas se protegen con middleware de autenticación. El endpoint `me` y cualquier endpoint administrativo futuro deben devolver `401` sin sesión válida. El backend nunca confía en guards, estados o permisos enviados por Vue.

### Sanctum token scope

La SPA first-party utiliza sesiones/cookies y CSRF. `personal_access_tokens` no es necesaria para Foundation y no se habilitará autenticación por tokens API. La implementación no debe ejecutar ciegamente un comando que publique migraciones de tokens sin inspeccionar sus artefactos; si un comando oficial genera archivos adicionales, deberán revisarse antes de aceptarlos.

## API Foundation

### Health check

Se implementará un health check técnico mínimo `GET /api/v1/health`. Debe demostrar que la aplicación está cargada, la API está disponible y el contrato JSON funciona, sin exponer dependencias internas, secretos ni detalles del servidor.

Respuesta mínima:

```json
{
  "data": {
    "status": "ok"
  }
}
```

Su comportamiento debe quedar cubierto por una prueba Feature. No será un sistema avanzado de observabilidad/readiness ni verificará proveedores externos en esta SPEC.

### Request and response rules

- API versionada desde el primer endpoint.
- JSON como formato de entrada y salida para la API.
- Validación de entrada en Form Requests cuando el endpoint la requiera.
- Serialización explícita; no devolver modelos completos por accidente.
- Errores en el envelope definido en AD-003.
- No colocar reglas de negocio en Controllers.

## Database Foundation

### Included framework persistence

Solo se incluirán tablas y migraciones técnicas estándar de Laravel 13 razonables para Foundation, según el skeleton seleccionado y el modo de autenticación elegido:

- `users` y requisitos estándar de autenticación administrativa;
- `password_reset_tokens` si el flujo estándar de recuperación se conserva;
- `sessions` si se usa driver de sesión database;
- `cache` y `cache_locks` si se usa driver de cache database;
- `jobs`, `job_batches` y `failed_jobs` si forman parte del skeleton o de la infraestructura queue seleccionada.

La migración de `users` no debe agregar campos de clientes, profesionales ni perfiles de negocio. `password_reset_tokens`, `job_batches` y otras tablas framework pueden conservarse como infraestructura estándar aunque sus flujos funcionales no se implementen en Foundation. `personal_access_tokens` no es necesaria para la SPA first-party basada en cookies y no debe agregarse para habilitar tokens API.

### Excluded tables

No crear en Foundation:

`customers`, `services`, `service_categories`, `professionals`, `professional_services`, `professional_shifts`, `schedule_blocks`, `appointments`, `appointment_reschedules`, `appointment_notifications`, `products`, `product_variants`, `orders`, `order_items`, `inventory_movements`, `reviews`, `favorites`, `homepage_sections`, `whatsapp` o cualquier tabla de negocio equivalente.

### Migrations

Cada migración futura debe tener una justificación. Las migraciones de Foundation deben ser ejecutables en una base nueva y no deben depender de datos demo.

## Queue

### Configuration

La configuración inicial usará `QUEUE_CONNECTION=database` o el equivalente de configuración de Laravel. La implementación creará las tablas framework de `jobs` y `failed_jobs`, sin crear jobs de dominio.

### Retry baseline

Los jobs futuros deberán declarar explícitamente sus intentos, backoff, timeout y comportamiento de fallo según su dominio. Foundation solo debe documentar y demostrar que un job técnico de prueba o un job de framework puede ser encolado, procesado y registrado como fallido, si el plan de implementación lo considera necesario.

No se debe inventar una política de retry para WhatsApp o citas antes de sus respectivas SPEC.

### Production requirement

Producción requiere un worker persistente equivalente a `php artisan queue:work`, supervisado por la infraestructura de producción. La herramienta concreta puede ser Supervisor, systemd o un process manager del PaaS y se decidirá con el hosting. El cron del scheduler no reemplaza al worker.

## Scheduler

La implementación debe registrar el punto de extensión de tareas programadas en la convención vigente de Laravel 13, preferentemente en `routes/console.php` o el lugar equivalente generado por el framework. Producción ejecutará conceptualmente `php artisan schedule:run` mediante cron cada minuto.

No se registran recordatorios, notificaciones ni tareas de negocio. Puede incluirse una tarea técnica no invasiva solo si sirve para demostrar la configuración y está cubierta por una prueba o verificación documentada.

La operación concreta del hosting y su herramienta de supervisión quedan pendientes, pero la frecuencia de un minuto es un requisito de despliegue de la Foundation.

## Timezone

### Current decision

La referencia técnica de aplicación será UTC. La timezone empresarial oficial no está confirmada y `BUSINESS_CONTEXT.md` la lista como pendiente. No se puede inferir una localidad a partir del nombre del negocio ni del horario de referencia.

### Foundation rule

- `APP_TIMEZONE=UTC` será la referencia técnica de Laravel para instantes.
- La futura configuración `business_timezone` será un identificador IANA configurable, por ejemplo `America/...`, sin seleccionar todavía una localidad concreta.
- Los instantes persistentes, como `starts_at`, `ends_at`, `scheduled_at`, `sent_at` y `created_at`, se normalizarán inequívocamente en UTC.
- Los horarios locales recurrentes del negocio, como turnos y horario comercial, representan reglas de reloj local en `business_timezone`; no son simples horas UTC.
- Las conversiones entre instantes y horario local ocurrirán en los límites de dominio, API, frontend, scheduler y mensajes.
- El frontend recibirá fechas en un formato estable, preferentemente ISO 8601, y no interpretará la timezone por heurística del navegador.

La timezone empresarial concreta permanece como `PENDING BUSINESS DATA`, pero no bloquea SPEC-001. Sí debe estar definida antes de implementar disponibilidad, citas, recordatorios o cualquier horario recurrente del negocio.

## Storage

La implementación preparará Laravel Storage con el disco local público basado en `storage/app/public` y el enlace simbólico estándar a `public/storage`, si el flujo de archivos de Foundation lo requiere.

El código futuro debe usar la abstracción `Storage` y nombres de discos/configuración, nunca rutas físicas acopladas. Esto permite evaluar S3, R2 u otro storage compatible después sin reescribir dominios.

No se implementan uploads de servicios, productos, galerías ni CMS en esta SPEC. No se agregan proveedores externos de almacenamiento.

## Security

### Baseline

- Hash de contraseñas mediante la configuración estándar de Laravel.
- `APP_KEY` generado por entorno y nunca versionado.
- Secretos únicamente en `.env` o el gestor de secretos del entorno.
- `.env.example` con nombres y placeholders no sensibles, sin tokens reales.
- `APP_DEBUG=false` en producción.
- HTTPS obligatorio en producción.
- Cookies de sesión `HttpOnly`, `Secure` según entorno y `SameSite` coherente con la arquitectura same-origin.
- Protección CSRF de Sanctum para la sesión SPA.
- El frontend no almacenará tokens de autenticación en `localStorage` ni `sessionStorage`; la sesión administrativa usará cookies y sesiones de Sanctum.
- CORS restringido; no usar `*` con credenciales.
- Rate limiting base para login y endpoints sensibles, usando capacidades nativas de Laravel.
- Validación y autorización en backend.
- No exponer stack traces, credenciales, consultas SQL o headers sensibles.
- Mass assignment controlado explícitamente en modelos que existan.
- Logs sin contraseñas, tokens, cookies completas ni datos sensibles innecesarios.
- Headers de seguridad y política de contenido se evaluarán con la configuración estándar disponible y el entorno de despliegue, sin añadir un paquete en Foundation por defecto.

### Future boundaries

El futuro `audit_logs` es un requisito de dominio administrativo y no se crea aquí. Logging técnico, auditoría de acciones y eventos de integración deben permanecer conceptualmente separados.

## Testing

### Backend

Pest será la herramienta principal. Foundation debe preparar suites `Feature` y `Unit`:

- Feature: aplicación, health check, contrato de error básico, autenticación login/logout/me y protección administrativa.
- Unit: serialización o utilidades transversales de Foundation que tengan lógica aislada y justifiquen una prueba.

No se agregan pruebas de clientes, citas, inventario, ecommerce, CMS o WhatsApp.

### Frontend

`Vitest` 4 y `Vue Test Utils` 2 están aprobados para Foundation. `vue-tsc` 3 será obligatorio para el typecheck de SFC Vue. La implementación debe incluir una suite pequeña para verificar bootstrap, cliente HTTP, router y los componentes técnicos mínimos que realmente se creen. No se requiere una suite extensa de módulos funcionales.

### E2E

Playwright se incorporará posteriormente en la etapa E2E/QA, cuando existan flujos completos de booking, administración y ecommerce. Está fuera de alcance de SPEC-001 y no se instalará durante Foundation.

### Minimum proof

La futura implementación debe demostrar al menos:

- la aplicación inicia con una base de datos de prueba;
- el health check responde con el contrato aprobado;
- un usuario no autenticado recibe `401` en una ruta administrativa;
- un login válido crea sesión y `me` devuelve el usuario mínimo;
- logout invalida la sesión;
- un error de validación usa el formato establecido;
- el bootstrap frontend, typecheck y build funcionan.

## Quality Gates

Los comandos se agregarán a scripts reales del proyecto antes de usarse en CI. No se deben declarar resultados de herramientas que no estén instaladas y configuradas.

### Backend gates

```text
php artisan test
vendor/bin/pint --test
vendor/bin/phpstan analyse
```

`vendor/bin/phpstan analyse` se ejecutará con `larastan/larastan` configurado para Laravel. La implementación seleccionará una versión 3.x compatible con Laravel 13 y PHP 8.3+; no se instalará PHPStan directo como alternativa separada.

### Frontend gates

```text
npm run typecheck
npm run lint
npm run test
npm run build
```

`npm run test` será obligatorio en Foundation y ejecutará Vitest 4 con Vue Test Utils 2.
`npm run typecheck` ejecutará `vue-tsc --noEmit`.

### Manual checks

- instalación desde un checkout limpio siguiendo el documento de desarrollo;
- arranque de Laravel y Vite;
- conexión a MySQL;
- login administrativo y protección de rutas;
- procesamiento de queue en entorno local;
- ejecución del scheduler sin tareas de negocio;
- ausencia de secretos en `.env.example` y logs de prueba.

## CI

La futura implementación debe crear un workflow inicial de GitHub Actions, sin despliegue. El workflow debe ejecutarse en cambios dirigidos a la rama de trabajo y en pull requests cuando se adopte ese flujo.

### Required checks

1. Checkout del repositorio.
2. Configuración de PHP 8.3+ y extensiones necesarias.
3. Instalación reproducible de Composer con lockfile cuando exista.
4. Preparación de MySQL 8.4 LTS para tests de integración, si las pruebas lo requieren.
5. Ejecución de Pint en modo verificación.
6. Ejecución de Pest.
7. Ejecución de `larastan/larastan` mediante su integración PHPStan.
8. Configuración de Node.js compatible.
9. Instalación reproducible de npm con lockfile cuando exista.
10. ESLint.
11. `vue-tsc --noEmit`.
12. Vitest con Vue Test Utils.
13. Build frontend de producción.

El workflow no publica artefactos de producción, no configura secretos de proveedores externos y no despliega. Las versiones exactas de runtimes deben fijarse según compatibilidad real del lockfile y documentarse durante implementación.

## Logging

Se usarán los canales y handlers estándar de Laravel, configurables por entorno. Foundation debe dejar claro cómo revisar errores de aplicación, fallos de queue y errores técnicos de integraciones futuras sin introducir una plataforma externa.

Los logs estructurados o con contexto adicional podrán agregarse cuando una SPEC lo necesite. Nunca deben registrar contraseñas, tokens, cookies, payloads sensibles completos ni secretos. Esto no reemplaza el futuro audit log de acciones administrativas.

## Seeders

La implementación debe separar datos mínimos técnicos de datos de demostración. Se recomienda:

- `DatabaseSeeder` como orquestador explícito;
- `BaseSeeder` para datos técnicos o mínimos no sensibles, si realmente se necesita;
- `DemoSeeder` separado y ejecutado solo mediante una instrucción explícita de desarrollo.

No se crean servicios, precios, clientes, profesionales, productos, citas ni otros datos ficticios de negocio. Producción no ejecuta `DemoSeeder`.

## Development Environment

### Requirements

- PHP 8.3+ con extensiones requeridas por Laravel y MySQL.
- Composer.
- Node.js y npm compatibles con Vite y Vue 3.
- MySQL 8.4 LTS.
- Git.

Docker no es requisito. Puede documentarse como alternativa local futura, pero no debe convertirse en dependencia de Foundation sin una decisión aprobada.

### Setup sequence

La documentación de implementación debe describir, sin secretos reales:

1. Clonar el repositorio y seleccionar la rama de SPEC.
2. Instalar dependencias PHP con Composer.
3. Copiar `.env.example` a `.env` y completar valores locales.
4. Generar `APP_KEY`.
5. Crear una base MySQL de desarrollo y configurar credenciales.
6. Ejecutar migraciones framework.
7. Instalar dependencias frontend con npm.
8. Ejecutar servidor Laravel y Vite.
9. Ejecutar quality gates.
10. Ejecutar worker database queue cuando se verifique la infraestructura.
11. Ejecutar scheduler únicamente como verificación técnica, sin tareas de negocio.

La implementación debe indicar comandos exactos cuando las herramientas y scripts existan; esta SPEC no simula esos comandos como ejecutados.

## Implementation Plan

La implementación futura debe ejecutarse en una rama propia de SPEC, no en `main`, y seguir este orden:

1. Inicializar Laravel 13 y fijar PHP 8.3 como mínimo.
2. Configurar `.env.example`, entorno, MySQL, locale, logging y timezone técnica UTC.
3. Configurar rutas API versionadas, contrato de respuestas y health check.
4. Integrar Vue 3, TypeScript, Vite y Tailwind.
5. Configurar `vue-router` y el fallback SPA.
6. Implementar la abstracción HTTP con `fetch` nativo.
7. Integrar Sanctum y validar el flujo SPA same-origin.
8. Implementar la autenticación administrativa mínima.
9. Preparar migraciones framework estrictamente necesarias.
10. Configurar database queue y scheduler sin jobs de dominio.
11. Configurar Storage local y el enlace público si corresponde.
12. Añadir Pest 4 y `pestphp/pest-plugin-laravel` con pruebas Feature/Unit.
13. Añadir Vitest 4, Vue Test Utils 2 y `vue-tsc` con pruebas frontend mínimas.
14. Añadir Pint, `larastan/larastan`, ESLint, `vue-tsc`, tests y build como scripts reproducibles.
15. Añadir GitHub Actions sin despliegue.
16. Ejecutar instalación limpia, pruebas, quality gates y revisiones de alcance.
17. Actualizar documentación y producir el reporte de SPEC-001.

Si durante la implementación aparece una decisión no resuelta sobre arquitectura, seguridad, timezone o dependencias relevantes, el trabajo debe detenerse y reportarse como `BLOCKER`.

## Acceptance Criteria

La SPEC está en `APPROVED FOR DEVELOPMENT` y permanece en progreso. Una futura implementación no se considerará aceptada hasta cumplir:

1. Laravel 13 inicia correctamente con PHP 8.3+.
2. MySQL 8.4 LTS está configurado mediante entorno y las migraciones framework aprobadas ejecutan sobre una base nueva.
3. `.env.example` existe y no contiene secretos, tokens reales ni credenciales reales.
4. Locale y `APP_TIMEZONE=UTC` están configurados; `business_timezone` está preparada como identificador IANA configurable y permanece como dato empresarial pendiente.
5. Vue 3, TypeScript, Vite y Tailwind están integrados y el build funciona.
6. La SPA tiene entry point, router y fallback claramente separados de `/api/*`, `/sanctum/*` y recursos backend.
7. La estructura frontend no contiene páginas funcionales de dominios fuera de Foundation.
8. La API utiliza la convención `/api/v1` y las superficies `public`, `admin` y `webhooks` quedan documentadas sin endpoints de negocio.
9. `GET /api/v1/health` responde `{"data":{"status":"ok"}}` y está cubierto por una prueba Feature.
10. El contrato de respuestas y errores está implementado o cubierto por pruebas mínimas para los endpoints existentes.
11. Sanctum SPA funciona con sesión/cookies y CSRF en el despliegue same-origin previsto.
12. Login válido, logout y usuario autenticado funcionan para `users` administrativos.
13. Las rutas administrativas rechazan solicitudes no autenticadas con `401`.
14. No existe dominio `customers` ni mezcla entre usuarios administrativos y clientes.
15. Existe la infraestructura aprobada para database queue, jobs y failed jobs.
16. La configuración del scheduler está preparada para `schedule:run` cada minuto en producción, sin lógica de recordatorios ni tareas de negocio.
17. Storage usa la abstracción Laravel y el disco local aprobado; no hay acoplamiento a rutas físicas ni proveedores externos.
18. Passwords, sesiones, CSRF, CORS, rate limiting, debug, HTTPS y secretos tienen configuración base documentada.
19. Pest ejecuta pruebas Feature y Unit mínimas de Foundation.
20. Las pruebas mínimas cubren arranque, autenticación, protección administrativa y formato de error cuando esos endpoints existan.
21. Vitest, Vue Test Utils y `vue-tsc` están instalados y cubren la infraestructura frontend mínima.
22. Pint, `larastan/larastan`, ESLint, `vue-tsc` y build se ejecutan mediante scripts reales y pasan.
23. CI ejecuta dependencias, lint, análisis estático, tests y build sin despliegue.
24. La documentación de instalación, workers, scheduler y quality gates está disponible.
25. No se han creado funcionalidades, tablas, endpoints, datos ni dependencias de dominios fuera de SPEC-001.
26. La revisión de seguridad no detecta secretos versionados ni exposición de stack traces en configuración de producción.
27. Se ha generado el reporte de SPEC con archivos, comandos ejecutados, limitaciones y pendientes.

## Risks

### R-001: Business timezone data pending

La timezone empresarial concreta aún no está confirmada. Mitigación: mantener `business_timezone` como identificador IANA configurable y no implementar horarios recurrentes, citas o recordatorios hasta recibir el dato empresarial.

### R-002: Sanctum deployment assumptions

La autenticación SPA con cookies depende de host, dominios stateful, HTTPS, `SameSite` y CSRF. Mitigación: documentar y probar el despliegue same-origin; cualquier separación futura requiere decisión propia.

### R-003: Framework version compatibility

Laravel 13, PHP 8.3+, MySQL 8.4 LTS, Vite 8, Tailwind, Node, Pest y herramientas estáticas deben ser compatibles. Mitigación: fijar versiones durante implementación con lockfiles y ejecutar CI limpio.

### R-004: Queue operational dependency

Database queue requiere worker persistente en producción. Mitigación: documentar worker y supervisión como requisito operativo antes de notificaciones.

### R-005: Scope leakage

La Foundation puede crecer accidentalmente hacia administración, CMS o negocio. Mitigación: aplicar la lista Out of Scope y rechazar migraciones, endpoints o páginas no técnicos.

### R-006: Frontend dependency growth

Agregar Pinia, Axios, librerías UI o paquetes de seguridad sin necesidad aumenta mantenimiento. Mitigación: wrapper `fetch`, estado local y capacidades Laravel mientras sean suficientes.

## Open Decisions (Non-Blocking)

### PENDING BUSINESS DATA: Business timezone

- **Context:** La agenda y los horarios recurrentes necesitarán una timezone empresarial.
- **Rule:** Debe recibirse como identificador IANA en una SPEC de negocio posterior.
- **Impact:** No bloquea Foundation; sí debe estar definida antes de implementar disponibilidad, citas, recordatorios u horarios recurrentes.

### PENDING EXTERNAL DECISION: Hosting and worker supervision

- **Context:** Scheduler y database queue necesitan cron y worker persistente.
- **Options:** Hosting administrado con cron/worker; VPS; otra plataforma compatible.
- **Recommendation:** Elegir antes de staging y validar localmente con los mismos supuestos operativos.
- **Impact:** No bloquea Foundation ni código local; sí el diseño de despliegue productivo.

## External Dependencies

- PHP runtime 8.3+.
- Composer y Packagist para dependencias PHP aprobadas.
- Node.js/npm y registry de paquetes frontend.
- MySQL 8.4 LTS.
- GitHub Actions para CI.
- Hosting futuro con HTTPS, almacenamiento persistente, cron y worker.

No se requieren Meta, gateways de pago, S3, R2, Cloudinary ni otros proveedores externos para Foundation.

## Proposed ADRs

La adopción de Laravel 13 está registrada y aceptada en `docs/architecture/adr/ADR-001-adopt-laravel-13.md`. La adopción de MySQL 8.4 LTS está registrada y aceptada en `docs/architecture/adr/ADR-002-adopt-mysql-8-4-lts.md`. No se propone ningún ADR adicional. La integración Laravel + Vue SPA, el modular monolith, Sanctum y database queue permanecen sin cambios.

Si una decisión posterior separa orígenes de SPA/API, cambia autenticación, introduce un proveedor obligatorio o cambia la cola, deberá crearse otro ADR antes de implementar ese cambio.

## Required Documentation Updates

- Mantener esta SPEC en estado `APPROVED FOR DEVELOPMENT` mientras existan checkpoints pendientes; no marcarla `ACCEPTED` ni `CLOSED` sin completar el ciclo completo.
- Mantener las decisiones de Laravel 13 y MySQL 8.4 LTS respaldadas por sus ADR aceptados.
- Si se aprueba para desarrollo, registrar el plan aprobado sin modificar el alcance silenciosamente.
- Durante implementación, actualizar `ARCHITECTURE.md` solo si una decisión aprobada cambia la arquitectura.
- Completar convenciones detalladas de API, seguridad y desarrollo cuando existan archivos dedicados o como parte del reporte de SPEC.
- Crear `docs/reports/SPEC-001-REPORT.md` únicamente después de la implementación y verificación; no forma parte de esta tarea.
- Mantener `CONTEXT_INDEX.md` apuntando a esta SPEC activa.
