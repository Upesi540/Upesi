<!-- pages/products/CheckoutPage.vue -->
<template>
  <q-page class="bg-grey-2">
    <q-form @submit.prevent="submitOrder" ref="formRef">
      <!-- HEADER AVEC RETOUR -->
      <div class="bg-primary text-white q-pa-md">
        <div class="row items-center">
          <q-btn flat round dense icon="arrow_back" @click="goBack" :disable="orderStore.isLoading" />
          <div class="text-h6 q-ml-sm">Validation de commande</div>
        </div>
        <div class="text-caption q-mt-xs q-ml-md">
          {{ cartStore.uniqueItemsCount }} article(s) · {{ formatPrice(cartStore.totalPrice) }} CFA
        </div>
      </div>

      <div class="container-narrow q-mx-auto q-pa-md">
        <div class="row q-col-gutter-lg">
          <!-- COLONNE GAUCHE -->
          <div class="col-12 col-md-7">
            <!-- ADRESSE -->
            <q-card flat class="rounded-20 q-mb-md">
              <q-card-section>
                <div class="text-subtitle1 text-weight-bolder q-mb-sm">
                  <q-icon name="location_on" size="sm" class="q-mr-sm text-primary" />
                  Adresse de livraison
                </div>
              </q-card-section>
              <q-separator />
              <q-card-section>
                <div class="row q-col-gutter-md">
                  <div class="col-12">
                    <q-input v-model="shippingAddress.address_line1" label="Adresse *" outlined dense
                      :rules="[val => !!val || 'Adresse requise']" lazy-rules />
                  </div>
                  <div class="col-12 col-md-6">
                    <q-input v-model="shippingAddress.city" label="Ville *" outlined dense
                      :rules="[val => !!val || 'Ville requise']" lazy-rules />
                  </div>
                  <div class="col-12 col-md-6">
                    <q-input v-model="shippingAddress.phone" label="Téléphone *" outlined dense type="tel"
                      :rules="[val => !!val || 'Téléphone requis']" lazy-rules />
                  </div>
                  <div class="col-12 col-md-6">
                    <q-input v-model="shippingAddress.region" label="Région" outlined dense />
                  </div>
                  <div class="col-12 col-md-6">
                    <q-input v-model="shippingAddress.postal_code" label="Code postal" outlined dense />
                  </div>
                </div>
              </q-card-section>
            </q-card>

            <!-- NOTES -->
            <q-card flat class="rounded-20">
              <q-card-section>
                <div class="text-subtitle1 text-weight-bolder q-mb-sm">
                  <q-icon name="notes" size="sm" class="q-mr-sm text-primary" />
                  Notes (optionnel)
                </div>
              </q-card-section>
              <q-separator />
              <q-card-section>
                <q-input v-model="notes" type="textarea" rows="3" outlined
                  placeholder="Instructions pour le livreur, horaire de livraison, etc." />
              </q-card-section>
            </q-card>
          </div>

          <!-- COLONNE DROITE - RÉSUMÉ -->
          <div class="col-12 col-md-5">
            <q-card flat class="rounded-20 sticky-summary">
              <q-card-section>
                <div class="text-subtitle1 text-weight-bolder">Résumé</div>
              </q-card-section>
              <q-separator />

              <!-- PRODUITS (limité à 3 avec voir plus) -->
              <q-card-section v-for="(item) in displayItems" :key="item.productId" class="q-py-sm">
                <div class="row items-center">
                  <q-img :src="item.image || '/icons/favicon-128x128.png'" class="rounded-10"
                    style="width: 45px; height: 45px" fit="cover" />
                  <div class="q-ml-sm flex-grow-1">
                    <div class="text-caption text-weight-bold">{{ truncate(item.title, 30) }}</div>
                    <div class="text-caption text-grey-7">
                      {{ item.quantity }} × {{ formatPrice(item.price) }} CFA
                    </div>
                  </div>
                  <div class="text-weight-bold text-primary">
                    {{ formatPrice(item.price * item.quantity) }} CFA
                  </div>
                </div>
              </q-card-section>

              <q-card-section v-if="cartStore.items.length > 3" class="q-py-none">
                <q-btn flat dense :label="showAll ? 'Voir moins' : `+ ${cartStore.items.length - 3} autre(s)`"
                  @click="showAll = !showAll" size="sm" class="text-primary" />
              </q-card-section>

              <q-separator />

              <!-- TOTAUX -->
              <q-card-section>
                <div class="row justify-between q-mb-sm">
                  <span class="text-grey-8">Sous-total</span>
                  <span>{{ formatPrice(cartStore.totalPrice) }} CFA</span>
                </div>
                <div class="row justify-between q-mb-sm text-grey-8">
                  <span>Frais de livraison</span>
                  <span class="text-green">Calculé par le vendeur</span>
                </div>
                <q-separator class="q-my-md" />
                <div class="row justify-between items-center">
                  <span class="text-h6 text-weight-bolder">Total</span>
                  <span class="text-h6 text-weight-bolder text-primary">
                    {{ formatPrice(cartStore.totalPrice) }} CFA
                  </span>
                </div>
                <div class="text-caption text-grey-6 text-center q-mt-sm">
                  <q-icon name="account_balance_wallet" size="xs" />
                  Paiement par wallet UPESI
                </div>
              </q-card-section>

              <q-card-section>
                <q-btn type="submit" color="primary" size="lg" class="full-width q-py-md text-weight-bolder rounded-15"
                  :loading="orderStore.isLoading" :disable="cartStore.items.length === 0 || !isFormValid" no-caps
                  unelevated>
                  <q-icon name="shopping_cart_checkout" left />
                  {{ orderStore.isLoading ? 'Traitement...' : 'Confirmer et payer' }}
                </q-btn>
              </q-card-section>
            </q-card>
          </div>
        </div>
      </div>
    </q-form>

    <!-- DIALOG PANIER VIDE -->
    <q-dialog v-model="showEmptyCartDialog" persistent>
      <q-card class="rounded-20" style="min-width: 300px">
        <q-card-section class="text-center">
          <q-icon name="shopping_basket" size="60px" color="grey-5" />
          <div class="text-h6 q-mt-md">Panier vide</div>
          <div class="text-caption text-grey-7">Ajoutez des produits avant de passer commande</div>
        </q-card-section>
        <q-card-actions align="center" class="q-mb-md">
          <q-btn color="primary" label="Découvrir le marché" @click="goToProducts" flat />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useCartStore } from 'src/stores/cart';
import { type CreateOrderPayload, useOrderStore } from 'src/stores/product-checkout';
import { QForm } from 'quasar';

const router = useRouter();
const cartStore = useCartStore();
const orderStore = useOrderStore();

const formRef = ref<QForm | null>(null);
const showAll = ref(false);
const showEmptyCartDialog = ref(false);

// Adresse de livraison
const shippingAddress = ref({
  address_line1: '',
  address_line2: '',
  city: '',
  region: '',
  postal_code: '',
  country: 'CI',
  phone: '',
});

const notes = ref('');

// Computed
const displayItems = computed(() => {
  if (showAll.value) return cartStore.items;
  return cartStore.items.slice(0, 3);
});

const isFormValid = computed(() => {
  return shippingAddress.value.address_line1 &&
    shippingAddress.value.city &&
    shippingAddress.value.phone;
});

// Méthodes
const formatPrice = (val: number) => {
  return new Intl.NumberFormat('fr-FR').format(Math.round(val));
};

const truncate = (str: string, max: number) => {
  return str.length > max ? str.substring(0, max) + '...' : str;
};

const goBack = () => {
  if (orderStore.isLoading) return;
  void router.push({ name: 'cart' });
};

const goToProducts = async () => {
  showEmptyCartDialog.value = false;
  await router.push('/products');
};

async function submitOrder() {
  // Vérifier validation formulaire
  const isValid = await formRef.value?.validate();
  if (!isValid) return;

  // Vérifier panier non vide
  if (cartStore.items.length === 0) {
    showEmptyCartDialog.value = true;
    return;
  }

  // Construire payload
  const payload: CreateOrderPayload = {
    items: cartStore.items.map(item => ({
      product_id: item.productId,
      quantity: item.quantity
    })),
    shipping_address: {
      address_line1: shippingAddress.value.address_line1,
      city: shippingAddress.value.city,
      country: 'CI',
      phone: shippingAddress.value.phone,
    },
    notes: notes.value || null
  };

  const order = await orderStore.createOrder(payload);

  if (order) {
    // Rediriger vers la page de confirmation
    // await router.push({ name: 'order-confirmation', params: { id: order.id } });
    await router.push('/user/profile');
  }
}

// Vérifier panier au montage
onMounted(() => {
  if (cartStore.items.length === 0) {
    showEmptyCartDialog.value = true;
  }
});
</script>

<style scoped lang="scss">
.container-narrow {
  max-width: 1200px;
}

.rounded-20 {
  border-radius: 20px;
}

.rounded-15 {
  border-radius: 15px;
}

.rounded-10 {
  border-radius: 10px;
}

.sticky-summary {
  @media (min-width: 1024px) {
    position: sticky;
    top: 20px;
  }
}

.flex-grow-1 {
  flex: 1;
}
</style>