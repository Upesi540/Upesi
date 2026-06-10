import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { api } from 'boot/axios';
import type { TickerResponse, MarketTrend } from 'src/types/ticker';
import type { ApiResponse } from 'src/types/api';

export const useMarketStore = defineStore(
  'market',
  () => {
    const trends = ref<MarketTrend[]>([]);
    const countryId = ref<string | null>(null);
    const isDetected = ref(false);
    const loading = ref(false);
    const lastUpdate = ref<Date | null>(null);

    const hasTrends = computed(() => trends.value.length > 0);

    /**
     * Action principale de récupération
     * @param forceRefresh - Si vrai, ignore le cache et force l'appel API
     * @param requestedCountryId - ID spécifique du pays
     */
    async function fetchTicker(requestedCountryId?: string, forceRefresh = false) {
      // STRATÉGIE DE CACHE :
      // Si on a déjà des données ET qu'on ne force pas le refresh ET que le pays n'a pas changé
      if (
        hasTrends.value &&
        !forceRefresh &&
        (!requestedCountryId || requestedCountryId === countryId.value)
      ) {
        return;
      }

      loading.value = true;
      try {
        const params = requestedCountryId ? { country_id: requestedCountryId } : {};
        const response = await api.get<ApiResponse<TickerResponse>>('/market/ticker', { params });

        const data = response.data.data;

        trends.value = data.trends;
        countryId.value = data.country_id;
        isDetected.value = data.is_detected;
        lastUpdate.value = new Date();
      } catch (error) {
        console.error('Erreur ticker:', error);
      } finally {
        loading.value = false;
      }
    }

    // Pour un rafraîchissement manuel (ex: bouton refresh ou pull-to-refresh)
    async function manualRefresh() {
      await fetchTicker(countryId.value ?? undefined, true);
    }

    return {
      trends,
      countryId,
      isDetected,
      loading,
      lastUpdate,
      hasTrends,
      fetchTicker,
      manualRefresh, // On expose le refresh forcé
    };
  },
  {
    // ✅ CONFIGURATION DE LA PERSISTANCE
    persist: {
      key: 'upesi-market-cache',
      // On ne garde que les données utiles, on ignore "loading"
      pick: ['trends', 'countryId', 'isDetected', 'lastUpdate'],
    },
  },
);
