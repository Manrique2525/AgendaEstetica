<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';
import UiContainer from '../components/ui/UiContainer.vue';
import { publicSite } from '../data/publicSite';

const menuOpen = ref(false);

function closeMenu(): void {
    menuOpen.value = false;
}

function handleKeydown(event: KeyboardEvent): void {
    if (event.key === 'Escape') closeMenu();
}

onMounted(() => window.addEventListener('keydown', handleKeydown));
onUnmounted(() => window.removeEventListener('keydown', handleKeydown));
</script>

<template>
    <a
        href="#main-content"
        class="sr-only fixed left-4 top-4 z-50 rounded-md bg-action-primary px-4 py-2 font-ui text-sm font-bold text-action-primary-foreground focus:not-sr-only focus:outline-none focus:ring-2 focus:ring-focus-ring focus:ring-offset-2"
    >
        Saltar al contenido principal
    </a>
    <div class="min-h-screen bg-surface-inverse text-text-inverse">
        <UiContainer>
            <header class="border-b border-white/15 py-6 sm:py-8">
                <div class="flex items-center justify-between gap-4">
                    <a href="/" class="min-w-0 rounded-md focus:outline-none focus:ring-2 focus:ring-focus-ring focus:ring-offset-2 focus:ring-offset-brand-black" @click="closeMenu">
                        <span class="block truncate font-display text-2xl leading-none text-text-inverse sm:text-3xl">{{ publicSite.business.name }}</span>
                        <span class="mt-1 block font-ui text-[0.68rem] font-semibold uppercase tracking-[0.2em] text-brand-gold">{{ publicSite.business.tagline }}</span>
                    </a>
                    <button
                        type="button"
                        class="inline-flex min-h-11 shrink-0 items-center justify-center rounded-md border border-white/30 px-3 font-ui text-sm font-bold text-text-inverse focus:outline-none focus:ring-2 focus:ring-focus-ring focus:ring-offset-2 focus:ring-offset-brand-black md:hidden"
                        :aria-expanded="menuOpen"
                        aria-controls="public-navigation"
                        @click="menuOpen = !menuOpen"
                    >
                        <span>{{ menuOpen ? 'Cerrar menú' : 'Abrir menú' }}</span>
                    </button>
                </div>
                <nav
                    id="public-navigation"
                    aria-label="Navegación principal"
                    :class="['mt-6 md:mt-5', menuOpen ? 'block' : 'hidden md:block']"
                >
                    <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                        <ul class="flex flex-col gap-1 md:flex-row md:items-center md:gap-2">
                            <li v-for="item in publicSite.navigation" :key="item.href">
                                <a
                                    :href="item.href"
                                    class="block rounded-md px-3 py-2 font-ui text-sm font-semibold text-text-inverse hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-focus-ring focus:ring-offset-2 focus:ring-offset-brand-black"
                                    @click="closeMenu"
                                >
                                    {{ item.label }}
                                </a>
                            </li>
                        </ul>
                        <a
                            href="/reservar"
                            class="inline-flex min-h-11 items-center justify-center rounded-md bg-action-primary px-4 py-2 font-ui text-sm font-bold text-action-primary-foreground hover:bg-action-primary-hover focus:outline-none focus:ring-2 focus:ring-focus-ring focus:ring-offset-2 focus:ring-offset-brand-black"
                            @click="closeMenu"
                        >
                            Solicitar cita
                        </a>
                    </div>
                </nav>
            </header>
            <main id="main-content" tabindex="-1" class="py-8 outline-none sm:py-12">
                <slot />
            </main>
            <footer class="border-t border-white/15 py-8" aria-label="Información de contacto">
                <div class="grid gap-6 font-body text-sm text-white/80 sm:grid-cols-2">
                    <div>
                        <p class="font-display text-2xl text-text-inverse">{{ publicSite.business.name }}</p>
                        <p class="mt-1 font-ui text-xs font-semibold uppercase tracking-[0.16em] text-brand-gold">{{ publicSite.business.tagline }}</p>
                    </div>
                    <div class="space-y-2 sm:text-right">
                        <a
                            :href="publicSite.business.whatsappUrl"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-block rounded-md text-text-inverse underline decoration-brand-pink underline-offset-4 focus:outline-none focus:ring-2 focus:ring-focus-ring focus:ring-offset-2 focus:ring-offset-brand-black"
                            :aria-label="`Abrir WhatsApp de ${publicSite.business.name}`"
                        >
                            WhatsApp {{ publicSite.business.displayPhone }}
                        </a>
                        <p v-for="line in publicSite.business.location" :key="line">{{ line }}</p>
                    </div>
                </div>
                <div class="mt-6 border-t border-white/10 pt-5">
                    <a
                        href="/fase-1#privacidad-seguridad"
                        class="inline-block rounded-md font-ui text-sm font-semibold text-text-inverse underline decoration-brand-pink underline-offset-4 focus:outline-none focus:ring-2 focus:ring-focus-ring focus:ring-offset-2 focus:ring-offset-brand-black"
                    >
                        Privacidad y seguridad
                    </a>
                </div>
            </footer>
        </UiContainer>
    </div>
</template>
