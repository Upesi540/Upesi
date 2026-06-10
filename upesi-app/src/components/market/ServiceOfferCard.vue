<template>
  <q-card flat bordered class="service-card transition-shadow cursor-pointer"
    @click="$router.push({ name: 'service-offer-detail', params: { id: offer.id } })">
    <q-card-section horizontal class="row no-wrap items-stretch">

      <div class="col-4 col-sm-3 col-md-4 relative-position overflow-hidden border-right-md">
        <q-img :src="offer.images?.[0] || 'https://cdn.quasar.dev/img/image-not-found.png'" class="full-height"
          :ratio="1" cover>
          <template v-slot:loading>
            <q-spinner-dots color="white" />
          </template>

          <div class="absolute-top-left q-pa-xs">
            <q-badge :color="offer.merchant.type === 'transporter' ? 'secondary' : 'secondary'"
              class="text-weight-bold shadow-2">
              {{ offer.merchant.type_label }}
            </q-badge>
          </div>
        </q-img>
      </div>

      <q-card-section class="col-8 col-sm-9 col-md-8 q-pa-md flex flex-column justify-between">
        <div>
          <div class="flex justify-between items-start no-wrap">
            <div class="text-overline text-primary font-weight-bold">{{ offer.service_category }}</div>
            <div class="text-h6 text-primary text-weight-bolder">
              {{ formatPrice(offer.price) }} <small class="text-caption">/{{ offer.price_unit }}</small>
            </div>
          </div>

          <h3 class="text-subtitle1 text-weight-bold q-mt-xs q-mb-sm line-clamp-1">
            {{ offer.title }}
          </h3>

          <p class="text-body2 text-grey-7 line-clamp-2 q-mb-md hide-on-mobile">
            {{ offer.description }}
          </p>

          <div class="row items-center q-gutter-x-md text-caption text-grey-6">
            <div class="flex items-center">
              <q-icon name="place" size="xs" color="red-5" class="q-mr-xs" />
              {{ offer.service_zones?.[0] }}{{ (offer.service_zones?.length ?? 0) > 1 ? '...' : '' }}
            </div>
            <div class="flex items-center hide-on-mobile">
              <q-icon name="storefront" size="xs" class="q-mr-xs" />
              {{ offer.merchant.shop_name }}
            </div>
          </div>
        </div>

        <div class="row items-center justify-between q-mt-md mt-auto">
          <q-btn flat color="primary" label="Voir le détail" no-caps icon-right="chevron_right" dense />
          <div class="text-caption text-grey-5 italic">
            Ajouté le {{ new Date(offer.created_at).toLocaleDateString() }}
          </div>
        </div>
      </q-card-section>
    </q-card-section>
  </q-card>
</template>

<script setup lang="ts">
import type { ServiceOffer } from 'src/types';

defineProps<{
  offer: ServiceOffer;
}>();

const formatPrice = (val: number) => {
  return new Intl.NumberFormat('fr-FR').format(val) + ' FCFA';
};
</script>

<style lang="scss" scoped>
.service-card {
  border-radius: 12px;
  overflow: hidden;
  height: 200px; // Hauteur fixe sur desktop pour l'alignement
  transition: all 0.3s ease;

  &:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
    border-color: var(--q-primary);
  }
}

.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.border-right-md {
  @media (min-width: $breakpoint-sm-min) {
    border-right: 1px solid rgba(0, 0, 0, 0.12);
  }
}

// Adaptations Mobiles
@media (max-width: $breakpoint-xs-max) {
  .service-card {
    height: auto; // On laisse grandir sur mobile
  }

  .hide-on-mobile {
    display: none !important;
  }
}
</style>
