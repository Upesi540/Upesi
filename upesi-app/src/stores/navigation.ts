import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { api } from 'boot/axios';
import type { ApiResponse } from 'src/types/api';
import type { appInitData } from 'src/types/appInit';
import type { Market, ServiceCategory } from 'src/types';

export const useNavigationStore = defineStore('app-init', () => {
  const serviceCategories = ref<ServiceCategory[]>([]);
  const markets = ref<Market[]>([]);
  const loading = ref(false);
  const isLoaded = ref(false);
  const error = ref<string | null>(null);

  const navigationData = computed(
    () =>
      ({
        markets: markets.value,
        service_categories: serviceCategories.value,
      }) as appInitData,
  );

  async function fetchNavigationData(forceRefresh = false) {
    // Si on a des données persistées et qu'on ne force pas, on évite l'appel API
    if (isLoaded.value && !forceRefresh) {
      return;
    }

    loading.value = true;
    error.value = null;

    try {
      const { data } = await api.get<ApiResponse<appInitData>>('/navigation');

      if (data.status && data.data) {
        serviceCategories.value = data.data.service_categories;
        markets.value = data.data.markets;
        isLoaded.value = true;
      }
    } catch (err) {
      error.value = 'Erreur lors du chargement des données';
      console.error(err);
    } finally {
      loading.value = false;
    }
  }

  return {
    serviceCategories,
    markets,
    navigationData,
    loading,
    isLoaded,
    error,
    fetchNavigationData,
  };
}, {
  // ✅ CONFIGURATION DE LA PERSISTANCE
  persist: {
    key: 'upesi-nav-cache',
    // On persiste les données métier et le flag isLoaded
    // On ignore 'loading' et 'error' pour repartir sur un état propre
    pick: ['serviceCategories', 'markets', 'isLoaded'],
  }
});
