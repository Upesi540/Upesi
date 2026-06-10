<template>
  <div class="q-py-sm bg-white overflow-hidden">
    <div class="q-px-md q-mb-xs text-weight-bold text-grey-9"
      :class="$q.screen.xs ? 'text-subtitle2' : 'text-subtitle1'">
      Nos Marchés
    </div>

    <div class="row items-start no-wrap-mobile" :class="[
      $q.screen.xs ? 'justify-start scroll-x-mobile q-px-md' : 'justify-center wrap q-px-sm q-col-gutter-md'
    ]">
      <div v-for="market in markets" :key="market.id" class="market-col column items-center cursor-pointer market-item"
        :class="{
          'col-auto q-mr-md': $q.screen.xs,
          'col-sm-3 col-md-2': !$q.screen.xs
        }" @click="$router.push('/market/' + market.id)">
        <q-avatar :size="$q.screen.xs ? '56px' : '85px'" class="market-bubble shadow-1">
          <q-img :src="market.image" img-class="icon-padding" />
        </q-avatar>

        <div class="text-weight-medium text-grey-8 text-center q-mt-xs market-label"
          :class="$q.screen.xs ? 'text-tiny' : 'text-subtitle2'">
          {{ market.name }}
        </div>
      </div>

      <div v-if="$q.screen.xs" class="q-pr-xs" style="min-width: 1px"></div>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Market } from 'src/types';

defineProps<{ markets: Market[] }>()
</script>

<style scoped lang="scss">
/* Empêche le retour à la ligne sur mobile */
.no-wrap-mobile {
  @media (max-width: 599px) {
    display: flex;
    flex-wrap: nowrap;
  }
}

.scroll-x-mobile {
  overflow-x: auto;
  overflow-y: hidden;
  padding-top: 4px;
  padding-bottom: 12px;
  -webkit-overflow-scrolling: touch;

  /* Masquage scrollbar */
  &::-webkit-scrollbar {
    display: none;
  }
}

.text-tiny {
  font-size: 0.72rem;
  line-height: 1.1;
  font-weight: 600;
}

.market-label {
  max-width: 70px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.market-bubble {
  border: 1.5px solid #f2f2f2;
  transition: all 0.3s ease;
  background: #ffffff;
}

.market-item:active {
  transform: scale(0.95);
}

:deep(.icon-padding) {
  padding: 12px;
}

.market-col {
  min-width: 68px;
}

@media (min-width: 600px) {
  .market-col {
    min-width: 110px;
  }

  :deep(.icon-padding) {
    padding: 18px;
  }
}
</style>
