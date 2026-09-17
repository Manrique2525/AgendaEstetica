<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import UiButton from './UiButton.vue';

interface Props {
    open: boolean;
    title: string;
    message: string;
    confirmLabel: string;
    pending?: boolean;
}

const props = withDefaults(defineProps<Props>(), { pending: false });
const emit = defineEmits<{ cancel: []; confirm: [] }>();
const dialog = ref<HTMLElement | null>(null);

function focusDialog(): void {
    dialog.value?.focus();
}

function handleKeydown(event: KeyboardEvent): void {
    if (props.open && event.key === 'Escape' && !props.pending) {
        emit('cancel');
    }
}

watch(() => props.open, (open) => {
    if (open) focusDialog();
});

onMounted(() => {
    window.addEventListener('keydown', handleKeydown);
    if (props.open) focusDialog();
});
onBeforeUnmount(() => window.removeEventListener('keydown', handleKeydown));
</script>

<template>
    <div v-if="open" class="fixed inset-0 z-50 flex items-end justify-center bg-black/40 p-4 sm:items-center" @click.self="!pending && emit('cancel')">
        <section
            ref="dialog"
            class="w-full max-w-md rounded-xl border border-border-default bg-surface-elevated p-6 text-text-primary shadow-xl"
            role="dialog"
            aria-modal="true"
            tabindex="-1"
            aria-labelledby="confirm-dialog-title"
            aria-describedby="confirm-dialog-message"
        >
            <h2 id="confirm-dialog-title" class="font-display text-3xl leading-tight">{{ title }}</h2>
            <p id="confirm-dialog-message" class="mt-3 font-body text-sm text-text-secondary">{{ message }}</p>
            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <UiButton variant="secondary" :disabled="pending" @click="emit('cancel')">Cerrar</UiButton>
                <UiButton :loading="pending" @click="emit('confirm')">{{ confirmLabel }}</UiButton>
            </div>
        </section>
    </div>
</template>
