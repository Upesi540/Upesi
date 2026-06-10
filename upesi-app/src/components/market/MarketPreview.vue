<template>
  <q-card flat bordered class="market-preview-card full-height">
    <q-card-section class="row items-center justify-between q-pb-sm">
      <div class="column">
        <span class="text-subtitle1 text-weight-bolder text-green-10">Prix du Jour</span>
        <div class="row items-center">
          <div class="pulse-red q-mr-xs"></div>
          <span class="text-caption text-grey-7">Direct Marché</span>
        </div>
      </div>
      <q-btn round flat icon="trending_up" color="green-8" to="/bourse" />
    </q-card-section>

    <q-card-section class="q-pa-none">
      <q-list padding>
        <template v-if="loading && !hasTrends">
          <q-item v-for="n in 4" :key="'skel-' + n" class="q-py-md">
            <q-item-section avatar>
              <q-skeleton type="QAvatar" size="32px" />
            </q-item-section>
            <q-item-section>
              <q-skeleton type="text" width="60%" />
            </q-item-section>
            <q-item-section side>
              <q-skeleton type="rect" width="50px" height="20px" />
            </q-item-section>
          </q-item>
        </template>

        <template v-else>
          <q-item v-for="(trend, index) in trends.slice(0, 4)" :key="index" class="market-item q-mx-sm q-mb-xs"
            clickable v-ripple @click="$router.push(`/products/crop/${trend.crop_id}`)">
            <q-item-section>
              <q-item-label class="text-weight-bold">{{ trend.name }}</q-item-label>
              <q-item-label caption>{{ trend.volume }} {{ trend.unit }}</q-item-label>
            </q-item-section>

            <q-item-section side class="text-right">
              <div class="text-weight-bold text-dark">{{ trend.price }} <small>CFA</small></div>
              <div :class="`text-caption text-weight-bold text-${trend.color}`">
                <q-icon :name="trend.icon" size="14px" />
                {{ trend.change }}%
              </div>
            </q-item-section>
          </q-item>
        </template>
      </q-list>
    </q-card-section>

    <q-card-actions class="q-px-md q-pb-md">

      <q-btn to="/bourse" outline label="Accéder à la bourse complète" color="primary" no-caps
        class="full-width rounded-btn text-weight-bold" />

    </q-card-actions>
  </q-card>
</template>
<script setup lang="ts">
import type { MarketTrend } from 'src/types/ticker';

// C'est ici que la magie opère 🪄
// On définit ce que le composant attend comme données
defineProps<{
  trends: MarketTrend[]; // Le tableau des produits (maïs, manioc, etc.)
  loading: boolean;      // L'état de chargement pour le squelette
  hasTrends: boolean;    // Pour savoir si on affiche les données ou le vide
}>();

// Optionnel : tu peux ajouter des fonctions de formatage ici
// const formatPrice = (val: number) => {
//   return new Intl.NumberFormat('fr-FR').format(val);
// };
</script>
<style scoped>
.text-weight-bold {
  font-size: 0.85rem;
}

.market-preview-card {
  border-radius: 20px;
  background: #ffffff;
}

.market-item {
  border-radius: 12px;
  transition: background 0.3s;
}

.market-item:hover {
  background: #f1f8e9;
  /* Un vert très léger pour le survol */
}

/* Petit point rouge qui clignote pour le "Direct" */
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
