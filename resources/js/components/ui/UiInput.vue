<script setup lang="ts">
import type { InputHTMLAttributes } from 'vue';

type SupportedInputType = Extract<InputHTMLAttributes['type'], 'text' | 'email' | 'password' | 'date'>;

interface Props {
    type?: SupportedInputType;
    disabled?: boolean;
    invalid?: boolean;
}

defineOptions({ inheritAttrs: false });

const props = withDefaults(defineProps<Props>(), {
    type: 'text',
    disabled: false,
    invalid: false,
});

const model = defineModel<string>({ default: '' });
</script>

<template>
    <input
        v-bind="$attrs"
        v-model="model"
        :type="props.type"
        :disabled="props.disabled"
        :class="[
            'block min-h-11 w-full rounded-md border bg-surface-elevated px-3 py-2 font-body text-base text-text-primary',
            'placeholder:text-text-secondary focus-visible:border-focus-ring focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus-ring',
            'disabled:cursor-not-allowed disabled:bg-disabled-surface disabled:text-disabled-foreground',
            props.invalid ? 'border-state-error' : 'border-border-default',
        ]"
        :aria-invalid="props.invalid || undefined"
    >
</template>
