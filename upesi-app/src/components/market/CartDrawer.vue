<template>
  <q-drawer v-model="cartStore.isDrawerOpen" side="right" overlay bordered :width="$q.screen.lt.sm ? $q.screen.width : 400" class="bg-grey-1 shadow-24">
    <div class="column full-height overflow-hidden">
      <div class="q-pa-md row items-center justify-between bg-white border-bottom-light">
        <div class="column">
          <span class="text-h6 text-weight-bolder">Mon Panier</span>
          <q-badge color="primary" class="self-start q-px-sm" rounded>
            {{ cartStore.items.length }} {{ cartStore.items.length > 1 ? 'articles' : 'article' }}
          </q-badge>
        </div>
        <q-btn flat round icon="close" color="grey-7" @click="cartStore.isDrawerOpen = false" />
      </div>

      <q-scroll-area class="col q-pa-md">
        <div v-if="cartStore.items.length === 0" class="column items-center justify-center q-mt-xl opacity-60">
          <q-icon name="o_shopping_basket" size="100px" color="grey-4" />
          <div class="text-h6 text-grey-6 q-mt-md">Votre panier est vide</div>
          <q-btn flat color="primary" label="Commencer mes achats" class="q-mt-md"
            @click="cartStore.isDrawerOpen = false" />
        </div>

        <q-list v-else separator class="cart-item-list">
          <q-item v-for="item in cartStore.items" :key="item.productId" class="q-px-none q-py-lg">
            <q-item-section avatar>
              <q-img :src="item.image || 'https://placehold.co/100x100?text=Produit'" ratio="1"
                class="rounded-borders shadow-1 border-light" :width="$q.screen.lt.sm ? '60px' : '80px'" />
            </q-item-section>

            <q-item-section>
              <q-item-label class="text-weight-bold text-grey-9 text-subtitle1 line-clamp-1">
                {{ item.title }}
              </q-item-label>
              <q-item-label class="text-primary text-weight-bolder text-body2">
                {{ formatPrice(item.price) }}
              </q-item-label>
              <div class="row items-center q-mt-xs">
                <span class="text-caption text-grey-7">Qté: {{ item.quantity }} {{ item.unit }}</span>
              </div>
            </q-item-section>

            <q-item-section side>
              <q-btn flat round size="sm" icon="delete_outline" color="negative" class="hover-bg-red-1"
                @click="cartStore.removeItem(item.productId)" />
            </q-item-section>
          </q-item>
        </q-list>
      </q-scroll-area>

      <div v-if="cartStore.items.length > 0" class="q-pa-lg bg-white shadow-up-10 border-top-light">
        <div class="row justify-between items-center q-mb-lg">
          <span class="text-subtitle1 text-grey-7 text-weight-medium">Total estimé</span>
          <span class="text-h5 text-weight-bolder text-green-10">{{ formatPrice(cartStore.totalPrice) }}</span>
        </div>

        <div class="column q-gutter-y-sm">
          <q-btn unelevated color="primary" class="full-width q-py-md btn-checkout" to="/checkout">
            <div class="row items-center">
              <span class="text-weight-bold">COMMANDER MAINTENANT</span>
              <q-icon name="arrow_forward" size="xs" class="q-ml-sm" />
            </div>
          </q-btn>

          <q-btn flat color="grey-8" label="Voir mon panier complet" class="full-width q-py-sm" to="/cart" />
        </div>
      </div>
    </div>
  </q-drawer>
</template>

<script setup lang="ts">
import { useCartStore } from 'src/stores/cart'

const cartStore = useCartStore()

// Formatage local pour Libreville (XOF)
const formatPrice = (price: string | number) =>
  new Intl.NumberFormat('fr-FR', {
    style: 'currency',
    currency: 'XOF',
    maximumFractionDigits: 0
  }).format(Number(price))
</script>

<style lang="scss" scoped>
.border-bottom-light {
  border-bottom: 1px solid rgba(0, 0, 0, 0.06);
}

.border-top-light {
  border-top: 1px solid rgba(0, 0, 0, 0.06);
}

.border-light {
  border: 1px solid rgba(0, 0, 0, 0.08);
}

.btn-checkout {
  border-radius: 12px;
  font-size: 1rem;
  box-shadow: 0 4px 15px rgba(var(--q-primary), 0.2);
}

.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.hover-bg-red-1 {
  transition: background 0.3s;

  &:hover {
    background: #fff5f5;
  }
}

.shadow-up-10 {
  box-shadow: 0 -10px 30px rgba(0, 0, 0, 0.05);
}
</style>
