<template>
  <div class="ticker-wrapper">
    <div class="ticker-container">
      <div class="ticker-content" :style="animationStyle">
        <div v-for="(item, index) in displayItems" :key="index" class="ticker-item">
          <span class="crop-name">{{ item.name }}</span>
          <span class="crop-price">{{ formatPrice(item.price) }} FCFA</span>

          <span :class="['crop-change', item.status]">
            {{ item.status === 'up' ? '▲' : '▼' }} {{ Math.abs(item.change) }}%
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type{ MarketTrend } from 'src/types/ticker';
import { computed } from 'vue';


const props = defineProps<{
  items: MarketTrend[];
  speed?: number;
}>();

// IMPORTANT : Pour un défilement infini sans "saut" visuel,
// on triple ou quadruple la liste si elle est courte.
const displayItems = computed(() => {
  if (props.items.length === 0) return [];
  return [...props.items, ...props.items, ...props.items];
});

const animationStyle = computed(() => ({
  animationDuration: `${props.speed}s`
}));

const formatPrice = (val: number) => {
  return new Intl.NumberFormat("fr-FR").format(val);
};
</script>

<style scoped>
.ticker-wrapper {
  width: 100%;
  background: #1a1a1a;
  /* border-bottom: 2px solid #ffd700; */
  overflow: hidden;
}

.ticker-container {
  display: flex;
  width: 100%;
  padding: 2px 0;
}

.ticker-content {
  display: flex;
  white-space: nowrap;
  /* On ne garde que "scroll" qui est le nom défini dans tes @keyframes */
  animation: scroll linear infinite;
}

.ticker-item {
  display: flex;
  align-items: center;
  padding: 0 20px;
  /* font-family: "Roboto Mono", monospace; */
  font-weight: 700;
  font-size: 12px;
  color: white;
}

.crop-name {
  color: #999;
  margin-right: 12px;
  font-size: 0.7rem;
}

.crop-price {
  letter-spacing: 0.5px;
}

.crop-change {
  margin-left: 10px;
  padding: 2px 6px;
  border-radius: 4px;
  font-size: 0.75rem;
}

/* Couleurs boursières classiques */
.up {
  color: #4caf50;
  background: rgba(76, 175, 80, 0.1);
}

.down {
  color: #f44336;
  background: rgba(244, 67, 54, 0.1);
}

@keyframes scroll {
  0% {
    transform: translateX(0);
  }

  100% {
    transform: translateX(-33.33%);
  }
}

.ticker-wrapper:hover .ticker-content {
  animation-play-state: paused;
  cursor: pointer;
  /* Pour inviter au clic plus tard */
}
</style>
