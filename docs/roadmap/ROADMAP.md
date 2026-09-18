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

`SPEC-006 - Public Booking` está en estado `CLOSED / MERGED` en `docs/specs/SPEC-006-public-booking.md`. La implementación, aceptación, cierre y merge están aprobados. SPEC-007 no está autorizado.

`EPIC-STOREFRONT-ACADEMIC-PHASE-1` está en estado `DEFINED / COORDINATION ONLY`. Esta agrupación documental consume los roadmap items 09-13 sin renombrarlos, mantiene SPEC-007 pausada y SPEC-008 reservada para Fake WhatsApp. SPEC-009 está `CLOSED / MERGED` después de completar A-C, Closure Review, merge `8b431f2` e Integrated Quality `35161901558`. SPEC-021 queda reservado para Appointment Request, sin Definition ni autorización. SPEC-022 Definition + Technical Discovery están integradas en main; Checkpoints A-D están completados y aprobados, Checkpoint E está implementado en su branch y listo para aprobación humana. Ver `docs/epics/EPIC-STOREFRONT-ACADEMIC-PHASE-1.md`, `docs/specs/SPEC-022-academic-phase-1-presentation.md` y `docs/reports/SPEC-022-DISCOVERY-REPORT.md`.

`SPEC-023 - Automated Production Deployment` está en estado `DEFINITION / IMPLEMENTED (pending activation)` en `docs/specs/SPEC-023-automated-production-deployment.md`. La rama de producción es `main` y el workflow `.github/workflows/deploy-production.yml` implementa el despliegue automático a alwaysdata. La activación en GitHub (Environment `production`, secretos y llave CI dedicada) queda pendiente y no autoriza iniciar otra SPEC. Ver `docs/deployment/alwaysdata-production.md`.

`SPEC-024 - Fiscal Demo & Consent UX` está en estado `DEVELOPMENT IN PROGRESS / CHECKPOINT B IMPLEMENTED / READY FOR HUMAN APPROVAL` en `docs/specs/SPEC-024-fiscal-demo-consent-ux.md`. Checkpoint A está aprobado/completo; Checkpoint B queda pendiente de aprobación formal y Checkpoint C no está autorizado. XML/Checkpoint D, CFDI, SAT/PAC y firma digital permanecen pendientes; no reabre SPEC-022 ni modifica SPEC-023.

Target de Foundation: Laravel 13, PHP 8.3+, MySQL 8.4 LTS, Vue 3, TypeScript, Vite, Tailwind CSS, Sanctum y Pest.
