<script setup lang="ts">
interface Props {
    disabled?: boolean;
    invalid?: boolean;
    rows?: number;
}

defineOptions({ inheritAttrs: false });

const props = withDefaults(defineProps<Props>(), {
    disabled: false,
    invalid: false,
    rows: 3,
});

const model = defineModel<string>({ default: '' });
</script>

<template>
    <textarea
        v-bind="$attrs"
        v-model="model"
        :rows="props.rows"
        :disabled="props.disabled"
        :class="[
            'block w-full rounded-md border bg-surface-elevated px-3 py-2 font-body text-base text-text-primary',
            'placeholder:text-text-secondary focus-visible:border-focus-ring focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-focus-ring',
            'disabled:cursor-not-allowed disabled:bg-disabled-surface disabled:text-disabled-foreground',
            props.invalid ? 'border-state-error' : 'border-border-default',
        ]"
        :aria-invalid="props.invalid || undefined"
    />
</template>
