<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { publicCatalogApi, type PublicCatalogProduct } from '../../services/api/publicCatalog';

const products = ref<PublicCatalogProduct[]>([]);
const loading = ref(true);
const error = ref('');

const groups = computed(() => {
    const map = new Map<string, PublicCatalogProduct[]>();

    for (const product of products.value) {
        const key = product.category?.name ?? 'Otros productos';
        const current = map.get(key) ?? [];
        current.push(product);
        map.set(key, current);
    }

    return Array.from(map.entries()).map(([name, items]) => ({ name, products: items }));
});

function anchor(name: string): string {
    return `product-${name.toLowerCase().normalize('NFD').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '')}`;
}

async function load(): Promise<void> {
    loading.value = true;
    error.value = '';

    try {
        products.value = await publicCatalogApi.listProducts();
    } catch {
        error.value = 'No pudimos cargar los productos. Intenta nuevamente.';
    } finally {
        loading.value = false;
    }
}

onMounted(load);
</script>

<template>
    <div class="mt-6" aria-live="polite">
        <p v-if="loading" role="status" class="font-body text-sm text-white/70">Cargando productos...</p>

        <p v-else-if="error" role="alert" class="rounded-md border border-state-error bg-red-950/40 p-3 font-body text-sm text-white">
            {{ error }}
            <button type="button" class="ml-2 font-ui font-bold underline focus:outline-none focus:ring-2 focus:ring-focus-ring" @click="load">Reintentar</button>
        </p>

        <p v-else-if="!products.length" class="font-body text-sm text-white/70">Nuestro catálogo se está preparando. Estas son las categorías que podrás conocer más adelante.</p>

        <div v-else class="space-y-8">
            <section v-for="group in groups" :key="group.name" :aria-labelledby="anchor(group.name)">
                <h3 :id="anchor(group.name)" class="font-ui text-sm font-bold uppercase tracking-[0.14em] text-brand-pink">{{ group.name }}</h3>
                <ul class="mt-4 grid gap-4 sm:grid-cols-2">
                    <li v-for="product in group.products" :key="product.id" class="flex flex-col rounded-lg border border-white/15 bg-white/5 p-5">
                        <div class="flex items-start justify-between gap-4">
                            <h4 class="font-display text-2xl text-text-inverse">{{ product.name }}</h4>
                            <span class="whitespace-nowrap font-ui text-sm font-bold text-brand-gold">{{ product.price_display }}</span>
                        </div>
                        <p v-if="product.description" class="mt-3 grow font-body text-sm leading-relaxed text-white/75">{{ product.description }}</p>
                    </li>
                </ul>
            </section>
        </div>
    </div>
</template>
