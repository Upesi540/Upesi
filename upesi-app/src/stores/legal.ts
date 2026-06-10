// stores/legal.ts
import { defineStore } from 'pinia';
import { ref } from 'vue';
import { api } from 'src/boot/axios';

export interface LegalDocument {
  id: string;
  title: string;
  slug: string;
  version: string | null;
  content: null; // contenu JSON du tiptap
  updated_at: string;
}

export const useLegalStore = defineStore(
  'legal',
  () => {
    const document = ref<LegalDocument | null>(null);
    const isLoading = ref(false);
    const error = ref<string | null>(null);

    async function fetchDocument(slug: string): Promise<void> {
      isLoading.value = true;
      error.value = null;
      try {
        const { data } = await api.get(`/legal/${slug}`);
        document.value = data.data;
      } catch (err) {
        const axiosError = err as { response?: { data?: { message?: string } } };
        error.value = axiosError.response?.data?.message || 'Document introuvable';
        document.value = null;
      } finally {
        isLoading.value = false;
      }
    }

    return { document, isLoading, error, fetchDocument };
  },
  {
    // ✅ CONFIGURATION DE LA PERSISTANCE
    persist: {
      key: 'upesi-market-cache',
      // On ne garde que les données utiles, on ignore "loading"
    },
  },
);
