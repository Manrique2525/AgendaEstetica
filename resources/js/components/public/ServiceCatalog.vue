<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { publicBookingApi, type PublicBookingService } from '../../services/api/publicBooking';

const services = ref<PublicBookingService[]>([]);
const loading = ref(true);
const error = ref('');

const groups = computed(() => {
    const map = new Map<string, PublicBookingService[]>();

    for (const service of services.value) {
        const key = service.category?.name ?? 'Otros servicios';
        const current = map.get(key) ?? [];
        current.push(service);
        map.set(key, current);
    }

    return Array.from(map.entries()).map(([name, items]) => ({ name, services: items }));
});

function anchor(name: string): string {
    return `catalog-${name.toLowerCase().normalize('NFD').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '')}`;
}

async function load(): Promise<void> {
    loading.value = true;
    error.value = '';

    try {
        services.value = await publicBookingApi.listServices();
    } catch {
        error.value = 'No pudimos cargar los servicios. Intenta nuevamente.';
    } finally {
        loading.value = false;
    }
}

onMounted(load);
</script>

<template>
    <div class="mt-6" aria-live="polite">
        <p v-if="loading" role="status" class="font-body text-sm text-white/70">Cargando servicios...</p>

        <p v-else-if="error" role="alert" class="rounded-md border border-state-error bg-red-950/40 p-3 font-body text-sm text-white">
            {{ error }}
            <button type="button" class="ml-2 font-ui font-bold underline focus:outline-none focus:ring-2 focus:ring-focus-ring" @click="load">Reintentar</button>
        </p>

        <p v-else-if="!services.length" class="font-body text-sm text-white/70">Aún no hay servicios publicados. Consulta por WhatsApp para más información.</p>

        <div v-else class="space-y-8">
            <section v-for="group in groups" :key="group.name" :aria-labelledby="anchor(group.name)">
                <h3 :id="anchor(group.name)" class="font-ui text-sm font-bold uppercase tracking-[0.14em] text-brand-pink">{{ group.name }}</h3>
                <ul class="mt-4 grid gap-4 sm:grid-cols-2">
                    <li v-for="service in group.services" :key="service.id" class="flex flex-col overflow-hidden rounded-lg border border-white/15 bg-white/5">
                        <img
                            v-if="service.image_url"
                            :src="service.image_url"
                            :alt="`Imagen de ${service.name}`"
                            class="h-44 w-full object-cover"
                            loading="lazy"
                        >
                        <div class="flex grow flex-col p-5">
                            <div class="flex items-start justify-between gap-4">
                                <h4 class="font-display text-2xl text-text-inverse">{{ service.name }}</h4>
                                <span class="whitespace-nowrap font-ui text-sm font-bold text-brand-gold">{{ service.pricing_display }}</span>
                            </div>
                            <p v-if="service.description" class="mt-3 grow font-body text-sm leading-relaxed text-white/75">{{ service.description }}</p>
                            <p class="mt-3 font-ui text-xs font-semibold uppercase tracking-[0.12em] text-white/60">{{ service.duration_minutes }} minutos</p>
                            <a :href="`/reservar?service=${service.id}`" class="mt-4 inline-flex min-h-11 items-center justify-center rounded-md border border-white/40 px-4 py-2 font-ui text-sm font-bold text-text-inverse hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-focus-ring focus:ring-offset-2 focus:ring-offset-brand-black">Reservar</a>
                        </div>
                    </li>
                </ul>
            </section>

            <p class="font-body text-xs leading-relaxed text-white/55">Los precios mostrados son orientativos y pueden confirmarse al momento de tu cita.</p>
        </div>
    </div>
</template>
