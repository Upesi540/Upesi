<template>
  <section class="partners-marquee q-py-xl bg-white overflow-hidden">
    <div class="text-center q-mb-lg">
      <span class="text-overline text-grey-6 tracking-widest">ILS NOUS ACCOMPAGNENT</span>
    </div>

    <div class="marquee-wrapper">
      <div class="marquee-content">
        <!-- Duplication pour effet infini -->
        <div v-for="i in 2" :key="i" class="marquee-group">
          <div
            v-for="partner in partners"
            :key="`${i}-${partner.id}`"
            class="partner-item"
            @click="openWebsite(partner.website_url)"
          >
            <q-img :src="partner.logo_url ?? logoUrl" fit="contain" class="marquee-logo" />
            <q-tooltip>{{ partner.name }}</q-tooltip>
          </div>
        </div>
      </div>
    </div>

    <div class="flex flex-center q-mt-lg">
      <!-- <q-btn flat dense no-caps color="grey-7" label="Devenir partenaire →" class="text-weight-bold" /> -->
    </div>
  </section>
</template>

<script setup lang="ts">
import type { Partner } from 'src/types';
import logoUrl from 'src/assets/logo.png';

defineProps<{ partners: Partner[] }>();

const openWebsite = (url: string | null) => {
  if (url) {
    // window.open(url, '_blank');
  }
};
</script>

<style lang="scss" scoped>
.partners-marquee {
  border-top: 1px solid #f5f5f5;
  border-bottom: 1px solid #f5f5f5;
}

.tracking-widest {
  letter-spacing: 0.2em;
}

.marquee-wrapper {
  display: flex;
  user-select: none;
  mask-image: linear-gradient(to right,
      hsl(0 0% 0% / 0),
      hsl(0 0% 0% / 1) 10%,
      hsl(0 0% 0% / 1) 90%,
      hsl(0 0% 0% / 0));
}

.marquee-content {
  display: flex;
  flex-shrink: 0;
  min-width: 100%;
  animation: scroll-left 30s linear infinite;
}

.marquee-wrapper:hover .marquee-content {
  animation-play-state: paused;
}

.marquee-group {
  display: flex;
  flex-shrink: 0;
  align-items: center;
  justify-content: space-around;
  min-width: 100%;
  // Espacement réduit entre les logos
  gap: 2rem; // 32px au lieu de 4rem
}

.partner-item {
  cursor: pointer;
  transition: transform 0.3s ease, filter 0.3s ease;
  // filter: grayscale(100%);
  opacity: 0.6;
  flex-shrink: 0;
  // Supprimer les marges individuelles
  margin: 0;

  &:hover {
    filter: grayscale(0%);
    opacity: 1;
    transform: scale(1.05);
  }
}

.marquee-logo {
  width: 100px; // légèrement réduit
  height: 50px;
  object-fit: contain;
}

@keyframes scroll-left {
  from {
    transform: translateX(0);
  }
  to {
    transform: translateX(-100%);
  }
}

// Mobile : espacement encore plus réduit
@media (max-width: 767px) {
  .marquee-group {
    gap: 1rem; // 16px au lieu de 2rem
  }
  .marquee-logo {
    width: 70px;
    height: 40px;
  }
}
</style>
