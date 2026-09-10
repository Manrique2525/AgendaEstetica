<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { ApiError } from '../../services/http';
import { publicBookingApi, type PublicBookingAvailability, type PublicBookingConfirmation, type PublicBookingProfessional, type PublicBookingService, type PublicBookingSlot } from '../../services/api/publicBooking';
import PublicLayout from '../../layouts/PublicLayout.vue';
import UiButton from '../../components/ui/UiButton.vue';
import UiCard from '../../components/ui/UiCard.vue';
import UiFormField from '../../components/ui/UiFormField.vue';
import UiInput from '../../components/ui/UiInput.vue';

const step = ref(1);
const timezone = ref('');
const services = ref<PublicBookingService[]>([]);
const professionals = ref<PublicBookingProfessional[]>([]);
const availability = ref<PublicBookingAvailability | null>(null);
const selectedServiceId = ref<number | null>(null);
const selectedProfessionalId = ref<number | null>(null);
const date = ref('');
const slot = ref<PublicBookingSlot | null>(null);
const name = ref('');
const phone = ref('');
const loading = ref(false);
const submitting = ref(false);
const error = ref('');
const fieldErrors = ref<Record<string, string>>({});
const confirmation = ref<PublicBookingConfirmation | null>(null);
let availabilityRequest = 0;
let idempotencyKey: string | null = null;
let fingerprint = '';

const selectedService = computed(() => services.value.find((item) => item.id === selectedServiceId.value));
const selectedProfessional = computed(() => professionals.value.find((item) => item.id === selectedProfessionalId.value));
const minDate = computed(() => businessDate(0));
const maxDate = computed(() => businessDate(90));
const canContinue = computed(() => Boolean(selectedService.value));
const contactValid = computed(() => name.value.trim().length > 0 && phone.value.trim().length > 0);

function businessDate(offset: number): string {
    const now = new Date();
    const parts = new Intl.DateTimeFormat('en-CA', { timeZone: timezone.value || 'UTC', year: 'numeric', month: '2-digit', day: '2-digit' }).formatToParts(now);
    const base = new Date(Date.UTC(Number(parts.find((part) => part.type === 'year')?.value), Number(parts.find((part) => part.type === 'month')?.value) - 1, Number(parts.find((part) => part.type === 'day')?.value) + offset));
    return base.toISOString().slice(0, 10);
}

function clearBookingError(): void { error.value = ''; fieldErrors.value = {}; }
function resetKey(): void { idempotencyKey = null; fingerprint = ''; }
function selectService(): void {
    selectedProfessionalId.value = null; professionals.value = []; date.value = ''; slot.value = null; availability.value = null; resetKey(); clearBookingError();
    if (selectedServiceId.value) publicBookingApi.listProfessionals(selectedServiceId.value).then((value) => { professionals.value = value; }).catch(() => { error.value = 'No pudimos cargar profesionales. Intenta nuevamente.'; });
}
function selectProfessional(): void { slot.value = null; availability.value = null; resetKey(); clearBookingError(); }
async function loadAvailability(): Promise<void> {
    if (!selectedServiceId.value || !selectedProfessionalId.value || !date.value) return;
    const request = ++availabilityRequest; loading.value = true; slot.value = null; clearBookingError();
    try {
        const result = await publicBookingApi.getAvailability(selectedServiceId.value, selectedProfessionalId.value, date.value);
        if (request === availabilityRequest) availability.value = result;
    } catch (exception) { if (request === availabilityRequest) error.value = exception instanceof ApiError && exception.status === 429 ? 'Has hecho muchas consultas. Espera un momento e inténtalo de nuevo.' : 'No pudimos cargar los horarios.'; }
    finally { if (request === availabilityRequest) loading.value = false; }
}
function chooseSlot(value: PublicBookingSlot): void { slot.value = value; resetKey(); clearBookingError(); }
function next(): void { clearBookingError(); if (step.value === 1 && canContinue.value) step.value = 2; else if (step.value === 2 && selectedProfessional.value) step.value = 3; else if (step.value === 3 && slot.value) step.value = 4; else if (step.value === 4) { if (!name.value.trim()) fieldErrors.value.name = 'Escribe tu nombre.'; if (!phone.value.trim()) fieldErrors.value.phone = 'Escribe tu teléfono.'; if (contactValid.value) step.value = 5; } }
function back(): void { clearBookingError(); step.value = Math.max(1, step.value - 1); }
function dateLabel(value: string): string { return new Intl.DateTimeFormat('es-MX', { timeZone: timezone.value || 'UTC', weekday: 'long', day: 'numeric', month: 'long' }).format(new Date(`${value}T12:00:00Z`)); }
function makeFingerprint(): string { return JSON.stringify([selectedServiceId.value, selectedProfessionalId.value, slot.value?.starts_at, name.value.trim().replace(/\s+/g, ' '), phone.value.trim()]); }
async function submit(): Promise<void> {
    if (submitting.value || !selectedServiceId.value || !selectedProfessionalId.value || !slot.value || !contactValid.value) return;
    clearBookingError(); submitting.value = true;
    const currentFingerprint = makeFingerprint();
    if (currentFingerprint !== fingerprint || !idempotencyKey) { idempotencyKey = crypto.randomUUID(); fingerprint = currentFingerprint; }
    try {
        confirmation.value = await publicBookingApi.createAppointment({ service_id: selectedServiceId.value, professional_id: selectedProfessionalId.value, starts_at: slot.value.starts_at, name: name.value.trim(), phone: phone.value.trim() }, idempotencyKey);
        step.value = 6;
    } catch (exception) {
        if (exception instanceof ApiError && exception.status === 422 && exception.errors) {
            fieldErrors.value = Object.fromEntries(Object.entries(exception.errors).map(([key, messages]) => [key, messages[0] ?? 'Revisa este campo.']));
            error.value = 'Revisa los datos marcados para continuar.';
        }
        else if (exception instanceof ApiError && exception.status === 409 && exception.code === 'appointment_unavailable') { resetKey(); step.value = 3; await loadAvailability(); error.value = 'Ese horario ya no está disponible. Elige otro para continuar.'; }
        else if (exception instanceof ApiError && exception.status === 429) error.value = 'No pudimos procesar tantas solicitudes. Espera un momento e inténtalo de nuevo.';
        else error.value = exception instanceof ApiError ? exception.message : 'No pudimos confirmar tu cita. Intenta nuevamente.';
    } finally { submitting.value = false; }
}

watch(selectedServiceId, selectService);
watch(selectedProfessionalId, selectProfessional);
watch(date, loadAvailability);
onMounted(async () => { loading.value = true; try { const [context, catalog] = await Promise.all([publicBookingApi.getContext(), publicBookingApi.listServices()]); timezone.value = context.timezone; services.value = catalog; } catch { error.value = 'No pudimos cargar las opciones de reserva.'; } finally { loading.value = false; } });
</script>

<template>
    <PublicLayout>
        <UiCard class="!p-5 sm:!p-8">
            <div v-if="!confirmation" aria-live="polite">
                <p class="font-ui text-xs font-bold uppercase tracking-[0.18em] text-brand-gold">Reserva tu cita</p>
                <h1 class="mt-3 font-display text-4xl leading-tight text-text-primary sm:text-5xl">Un momento para ti</h1>
                <p class="mt-3 font-body text-base leading-relaxed text-text-secondary">Elige tu servicio, horario y déjanos tus datos. Tu cita quedará confirmada al finalizar.</p>
                <div class="mt-7 flex items-center gap-2" aria-label="Progreso de reserva">
                    <span v-for="number in 5" :key="number" :class="['h-1.5 flex-1 rounded-full', number <= step ? 'bg-action-primary' : 'bg-border-default']" />
                </div>
                <p v-if="error" role="alert" class="mt-5 rounded-md border border-state-error bg-red-50 p-3 font-body text-sm text-state-error">{{ error }}</p>

                <section v-if="step === 1" class="mt-8 space-y-4" aria-labelledby="service-title">
                    <h2 id="service-title" class="font-display text-2xl">1. Elige un servicio</h2>
                    <button v-for="service in services" :key="service.id" type="button" :aria-pressed="selectedServiceId === service.id" :class="['w-full rounded-lg border p-4 text-left transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus-ring', selectedServiceId === service.id ? 'border-action-primary bg-amber-50' : 'border-border-default bg-surface-elevated hover:border-action-primary']" @click="selectedServiceId = service.id">
                        <span class="flex items-start justify-between gap-4"><span class="font-ui font-bold">{{ service.name }}</span><span class="font-ui text-sm font-semibold text-action-primary">{{ service.pricing_display }}</span></span>
                        <span class="mt-1 block font-body text-sm text-text-secondary">{{ service.duration_minutes }} minutos</span>
                    </button>
                    <p v-if="!loading && !services.length" class="font-body text-sm text-text-secondary">No hay servicios disponibles por ahora.</p>
                </section>

                <section v-else-if="step === 2" class="mt-8 space-y-4" aria-labelledby="professional-title"><h2 id="professional-title" class="font-display text-2xl">2. Elige a tu profesional</h2><p class="font-body text-sm text-text-secondary">Servicio: {{ selectedService?.name }}</p><button v-for="professional in professionals" :key="professional.id" type="button" :aria-pressed="selectedProfessionalId === professional.id" :class="['w-full rounded-lg border p-4 text-left font-ui font-bold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus-ring', selectedProfessionalId === professional.id ? 'border-action-primary bg-amber-50' : 'border-border-default']" @click="selectedProfessionalId = professional.id">{{ professional.name }}</button><p v-if="!professionals.length" class="font-body text-sm text-text-secondary">No hay profesionales disponibles para este servicio.</p></section>

                <section v-else-if="step === 3" class="mt-8 space-y-5" aria-labelledby="time-title"><h2 id="time-title" class="font-display text-2xl">3. Elige fecha y hora</h2><UiFormField id="booking-date" label="Fecha" required><template #default="{ inputId, describedBy, invalid }"><UiInput :id="inputId" v-model="date" type="date" :min="minDate" :max="maxDate" :aria-describedby="describedBy" :invalid="invalid" /></template></UiFormField><div v-if="loading" role="status" class="font-body text-sm text-text-secondary">Buscando horarios...</div><div v-else-if="availability" class="grid grid-cols-2 gap-3 sm:grid-cols-3"><button v-for="availableSlot in availability.slots" :key="availableSlot.starts_at" type="button" :aria-pressed="slot?.starts_at === availableSlot.starts_at" :class="['min-h-12 rounded-md border px-3 font-ui font-bold focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus-ring', slot?.starts_at === availableSlot.starts_at ? 'border-action-primary bg-action-primary text-white' : 'border-border-default bg-surface-elevated']" @click="chooseSlot(availableSlot)">{{ availableSlot.local_start }}</button></div><p v-if="availability && !availability.slots.length" class="font-body text-sm text-text-secondary">No hay horarios disponibles para esta fecha.</p><button v-if="availability" type="button" class="font-ui text-sm font-bold text-action-primary underline" @click="loadAvailability">Actualizar horarios</button></section>

                <section v-else-if="step === 4" class="mt-8 space-y-5" aria-labelledby="contact-title"><h2 id="contact-title" class="font-display text-2xl">4. Tus datos</h2><UiFormField id="booking-name" label="Nombre" required :error="fieldErrors.name"><template #default="{ inputId, describedBy, invalid }"><UiInput :id="inputId" v-model="name" autocomplete="name" :aria-describedby="describedBy" :invalid="invalid" /></template></UiFormField><UiFormField id="booking-phone" label="Teléfono" required help="Lo usaremos únicamente para identificar tu reserva." :error="fieldErrors.phone"><template #default="{ inputId, describedBy, invalid }"><UiInput :id="inputId" v-model="phone" type="text" inputmode="tel" autocomplete="tel" :aria-describedby="describedBy" :invalid="invalid" /></template></UiFormField></section>

                <section v-else-if="step === 5" class="mt-8 space-y-5" aria-labelledby="review-title"><h2 id="review-title" class="font-display text-2xl">5. Revisa tu cita</h2><dl class="space-y-3 rounded-lg bg-surface-subtle p-4 font-body"><div class="flex justify-between gap-4"><dt class="text-text-secondary">Servicio</dt><dd class="text-right font-semibold">{{ selectedService?.name }}</dd></div><div class="flex justify-between gap-4"><dt class="text-text-secondary">Profesional</dt><dd class="text-right font-semibold">{{ selectedProfessional?.name }}</dd></div><div class="flex justify-between gap-4"><dt class="text-text-secondary">Fecha</dt><dd class="text-right font-semibold">{{ dateLabel(date) }}</dd></div><div class="flex justify-between gap-4"><dt class="text-text-secondary">Hora</dt><dd class="text-right font-semibold">{{ slot?.local_start }} - {{ slot?.local_end }}</dd></div><div class="flex justify-between gap-4"><dt class="text-text-secondary">Precio</dt><dd class="text-right font-semibold">{{ selectedService?.pricing_display }}</dd></div></dl><p class="font-body text-sm text-text-secondary">Al confirmar, tu cita quedará registrada como confirmada.</p></section>

                <div v-if="step < 6" class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-between"><UiButton v-if="step > 1" variant="secondary" type="button" @click="back">Atrás</UiButton><span v-else /><UiButton v-if="step < 5" type="button" :disabled="(step === 1 && !selectedService) || (step === 2 && !selectedProfessional) || (step === 3 && !slot)" @click="next">Continuar</UiButton><UiButton v-else type="button" :loading="submitting" @click="submit">Confirmar cita</UiButton></div>
            </div>
            <div v-else class="py-8 text-center" role="status" aria-live="polite"><p class="font-ui text-sm font-bold uppercase tracking-[0.16em] text-action-primary">Cita confirmada</p><h1 class="mt-4 font-display text-4xl text-text-primary">{{ confirmation.message }}</h1><p class="mt-5 font-body text-lg text-text-secondary">{{ confirmation.service.name }} con {{ confirmation.professional.name }}</p><p class="mt-2 font-body text-base font-semibold text-text-primary">{{ dateLabel(date) }} a las {{ slot?.local_start }}</p><p class="mt-6 font-body text-sm text-text-secondary">Te esperamos. Guarda estos datos para tu visita.</p></div>
        </UiCard>
    </PublicLayout>
</template>
