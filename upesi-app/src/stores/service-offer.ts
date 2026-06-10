// src/stores/service-offer.ts
import { defineStore } from 'pinia';
import { api } from 'src/boot/axios';
import { ref } from 'vue';
import type { PaginationMeta } from 'src/types/api';
import type { ServiceOffer } from 'src/types';

export const useServiceOfferStore = defineStore('serviceOffer', () => {
  // --- STATE ---
  const offers = ref<ServiceOffer[]>([]);
  const currentOffer = ref<ServiceOffer | null>(null);
  const featuredOffers = ref<ServiceOffer[]>([]);
  const zones = ref<string[]>([]); // Pour stocker les zones uniques

  const loading = ref(true);
  const loadingOffer = ref(false);
  const loadingZones = ref(false);

  const pagination = ref<PaginationMeta>({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
  });

  // --- ACTIONS ---

  /**
   * Récupère toutes les offres avec filtres (search, zone, price, etc.)
   */
  async function fetchOffers(params?: Record<string, unknown>) {
    loading.value = true;
    try {
      const { data } = await api.get('/service-offers', { params });
      offers.value = data.data;
      pagination.value = data.meta || {};
    } catch (e) {
      console.error('Erreur fetchOffers:', e);
    } finally {
      loading.value = false;
    }
  }

  /**
   * Récupère le détail d'une offre (avec gestion du cache local)
   */
  async function fetchOfferById(id: string) {
    // Cache local : on cherche si on l'a déjà en liste
    const existing = offers.value.find((o) => String(o.id) === String(id));
    if (existing) {
      currentOffer.value = existing;
    }

    loadingOffer.value = !existing;

    try {
      const { data } = await api.get(`/service-offers/${id}`);
      currentOffer.value = data.data;
    } catch (e) {
      console.error('Erreur fetchOfferById:', e);
    } finally {
      loadingOffer.value = false;
    }
  }

  /**
   * Récupère les offres en vedette
   */
  async function fetchFeatured(limit = 10) {
    loading.value = true;
    try {
      const { data } = await api.get('/service-offers/featured', { params: { limit } });
      featuredOffers.value = data.data;
    } catch (e) {
      console.error('Erreur fetchFeatured:', e);
    } finally {
      loading.value = false;
    }
  }

  /**
   * Récupère les offres par Catégorie (slug)
   */
  async function fetchByCategory(categorySlug: string, params?: Record<string, unknown>) {
    loading.value = true;
    try {
      // Correction : enlever 'by-'
      const { data } = await api.get(`/service-offers/category/${categorySlug}`, { params });
      offers.value = data.data;
      pagination.value = data.meta || {};
    } catch (e) {
      console.error('Erreur fetchByCategory:', e);
    } finally {
      loading.value = false;
    }
  }

  /**
   * Récupère les offres par Service spécifique (slug ou ID)
   */
  async function fetchByService(serviceSlug: string, params?: Record<string, unknown>) {
    loading.value = true;
    try {
      // Correction : enlever 'by-service' pour correspondre à la route Laravel
      const { data } = await api.get(`/service-offers/service/${serviceSlug}`, { params });
      offers.value = data.data;
      pagination.value = data.meta || {};
    } catch (e) {
      console.error('Erreur fetchByService:', e);
    } finally {
      loading.value = false;
    }
  }

  /**
   * Récupère la liste des zones géographiques disponibles
   */
  async function fetchZones() {
    loadingZones.value = true;
    try {
      const { data } = await api.get('/service-offers/zones');
      zones.value = data.data;
    } catch (e) {
      console.error('Erreur fetchZones:', e);
    } finally {
      loadingZones.value = false;
    }
  }

  /**
   * Nettoyer l'offre courante (utile au Leave de la page detail)
   */
  function resetCurrentOffer() {
    currentOffer.value = null;
  }

  return {
    // state
    offers,
    currentOffer,
    featuredOffers,
    zones,
    loading,
    loadingOffer,
    loadingZones,
    pagination,
    // actions
    fetchOffers,
    fetchOfferById,
    fetchFeatured,
    fetchByCategory,
    fetchByService,
    fetchZones,
    resetCurrentOffer,
  };
});
