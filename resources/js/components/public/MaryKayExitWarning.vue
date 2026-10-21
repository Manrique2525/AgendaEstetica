<script setup lang="ts">
import { ref, onBeforeUnmount, onMounted } from 'vue';
import UiButton from '../ui/UiButton.vue';

interface Props {
    open: boolean;
    destinationUrl: string;
    destinationLabel: string;
}

interface Emits {
    close: [];
    confirm: [];
}

const props = withDefaults(defineProps<Props>(), {
    destinationLabel: 'sitio externo',
});

const emit = defineEmits<Emits>();
const overlayRef = ref<HTMLDivElement | null>(null);
const dialogRef = ref<HTMLDivElement | null>(null);

function handleKeydown(event: KeyboardEvent): void {
    if (props.open && event.key === 'Escape' && !event.defaultPrevented) {
        emit('close');
    }
}

function focusDialog(): void {
    dialogRef.value?.focus();
}

function handleOverlayClick(event: MouseEvent): void {
    if (event.target === overlayRef.value) {
        emit('close');
    }
}

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
    if (props.open) focusDialog();
});

onBeforeUnmount(() => {
    window.removeEventListener('keydown', handleKeydown);
});
</script>

<template>
    <Teleport to="body">
        <div
            v-if="open"
            ref="overlayRef"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 sm:items-center"
            @click="handleOverlayClick"
            role="dialog"
            aria-modal="true"
            aria-labelledby="exit-warning-title"
            aria-describedby="exit-warning-message"
        >
            <section
                ref="dialogRef"
                class="w-full max-w-md rounded-xl border border-border-default bg-surface-elevated p-6 text-text-primary shadow-xl"
                tabindex="-1"
            >
                <div class="flex items-center gap-3 mb-4">
                    <div class="inline-flex items-center justify-center size-10 rounded-full bg-brand-gold/15 text-brand-gold" aria-hidden="true">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c.867 0 1.547-.78 1.547-1.75v-3.5a.75.75 0 00-.75-.75h-12a.75.75 0 01-.75-.75v-3.5a.75.75 0 01.75-.75h12a.75.75 0 01.75.75v3.5c0 .97.78 1.75 1.547 1.75z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-ui text-sm font-semibold text-brand-gold">Advertencia de salida</p>
                    </div>
                </div>

                <h2 id="exit-warning-title" class="font-display text-2xl font-bold text-text-primary">Vas a salir de AgendaEstetica</h2>

                <p id="exit-warning-message" class="mt-3 font-body text-sm leading-relaxed text-text-secondary">
                    Estás a punto de visitar <strong>{{ destinationLabel }}</strong>.
                    La disponibilidad de productos, las condiciones de compra, los precios y el tratamiento de tus datos en ese sitio dependen de su responsable.
                </p>

                <div class="mt-4 p-3 rounded-lg border border-brand-gold/30 bg-brand-gold/5" role="note" aria-label="Información del destino">
                    <p class="font-ui text-xs font-semibold text-brand-gold">Destino:</p>
                    <p class="mt-1 font-mono text-sm text-text-secondary break-all">{{ destinationUrl }}</p>
                </div>

                <p class="mt-4 font-body text-sm text-text-secondary">Antes de continuar, revisa sus condiciones y su aviso de privacidad.</p>

                <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                    <UiButton
                        variant="secondary"
                        @click="emit('close')"
                        class="w-full sm:w-auto"
                    >
                        Cancelar
                    </UiButton>
                    <UiButton
                        @click="emit('confirm')"
                        class="w-full sm:w-auto"
                    >
                        Continuar a {{ destinationLabel }}
                    </UiButton>
                </div>
            </section>
        </div>
    </Teleport>
</template>