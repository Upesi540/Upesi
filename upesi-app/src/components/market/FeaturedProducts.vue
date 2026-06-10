<template>
  <section class="featured-products q-py-lg q-py-md-xl bg-white">
    <div class="container-max q-px-md">

      <div class="row items-end justify-between q-mb-lg q-mb-md-xl">
        <div class="col-12 col-md-auto q-mb-md q-mb-md-none text-left">
          <h2 class="text-h5 text-md-h4 text-weight-bolder text-grey-10 q-ma-none flex items-center">
            Offres Vedettes
            <q-icon name="local_fire_department" color="orange-5" size="1em" class="q-ml-sm" />
          </h2>
          <p class="text-grey-7 q-mt-sm q-mb-none text-body2">
            Les meilleures opportunités de gros sélectionnées pour vous.
          </p>
        </div>
        <div class="col-12 col-sm-auto q-mt-md q-mt-sm-none">
          <q-btn to="/products" color="primary" label="Explorer tout le marché" icon-right="arrow_forward" rounded
            unelevated no-caps class="px-lg shadow-sm" />
        </div>
      </div>

      <div v-if="loading" class="row q-col-gutter-md">
        <div v-for="n in 4" :key="n" class="col-6 col-sm-6 col-md-3 col-lg-3">
          <q-card flat class="product-card skeleton-card">
            <q-skeleton height="180px" square />
            <q-card-section>
              <q-skeleton type="text" width="60%" class="q-mb-sm" />
              <q-skeleton type="rect" height="40px" />
            </q-card-section>
          </q-card>
        </div>
      </div>

      <div v-else-if="products && products.length > 0" class="row q-col-gutter-md">
        <div v-for="product in products" :key="product.id" class="col-6 col-sm-6 col-md-3 col-lg-3">
          <ProductCard :product="product" />
        </div>
      </div>

      <div v-else class="column flex-center q-pa-xl text-center border-dashed rounded-xl bg-grey-1">
        <q-icon name="search_off" size="48px" color="grey-4" />
        <div class="text-subtitle1 text-grey-7 q-mt-md">Aucune offre vedette disponible.</div>
      </div>

    </div>
  </section>
</template>

<script setup lang="ts">
import type { Product } from 'src/types/product';
import ProductCard from './ProductCard.vue';

defineProps<{
  products: Product[],
  loading: boolean
}>();

</script>

<style lang="scss" scoped>
.container-max {
  max-width: 1280px;
  margin: 0 auto;
}
</style>
