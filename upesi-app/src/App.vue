<template>
  <router-view />
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { useNavigationStore } from 'src/stores/navigation';
import { useMarketStore } from 'src/stores/market';
import { App } from '@capacitor/app';
import { StatusBar, Style } from '@capacitor/status-bar'
import { Capacitor } from '@capacitor/core'
import { useAuthStore } from './stores/auth';
import { api } from './boot/axios';

const navStore = useNavigationStore();
const marketStore = useMarketStore();
const authStore = useAuthStore();

const refreshAllData = () => {
  // On lance sans bloquer
  void navStore.fetchNavigationData(true);
  void marketStore.fetchTicker(undefined, true);
};

const hideLoader = () => {
  const loader = document.getElementById('upesi-loader-container');
  if (loader) {
    loader.classList.add('loader-hidden');
    setTimeout(() => {
      if (loader && loader.parentNode) {
        loader.remove();
      }
    }, 300); // Réduit à 300ms au lieu de 500ms
  }
};

onMounted(async () => {
  // Cacher le loader TRÈS rapidement (max 1-2 secondes)
  const timeoutId = setTimeout(() => {
    hideLoader();
  }, 1500); // Force la disparition après 1.5s maximum

  try {
    // Vérification version en arrière-plan sans bloquer
    const versionPromise = (async () => {
      try {
        const { data } = await api.get('/app-status');
        const serverVersion = data.version;
        const localVersion = localStorage.getItem('upesi_version');

        if (localVersion && localVersion !== serverVersion) {
          if ('serviceWorker' in navigator) {
            const registrations = await navigator.serviceWorker.getRegistrations();
            for (const reg of registrations) await reg.unregister();
          }
          const cacheNames = await caches.keys();
          await Promise.all(cacheNames.map(name => caches.delete(name)));
          localStorage.setItem('upesi_version', serverVersion);
          window.location.reload();
        } else if (!localVersion) {
          localStorage.setItem('upesi_version', serverVersion);
        }
      } catch (e) {
        console.error("Check version failed", e);
      }
    })();

    // Auth en parallèle
    const authPromise = authStore.init().catch(e => {
      if (process.env.DEV) console.error("Erreur init Auth:", e);
    });

    // Attendre au minimum que l'UI soit prête (mais pas plus de 1s)
    await Promise.race([
      Promise.all([versionPromise, authPromise]),
      new Promise(resolve => setTimeout(resolve, 800))
    ]);

  } catch (e) {
    console.error("Erreur initialisation:", e);
  } finally {
    // On cache le loader immédiatement
    clearTimeout(timeoutId);
    hideLoader();
  }

  // StatusBar (ne bloque pas)
  if (Capacitor.isNativePlatform()) {
    try {
      await StatusBar.setBackgroundColor({ color: '#1B4D24' });
      await StatusBar.setStyle({ style: Style.Dark });
      await StatusBar.setOverlaysWebView({ overlay: false });
    } catch (e) {
      console.error("Erreur StatusBar:", e);
    }
  }

  // Listeners
  void App.addListener('appUrlOpen', () => {
    void refreshAllData();
  });

  // Chargement des données en arrière-plan
  refreshAllData();
});
</script>

<style>
/* Optionnel: animation de fade-out plus rapide */
#upesi-loader-container {
  transition: opacity 0.2s ease-out;
}

#upesi-loader-container.loader-hidden {
  opacity: 0;
  pointer-events: none;
}
</style>
