<template>
  <q-layout view="lHh Lpr lFf" class="bg-grey-1">

    <AppHeader :navigation="navStore.navigationData" :loading="navStore.loading">
      <template v-slot:bottom>
        <PriceTicker v-if="shouldShowTicker" :items="marketStore.trends" :speed="60" />
      </template>
    </AppHeader>

    <CartDrawer />

    <q-page-container>
      <router-view v-slot="{ Component }">
        <transition name="fade" mode="out-in">
          <component :is="Component" />
        </transition>
      </router-view>

      <WebFooter />

      <q-page-sticky
        v-if="showFab"
        position="bottom-right"
        :offset="[18, 80]"
      >
        <q-btn
          fab
          icon="download"
          color="primary"
          @click="promptInstall"
          class="shadow-5 pulse-animation"
        >
          <q-badge floating color="red" rounded />
          <q-tooltip v-if="!$q.platform.is.mobile">Installer Upesi App</q-tooltip>
        </q-btn>
      </q-page-sticky>
    </q-page-container>

    <MobileFooter v-if="$q.screen.lt.md && !$route.meta.hideBottomNav" />

  </q-layout>
</template>

<script setup lang="ts">
import AppHeader from 'components/layout/AppHeader.vue';
import MobileFooter from 'components/layout/MobileFooter.vue';
import WebFooter from 'src/components/layout/WebFooter.vue';
import PriceTicker from 'components/layout/PriceTicker.vue';
import CartDrawer from 'src/components/market/CartDrawer.vue';

import { useNavigationStore } from 'src/stores/navigation';
import { useMarketStore } from 'src/stores/market';
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import { useQuasar } from 'quasar';

const $q = useQuasar();
const route = useRoute();
const marketStore = useMarketStore();
const navStore = useNavigationStore();

// eslint-disable-next-line @typescript-eslint/no-explicit-any
const deferredPrompt = ref<any>(null);
const canInstall = ref(false);

// Condition stricte : Doit être installable ET être sur un appareil Mobile/Tablette
const showFab = computed(() => {
  return canInstall.value && ($q.platform.is.mobile || $q.platform.is.android);
});

const shouldShowTicker = computed(() => {
  const allowedNames = ['home', 'market'];
  const currentName = route.name as string;
  return allowedNames.includes(currentName) || route.path === '/';
});

onMounted(async () => {
  await Promise.allSettled([
    navStore.fetchNavigationData(),
    marketStore.fetchTicker()
  ]);

  // Détection Android/Chrome
  window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt.value = e;
    canInstall.value = true;
  });

  // Détection iOS (iPhone/iPad)
  const isIOS = $q.platform.is.ios;
  const isStandalone = window.matchMedia('(display-mode: standalone)').matches;

  // Sur iOS, on affiche le bouton si on n'est pas déjà dans l'app installée
  if (isIOS && !isStandalone) {
    canInstall.value = true;
  }

  window.addEventListener('appinstalled', () => {
    canInstall.value = false;
  });
});

const promptInstall = async () => {
  if ($q.platform.is.ios) {
    $q.dialog({
      title: 'Installer Upesi',
      message: 'Appuyez sur l\'icône "Partager" de Safari (le carré avec une flèche en bas), puis faites défiler et choisissez "Sur l\'écran d\'accueil".',
      ok: { label: 'J\'ai compris', color: 'primary', flat: true }
    });
  } else if (deferredPrompt.value) {
    deferredPrompt.value.prompt();
    const { outcome } = await deferredPrompt.value.userChoice;
    if (outcome === 'accepted') canInstall.value = false;
    deferredPrompt.value = null;
  }
};
</script>

<style>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

body.screen--xs, body.screen--sm { padding-bottom: 60px; }

.pulse-animation {
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.08); }
  100% { transform: scale(1); }
}
</style>
