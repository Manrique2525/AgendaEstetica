<script setup lang="ts">
import { computed, useId } from 'vue';

interface Props {
    label: string;
    id?: string;
    help?: string;
    error?: string;
    required?: boolean;
}

const props = defineProps<Props>();
const generatedId = useId();

const inputId = computed(() => props.id ?? generatedId);
const helpId = computed(() => `${inputId.value}-help`);
const errorId = computed(() => `${inputId.value}-error`);
const describedBy = computed(() => [props.help ? helpId.value : null, props.error ? errorId.value : null].filter(Boolean).join(' ') || undefined);
const invalid = computed(() => Boolean(props.error));
</script>

<template>
    <div class="space-y-2">
        <label :for="inputId" class="block font-ui text-sm font-semibold text-text-primary">
            {{ label }}
            <span v-if="required" aria-hidden="true"> *</span>
        </label>

        <slot :input-id="inputId" :described-by="describedBy" :invalid="invalid" />

        <p v-if="help" :id="helpId" class="font-body text-sm text-text-secondary">
            {{ help }}
        </p>
        <p v-if="error" :id="errorId" class="font-body text-sm text-state-error">
            {{ error }}
        </p>
    </div>
</template>
