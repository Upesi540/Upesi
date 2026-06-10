<template>
  <section class="crops-section q-py-lg q-py-md-xl bg-grey-1">
    <div class="container-max">

      <div class="q-px-md q-mb-md">
          <h2 class="text-h5 text-md-h4 text-weight-bolder text-grey-10 q-ma-none flex items-center">
          Cultures Populaires
          <div class="trending-icon-container q-ml-sm">
            <q-icon name="trending_up" color="green-6" size="0.8em" />
          </div>
        </h2>
        <p class="text-grey-7 q-mt-xs q-mb-none text-body2 text-md-body1">
          Les spéculations les plus suivies par les acteurs du marché.
        </p>
      </div>

      <q-carousel
        v-model="slide"
        infinite
        swipeable
        :autoplay="3000"
        animated
        transition-prev="slide-right"
        transition-next="slide-left"
        height="auto"
        class="bg-transparent overflow-visible custom-carousel"
        control-color="primary"
        arrows
      >
        <q-carousel-slide
          v-for="(group, gIndex) in groupedCrops"
          :key="gIndex"
          :name="gIndex"
          class="row no-wrap q-px-md flex-center carousel-slide-custom"
        >
          <div v-for="crop in group" :key="crop.id" class="crop-col">
            <q-card @click="$router.push(`/products/crop/${crop.id}`)" flat class="crop-card column items-center justify-center q-pa-md">
              <div class="icon-wrapper q-mb-sm">
                <span class="emoji-icon">{{ getCropEmoji(crop.name) }}</span>
              </div>

              <div class="text-subtitle2 text-weight-bold text-grey-10 text-center line-clamp-1">
                {{ crop.name }}
              </div>
              <div class="text-caption text-grey-6 text-center ellipsis scientific-text">
                {{ crop.scientific_name || 'Specie' }}
              </div>

              <div class="variety-count q-mt-sm">
                {{ Array.isArray(crop.variety) ? crop.variety.length : 0 }} variétés
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
import type { Crop } from 'src/types';

const props = defineProps<{ crops: Crop[] }>();
const slide = ref(0);
const $q = useQuasar();

// On groupe par 2 sur mobile et 5 sur desktop
const groupedCrops = computed(() => {
  const size = $q.screen.lt.md ? 2 : 5;
  const result = [];
  for (let i = 0; i < props.crops.length; i += size) {
    result.push(props.crops.slice(i, i + size));
  }
  return result;
});

const getCropEmoji = (name: string) => {
  const emojis: Record<string, string> = {
    'Maïs': '🌽', 'Riz': '🍚', 'Mil': '🌾', 'Sorgho': '🥣', 'Fonio': '🥣',
    'Blé': '🍞', 'Orge': '🍺', 'Haricot': '🫘', 'Niébé': '🫛', 'Arachide': '🥜',
    'Soja': '🌱', 'Pois': '🫛', 'Manioc': '🪵', 'Yam': '🥔'
  };
  return emojis[name] || '🌿';
};
</script>

<style lang="scss" scoped>
.container-max {
  max-width: 1440px; /* Un peu plus large pour 5 cartes */
  margin: 0 auto;
}

/* Gestion du 5 par ligne (Desktop) et 2 par ligne (Mobile) */
.crop-col {
  padding: 8px;
  width: 50%; /* 2 par ligne sur mobile */

  @media (min-width: 1024px) {
    width: 20%; /* 5 par ligne sur desktop */
  }
}

.carousel-slide-custom {
  padding-bottom: 40px;
  min-height: 220px;
  @media (min-width: 1024px) { min-height: 300px; }
}

.crop-card {
  border-radius: 28px;
  background: white;
  height: 100%;
  min-height: 180px;
  border: 1px solid #edf2f7;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
  cursor: grab; /* Indique qu'on peut tirer la carte */

  &:active { cursor: grabbing; }

  @media (min-width: 1024px) {
    min-height: 220px;
    padding: 24px !important;
  }

  &:hover {
    transform: translateY(-8px);
    border-color: var(--q-primary);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.08);
  }
}

.icon-wrapper {
  width: 54px;
  height: 54px;
  background: #f1f8e9;
  border-radius: 18px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.8rem;

  @media (min-width: 1024px) {
    width: 70px;
    height: 70px;
    font-size: 2.2rem;
  }
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

.variety-count {
  font-size: 0.75rem;
  font-weight: 800;
  color: var(--q-primary);
  background: rgba(var(--q-primary), 0.1);
  padding: 4px 12px;
  border-radius: 20px;
}

.scientific-text { font-size: 0.7rem; opacity: 0.6; }
.line-clamp-1 { display: -webkit-box; -webkit-line-clamp: 1; -webkit-box-orient: vertical; overflow: hidden; }

/* Rendre le carousel "Grabable" visuellement */
.custom-carousel {
  cursor: grab;
  &:active { cursor: grabbing; }
}
</style>
