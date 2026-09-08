# Architecture

## Decisión base

El sistema será un modular monolith en un solo repositorio. Laravel y Vue se desplegarán juntos; no habrá repositorios separados de backend y frontend.

## Stack

Laravel 12, PHP 8.3+, MySQL 8, Vue 3, TypeScript, Tailwind CSS, Vite, Sanctum y Pest.

## Capas backend

```text
Route
  -> Controller
  -> Form Request
  -> Action / Service
  -> Domain / Model
  -> Database
```

Los Controllers coordinan entrada y salida. Las reglas de negocio viven en Actions, Services y el dominio, con pruebas. Los servicios externos se abstraen y no se invocan directamente desde Controllers.

## Frontend

La aplicación Vue se organizará por componentes, páginas, layouts, módulos, composables y stores. El frontend presenta estado y errores del backend, pero no reemplaza validaciones ni decisiones de negocio.

## API

La convención prevista es `/api/v1/public/`, `/api/v1/admin/` y `/api/v1/webhooks/`. El contrato de errores, paginación, autenticación y versionado se detallará antes de la SPEC que lo implemente.

## Persistencia

MySQL 8 será la base de datos inicial. Se usarán claves internas BIGINT y códigos públicos seguros para citas y pedidos. Las migraciones deben ser reversibles o incluir una estrategia de despliegue documentada.

## Jobs y notificaciones

La cola inicial será database queue. Laravel Scheduler despachará notificaciones vencidas a jobs. La integración Meta queda detrás de `WhatsAppServiceInterface` y no forma parte de Foundation.

## ADR

Las decisiones arquitectónicas futuras se registrarán en `docs/architecture/adr/` con contexto, opciones, decisión y consecuencias.
