<template>
  <q-page class="bg-grey-2">
    <q-form @submit.prevent="submitRequest" ref="formRef">
      <!-- Header -->
      <div class="bg-primary text-white q-pa-md">
        <div class="row items-center">
          <q-btn flat round dense icon="arrow_back" @click="$router.back()" :disable="requestStore.isLoading" />
          <div class="text-h6 q-ml-sm">Demande de service</div>
        </div>
        <div class="text-caption q-mt-xs q-ml-md">
          Offre : {{ offer?.title }}
        </div>
      </div>

      <div class="container-narrow q-mx-auto q-pa-md">
        <div class="row q-col-gutter-lg">
          <!-- Formulaire principal -->
          <div class="col-12 col-md-7">
            <!-- Description -->
            <q-card flat class="rounded-20 q-mb-md">
              <q-card-section>
                <div class="text-subtitle1 text-weight-bolder q-mb-sm">
                  <q-icon name="description" size="sm" class="q-mr-sm text-primary" />
                  Décrivez votre besoin
                </div>
              </q-card-section>
              <q-separator />
              <q-card-section>
                <q-input
                  v-model="form.description"
                  type="textarea"
                  rows="4"
                  outlined
                  placeholder="Expliquez clairement ce que vous attendez du prestataire (ex: type de culture, superficie, dates précises, adresses...)"
                  :rules="[val => !!val || 'Description requise']"
                  lazy-rules
                />
              </q-card-section>
            </q-card>

            <!-- Détails additionnels (JSON) -->
            <q-card flat class="rounded-20 q-mb-md">
              <q-card-section>
                <div class="text-subtitle1 text-weight-bolder q-mb-sm">
                  <q-icon name="fact_check" size="sm" class="q-mr-sm text-primary" />
                  Informations complémentaires (optionnel)
                </div>
              </q-card-section>
              <q-separator />
              <q-card-section>
                <div class="row q-col-gutter-md">
                  <template v-if="offer?.service_category === 'transport'">
                    <div class="col-12">
                      <q-input v-model="details.pickup_address" label="Adresse de chargement" outlined dense />
                    </div>
                    <div class="col-12">
                      <q-input v-model="details.delivery_address" label="Adresse de livraison" outlined dense />
                    </div>
                    <div class="col-12 col-md-6">
                      <q-input v-model.number="details.distance_km" label="Distance approximative (km)" type="number" outlined dense />
                    </div>
                    <div class="col-12 col-md-6">
                      <q-input v-model.number="details.weight_kg" label="Poids estimé (kg)" type="number" outlined dense />
                    </div>
                  </template>
                  <template v-else>
                    <div class="col-12">
                      <q-input v-model="details.location" label="Localisation du champ / exploitation" outlined dense />
                    </div>
                    <div class="col-12 col-md-6">
                      <q-input v-model.number="details.area_hectares" label="Superficie (hectares)" type="number" outlined dense />
                    </div>
                  </template>
                </div>
              </q-card-section>
            </q-card>

            <!-- Date souhaitée -->
            <q-card flat class="rounded-20">
              <q-card-section>
                <div class="text-subtitle1 text-weight-bolder q-mb-sm">
                  <q-icon name="calendar_today" size="sm" class="q-mr-sm text-primary" />
                  Date souhaitée (optionnel)
                </div>
              </q-card-section>
              <q-separator />
              <q-card-section>
                <q-input
                  v-model="form.scheduled_at"
                  type="datetime-local"
                  outlined
                  dense
                  :rules="[]"
                  stack-label
                />
              </q-card-section>
            </q-card>
          </div>

          <!-- Résumé & paiement -->
          <div class="col-12 col-md-5">
            <q-card flat class="rounded-20 sticky-summary">
              <q-card-section>
                <div class="text-subtitle1 text-weight-bolder">Résumé de la demande</div>
              </q-card-section>
              <q-separator />

              <q-card-section>
                <div class="row justify-between q-mb-sm">
                  <span class="text-grey-8">Service</span>
                  <span class="text-weight-bold">{{ offer?.title }}</span>
                </div>
                <div class="row justify-between q-mb-sm">
                  <span class="text-grey-8">Prestataire</span>
                  <span>{{ offer?.merchant?.shop_name }}</span>
                </div>
                <div class="row justify-between q-mb-sm">
                  <span class="text-grey-8">Tarif</span>
                  <span class="text-weight-bold text-primary">{{ formatPrice(offer?.price) }} CFA / {{ offer?.price_unit }}</span>
                </div>
                <q-separator class="q-my-md" />
                <div class="row justify-between items-center">
                  <span class="text-h6 text-weight-bolder">Total à payer</span>
                  <span class="text-h6 text-weight-bolder text-primary">
                    {{ formatPrice(offer?.price) }} CFA
                  </span>
                </div>
                <div class="text-caption text-grey-6 text-center q-mt-sm">
                  <q-icon name="account_balance_wallet" size="xs" />
                  Le montant sera prélevé de votre wallet et bloqué jusqu'à la fin du service.
                </div>
              </q-card-section>

              <q-card-section>
                <q-btn
                  type="submit"
                  color="primary"
                  size="lg"
                  class="full-width q-py-md text-weight-bolder rounded-15"
                  :loading="requestStore.isLoading"
                  :disable="!form.description"
                  no-caps
                  unelevated
                >
                  <q-icon name="send" left />
                  {{ requestStore.isLoading ? 'Envoi en cours...' : 'Envoyer la demande' }}
                </q-btn>
                <div class="text-center q-mt-sm">
                  <q-btn flat dense label="Annuler" @click="$router.back()" />
                </div>
              </q-card-section>
            </q-card>
          </div>
        </div>
      </div>
    </q-form>

    <q-dialog v-model="showErrorDialog" persistent>
      <q-card class="rounded-20">
        <q-card-section class="text-center">
          <q-icon name="error_outline" size="60px" color="red" />
          <div class="text-h6 q-mt-md">{{ errorMessage }}</div>
        </q-card-section>
        <q-card-actions align="center" class="q-mb-md">
          <q-btn flat label="Fermer" color="primary" @click="showErrorDialog = false" />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </q-page>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { type QForm } from 'quasar';
import { useServiceOfferStore } from 'src/stores/service-offer';
import { useServiceRequestStore, type CreateServiceRequestPayload } from 'src/stores/service-request';

const router = useRouter();
const route = useRoute();

const offerStore = useServiceOfferStore();
const requestStore = useServiceRequestStore();

const formRef = ref<QForm | null>(null);
const showErrorDialog = ref(false);
const errorMessage = ref('');

// Formulaire
const form = ref({
  description: '',
  scheduled_at: '',
});

// Détails : un objet avec des valeurs de type string | number | undefined
const details = ref<Record<string, string | number | undefined>>({
  pickup_address: undefined,
  delivery_address: undefined,
  distance_km: undefined,
  weight_kg: undefined,
  location: undefined,
  area_hectares: undefined,
});

// Offre courante
const offer = computed(() => offerStore.currentOffer);

onMounted(async () => {
  const offerId = route.params.offerId as string;
  if (!offerId) {
    errorMessage.value = 'Offre introuvable';
    showErrorDialog.value = true;
    return;
  }
  if (!offerStore.currentOffer || offerStore.currentOffer.id !== offerId) {
    await offerStore.fetchOfferById(offerId);
  }
  if (!offerStore.currentOffer) {
    errorMessage.value = 'Offre introuvable ou indisponible';
    showErrorDialog.value = true;
  }
});

const formatPrice = (val?: number) => {
  if (!val) return '0';
  return new Intl.NumberFormat('fr-FR').format(Math.round(val));
};

async function submitRequest() {
  const isValid = await formRef.value?.validate();
  if (!isValid) return;
  if (!offer.value) return;

  // Filtrer les champs non vides
  const cleanedDetails: Record<string, string | number> = {};
  Object.entries(details.value).forEach(([key, val]) => {
    if (val !== undefined && val !== null && val !== '') {
      cleanedDetails[key] = val;
    }
  });

  const payload: CreateServiceRequestPayload = {
    service_offer_id: offer.value.id,
    description: form.value.description,
    scheduled_at: form.value.scheduled_at || null,
  };

  if (Object.keys(cleanedDetails).length > 0) {
    payload.details = cleanedDetails;
  }

  const result = await requestStore.createRequest(payload);
  if (result) {
   await router.push('/user/profile');
  }
}
</script>

<style scoped lang="scss">
.container-narrow {
  max-width: 1200px;
}
.rounded-20 { border-radius: 20px; }
.rounded-15 { border-radius: 15px; }
.sticky-summary {
  @media (min-width: 1024px) {
    position: sticky;
    top: 20px;
  }
}
</style>