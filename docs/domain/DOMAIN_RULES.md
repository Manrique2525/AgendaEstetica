# Domain Rules

## Citas

- Una cita requiere aprobación.
- No se aceptan solapamientos de profesional.
- La disponibilidad respeta turnos, bloqueos, duración del servicio y capacidad general.
- La disponibilidad se recalcula en backend al persistir.
- Las transiciones de estado son explícitas; una cita cancelada no vuelve a confirmada sin una decisión de dominio aprobada.
- Reprogramar conserva el historial y cambia `schedule_version`.

## Estados iniciales previstos

`REQUEST_RECEIVED`, `PENDING_APPROVAL`, `APPROVED`, `DEPOSIT_PENDING`, `CONFIRMED`, `REJECTED`, `RESCHEDULED`, `CANCELLED`, `COMPLETED`, `NO_SHOW`.

## Notificaciones

Solo se envían a citas elegibles, futuras, con horario vigente, consentimiento y WhatsApp disponible. Las notificaciones duplicadas se impiden con idempotencia. Las pendientes se cancelan al cancelar o reprogramar.

## Clientes y usuarios

`users` representa administradores. `customers` representa clientes y no se mezcla con autenticación administrativa.

## Contenido

El CMS permite editar contenido estructurado, orden y visibilidad. No permite HTML, CSS, JavaScript ni layout arbitrario.

## Comercio

Los movimientos de inventario son auditables. Los items de pedido conservan nombre, SKU, variante, precio y cantidad al momento de compra.
