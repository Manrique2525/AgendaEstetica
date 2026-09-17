# Domain Rules

## Citas

- Appointment Engine V1 creates an authoritative `confirmed` appointment; request/approval workflow remains a future consumer concern.
- No se aceptan solapamientos de profesional.
- La disponibilidad respeta turnos, bloqueos, duración del servicio y capacidad general.
- La disponibilidad se recalcula en backend al persistir.
- Las transiciones de estado son explícitas; una cita cancelada no vuelve a confirmada sin una decisión de dominio aprobada.
- Reprogramar conserva el historial; SPEC-004 V1 uses deterministic locking and authoritative revalidation instead of `schedule_version`.

## Estados iniciales previstos

The broader candidate vocabulary remains future-work context. The approved SPEC-004 V1 persistent statuses are `confirmed`, `cancelled`, `completed` and `no_show`.

## Notificaciones

Solo se envían a citas elegibles, futuras, con horario vigente, consentimiento y WhatsApp disponible. Las notificaciones duplicadas se impiden con idempotencia. Las pendientes se cancelan al cancelar o reprogramar.

## Clientes y usuarios

`users` representa administradores. `customers` representa clientes y no se mezcla con autenticación administrativa.

## Contenido

El CMS permite editar contenido estructurado, orden y visibilidad. No permite HTML, CSS, JavaScript ni layout arbitrario.

## Comercio

Los movimientos de inventario son auditables. Los items de pedido conservan nombre, SKU, variante, precio y cantidad al momento de compra.
