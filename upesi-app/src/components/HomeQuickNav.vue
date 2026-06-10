<template>
  <div class="q-py-md bg-grey-1">
    <!-- Header avec lien "Voir tout" -->
    <div class="row items-center justify-between q-px-md q-mb-sm">
      <div class="text-subtitle2 text-weight-bolder text-grey-9 text-uppercase" style="letter-spacing: 0.5px">
        Nos Marchés et Services
      </div>
      <q-btn flat no-caps color="primary" label="Voir tout" density="compact" to="/markets" class="text-weight-bold" />
    </div>

    <!-- Conteneur Défilant -->
    <div class="horizontal-scroll q-px-md no-wrap row q-gutter-x-sm">
      <!-- Marchés -->
      <div
        v-for="market in navigation.markets.slice(0, 3)"
        :key="'m-' + market.id"
        class="nav-item-col"
        @click="router.push(`products/market/${market.id}`)"
      >
        <q-card flat bordered class="nav-card flex flex-center">
          <q-card-section class="column items-center q-pa-xs full-width">
            <q-avatar size="60px" color="blue-1" text-color="primary">
              <img :src="market.image">
            </q-avatar>
            <div class="nav-label text-weight-bold q-mt-xs text-center">
              {{ market.name }}
            </div>
          </q-card-section>
        </q-card>
      </div>

      <!-- Services -->
      <div
        v-for="sCat in navigation.service_categories"
        :key="'s-' + sCat.id"
        class="nav-item-col"
        @click="router.push(`/services/category/${sCat.slug}`)"
      >
        <q-card flat bordered class="nav-card flex flex-center">
          <q-card-section class="column items-center q-pa-xs full-width">
            <q-avatar size="60px" color="blue-1" text-color="primary">
              <img v-if="sCat.icon" :src="sCat.icon">
              <q-icon v-else name="engineering" size="24px" />
            </q-avatar>
            <div class="nav-label text-weight-bold q-mt-xs text-center">
              {{ sCat.name }}
            </div>
          </q-card-section>
        </q-card>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router';
import type { appInitData } from 'src/types/appInit';

const router = useRouter();
defineProps<{
  navigation: appInitData;
}>();
</script>

<style scoped lang="scss">
.horizontal-scroll {
  overflow-x: auto;
  overflow-y: hidden;
  display: flex;
  -webkit-overflow-scrolling: touch;
  scrollbar-width: none;
  &::-webkit-scrollbar { display: none; }
  padding-bottom: 8px;
}

.nav-item-col {
  // Mobile : 3 éléments visibles
  flex: 0 0 28%;
  max-width: 28%;
  cursor: pointer;

  @media (min-width: $breakpoint-md-min) {
    // PC : Exactement 5 éléments par ligne (100/5 - gap)
    flex: 0 0 calc(20% - 8px);
    max-width: calc(20% - 8px);
  }
}

.nav-card {
  min-height: 100px; // min-height au lieu de height pour laisser le texte respirer
  height: 100%; // Force l'alignement si les textes ont des longueurs différentes
  border-radius: 12px;
  background: white;
  border: 1px solid rgba(0, 0, 0, 0.05);
  transition: all 0.2s ease-in-out;

  &:hover {
    border-color: var(--q-primary);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
  }
}

.nav-label {
  font-size: 11px;
  color: #333;
  width: 100%;
  line-height: 1.2;
  // Gestion propre du texte sur 2 lignes max
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  word-break: break-word;
}

:deep(.q-avatar img) {
  object-fit: contain;
  padding: 2px;
}
</style>
