<script setup lang="ts">
import { computed } from 'vue';

type ButtonVariant = 'primary' | 'secondary';
type ButtonType = 'button' | 'submit' | 'reset';

interface Props {
    variant?: ButtonVariant;
    type?: ButtonType;
    disabled?: boolean;
    loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    variant: 'primary',
    type: 'button',
    disabled: false,
    loading: false,
});

const isDisabled = computed(() => props.disabled || props.loading);

const variantClasses: Record<ButtonVariant, string> = {
    primary: 'bg-action-primary text-action-primary-foreground hover:bg-action-primary-hover',
    secondary: 'border border-border-strong bg-action-secondary text-action-secondary-foreground hover:bg-action-secondary-hover',
};

const classes = computed(() => [
    'inline-flex min-h-11 items-center justify-center gap-2 rounded-md px-4 py-2 font-ui text-sm font-bold',
    'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus-ring focus-visible:ring-offset-2',
    'disabled:cursor-not-allowed disabled:bg-disabled-surface disabled:text-disabled-foreground',
    variantClasses[props.variant],
]);
</script>

<template>
    <button
        v-bind="$attrs"
        :type="type"
        :class="classes"
        :disabled="isDisabled"
        :aria-busy="loading || undefined"
    >
        <span v-if="loading" class="sr-only">Cargando</span>
        <span v-if="loading" aria-hidden="true" class="size-3 rounded-full border-2 border-current border-t-transparent" />
        <slot />
    </button>
</template>
