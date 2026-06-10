<template>
  <q-page class="bg-grey-2 q-pb-xl">
    <q-pull-to-refresh @refresh="refreshPage" color="primary" bg-color="white" icon="refresh">
      <div class="row q-col-gutter-md q-pa-md">
        <div class="col-12 col-md-8">
          <HomeCarousel :slides="homeStore.slides" :loading="homeStore.isLoading" />
        </div>

        <div class="col-12 col-md-4">
          <MarketPreview :trends="marketStore.trends" :loading="marketStore.loading"
            :has-trends="marketStore.hasTrends" />
        </div>
      </div>
      <HomeQuickNav :navigation="navigationStore.navigationData" />

      <!-- <HomeMarkets v-if="$q.screen.lt.md" :markets="navigationStore.markets" /> -->
      <PopularCrops :crops="homeStore.popularCrops" />
      <FeaturedProducts :loading="homeStore.loading" :products="homeStore.featuredProducts" />
      <FeaturedServices :loading="homeStore.loading" :services="homeStore.featured_services" />

      <HomeStats v-if="homeStore.stats" :stats="homeStore.stats" class="q-my-xl" />

      <HomeServices />

      <PartnerComponent :partners="homeStore.partners" />

    </q-pull-to-refresh>
  </q-page>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { useHomeStore } from 'src/stores/home';
import { useMarketStore } from 'src/stores/market';
import { useNavigationStore } from 'src/stores/navigation';

// Imports des composants
import HomeStats from 'src/components/HomeStats.vue';
import HomeCarousel from 'src/components/layout/HomeCarousel.vue';
// import HomeMarkets from 'src/components/layout/HomeMarkets.vue';
import HomeServices from 'src/components/layout/HomeServices.vue';
import PartnerComponent from 'src/components/layout/PartnerComponent.vue';
import FeaturedProducts from 'src/components/market/FeaturedProducts.vue';
import MarketPreview from 'src/components/market/MarketPreview.vue';
import PopularCrops from 'src/components/market/PopularCrops.vue';
import FeaturedServices from 'src/components/market/FeaturedServices.vue';
import HomeQuickNav from 'src/components/HomeQuickNav.vue';

const homeStore = useHomeStore();
const marketStore = useMarketStore();
const navigationStore = useNavigationStore();

/**
 * Fonction de rafraîchissement manuel (Pull)
 * @param done - Callback Quasar pour arrêter l'animation
 */
const refreshPage = async (done: () => void) => {
  try {
    // On force le rafraîchissement de tous les stores nécessaires
    await Promise.allSettled([
      homeStore.fetchHomeData(true), // On suppose que tu as ajouté l'argument forceRefresh
      marketStore.fetchTicker(undefined, true),
      navigationStore.fetchNavigationData(true),

    ]);
  } catch (error) {
    console.error('Erreur lors du rafraîchissement:', error);
  } finally {
    // On cache l'icône de chargement de Quasar
    done();
  }
};

onMounted(async () => {
  // Chargement initial (utilise le cache si disponible)
  await Promise.allSettled([
    homeStore.fetchHomeData(),
  ]);
});
</script>

<style scoped>
/* Tes styles existants restent les mêmes */
.market-dashboard {
  border-radius: 24px;
  background: white;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03) !important;
  border: 1px solid rgba(0, 0, 0, 0.05);
}

/* ... reste du CSS ... */
</style>
