// stores/home.ts
import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { api } from 'boot/axios';
import type { ApiResponse } from 'src/types/api';
import type { Category, Crop, Market, Partner, Product, ServiceOffer, Slide } from 'src/types';
import type { HomeData, HomeStatsExtended } from 'src/types/home';

export const useHomeStore = defineStore('home', () => {
  // État
  const slides = ref<Slide[]>([]);
  const markets = ref<Market[]>([]);
  const categories = ref<Category[]>([]);
  const popularCrops = ref<Crop[]>([]);
  const featured_services = ref<ServiceOffer[]>([]);
  const featuredProducts = ref<Product[]>([]);
  const partners = ref<Partner[]>([]);
  const stats = ref<HomeStatsExtended>({
    total_products: 0,
    active_markets: 0,
    product_categories: 0,
    crop_varieties: 0,
    active_farmers: 0,
    active_buyers: 0,
    avg_products_per_farmer: 0,
  });
  const loading = ref(false);
  const error = ref<string | null>(null);

  // Getters
  const hasSlides = computed(() => slides.value.length > 0);
  const hasServiceOffers= computed(()=>featured_services.value.length>0);
  const hasMarkets = computed(() => markets.value.length > 0);
  const hasPartners = computed(() => partners.value.length > 0);
  const isLoading = computed(() => loading.value);
  const hasError = computed(() => error.value !== null);

  // stores/home.ts

  async function fetchHomeData(forceRefresh = false) {
    // 1. Si on a déjà des slides et qu'on ne force pas le rafraîchissement
    // On arrête l'exécution ici. Pas de chargement, pas de saut d'UI.
    if (slides.value.length > 0 && !forceRefresh) {
      return;
    }

    loading.value = true;
    error.value = null;

    try {
      const { data } = await api.get<ApiResponse<HomeData>>('/home');

      if (data.status && data.data) {
        slides.value = data.data.slides;
        markets.value = data.data.markets;
        featured_services.value=data.data.featured_services;
        categories.value = data.data.categories;
        popularCrops.value = data.data.popular_crops;
        featuredProducts.value = data.data.featured_products;
        partners.value = data.data.partners || [];
        stats.value = data.data.stats;
      }
    } catch (err) {
      error.value = 'Erreur lors du chargement des données';
      console.error(err);
    } finally {
      loading.value = false;
    }
  }
  return {
    // État
    slides,
    markets,
    categories,
    popularCrops,
    featuredProducts,
    featured_services,
    partners,
    stats,
    loading,
    error,

    // Getters
    hasSlides,
    hasServiceOffers,
    hasMarkets,
    hasPartners,
    isLoading,
    hasError,

    // Actions
    fetchHomeData,
  };
});
