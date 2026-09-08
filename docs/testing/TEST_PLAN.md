# Test Plan

## Principios

- Toda regla crítica de negocio debe tener prueba automatizada.
- Las pruebas deben cubrir casos válidos, inválidos, límites, duplicados y concurrencia relevante.
- No se usan datos ficticios como datos reales de producción.
- Los criterios de aceptación de cada SPEC son la fuente de verificación funcional.

## Capas

- Unitarias: reglas puras, enums, transiciones y servicios de dominio.
- Feature/API: autorización, validación, persistencia y contratos HTTP.
- Frontend: componentes y flujos donde aporten valor.
- E2E: flujos críticos de booking, reprogramación, notificaciones, ecommerce y administración.

## Comandos previstos

```text
php artisan test
vendor/bin/pint --test
vendor/bin/phpstan analyse (Larastan)
npm run typecheck
npm run lint
npm run test
npm run build
```

Los comandos se habilitarán conforme exista la aplicación. Un resultado no ejecutado debe reportarse como no ejecutado.

## Flujos críticos

- Solicitud, aprobación y confirmación de una cita.
- Rechazo, cancelación y reprogramación con historial.
- Prevención de solapamientos y respeto de capacidad.
- Creación, cancelación, reintento e idempotencia de notificaciones.
- Producto, variante, carrito, checkout, pedido e inventario.
- Login y acciones administrativas autorizadas.

## Definition of Done

Código, Acceptance Criteria, pruebas necesarias, build, lint, análisis estático, revisión de seguridad, edge cases, documentación y reporte de SPEC completos, sin cambios fuera de alcance.
