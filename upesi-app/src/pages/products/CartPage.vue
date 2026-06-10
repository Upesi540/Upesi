<template>
  <q-page class="bg-grey-2 q-pa-md">
    <div class="container-narrow q-mx-auto">

      <div class="row items-center q-mb-lg">
        <q-btn flat round icon="arrow_back" color="primary" @click="$router.back()" class="q-mr-sm" />
        <h1 class="text-h5 text-weight-bolder q-ma-none">Mon Panier</h1>
        <q-badge color="primary" class="q-ml-md q-px-sm" rounded v-if="cartStore.items.length > 0">
          {{ cartStore.uniqueItemsCount }} articles
        </q-badge>
      </div>

      <div v-if="cartStore.items.length > 0" class="row q-col-gutter-lg">

        <div class="col-12 col-md-8">
          <q-card flat bordered class="rounded-20">
            <q-list separator>
              <q-item v-for="item in cartStore.items" :key="item.productId" class="q-py-md q-px-sm">

                <q-item-section avatar>
                  <q-img :src="item.image || 'icons/favicon-128x128.png'" class="rounded-10 shadow-1"
                    style="width: 70px; height: 70px" />
                </q-item-section>

                <q-item-section>
                  <q-item-label class="text-weight-bold text-subtitle2">{{ item.title }}</q-item-label>
                  <q-item-label caption class="text-primary text-weight-bold">
                    {{ formatPrice(item.price) }} / {{ item.unit }}
                  </q-item-label>
                </q-item-section>

                <q-item-section side>
                  <div class="column items-center">
                    <div class="row items-center no-wrap bg-grey-2 rounded-pill q-px-xs border-grey-3">
                      <q-btn flat round size="xs" color="primary" icon="remove" @click="changeQty(item, -0.1)" />

                      <q-input
                        :model-value="item.quantity"
                        @update:model-value="(val) => updateInputQty(item, val)"
                        type="number"
                        dense
                        borderless
                        input-class="text-center text-weight-bolder text-primary"
                        style="width: 60px"
                      />

                      <q-btn flat round size="xs" color="primary" icon="add" @click="changeQty(item, 0.1)" />
                    </div>
                    <div class="text-caption text-weight-bold q-mt-xs text-grey-9">
                      {{ formatPrice(item.price * item.quantity) }} CFA
                    </div>
                  </div>
                </q-item-section>

                <q-item-section side>
                  <q-btn flat round dense color="red-4" icon="delete_outline" @click="confirmRemove(item.productId)" />
                </q-item-section>
              </q-item>
            </q-list>
          </q-card>
        </div>

        <div class="col-12 col-md-4">
          <q-card flat bordered class="rounded-20 sticky-summary shadow-upesi">
            <q-card-section>
              <div class="text-subtitle1 text-weight-bolder q-mb-md">Résumé de la commande</div>

              <div class="row justify-between q-mb-sm text-grey-8">
                <span>Total ({{ cartStore.totalItems }} /unités)</span>
                <span class="text-weight-bold">{{ formatPrice(cartStore.totalPrice) }} CFA</span>
              </div>

              <div class="row justify-between q-mb-sm text-grey-8">
                <span>Frais UPESI</span>
                <span class="text-green text-weight-bold">Gratuit</span>
              </div>

              <q-separator class="q-my-md" />

              <div class="row justify-between items-center q-mb-lg">
                <span class="text-h6 text-weight-bolder">Total</span>
                <span class="text-h6 text-weight-bolder text-primary">
                  {{ formatPrice(cartStore.totalPrice) }} <small>CFA</small>
                </span>
              </div>

              <q-btn color="primary" to="/checkout" label="Passer à la caisse" class="full-width q-py-md text-weight-bolder rounded-15"
                unelevated no-caps size="lg"  />

              <div class="row justify-center q-mt-md">
                <q-btn flat color="grey-7" label="Vider le panier" icon="delete_sweep" no-caps @click="confirmClear" />
              </div>
            </q-card-section>
          </q-card>
        </div>

      </div>

      <div v-else class="full-width flex flex-center q-py-xl">
        <div class="column items-center justify-center q-pa-xl bg-white rounded-20 shadow-upesi text-center"
          style="width: 100%; max-width: 500px; min-height: 350px">
          <q-icon name="shopping_basket" size="100px" color="grey-3" />
          <div class="text-h5 text-weight-bolder text-grey-8 q-mt-md">Votre panier est vide</div>
          <p class="text-grey-6 q-mt-sm">Ajoutez des produits frais du marché pour commencer vos achats.</p>
          <q-btn color="primary" label="Découvrir le marché" to="/products" unelevated rounded no-caps
            class="q-px-xl q-py-md q-mt-lg text-weight-bold" />
        </div>
      </div>

    </div>
  </q-page>
</template>

<script setup lang="ts">
import { useCartStore, type CartItem } from 'src/stores/cart';
import { useQuasar } from 'quasar';

const cartStore = useCartStore();
const $q = useQuasar();

const formatPrice = (val: number) => {
  return new Intl.NumberFormat('fr-FR').format(Math.round(val));
};

const changeQty = (item: CartItem, delta: number) => {
  const newQty = Math.round((item.quantity + delta) * 10) / 10;
  if (newQty > 0) {
    cartStore.updateQuantity(item.productId, newQty);
  } else {
    confirmRemove(item.productId);
  }
};

const updateInputQty = (item: CartItem, val: string | number | null) => {
  if (val === null || val === '') return;
  const num = typeof val === 'string' ? parseFloat(val.replace(',', '.')) : val;
  if (!isNaN(num) && num > 0) {
    cartStore.updateQuantity(item.productId, num);
  }
};

const confirmRemove = (id: string) => {
  $q.dialog({
    title: 'Supprimer ?',
    message: 'Retirer cet article du panier ?',
    cancel: { flat: true, color: 'grey-7', label: 'Annuler' },
    ok: { unelevated: true, color: 'red-5', label: 'Supprimer' },
    persistent: true
  }).onOk(() => {
    cartStore.removeItem(id);
  });
};

const confirmClear = () => {
  $q.dialog({
    title: 'Vider le panier',
    message: 'Êtes-vous sûr de vouloir tout supprimer ?',
    cancel: true,
    ok: { unelevated: true, color: 'primary', label: 'Confirmer' },
    persistent: true
  }).onOk(() => {
    cartStore.clearCart();
  });
};
</script>

<style scoped lang="scss">
.container-narrow {
  max-width: 1000px;
}

.rounded-20 { border-radius: 20px; }
.rounded-10 { border-radius: 10px; }
.rounded-15 { border-radius: 15px; }

.border-grey-3 { border: 1px solid #eee; }

.shadow-upesi {
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05) !important;
}

.sticky-summary {
  @media (min-width: 1024px) {
    position: sticky;
    top: 20px;
  }
}

/* Chrome, Safari, Edge, Opera : masquer les flèches du type="number" */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
input[type=number] {
  -moz-appearance: textfield;
}
</style>
