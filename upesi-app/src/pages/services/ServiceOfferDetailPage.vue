<template>
  <q-page class="bg-grey-1">
    <div v-if="loadingOffer" class="q-pa-md">
      <q-skeleton height="300px" square class="rounded-borders" />
      <div class="q-mt-md">
        <q-skeleton type="text" width="60%" height="40px" />
        <q-skeleton type="text" width="40%" />
        <q-skeleton type="rect" height="100px" class="q-mt-md" />
      </div>
    </div>

    <div v-else-if="currentOffer" class="row justify-center q-pb-xl">
      <div class="col-12 col-md-10 col-lg-8">

        <div class="relative-position overflow-hidden shadow-1 no-border-radius-mobile">
          <q-btn icon="arrow_back" flat round color="white" class="absolute-top-left q-ma-md z-max bg-black-30"
            @click="$router.back()" />

          <q-carousel v-if="currentOffer.images?.length" v-model="slide" arrows navigation infinite animated
            height="400px" class="bg-grey-3">
            <q-carousel-slide v-for="(img, index) in currentOffer.images" :key="index" :name="index" :img-src="img" />
          </q-carousel>
          <div v-else class="flex flex-center bg-grey-3" style="height: 350px">
            <q-icon name="image" size="100px" color="grey-5" />
          </div>
        </div>

        <div class="row q-col-gutter-lg q-pa-md q-pa-sm-lg">

          <div class="col-12 col-md-7 col-lg-8">
            <div class="bg-white q-pa-lg rounded-borders shadow-sm">
              <div class="flex justify-between items-start no-wrap">
                <div>
                  <q-chip color="primary" text-color="white" size="sm" icon="category"
                    class="q-mb-sm text-uppercase text-weight-bold">
                    {{ currentOffer.service_category }}
                  </q-chip>
                  <h1 class="text-h5 text-weight-bolder q-ma-none line-height-tight">
                    {{ currentOffer.title }}
                  </h1>
                </div>
                <q-btn flat round icon="share" color="grey-7" />
              </div>

              <div class="flex items-center q-mt-sm text-grey-7">
                <q-icon name="place" color="red" size="xs" />
                <span class="q-ml-xs text-caption">
                  Disponible en : {{ currentOffer.service_zones?.join(', ') || 'Tout le pays' }}
                </span>
              </div>

              <q-separator class="q-my-lg" />

              <div class="text-subtitle1 text-weight-bold q-mb-sm">Description du service</div>
              <p class="text-body1 text-grey-9 text-justify pre-wrap">
                {{ currentOffer.description || "Aucune description fournie pour ce service." }}
              </p>

              <div class="row q-gutter-sm q-mt-md">
                <q-chip outline color="secondary" icon="schedule">Réponse rapide</q-chip>
                <q-chip outline color="secondary" icon="verified_user">Prestataire vérifié</q-chip>
              </div>
            </div>
          </div>

          <div class="col-12 col-md-5 col-lg-4">
            <div class="sticky-sidebar">
              <div class="bg-white q-pa-lg rounded-borders shadow-sm q-mb-md">
                <div class="text-h4 text-weight-bolder text-primary">
                  {{ currentOffer.price }} <small class="text-caption text-grey-8">FCFA / {{ currentOffer.price_unit
                    }}</small>
                </div>

                <!-- <q-btn
                  color="primary"
                  unelevated
                  class="full-width q-py-md q-mt-lg rounded-borders"
                  label="Contacter le prestataire"
                  icon="whatsapp"
                  no-caps
                /> -->
                <!-- <q-btn
                  outline
                  color="primary"
                  class="full-width q-py-md q-mt-sm rounded-borders"
                  label="Demander un devis"
                  no-caps
                /> -->
                <q-btn color="primary" unelevated class="full-width q-py-md q-mt-lg rounded-borders"
                  label="Demander ce service" icon="send" no-caps
                  :to="{ name: 'service-request-create', params: { offerId: currentOffer.id } }" />
              </div>

              <div class="bg-white q-pa-md rounded-borders shadow-sm">
                <div class="text-subtitle2 q-mb-md text-grey-7">Prestataire</div>
                <div class="flex items-center">
                  <q-avatar size="50px" class="bg-primary text-white shadow-1">
                    {{ currentOffer.merchant.shop_name.charAt(0) }}
                  </q-avatar>
                  <div class="q-ml-md">
                    <div class="text-weight-bold text-subtitle1">{{ currentOffer.merchant.shop_name }}</div>
                    <q-badge :color="currentOffer.merchant.type === 'transporter' ? 'orange' : 'blue'">
                      {{ currentOffer.merchant.type_label }}
                    </q-badge>
                  </div>
                </div>
                <q-separator class="q-my-md" />
                <div class="text-caption text-grey-6 flex items-center">
                  <q-icon name="event" class="q-mr-xs" />
                  Membre depuis {{ new Date(currentOffer.created_at).toLocaleDateString() }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="flex flex-center q-pa-xl">
      <div class="text-center">
        <q-icon name="error_outline" size="xl" color="grey-5" />
        <div class="text-h6 text-grey-7">Offre introuvable</div>
        <q-btn label="Retour aux offres" flat color="primary" class="q-mt-md" to="/services/offers" />
      </div>
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import { useServiceOfferStore } from 'src/stores/service-offer';
import { storeToRefs } from 'pinia';

const route = useRoute();
const store = useServiceOfferStore();
const { currentOffer, loadingOffer } = storeToRefs(store);

const slide = ref(0);

onMounted(async () => {
  const id = route.params.id as string;
  if (id) {
    await store.fetchOfferById(id);
  }
});

onUnmounted(() => {
  store.resetCurrentOffer();
});
</script>

<style lang="scss" scoped>
.line-height-tight {
  line-height: 1.2;
}

.pre-wrap {
  white-space: pre-wrap;
}

.bg-black-30 {
  background: rgba(0, 0, 0, 0.3);
}

.shadow-sm {
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.sticky-sidebar {
  @media (min-width: $breakpoint-md-min) {
    position: sticky;
    top: 80px;
  }
}

// Design "No border radius" sur mobile pour l'image
@media (max-width: $breakpoint-xs-max) {
  .no-border-radius-mobile {
    border-radius: 0 !important;
  }
}

.rounded-borders {
  border-radius: 12px;
}
</style>
