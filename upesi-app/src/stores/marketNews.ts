import { ref } from 'vue'
import { defineStore } from 'pinia'
import { api } from 'boot/axios'
import type { ApiResponse, PaginationMeta } from 'src/types/api'
import type { MarketNews, NewsCategory } from 'src/types/marketNews'

export const useMarketNewsStore = defineStore('marketNews', () => {
  // State
  const newsList = ref<MarketNews[]>([])
  const pagination = ref<PaginationMeta | null>(null)
  const currentNews = ref<MarketNews | null>(null)
  const loading = ref(true)
  const categories = ref<NewsCategory[]>([])

  // Actions
  async function fetchNews(page = 1, perPage = 12, filters: Record<string, unknown> = {}) {
    loading.value = true
    try {
      const params = { page, per_page: perPage, ...filters }
      const { data } = await api.get<ApiResponse<MarketNews[]>>('/news', { params })
      if (data.status) {
        newsList.value = data.data
        pagination.value = data.meta
      }
      return data
    } catch (error) {
      console.error('Erreur chargement actualités', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  async function fetchNewsBySlug(slug: string) {
    loading.value = true
    try {
      const { data } = await api.get<ApiResponse<MarketNews>>(`/news/${slug}`)
      if (data.status) {
        currentNews.value = data.data
      }
      return data
    } catch (error) {
      console.error('Erreur chargement actualité', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  async function fetchCategories() {
    // Si vous avez une route dédiée, sinon on peut les charger depuis un autre endpoint
    // Ici on suppose une route /v1/news-categories
    try {
      const { data } = await api.get<ApiResponse<NewsCategory[]>>('/news-categories')
      if (data.status) {
        categories.value = data.data
      }
    } catch (error) {
      console.error('Erreur chargement catégories', error)
    }
  }

  function clearCurrentNews() {
    currentNews.value = null
  }

  return {
    newsList,
    pagination,
    currentNews,
    loading,
    categories,
    fetchNews,
    fetchNewsBySlug,
    fetchCategories,
    clearCurrentNews,
  }
})
