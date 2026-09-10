# Roadmap

## Orden aprobado provisional

00. Governance / Context
01. UX y design system
02. Foundation técnica
03. Business core
04. Appointment engine
05. Admin agenda
06. Public booking
07. Notification engine
08. Fake WhatsApp
09. CMS / landing
10. Ecommerce catalog
11. Inventory
12. Cart / checkout / orders
13. Reviews / favorites
14. Meta WhatsApp
15. Integración pública completa
16. Security hardening
17. Performance, accessibility y SEO
18. E2E / QA
19. Staging
20. Production

## Política de avance

Cada etapa requiere SPEC, discovery, plan aprobado, implementación, pruebas, auditoría, documentación y aceptación. No se inicia automáticamente la siguiente etapa.

## Estado de SPECs

`SPEC-001 - Project Foundation` está en estado `CLOSED` en `docs/specs/SPEC-001-project-foundation.md`. No se inicia automáticamente ninguna SPEC posterior.

`SPEC-002 - UX and Design System Foundation` está en estado `CLOSED` en `docs/specs/SPEC-002-ux-design-system.md`. SPEC-003 y el siguiente item del roadmap no se inician automáticamente.

`SPEC-003 - Business Core` está en estado `CLOSED` en `docs/specs/SPEC-003-business-core.md`. Checkpoints A-D están completados y la aceptación humana fue aprobada. SPEC-004 está cerrada con aceptación humana; merge y SPEC-005 requieren autorización separada.

`SPEC-004 - Appointment Engine` está en estado `CLOSED` en `docs/specs/SPEC-004-appointment-engine.md`. Checkpoints A-F están completados y aceptados; merge a main y SPEC-005 no están autorizados.

`SPEC-005 - Admin Agenda` está en estado `CLOSED / MERGED` en `docs/specs/SPEC-005-admin-agenda.md`. Checkpoints A-E están completados y la integración fue aprobada.

`SPEC-006 - Public Booking` está en estado `CLOSED / MERGED` en `docs/specs/SPEC-006-public-booking.md`. La implementación, aceptación, cierre y merge están aprobados.

`SPEC-007 - Notification Engine` está en estado `DEFINITION COMPLETED / READY FOR HUMAN REVIEW` en `docs/specs/SPEC-007-notification-engine.md`. Technical Discovery, Development, Checkpoint A y SPEC-008+ no están autorizados.

Target de Foundation: Laravel 13, PHP 8.3+, MySQL 8.4 LTS, Vue 3, TypeScript, Vite, Tailwind CSS, Sanctum y Pest.
