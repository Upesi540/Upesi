// src/stores/product.ts
import { defineStore } from 'pinia';
import { api } from 'src/boot/axios';
import type { Product} from 'src/types';
import { ref} from 'vue';

export const useProductStore = defineStore('product', () => {
  // --- STATE ---
  const products = ref<Product[]>([]);
  const currentProduct = ref<Product | null>(null);
  const featuredProducts = ref<Product[]>([]);
  const similarProducts = ref<Product[]>([]);
  const loading = ref(true);          // global/list loading
  const loadingProduct = ref(false);   // detail loading
  const loadingSimilar = ref(false);   // similar loading
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
  });

  // --- ACTIONS ---

  /**
   * Récupère la liste des produits avec filtres (pagination)
   */
  async function fetchProducts(params?: Record<string, unknown>) {
    loading.value = true;
    try {
      const { data } = await api.get('/products', { params });
      products.value = data.data;
      pagination.value = data.meta || data.pagination || {};
    } catch (e) {
      console.error('Erreur fetchProducts:', e);
    } finally {
      loading.value = false;
    }
  }

  /**
   * Récupère un produit par son ID (avec cache)
   */
 async function fetchProductById(id: string) {
    // 1. On cherche l'index au lieu de l'objet complet pour éviter la récursion de type
    const index = products.value.findIndex((p) => String(p.id) === String(id));

    const existing = index !== -1 ? products.value[index] : null;

    if (existing) {
      // On assigne sans crainte, TS ne re-vérifie pas toute la structure ici
      currentProduct.value = existing;
    }

    // On ne montre le spinner que si on n'a rien en cache
    loadingProduct.value = !existing;

    try {
      const { data } = await api.get(`/products/${id}`);
      // Mise à jour avec les données fraîches du serveur
      currentProduct.value = data.data;
    } catch (e) {
      console.error('Erreur fetchProductById:', e);
    } finally {
      loadingProduct.value = false;
    }
  }

  /**
   * Récupère les produits similaires
   */
  async function fetchSimilar(productId: string, limit = 6) {
    loadingSimilar.value = true;
    try {
      const { data } = await api.get(`/products/${productId}/similar`, { params: { limit } });
      similarProducts.value = data.data;
    } catch (e) {
      console.error('Erreur fetchSimilar:', e);
    } finally {
      loadingSimilar.value = false;
    }
  }

  /**
   * Récupère les produits en vedette
   */
  async function fetchFeatured(params?: Record<string, unknown>) {
    loading.value = true;
    try {
      const { data } = await api.get('/products/featured', { params });
      featuredProducts.value = data.data;
    } catch (e) {
      console.error('Erreur fetchFeatured:', e);
    } finally {
      loading.value = false;
    }
  }

  /**
   * Récupère les produits par culture
   */
  async function fetchByCrop(cropId: string, params?: Record<string, unknown>) {
    loading.value = true;
    try {
      const { data } = await api.get(`/products/by-crop/${cropId}`, { params });
      products.value = data.data;
      pagination.value = data.meta || data.pagination || {};
    } catch (e) {
      console.error('Erreur fetchByCrop:', e);
    } finally {
      loading.value = false;
    }
  }

  /**
   * Récupère les produits par catégorie
   */
  async function fetchByCategory(categoryId: string, params?: Record<string, unknown>) {
    loading.value = true;
    try {
      const { data } = await api.get(`/products/by-category/${categoryId}`, { params });
      products.value = data.data;
      pagination.value = data.meta || data.pagination || {};
    } catch (e) {
      console.error('Erreur fetchByCategory:', e);
    } finally {
      loading.value = false;
    }
  }

  /**
   * Récupère les produits par marché
   */
  async function fetchByMarket(marketId: string, params?: Record<string, unknown>) {
    loading.value = true;
    try {
      const { data } = await api.get(`/products/by-market/${marketId}`, { params });
      products.value = data.data;
      pagination.value = data.meta || data.pagination || {};
    } catch (e) {
      console.error('Erreur fetchByMarket:', e);
    } finally {
      loading.value = false;
    }
  }

  /**
   * Récupère les produits par pays
   */
  async function fetchByCountry(countryId: string, params?: Record<string, unknown>) {
    loading.value = true;
    try {
      const { data } = await api.get(`/products/by-country/${countryId}`, { params });
      products.value = data.data;
      pagination.value = data.meta || data.pagination || {};
    } catch (e) {
      console.error('Erreur fetchByCountry:', e);
    } finally {
      loading.value = false;
    }
  }

  /**
   * Récupère les produits par ville
   */
  async function fetchByCity(cityId: string, params?: Record<string, unknown>) {
    loading.value = true;
    try {
      const { data } = await api.get(`/products/by-city/${cityId}`, { params });
      products.value = data.data;
      pagination.value = data.meta || data.pagination || {};
    } catch (e) {
      console.error('Erreur fetchByCity:', e);
    } finally {
      loading.value = false;
    }
  }

  function resetCurrentProduct() {
    currentProduct.value = null;
    similarProducts.value = [];
  }

  return {
    // state
    products,
    currentProduct,
    featuredProducts,
    similarProducts,
    loading,
    loadingProduct,
    loadingSimilar,
    pagination,
    // actions
    fetchProducts,
    fetchProductById,
    fetchSimilar,
    fetchFeatured,
    fetchByCrop,
    fetchByCategory,
    fetchByMarket,
    fetchByCountry,
    fetchByCity,
    resetCurrentProduct,
  };
});
