<template>
  <section class="services-section q-py-lg q-py-md-xl bg-grey-1">
    <div class="container-max">

      <div class="q-px-md q-mb-md">
        <h2 class="text-h5 text-md-h4 text-weight-bolder text-grey-10 q-ma-none flex items-center">
          Services à la une
          <div class="trending-icon-container q-ml-sm">
            <q-icon name="handyman" color="green-6" size="0.8em" />
          </div>
        </h2>
        <p class="text-grey-7 q-mt-xs q-mb-none text-body2 text-md-body1">
          Les services les plus demandés par les acteurs du marché.
        </p>
      </div>

      <q-carousel v-model="slide" infinite swipeable :autoplay="3000" animated transition-prev="slide-right"
        transition-next="slide-left" height="auto" class="bg-transparent overflow-visible custom-carousel"
        control-color="primary" arrows>
        <q-carousel-slide v-for="(group, gIndex) in groupedServices" :key="gIndex" :name="gIndex"
          class="row no-wrap q-px-md flex-center carousel-slide-custom">
          <div v-for="service in group" :key="service.id" class="service-col">
            <q-card  @click="$router.push({ name: 'service-offer-detail', params: { id: service.id } })" flat class="service-card column items-center justify-center q-pa-md">
              <!-- Image ou icône -->
              <div class="image-wrapper q-mb-sm">
                <q-img v-if="service.images && service.images[0]" :src="service.images[0]" class="service-image"
                  fit="cover" />
                <div v-else class="placeholder-icon">
                  <q-icon :name="getServiceIcon(service.service_category)" size="2rem" color="primary" />
                </div>
              </div>

              <div class="text-subtitle2 text-weight-bold text-grey-10 text-center line-clamp-1">
                {{ service.title }}
              </div>

              <div class="text-caption text-grey-6 text-center ellipsis">
                {{ service.service_name }}
              </div>

              <div class="price-tag q-mt-sm">
                {{ formatPrice(service.price) }} FCFA / {{ service.price_unit }}
              </div>

              <div class="merchant-info text-caption text-grey-5 q-mt-xs">
                par {{ service.merchant.shop_name }}
              </div>
            </q-card>
          </div>
        </q-carousel-slide>
      </q-carousel>

    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { useQuasar } from 'quasar';
import type { ServiceOffer } from 'src/types';

const props = defineProps<{ services: ServiceOffer[] }>();
const slide = ref(0);
const $q = useQuasar();

// Grouper par 2 sur mobile, 5 sur desktop
const groupedServices = computed(() => {
  const size = $q.screen.lt.md ? 2 : 5;
  const result = [];
  for (let i = 0; i < props.services.length; i += size) {
    result.push(props.services.slice(i, i + size));
  }
  return result;
});

const getServiceIcon = (category: string | null): string => {
  if (category === 'logistique') return 'local_shipping';
  if (category === 'prestation') return 'agriculture';
  return 'handyman';
};

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('fr-FR').format(price);
};
</script>

<style lang="scss" scoped>
.container-max {
  max-width: 1440px;
  margin: 0 auto;
}

.service-col {
  padding: 8px;
  width: 50%; // 2 par ligne sur mobile

  @media (min-width: 1024px) {
    width: 20%; // 5 par ligne sur desktop
  }
}

.carousel-slide-custom {
  padding-bottom: 40px;
  min-height: 280px;

  @media (min-width: 1024px) {
    min-height: 360px;
  }
}

.service-card {
  border-radius: 28px;
  background: white;
  height: 100%;
  min-height: 220px;
  border: 1px solid #edf2f7;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
  cursor: grab;

  &:active {
    cursor: grabbing;
  }

  @media (min-width: 1024px) {
    min-height: 280px;
    padding: 24px !important;
  }

  &:hover {
    transform: translateY(-8px);
    border-color: var(--q-primary);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.08);
  }
}

.image-wrapper {
  width: 70px;
  height: 70px;
  background: #f1f8e9;
  border-radius: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;

  @media (min-width: 1024px) {
    width: 90px;
    height: 90px;
  }
}

.service-image {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.placeholder-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
}

.price-tag {
  font-size: 0.85rem;
  font-weight: 800;
  color: var(--q-primary);
  background: rgba(var(--q-primary), 0.1);
  padding: 4px 12px;
  border-radius: 20px;
}

.merchant-info {
  font-size: 0.7rem;
  font-weight: 500;
}

.trending-icon-container {
  background: #f0fdf4;
  width: 32px;
  height: 32px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #dcfce7;
}

.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.custom-carousel {
  cursor: grab;

  &:active {
    cursor: grabbing;
  }
}
</style>
