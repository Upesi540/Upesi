<template>
  <div class="carousel-wrapper" :class="{ 'mobile-view': $q.screen.lt.md }">
    <!-- SKELETON LOADER -->
    <div v-if="loading" class="custom-carousel skeleton-carousel" :style="{ height: carouselHeight }">
      <div class="skeleton-overlay"></div>
      <div class="full-height flex items-center justify-center">
        <div class="container-content" :class="$q.screen.lt.md ? 'q-px-lg text-center' : 'q-px-xl'">
          <div class="skeleton-badge"></div>
          <div class="skeleton-title"></div>
          <div class="skeleton-description"></div>
          <div class="skeleton-button"></div>
        </div>
      </div>
    </div>

    <!-- CAROUSEL RÉEL -->
    <q-carousel
      v-else
      v-model="slide"
      animated infinite
      :autoplay="5000"
      transition-prev="fade"
      transition-next="fade"
      :height="carouselHeight"
      class="custom-carousel shadow-upesi"
    >
      <q-carousel-slide v-for="s in slides" :key="s.id" :name="s.id" :img-src="s.image_url??''" class="q-pa-none">
        <div class="absolute-full gradient-overlay"></div>

        <div class="full-height flex items-center justify-center">
          <div class="container-content" :class="$q.screen.lt.md ? 'q-px-lg text-center' : 'q-px-xl'">
            <div class="badge-wrapper animate-fade-down">
              <div class="inline-block q-px-md q-py-xs rounded-pill bg-blur-white text-white q-mb-sm">
                <q-icon name="public" size="14px" class="q-mr-xs" color="lime" />
                <span class="text-caption text-weight-bolder">{{ s.title }}</span>
              </div>
            </div>

            <div class="text-white text-weight-bolder q-mb-sm animate-fade-left title-text"
              :class="$q.screen.lt.sm ? 'text-h5' : ($q.screen.lt.md ? 'text-h4' : 'text-h3')">
              {{ s.title.split(' ')[0] }} <span class="text-accent-upesi">{{ s.title.split(' ').slice(1).join(' ') }}</span>
            </div>

            <p class="text-grey-3 q-mb-lg description-text animate-fade-up">
              {{ s.sub_title }}
            </p>

            <q-btn unelevated rounded to="/products" :style="{ backgroundColor: s.button_color, color: s.button_text_color }" :label="s.button_text" class="btn-premium text-weight-bolder"
              :class="$q.screen.lt.sm ? 'q-px-md q-py-xs' : ($q.screen.lt.md ? 'q-px-lg q-py-sm' : 'q-px-xl q-py-md')" no-caps>
              <q-icon name="arrow_forward" size="xs" class="q-ml-sm icon-move" />
            </q-btn>
          </div>
        </div>
      </q-carousel-slide>

      <template v-slot:navigation-icon="{ active, onClick }">
        <q-btn v-if="active" size="6px" icon="circle" color="white" flat round dense @click="onClick" />
        <q-btn v-else size="4px" icon="circle" color="white" flat round dense @click="onClick" style="opacity: 0.4" />
      </template>
    </q-carousel>
  </div>
</template>

<script setup lang="ts">
import type { Slide } from 'src/types';
import { ref, watch, computed } from 'vue';
import { useQuasar } from 'quasar';

const $q = useQuasar();

const props = defineProps<{
  slides: Slide[];
  loading: boolean;
}>();

const slide = ref<number | string | null>(null);

// Hauteur dynamique du carrousel
const carouselHeight = computed(() => {
  if ($q.screen.lt.sm) return '280px';      // Téléphones (moins de 600px)
  if ($q.screen.lt.md) return '350px';      // Tablettes (600px à 1024px)
  return '480px';                            // Desktop (plus de 1024px)
});

// Surveille la fin du chargement pour assigner la première slide
watch(() => props.loading, (isLoading) => {
  if (!isLoading && props.slides && props.slides.length > 0) {
    slide.value = props.slides[0]!.id;
  }
}, { immediate: true });
</script>

<style scoped lang="scss">
.custom-carousel {
  border-radius: 30px;
  overflow: hidden;
}

.gradient-overlay {
  background: radial-gradient(circle at 20% 50%, rgba(0, 0, 0, 0.65) 0%, rgba(0, 0, 0, 0.3) 100%);
}

.mobile-view .gradient-overlay {
  background: linear-gradient(to bottom, rgba(0, 0, 0, 0.2) 0%, rgba(0, 0, 0, 0.8) 100%);
}

.skeleton-carousel {
  position: relative;
  width: 100%;
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
  border-radius: 30px;
  overflow: hidden;
}

.skeleton-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.3);
}

.skeleton-badge {
  width: 120px;
  height: 30px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 20px;
  margin-bottom: 20px;
}

.skeleton-title {
  width: 300px;
  height: 50px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  margin-bottom: 15px;
}

.skeleton-description {
  width: 400px;
  height: 60px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  margin-bottom: 25px;
}

.skeleton-button {
  width: 150px;
  height: 48px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 30px;
}

@keyframes shimmer {
  0% {
    background-position: -1000px 0;
  }
  100% {
    background-position: 1000px 0;
  }
}

.bg-blur-white {
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

.title-text {
  line-height: 1.1;
  letter-spacing: -0.5px;
}

.description-text {
  max-width: 500px;
  font-size: 1.1rem;
  line-height: 1.3;

  @media (max-width: 600px) {
    font-size: 0.9rem;
    margin-bottom: 20px;
  }

  @media (max-width: 400px) {
    font-size: 0.8rem;
    margin-bottom: 16px;
  }
}

.btn-premium {
  transition: all 0.3s ease;

  &:active {
    transform: scale(0.95);
  }

  @media (max-width: 400px) {
    font-size: 12px !important;
  }
}

.container-content {
  width: 100%;
  z-index: 2;
}

.mobile-view .container-content {
  margin-top: auto;
  margin-bottom: 30px;
}

/* Animations */
.animate-fade-up {
  animation: fadeInUp 0.8s both;
}

.animate-fade-left {
  animation: fadeInLeft 0.8s 0.2s both;
}

.animate-fade-down {
  animation: fadeInDown 0.8s 0.1s both;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes fadeInLeft {
  from {
    opacity: 0;
    transform: translateX(-20px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

@keyframes fadeInDown {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
