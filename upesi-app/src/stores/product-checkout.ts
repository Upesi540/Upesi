// stores/order.ts
import { defineStore } from 'pinia';
import { ref } from 'vue';
import { api } from 'src/boot/axios';
import { useCartStore } from './cart';
import { useQuasar } from 'quasar';
import type { AxiosError } from 'axios';

// ==================== TYPES ====================
export interface OrderItem {
  product_id: string;
  quantity: number;
}

export interface ShippingAddress {
  address_line1: string;
  address_line2?: string;
  city: string;
  region?: string;
  postal_code?: string;
  country: string;
  phone: string;
}

export interface CreateOrderPayload {
  items: OrderItem[];
  shipping_address: ShippingAddress;
  notes?: string | null;
}

export interface OrderResponse {
  id: string;
  order_number: string;
  status: string;
  payment_status: string;
  total: number;
  subtotal: number;
  shipping_cost: number;
  service_fee: number;
  ordered_at: string;
  items?: OrderItemResponse[];
}

export interface OrderItemResponse {
  id: string;
  product_name: string;
  quantity: number;
  unit_price: number;
  subtotal: number;
  seller_status: string;
  merchant_profile_id: string;
}

export interface ApiErrorResponse {
  message: string;
  errors?: Record<string, string[]>;
}

// ==================== STORE ====================
export const useOrderStore = defineStore('order', () => {
  const $q = useQuasar();
  const cartStore = useCartStore();
  
  const isLoading = ref<boolean>(false);
  const currentOrder = ref<OrderResponse | null>(null);
  const lastError = ref<string | null>(null);

  /**
   * Créer une commande
   */
  async function createOrder(payload: CreateOrderPayload): Promise<OrderResponse | null> {
    isLoading.value = true;
    lastError.value = null;
    
    try {
      const response = await api.post<{ success: boolean; data: OrderResponse }>('/orders', payload);
      
      if (response.data.success || response.status === 201) {
        const orderData = response.data.data;
        
        // Vider le panier après commande réussie
        cartStore.clearCart();
        
        currentOrder.value = orderData;
        
        $q.notify({
          type: 'positive',
          message: 'Commande créée avec succès !',
          position: 'top',
          timeout: 3000
        });
        
        return orderData;
      }
      
      throw new Error('Erreur lors de la création de la commande');
      
    } catch (error) {
      const axiosError = error as AxiosError<ApiErrorResponse>;
      const errorMessage = axiosError.response?.data?.message || axiosError.message || 'Erreur lors de la création de la commande';
      
      lastError.value = errorMessage;
      
      $q.notify({
        type: 'negative',
        message: errorMessage,
        position: 'top',
        timeout: 4000
      });
      
      return null;
    } finally {
      isLoading.value = false;
    }
  }


  /**
   * Récupérer une commande spécifique
   */
  async function fetchOrder(orderId: string): Promise<OrderResponse | null> {
    isLoading.value = true;
    
    try {
      const response = await api.get<{ success: boolean; order: OrderResponse } | OrderResponse>(`/orders/${orderId}`);
      
      if (response.data && typeof response.data === 'object' && 'order' in response.data) {
        return response.data.order;
      }
      
      return response.data;
      
    } catch (error) {
      const axiosError = error as AxiosError<ApiErrorResponse>;
      const errorMessage = axiosError.response?.data?.message || 'Commande introuvable';
      
      $q.notify({
        type: 'negative',
        message: errorMessage,
        position: 'top'
      });
      
      return null;
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Réinitialiser l'état
   */
  function reset() {
    isLoading.value = false;
    currentOrder.value = null;
    lastError.value = null;
  }

  return {
    isLoading,
    currentOrder,
    lastError,
    createOrder,
    fetchOrder,
    reset,
  };
});