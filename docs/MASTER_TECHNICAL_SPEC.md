# Master Technical Specification

## Estado

`DRAFT` - documento inicial de gobierno. No autoriza por sí mismo la implementación de todos los módulos.

## Objetivo

Construir un sistema mantenible y documentado para Salón y Barbería Yaris, con backend Laravel y frontend Vue en un único repositorio.

## Arquitectura aprobada

- Laravel 13.
- PHP 8.3+.
- MySQL 8.
- Vue 3, TypeScript, Tailwind CSS y Vite.
- Sanctum para autenticación administrativa.
- Pest para pruebas backend.
- Arquitectura modular monolith.
- API versionada bajo `/api/v1/`, separando `public`, `admin` y `webhooks`.

## Actores y límites

Yaris administra. Los profesionales no son usuarios. Los clientes no tienen cuenta en V1. El backend gobierna permisos, disponibilidad, transiciones, capacidad, consentimiento e idempotencia.

## Módulos

1. Foundation y autenticación administrativa.
2. Business core: clientes, categorías, servicios, profesionales, relaciones, turnos, bloqueos y configuración.
3. Appointment domain: citas, estados, disponibilidad, solapamientos, capacidad, historial, reprogramación y anticipos.
4. Agenda administrativa y reserva pública.
5. Notification engine con cola database y proveedor falso.
6. CMS estructurado.
7. Ecommerce, inventario, carrito, checkout y pedidos.
8. Reviews, favoritos, Meta WhatsApp y operación pública completa.

## Reglas técnicas clave

- Los estados de cita serán PHP backed enums y strings en base de datos, no MySQL ENUM.
- Las reprogramaciones conservan historial y aumentan `schedule_version`.
- Las notificaciones son idempotentes por cita, versión y tipo.
- Los pedidos guardan snapshots de los datos comerciales de sus items.
- El inventario se audita mediante movimientos.
- Meta WhatsApp se integra detrás de una abstracción y después del proveedor falso.

## Fuera de alcance inicial

Microservicios, Kubernetes, Kafka, RabbitMQ, Redis sin justificación, cuentas de clientes, pagos reales, page builder libre y servicios a domicilio.

## Cambio de esta especificación

Un cambio funcional requiere una SPEC. Un cambio arquitectónico requiere decisión aprobada y ADR antes de implementación.
