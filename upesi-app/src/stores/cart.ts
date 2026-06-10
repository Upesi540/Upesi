import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export interface CartItem {
  productId: string;
  quantity: number;
  price: number;
  title: string;
  image: string | null;
  unit: string;
}

export const useCartStore = defineStore(
  'cart',
  () => {
    // --- STATE ---
    const items = ref<CartItem[]>([]);
    const isDrawerOpen = ref<boolean>(false);

    const isShaking = ref<boolean>(false);
    // --- GETTERS ---
    const uniqueItemsCount = computed(() => items.value.length);

    const totalItems = computed(() => items.value.reduce((sum, item) => sum + item.quantity, 0));

    const totalPrice = computed(() =>
      items.value.reduce((sum, item) => sum + item.price * item.quantity, 0),
    );

    // --- ACTIONS ---
    function addItem(newItem: CartItem) {
      const existing = items.value.find((i) => i.productId === newItem.productId);
      if (existing) {
        existing.quantity += newItem.quantity;
      } else {
        items.value.push(newItem);
      }
      isShaking.value = true;

      // Plus besoin de save() ici !
      isDrawerOpen.value = true;
      setTimeout(() => {
        isShaking.value = false;
      }, 500);
    }

    function removeItem(productId: string) {
      items.value = items.value.filter((i) => i.productId !== productId);
    }

    function updateQuantity(productId: string, quantity: number) {
      const item = items.value.find((i) => i.productId === productId);
      if (item) {
        item.quantity = quantity;
      }
    }

    function clearCart() {
      items.value = [];
    }

    return {
      items,
      totalItems,
      totalPrice,
      uniqueItemsCount,
      isDrawerOpen,
      isShaking,
      addItem,
      removeItem,
      updateQuantity,
      clearCart,
    };
  },
  {
    // ✅ CONFIGURATION DE LA PERSISTANCE
    persist: {
      key: 'upesi-cart', // Nom de la clé dans le localStorage
      pick: ['items'], // Optionnel : On ne persiste QUE les articles, pas l'état du Drawer
    },
  },
);
