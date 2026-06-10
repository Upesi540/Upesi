<!-- pages/products/OrderConfirmationPage.vue -->
<template>
  <q-page class="bg-grey-2 flex flex-center">
    <div class="text-center q-pa-xl">
      <q-icon name="check_circle" size="80px" color="positive" />
      <div class="text-h5 text-weight-bolder q-mt-md">Commande confirmée !</div>
      <div class="text-grey-7 q-mt-sm">
        Votre commande #{{ order?.order_number }} a été enregistrée
      </div>
      <div class="q-mt-md">
        <q-btn color="primary" label="Voir mes commandes" @click="goToOrders" class="q-mr-sm" />
        <q-btn flat label="Continuer mes achats" @click="goToProducts" />
      </div>
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { Browser } from '@capacitor/browser';
import { Loading, QSpinnerFacebook, useQuasar } from 'quasar';
import { useAuthStore } from 'src/stores/auth';
import  { type OrderResponse, useOrderStore } from 'src/stores/product-checkout';
import { ref, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';

const router = useRouter();
const route = useRoute();
const orderStore = useOrderStore();
const order = ref<OrderResponse | null>(null);
const auth = useAuthStore();
const $q = useQuasar();
onMounted(async () => {
  const orderId = route.params.id as string;
  if (orderId) {
    order.value = await orderStore.fetchOrder(orderId);
  }
});

const goToOrders = async () => {
  await router.push('/user/profile');
  await openFilament('/app/purchase-orders')
};

async function openFilament(destination: string) {
  Loading.show({
    spinner: QSpinnerFacebook,
    spinnerColor: 'white',
    backgroundColor: 'primary',
    message: 'Connexion sécurisée...',
    messageColor: 'white'
  });

  try {
    const url = await auth.generateMagicLink(destination);
    if (url) {
      await Browser.open({ url: url });
    } else {
      throw new Error('Erreur de génération du lien');
    }
  } catch {
    $q.notify({
      color: 'negative',
      message: 'Erreur technique lors de la redirection.',
      icon: 'report_problem'
    });
  } finally {
    Loading.hide();
  }
}
const goToProducts = async() => {
 await router.push('/products');
};
</script>