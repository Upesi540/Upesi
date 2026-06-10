// stores/search.ts
import { defineStore } from 'pinia';
import { ref } from 'vue';
import { api } from 'src/boot/axios';
import type { Product } from 'src/types';
import type { ServiceOffer } from 'src/types';

export interface SearchResults {
  products: {
    data: Product[];
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
  } | null;
  services: {
    data: ServiceOffer[];
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
  } | null;
}

export const useSearchStore = defineStore('search', () => {
  const isLoading = ref(false);
  const results = ref<SearchResults>({ products: null, services: null });
  const suggestions = ref<Array<{ id: string; title: string; type: string; image: string | null }>>(
    [],
  );
  const lastError = ref<string | null>(null);

  async function search(
    query: string,
    type: 'all' | 'products' | 'services' = 'all',
    perPage = 20,
    page = 1,
  ) {
    isLoading.value = true;
    lastError.value = null;
    try {
      const response = await api.get('/search', {
        params: { q: query, type, per_page: perPage, page },
      });
      console.log('🔍 API Response:', response);
      console.log('🔍 API data:', response.data);

      const payload = response.data.data; // suppose que la réponse est { data: { products, services } }
      console.log('🔍 Payload:', payload);

      if (type === 'products') {
        results.value.products = payload.products;
      } else if (type === 'services') {
        results.value.services = payload.services;
      } else {
        results.value = payload;
      }
      return results.value;
    } catch (error) {
      console.error('Search error:', error);
      // ...
    } finally {
      isLoading.value = false;
    }
  }

  async function fetchSuggestions(query: string, limit = 10): Promise<void> {
    if (!query.trim()) {
      suggestions.value = [];
      return;
    }
    try {
      const { data } = await api.get('/search/autocomplete', { params: { q: query, limit } });
      suggestions.value = data.data;
    } catch (error) {
      console.log(error);

      suggestions.value = [];
    }
  }

  function clearResults(): void {
    results.value = { products: null, services: null };
    suggestions.value = [];
    lastError.value = null;
  }

  return {
    isLoading,
    results,
    suggestions,
    lastError,
    search,
    fetchSuggestions,
    clearResults,
  };
});
