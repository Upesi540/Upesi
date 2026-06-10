<template>
  <q-page class="bg-grey-2 q-pb-xl">
    <div class="row q-col-gutter-md q-pa-md">
      <div class="col-12">
        <div class="row items-center justify-between q-mb-md">
          <div>
            <span class="text-h5 text-weight-bolder text-green-10">Bourse Agricole</span>
            <div class="row items-center">
              <div class="pulse-red q-mr-xs"></div>
              <span class="text-caption text-grey-7">Direct Marché - Tous les produits</span>
            </div>
          </div>
          <q-btn round flat icon="refresh" color="green-8" @click="refreshData" :loading="marketStore.loading" />
        </div>

        <q-card flat bordered class="market-full-card">
          <q-list padding>
            <template v-if="marketStore.loading && !marketStore.hasTrends">
              <q-item v-for="n in 20" :key="'skel-' + n" class="q-py-md">
                <q-item-section avatar>
                  <q-skeleton type="QAvatar" size="40px" />
                </q-item-section>
                <q-item-section>
                  <q-skeleton type="text" width="60%" />
                </q-item-section>
                <q-item-section side>
                  <q-skeleton type="rect" width="80px" height="24px" />
                </q-item-section>
              </q-item>
            </template>

            <template v-else>
              <q-item v-for="(trend,index) in marketStore.trends" :key="index" class="market-item q-mx-sm q-mb-xs"
                clickable v-ripple @click="$router.push(`/products/crop/${trend.crop_id}`)">
                <q-item-section avatar>
                  <q-avatar :color="trend.change >= 0 ? 'positive' : 'negative'" text-color="white" size="40px">
                    <q-icon :name="trend.icon" size="24px" />
                  </q-avatar>
                </q-item-section>

                <q-item-section>
                  <q-item-label class="text-weight-bold">{{ trend.name }}</q-item-label>
                  <q-item-label caption>{{ trend.volume }} {{ trend.unit }}</q-item-label>
                </q-item-section>

                <q-item-section side class="text-right">
                  <div class="text-h6 text-weight-bold text-dark">{{ formatPrice(trend.price) }} <small>CFA</small></div>
                  <div :class="`text-caption text-weight-bold text-${trend.color}`">
                    <q-icon :name="trend.icon" size="14px" />
                    {{ trend.change >= 0 ? '+' : '' }}{{ trend.change }}%
                  </div>
                </q-item-section>
              </q-item>
            </template>
          </q-list>
        </q-card>
      </div>
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { onMounted } from 'vue';
import { useMarketStore } from 'src/stores/market';

defineOptions({
  name: 'BoursePage'
});

const marketStore = useMarketStore();

const formatPrice = (price: number) => {
  return new Intl.NumberFormat('fr-FR').format(price);
};

const refreshData = async () => {
  await marketStore.fetchTicker(undefined, true);
};

onMounted(async () => {
  if (marketStore.trends.length === 0) {
    await marketStore.fetchTicker();
  }
});
</script>

<style scoped>
.market-full-card {
  border-radius: 20px;
  background: #ffffff;
}

.market-item {
  border-radius: 12px;
  transition: background 0.3s;
}

.market-item:hover {
  background: #f1f8e9;
}

.pulse-red {
  width: 8px;
  height: 8px;
  background: #ff5252;
  border-radius: 50%;
  box-shadow: 0 0 0 rgba(255, 82, 82, 0.4);
  animation: pulse 2s infinite;
}

@keyframes pulse {
  0% {
    transform: scale(0.95);
    box-shadow: 0 0 0 0 rgba(255, 82, 82, 0.7);
  }
  70% {
    transform: scale(1);
    box-shadow: 0 0 0 5px rgba(255, 82, 82, 0);
  }
  100% {
    transform: scale(0.95);
    box-shadow: 0 0 0 0 rgba(255, 82, 82, 0);
  }
}
</style>
