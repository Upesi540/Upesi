import { ref } from 'vue'
import { defineStore } from 'pinia'
import { api } from 'boot/axios'
import type { ApiResponse, PaginationMeta } from 'src/types/api'
import type { Project } from 'src/types/project'

export const useProjectStore = defineStore('project', () => {
  // State
  const projects = ref<Project[]>([])
  const pagination = ref<PaginationMeta | null>(null)
  const currentProject = ref<Project | null>(null)
  const loading = ref(false)

  // Actions
  async function fetchProjects(page = 1, perPage = 12, filters = {}) {
    loading.value = true
    try {
      const params = { page, per_page: perPage, ...filters }
      const { data } = await api.get<ApiResponse<Project[]>>('/projects', { params })
      if (data.status) {
        projects.value = data.data
        pagination.value = data.meta
      }
      return data
    } catch (error) {
      console.error('Erreur chargement projets', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  async function fetchProjectBySlug(slug: string) {
    loading.value = true
    try {
      const { data } = await api.get<ApiResponse<Project>>(`/projects/${slug}`)
      if (data.status) {
        currentProject.value = data.data
      }
      return data
    } catch (error) {
      console.error('Erreur chargement projet', error)
      throw error
    } finally {
      loading.value = false
    }
  }

  function clearCurrentProject() {
    currentProject.value = null
  }

  // Expose state and actions
  return {
    // state
    projects,
    pagination,
    currentProject,
    loading,
    // actions
    fetchProjects,
    fetchProjectBySlug,
    clearCurrentProject,
  }
})
