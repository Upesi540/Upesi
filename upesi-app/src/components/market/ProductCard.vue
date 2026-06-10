<template>
  <q-card v-ripple class="product-card cursor-pointer q-hoverable" @click="$router.push(`/products/${product.id}`)">
    <div class="image-container overflow-hidden">
      <q-img :src="product.images?.[0] || '/images/placeholder.png'" class="product-image">
        <div class="absolute-full gradient-overlay"></div>

        <div class="absolute-top-left q-ma-sm" style="background: transparent !important;">
          <div class="glass-badge">
            <q-icon name="storefront" size="14px" class="q-mr-xs" />
            <span class="shop-name">{{ product.merchant_profile?.shop_name }}</span>
          </div>
        </div>

        <div class="absolute-bottom q-pa-md bg-transparent">
          <div class="crop-category text-uppercase q-mb-xs">
            {{ product.crop?.name }}
            <q-icon name="location_on" /> <!-- 📍 Classique, recommandée -->
            {{ product.location?.country }}
          </div>
          <div class="product-title text-weight-bold">
            {{ product.title }}
            <q-icon name="storefront" size="14px" class="q-mr-xs" />
          </div>
        </div>
      </q-img>
    </div>

    <q-card-section class="price-section row items-center justify-between">
      <div class="col price-col">
        <div class="price-label text-grey-7">Prix unitaire</div>
        <div class="price-value text-weight-bolder text-secondary">
          {{ formatPrice(product.unit_price) }}
        </div>
      </div>

      <div class="action-btn">
        <q-btn round color="primary" icon="arrow_forward" size="sm" unelevated class="arrow-icon" />
      </div>
    </q-card-section>
  </q-card>
</template>

<script setup lang="ts">
import type { Product } from 'src/types';

defineProps<{
  product: Product
}>();

const formatPrice = (price: string | number) => {
  return new Intl.NumberFormat('fr-FR').format(Number(price)) + ' FCFA';
};
</script>

<style lang="scss" scoped>
.product-card {
  border-radius: 24px;
  border: 1px solid rgba(0, 0, 0, 0.05);
  background: white;
  transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
  max-width: 100%;

  &:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1) !important;

    .product-image {
      transform: scale(1.1);
    }

    .arrow-icon {
      transform: translateX(3px);
    }
  }

  &:active {
    transform: scale(0.96);
  }
}

.product-image {
  height: 180px;
  transition: transform 0.6s ease;

  @media (max-width: 600px) {
    height: 160px;
  }
}

.gradient-overlay {
  background: linear-gradient(to bottom, rgba(0, 0, 0, 0) 30%, rgba(0, 0, 0, 0.85) 100%);
}

// --- TEXTES RESPONSIVES ---

.product-title {
  color: white;
  text-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;

  font-size: 1rem;

  @media (max-width: 600px) {
    font-size: .8rem;
  }
}

.crop-category {
  color: rgba(255, 255, 255, 0.8);
  letter-spacing: 1px;
  font-weight: 500;

  font-size: 0.55rem;

  @media (max-width: 600px) {
    font-size: 0.55rem;
  }
}

.shop-name {
  display: block;
  max-width: 140px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-size: 0.6rem;

  @media (max-width: 600px) {
    font-size: 0.7rem;
    max-width: 110px;
  }
}

.price-value {
  font-size: 1rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;

  @media (max-width: 600px) {
    font-size: 0.7rem;
  }
}

.price-section {
  padding: 16px;

  @media (max-width: 600px) {
    padding: 12px;
  }
}

.price-col {
  min-width: 0; // Permet au texte de s'écrémer dans le flex
}

// --- AUTRES STYLES ---

.glass-badge {
  background: var(--q-secondary);
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
  color: white;
  padding: 6px 14px;
  border-radius: 100px;
  border: 1px solid rgba(255, 255, 255, 0.3);
  display: flex;
  align-items: center;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.price-label {
  font-size: 0.65rem;
  text-transform: uppercase;
  letter-spacing: 0.8px;
  font-weight: 700;
}

.arrow-icon {
  transition: transform 0.3s ease;
}
</style>
