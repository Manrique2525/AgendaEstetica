<script setup lang="ts">
import UiButton from '../ui/UiButton.vue';
import UiConfirmDialog from '../ui/UiConfirmDialog.vue';

interface Column {
    key: string;
    label: string;
    sortable?: boolean;
    render?: (row: Record<string, unknown>) => string | number | null;
    class?: string;
    width?: string;
}

interface Props {
    columns: Column[];
    rows: Record<string, unknown>[];
    loading?: boolean;
    emptyMessage?: string;
    rowKey: string;
    onEdit?: (row: Record<string, unknown>) => void;
    onToggle?: (row: Record<string, unknown>) => void;
    onDelete?: (row: Record<string, unknown>) => void;
    pendingDelete?: Record<string, unknown> | null;
    deleting?: boolean;
    toggleLabel?: (row: Record<string, unknown>) => string;
}

const props = withDefaults(defineProps<Props>(), {
    loading: false,
    emptyMessage: 'Sin registros.',
    rowKey: 'id',
    toggleLabel: (row: Record<string, unknown>) => (row.active as boolean ? 'Desactivar' : 'Activar'),
});

const emit = defineEmits<{
    edit: [Record<string, unknown>];
    toggle: [Record<string, unknown>];
    delete: [Record<string, unknown>];
    confirmDelete: [];
    cancelDelete: [];
}>();

function handleEdit(row: Record<string, unknown>): void {
    emit('edit', row);
}

function handleToggle(row: Record<string, unknown>): void {
    emit('toggle', row);
}

function handleDelete(row: Record<string, unknown>): void {
    emit('delete', row);
}

function handleConfirmDelete(): void {
    emit('confirmDelete');
}

function handleCancelDelete(): void {
    emit('cancelDelete');
}

function getCellValue(row: Record<string, unknown>, column: Column): string {
    if (column.render) {
        const value = column.render(row);
        return value === null ? '' : String(value);
    }
    const value = row[column.key];
    return value === null || value === undefined ? '' : String(value);
}
</script>

<template>
    <div v-if="loading" role="status" class="flex items-center justify-center py-12">
        <div class="size-8 border-3 border-brand-turquoise border-t-transparent rounded-full animate-spin" aria-hidden="true" />
        <span class="sr-only">Cargando...</span>
    </div>

    <p v-else-if="!rows.length" class="text-center py-12 font-body text-sm text-text-secondary">
        {{ emptyMessage }}
    </p>

    <div v-else class="overflow-x-auto">
        <table class="w-full border-collapse" role="grid">
            <thead>
                <tr class="border-b border-border-strong">
                    <th v-for="column in columns" :key="column.key" :class="['px-4 py-3 text-left font-ui text-sm font-semibold text-text-secondary', column.class]" :style="{ width: column.width }">
                        {{ column.label }}
                    </th>
                    <th class="px-4 py-3 text-right font-ui text-sm font-semibold text-text-secondary w-36">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border-default">
                <tr v-for="row in rows" :key="row[props.rowKey] as string" class="hover:bg-surface-subtle transition-colors">
                    <td v-for="column in columns" :key="column.key" :class="['px-4 py-3 font-body text-sm text-text-primary', column.class]">
                        {{ getCellValue(row, column) }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <UiButton
                                v-if="onEdit"
                                type="button"
                                variant="secondary"
                                size="sm"
                                @click="handleEdit(row)"
                            >
                                Editar
                            </UiButton>
                            <UiButton
                                v-if="onToggle"
                                type="button"
                                variant="secondary"
                                size="sm"
                                @click="handleToggle(row)"
                            >
                                {{ toggleLabel(row) }}
                            </UiButton>
                            <UiButton
                                v-if="onDelete"
                                type="button"
                                variant="secondary"
                                size="sm"
                                @click="handleDelete(row)"
                            >
                                Eliminar
                            </UiButton>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <UiConfirmDialog
        :open="pendingDelete !== null"
        title="Eliminar registro"
        :message="`¿Confirmas eliminar ${pendingDelete?.name ?? ''}? Esta acción no se puede deshacer.`"
        confirm-label="Eliminar"
        :pending="deleting"
        @cancel="handleCancelDelete"
        @confirm="handleConfirmDelete"
    />
</template>