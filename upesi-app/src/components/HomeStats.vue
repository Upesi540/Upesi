<template>
  <div v-intersection="onIntersection" class="stats-section q-py-xl overflow-hidden relative-position text-white">
    <div class="absolute-full overflow-hidden no-pointer-events">
      <div class="bg-circle-1"></div>
      <div class="bg-circle-2"></div>
    </div>

    <div class="container-max q-px-md relative-z">
      <div class="text-center q-mb-xl">
        <div class="text-overline text-orange-4 text-weight-bolder">L'Impact Upesi</div>
        <h2 class="text-h4 text-weight-bolder">L'agriculture africaine en chiffres</h2>
      </div>

      <div class="row q-col-gutter-lg justify-center">
        <div v-for="(stat, index) in statsList" :key="index" class="col-6 col-sm-4 col-md-3">
          <div class="stat-card">
            <div class="stat-icon-box q-mb-md" :class="`bg-${stat.color}-transparent`">
              <q-icon :name="stat.icon" size="24px" :color="stat.color" />
            </div>

            <div class="column items-center">
              <div class="row items-baseline no-wrap">
                <div :id="'countup-' + index" class="text-h5 text-weight-bolder text-glow">0</div>
                <div v-if="stat.plus" class="text-h5 text-orange-4 q-ml-xs">+</div>
              </div>

              <div class="text-caption text-uppercase text-weight-bold text-grey-4 text-center q-mt-xs">
                {{ stat.label }}
              </div>
            </div>

            <div class="stat-progress q-mt-md" :style="{ backgroundColor: `var(--q-${stat.color})` }"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script setup lang="ts">
import { ref, watch, nextTick } from 'vue'; // Ajout de ref
import { CountUp } from 'countup.js';
import type { HomeStatsExtended } from 'src/types/home';

const props = defineProps<{ stats: HomeStatsExtended | null }>();
const hasAnimated = ref(false); // Pour ne pas relancer l'animation à chaque scroll

const statsList: { icon: string; label: string; key: keyof HomeStatsExtended; color: string; plus?: boolean }[] = [
  { icon: 'inventory_2', label: 'Produits', key: 'total_products', color: 'lime', plus: true },
  { icon: 'agriculture', label: 'Agriculteurs', key: 'active_farmers', color: 'orange' },
  { icon: 'shopping_cart', label: 'Acheteurs', key: 'active_buyers', color: 'blue' },
  { icon: 'category', label: 'Variétés', key: 'crop_varieties', color: 'yellow' },
  { icon: 'storefront', label: 'Marchés', key: 'active_markets', color: 'cyan' },
  { icon: 'groups', label: 'Coopératives', key: 'product_categories', color: 'purple' },
  { icon: 'trending_up', label: 'Moyenne/Prod', key: 'avg_products_per_farmer', color: 'green' }
];

const countUpOptions = {
  duration: 4,
  useEasing: true,
  useGrouping: true,
  separator: ' ',
};

// Fonction de déclenchement au scroll
const onIntersection = (entry: IntersectionObserverEntry) => {
  // Si la section est visible à l'écran et n'a pas encore animé
  if (entry.isIntersecting && !hasAnimated.value && props.stats) {
    initCounters();
    hasAnimated.value = true;
  }
};

const initCounters = () => {
  void nextTick(() => {
    statsList.forEach((stat, index) => {
      const targetId = 'countup-' + index;
      const finalValue = Number(props.stats?.[stat.key]) || 0;

      if (finalValue > 0) {
        const decimalPlaces = stat.key === 'avg_products_per_farmer' ? 1 : 0;
        const demo = new CountUp(targetId, finalValue, { ...countUpOptions, decimalPlaces });

        if (!demo.error) {
          void demo.start();
        }
      }
    });
  });
};

// On surveille si les stats arrivent APRÈS que l'utilisateur soit déjà sur la section
watch(() => props.stats, (newStats) => {
  // Si les stats arrivent et que l'élément est déjà visible (on peut simplifier ici)
  if (newStats && hasAnimated.value) {
    initCounters();
  }
}, { deep: true });
</script>

<style lang="scss" scoped>
.text-caption {
  font-size: 0.65rem;
  line-height: 1rem;
}
.stats-section {
  background: linear-gradient(135deg, #0a192f 0%, #062112 100%);
  /* Dégradé profond Indigo/Vert */
  border-radius: 40px;
  margin: 20px;
}

.container-max {
  max-width: 1200px;
  margin: 0 auto;
}

.relative-z {
  z-index: 2;
  position: relative;
}

/* Styles des cartes */
.stat-card {
  background: rgba(255, 255, 255, 0.03);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 24px;
  padding: 24px 16px;
  height: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);

  &:hover {
    transform: translateY(-10px);
    background: rgba(255, 255, 255, 0.07);
    border-color: rgba(255, 255, 255, 0.2);
  }
}

.stat-icon-box {
  padding: 12px;
  border-radius: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* Couleurs transparentes pour les icônes */
.bg-lime-transparent {
  background: rgba(205, 220, 57, 0.15);
}

.bg-orange-transparent {
  background: rgba(255, 152, 0, 0.15);
}

.bg-blue-transparent {
  background: rgba(33, 150, 243, 0.15);
}

.bg-yellow-transparent {
  background: rgba(255, 235, 59, 0.15);
}

.bg-cyan-transparent {
  background: rgba(0, 188, 212, 0.15);
}

.bg-purple-transparent {
  background: rgba(156, 39, 176, 0.15);
}

.bg-green-transparent {
  background: rgba(76, 175, 80, 0.15);
}

.text-glow {
  text-shadow: 0 0 15px rgba(255, 255, 255, 0.2);
}

.stat-progress {
  width: 30px;
  height: 3px;
  border-radius: 10px;
  opacity: 0.6;
}

/* Cercles décoratifs en arrière-plan */
.bg-circle-1 {
  position: absolute;
  top: -100px;
  right: -100px;
  width: 400px;
  height: 400px;
  background: radial-gradient(circle, rgba(76, 175, 80, 0.05) 0%, transparent 70%);
  border-radius: 50%;
}

.bg-circle-2 {
  position: absolute;
  bottom: -150px;
  left: -150px;
  width: 500px;
  height: 500px;
  background: radial-gradient(circle, rgba(33, 150, 243, 0.05) 0%, transparent 70%);
  border-radius: 50%;
}

/* Optimisation Mobile */
@media (max-width: 600px) {
  .text-h3 {
    font-size: 1.8rem;
  }

  .stat-card {
    padding: 16px 8px;
  }

  .stats-section {
    border-radius: 20px;
    margin: 10px;
  }
}
</style>
