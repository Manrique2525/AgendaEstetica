<script setup lang="ts">
import { ref } from 'vue';
import PublicLayout from '../../layouts/PublicLayout.vue';
import UiButton from '../../components/ui/UiButton.vue';
import UiCard from '../../components/ui/UiCard.vue';
import UiFormField from '../../components/ui/UiFormField.vue';
import UiInput from '../../components/ui/UiInput.vue';

const phone = ref('');
const demoMessageVisible = ref(false);

function continueDemo(): void {
    demoMessageVisible.value = true;
}
</script>

<template>
    <PublicLayout>
        <div class="space-y-10">
            <section aria-labelledby="customer-access-title" class="rounded-xl border border-white/15 bg-gradient-to-br from-brand-black via-brand-black to-brand-fuchsia/20 px-6 py-10 sm:px-10 sm:py-14">
                <p class="font-ui text-xs font-bold uppercase tracking-[0.2em] text-brand-pink">Presentación académica</p>
                <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <h1 id="customer-access-title" class="font-display text-5xl leading-[0.95] text-text-inverse sm:text-7xl">Acceso de clientes</h1>
                    <span class="self-start rounded-full border border-brand-gold/60 px-3 py-1 font-ui text-xs font-bold tracking-[0.08em] text-brand-gold">DEMOSTRACIÓN</span>
                </div>
                <p class="mt-5 max-w-2xl font-body text-lg leading-relaxed text-white/80">Esta pantalla muestra el concepto de acceso futuro mediante teléfono y WhatsApp. La verificación todavía está pendiente de integración.</p>
            </section>

            <section aria-labelledby="demo-title">
                <UiCard class="!border-white/15 !bg-white/5 !text-text-inverse !shadow-none sm:!p-10">
                    <p class="font-ui text-xs font-bold uppercase tracking-[0.18em] text-brand-gold">Acceso de clientes</p>
                    <h2 id="demo-title" class="mt-2 font-display text-4xl text-text-inverse">Demostración de experiencia</h2>
                    <p class="mt-4 max-w-2xl font-body leading-relaxed text-white/75">En un desarrollo posterior, este dato podría iniciar un proceso de registro y verificación por WhatsApp. Hoy el campo es únicamente visual: no se envía, no se guarda y un número válido no se considera una identidad autenticada.</p>
                    <form class="mt-8 max-w-xl space-y-5" aria-describedby="demo-help" @submit.prevent="continueDemo">
                        <UiFormField id="demo-phone" label="Teléfono para la demostración" help="Dato de ejemplo únicamente. No se almacenará ni se enviará.">
                            <template #default="{ inputId, describedBy, invalid }">
                                <UiInput :id="inputId" v-model="phone" type="tel" inputmode="tel" autocomplete="tel" :aria-describedby="describedBy" :invalid="invalid" />
                            </template>
                        </UiFormField>
                        <p id="demo-help" class="font-body text-sm leading-relaxed text-white/65">Continuar solo muestra el estado pendiente; no inicia sesión ni crea una cuenta.</p>
                        <UiButton type="submit">Continuar con la demostración</UiButton>
                    </form>
                    <p v-if="demoMessageVisible" role="status" aria-live="polite" class="mt-6 rounded-md border border-brand-gold/50 bg-brand-gold/10 p-4 font-body text-sm leading-relaxed text-white">Demostración: la verificación del número todavía está pendiente de integración. No se ha iniciado sesión.</p>
                </UiCard>
            </section>

            <section aria-labelledby="whatsapp-title">
                <UiCard class="!border-white/15 !bg-white/5 !text-text-inverse !shadow-none sm:!p-10">
                    <div class="grid gap-8 md:grid-cols-[1fr_auto] md:items-center">
                        <div>
                            <p class="font-ui text-xs font-bold uppercase tracking-[0.18em] text-brand-pink">Canal futuro</p>
                            <h2 id="whatsapp-title" class="mt-2 font-display text-4xl text-text-inverse">WhatsApp para contacto</h2>
                            <p class="mt-4 max-w-2xl font-body leading-relaxed text-white/75">Puedes contactar a Yaris por WhatsApp. Abrir este canal no autentica, no verifica identidad, no crea una cuenta y no inicia una sesión.</p>
                        </div>
                        <a href="https://wa.me/529932294158" target="_blank" rel="noopener noreferrer" aria-label="Contactar a Yaris por WhatsApp, abre en una pestaña nueva" class="inline-flex min-h-11 items-center justify-center rounded-md border border-white/40 px-4 py-2 font-ui text-sm font-bold text-text-inverse hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-focus-ring focus:ring-offset-2 focus:ring-offset-brand-black">Contactar por WhatsApp</a>
                    </div>
                </UiCard>
            </section>

            <aside aria-label="Aviso provisional de privacidad" class="rounded-lg border border-brand-pink/40 bg-white/5 p-5 font-body text-sm leading-relaxed text-white/75">
                La información de privacidad y seguridad de esta fase es provisional y está pendiente de revisión. El teléfono de esta demostración no se persiste ni se transmite.
                <a href="/fase-1#privacidad-seguridad" class="font-ui font-bold text-brand-pink underline underline-offset-4 focus:outline-none focus:ring-2 focus:ring-focus-ring">Consulta el aviso provisional</a>
            </aside>

            <section aria-labelledby="admin-title" class="border-t border-white/15 pt-8">
                <h2 id="admin-title" class="font-display text-4xl text-text-inverse">Acceso administrativo</h2>
                <p class="mt-3 max-w-2xl font-body leading-relaxed text-white/75"><strong class="text-white">FUNCIONAL.</strong> Este acceso es exclusivo para Yaris y reutiliza la autenticación administrativa existente. No es un acceso de clientes.</p>
                <a href="/admin/login" class="mt-5 inline-flex min-h-11 items-center justify-center rounded-md bg-action-primary px-4 py-2 font-ui text-sm font-bold text-action-primary-foreground hover:bg-action-primary-hover focus:outline-none focus:ring-2 focus:ring-focus-ring focus:ring-offset-2 focus:ring-offset-brand-black">Ir a acceso administrativo</a>
            </section>
        </div>
    </PublicLayout>
</template>
