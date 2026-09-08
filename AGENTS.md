# AGENTS.md

## READ BEFORE WRITE

Este archivo es la constitución operativa del proyecto Salón y Barbería Yaris.
Debe leerse antes de modificar código o documentación.

## Reglas invariables

1. No desarrollar directamente sobre `main`. Cada SPEC usa una rama propia.
2. No hacer `force push`, merge o push sin autorización explícita.
3. No cambiar la arquitectura sin aprobación y ADR cuando corresponda.
4. Leer el contexto, la arquitectura y la SPEC activa antes de modificar archivos.
5. El backend es autoridad sobre las reglas de negocio y las validaciones.
6. Cada cambio funcional debe pertenecer a una SPEC con Acceptance Criteria.
7. No implementar funcionalidades fuera del alcance de la SPEC activa o de V1.
8. No inventar datos reales del negocio, precios, personas, fotografías o testimonios.
9. Cambios estructurales y migraciones requieren justificación documentada.
10. Toda regla crítica debe tener pruebas.
11. No llamar servicios externos desde Controllers.
12. No colocar reglas importantes de negocio en componentes Vue.
13. No duplicar lógica ni mezclar dominios sin una razón documentada.
14. Documentar cambios significativos y generar el reporte de la SPEC.
15. Si existe una contradicción arquitectónica o de negocio, detenerse y reportarla.
16. No iniciar automáticamente otra SPEC al terminar una anterior.

## Documentos obligatorios

Antes de trabajo importante, leer:

- `docs/context/PROJECT_CONTEXT.md`
- `docs/context/BUSINESS_CONTEXT.md`
- `docs/MASTER_TECHNICAL_SPEC.md`
- `docs/architecture/ARCHITECTURE.md`
- `docs/domain/DOMAIN_RULES.md`
- la SPEC activa en `docs/specs/`

Para cambios de pruebas, leer también `docs/testing/TEST_PLAN.md`.

## Arquitectura y código

- Stack aprobado: Laravel 13, PHP 8.3+, MySQL 8, Vue 3, TypeScript, Tailwind CSS, Vite, Sanctum y Pest.
- Arquitectura: modular monolith en un solo repositorio.
- Flujo backend preferido: Route -> Controller -> Form Request -> Action/Service -> Domain/Model -> Database.
- Controllers coordinan; no contienen reglas complejas de negocio.
- La disponibilidad, estados, capacidad, permisos y validaciones críticas se recalculan en backend.
- Antes de agregar una dependencia relevante se debe justificar necesidad, mantenimiento e impacto.

## Git

- Rama estable: `main`.
- Ramas de SPEC: `feat/spec-NNN-nombre`.
- Correcciones: `fix/nombre`.
- Documentación: `docs/nombre`.
- Trabajo técnico: `chore/nombre`.
- Nunca reescribir historia compartida.

## Calidad

Antes de considerar una SPEC terminada, ejecutar los comandos disponibles para el estado del proyecto:

```text
php artisan test
vendor/bin/pint
npm run typecheck
npm run lint
npm run test
npm run build
```

Si un comando todavía no existe, documentar la limitación en el reporte; no simular resultados.

## Alcance de OpenCode

OpenCode puede modificar únicamente archivos necesarios para la SPEC activa, sus pruebas y documentación asociada. No debe eliminar migraciones, cambiar framework, cambiar base de datos, cambiar autenticación, modificar estados de negocio, agregar proveedores externos o crear microservicios sin decisión aprobada.
