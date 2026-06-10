// stores/location.ts (version simplifiée)
import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { api } from 'boot/axios';
import type{ Country, State } from 'src/types/country';

export const useLocationStore = defineStore('location', () => {
  const countries = ref<Country[]>([]);
  const states = ref<State[]>([]);
  const loading = ref(false);
  const isLoaded = ref(false);

  // Cache simple avec objets
  const statesByCountry = ref<Record<string, State[]>>({});


  // Dans locationStore
const countryOptions = computed(() =>
  countries.value.map(c => ({ label: `${c.emoji || '🌍'} ${c.name}`, value: c.id }))
);

const getStateOptions = (countryId: string) => {
  const statesList = statesByCountry.value[countryId] || [];
  return statesList.map(s => ({ label: s.name, value: s.id }));
};



  async function fetchCountries(forceRefresh = false) {
    if (countries.value.length && !forceRefresh) return;

    loading.value = true;
    try {
      const { data } = await api.get('/locations/countries');
      countries.value = data.data;
      isLoaded.value = true;
    } catch (error) {
      console.error(error);
    } finally {
      loading.value = false;
    }
  }

  async function fetchStatesByCountry(countryId: string) {
    // Vérifier le cache
    if (statesByCountry.value[countryId]) {
      states.value = statesByCountry.value[countryId];
      return;
    }

    loading.value = true;
    try {
      const { data } = await api.get(`/locations/countries/${countryId}/states`);
      statesByCountry.value[countryId] = data.data;
      states.value = data.data;
    } catch (error) {
      console.error(error);
    } finally {
      loading.value = false;
    }
  }

  return {
    countries,
    states,
    loading,
    isLoaded,
    countryOptions,
    fetchCountries,
    getStateOptions,
    fetchStatesByCountry,
  };
}, {
  persist: {
    key: 'location-cache',
    pick: ['countries', 'statesByCountry', 'isLoaded'],
  },
});
