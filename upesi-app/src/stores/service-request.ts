// stores/service-request.ts
import { defineStore } from 'pinia';
import { ref } from 'vue';
import { api } from 'src/boot/axios';
import { useQuasar } from 'quasar';
import type { AxiosError } from 'axios';

export interface CreateServiceRequestPayload {
  service_offer_id: string;
  description?: string;
  details?: Record<string, unknown>|null;
  scheduled_at?: string | null;
}

export interface ServiceRequestResponse {
  id: string;
  request_number: string;
  status: string;
  total: number;
}

export const useServiceRequestStore = defineStore('serviceRequest', () => {
  const $q = useQuasar();
  const isLoading = ref(false);
  const lastError = ref<string | null>(null);
  const currentRequest = ref<ServiceRequestResponse | null>(null);

  async function createRequest(payload: CreateServiceRequestPayload): Promise<ServiceRequestResponse | null> {
    isLoading.value = true;
    lastError.value = null;

    try {
      const { data } = await api.post<{ data: ServiceRequestResponse }>('/service-requests', payload);
      const requestData = data.data;
      currentRequest.value = requestData;

      $q.notify({
        type: 'positive',
        message: 'Demande de service envoyée avec succès !',
        position: 'top',
        timeout: 3000,
      });

      return requestData;
    } catch (error) {
      const axiosError = error as AxiosError<{ message: string }>;
      const message = axiosError.response?.data?.message || 'Erreur lors de la création de la demande';
      lastError.value = message;
      $q.notify({
        type: 'negative',
        message,
        position: 'top',
      });
      return null;
    } finally {
      isLoading.value = false;
    }
  }

  function reset() {
    isLoading.value = false;
    currentRequest.value = null;
    lastError.value = null;
  }

  return {
    isLoading,
    lastError,
    currentRequest,
    createRequest,
    reset,
  };
});