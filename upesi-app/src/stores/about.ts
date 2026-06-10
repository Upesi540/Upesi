// stores/about.ts
import { defineStore } from 'pinia';
import { ref } from 'vue';
import { api } from 'src/boot/axios';
import type{ AxiosError } from 'axios';

export interface TeamMember {
  id: string;
  first_name: string;
  last_name: string;
  full_name: string;
  role: string;
  bio: string | null;
  photo_path: string | null;
  email: string | null;
  phone: string | null;
  social_links: {
    linkedin?: string;
    twitter?: string;
    facebook?: string;
  };
  sort_order: number;
}

export const useAboutStore = defineStore('about', () => {
  const teamMembers = ref<TeamMember[]>([]);
  const isLoading = ref(false);
  const error = ref<string | null>(null);

  async function fetchTeamMembers(): Promise<void> {
    isLoading.value = true;
    error.value = null;
    try {
      const { data } = await api.get('/about/team');
      teamMembers.value = data.data;
    } catch (err) {
      const axiosError = err as AxiosError;
      const message = (axiosError.response?.data as { message?: string })?.message;
      error.value = message || 'Erreur lors du chargement de l\'équipe';
      console.error(error.value);
    } finally {
      isLoading.value = false;
    }
  }

  return {
    teamMembers,
    isLoading,
    error,
    fetchTeamMembers,
  };
});