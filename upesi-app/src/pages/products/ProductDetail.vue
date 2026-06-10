<template>
  <q-page class="bg-grey-2 q-pb-xl">
    <q-pull-to-refresh @refresh="refreshPage" color="primary" bg-color="white" icon="refresh">

      <div v-if="productStore.loadingProduct" class="container q-mx-auto q-px-md q-py-lg">
        <div class="row q-col-gutter-xl">
          <div class="col-12 col-md-7">
            <q-skeleton height="450px" square class="border-radius-lg shadow-sm" animation="wave" />
            <div class="row q-gutter-sm q-mt-md justify-center">
              <q-skeleton v-for="i in 3" :key="i" size="60px" square class="border-radius-md" />
            </div>
            <div class="q-mt-lg gt-sm">
              <q-skeleton type="text" width="30%" class="q-mb-sm" />
              <q-skeleton height="150px" class="border-radius-lg" />
            </div>
          </div>
          <div class="col-12 col-md-5">
            <q-card flat class="q-pa-lg border-radius-lg bg-white">
              <q-skeleton type="QBadge" class="q-mb-sm" />
              <q-skeleton type="text" class="text-h4 q-mb-sm" />
              <q-skeleton type="text" width="40%" class="q-mb-lg" />
              <q-skeleton height="100px" class="q-mb-lg border-radius-lg" />
              <q-skeleton height="50px" class="border-radius-md" />
            </q-card>
          </div>
        </div>
      </div>

      <div v-else-if="product" class="container q-mx-auto q-px-md q-py-lg">
        <div class="row q-mb-md gt-xs">
          <q-breadcrumbs class="text-grey-6" active-color="primary">
            <q-breadcrumbs-el icon="home" to="/" />
            <q-breadcrumbs-el :label="product.crop?.name || 'Bourse'" />
            <q-breadcrumbs-el :label="product.title" class="text-weight-bold" />
          </q-breadcrumbs>
        </div>

        <div class="row q-col-gutter-lg">
          <div class="col-12 col-md-7">
            <q-card flat class="main-gallery-card bg-white shadow-sm overflow-hidden border-light border-radius-lg">
              <q-img :src="selectedImage || product.images[0]" :alt="product.title" class="main-product-img"
                fit="cover">
                <div v-if="isAlreadyInCart" class="absolute-top-right q-pa-sm">
                  <q-badge color="secondary" class="q-pa-sm shadow-2 text-weight-bold">
                    <q-icon name="shopping_cart_checkout" class="q-mr-xs" /> DÉJÀ AU PANIER
                  </q-badge>
                </div>
              </q-img>

              <div v-if="product.images.length > 1" class="row q-gutter-sm justify-center q-py-md bg-grey-1">
                <q-img v-for="(img, idx) in product.images" :key="idx" :src="img"
                  class="thumb-img cursor-pointer transition-03 shadow-sm border-radius-md"
                  :class="{ 'thumb-active': (selectedImage || product.images[0]) === img }"
                  @click="selectedImage = img" />
              </div>
            </q-card>

            <div class="q-mt-lg gt-sm">
              <div class="text-subtitle1 text-weight-bold q-mb-sm text-primary">Description détaillée</div>
              <div class="description-text q-pa-md bg-white rounded-borders border-light shadow-xs">
                {{ product.description }}
              </div>
            </div>
          </div>

          <div class="col-12 col-md-5">
            <div class="sticky-column">
              <q-card flat class="q-pa-lg shadow-sm border-radius-lg bg-white border-light">
                <div class="row items-center q-mb-sm">
                  <q-chip outline color="green-8" icon="verified" size="sm" class="text-weight-bold uppercase">Produit
                    local</q-chip>
                  <q-space />
                  <q-badge :color="parseInt(product.quantity) > 0 ? 'positive' : 'negative'" rounded class="q-mr-xs" />
                  <span class="text-caption text-weight-medium text-grey-7">{{ product.quantity }} {{
                    product.unit.symbol }} en stock</span>
                </div>

                <h1 class="product-title q-mt-none q-mb-xs text-weight-bold">{{ product.title }}</h1>
                <div class="text-caption text-grey-6 q-mb-md">Vendeur : <span class="text-primary text-weight-bold">{{
                  product.user.full_name }}</span></div>

                <div
                  class="price-hero-modern q-pa-md q-mb-lg flex items-center justify-between bg-green-1 border-radius-md">
                  <div class="column">
                    <div class="responsive-price text-weight-bolder text-green-10">
                      {{ formatPrice(product.unit_price) }}
                    </div>
                    <div class="text-caption text-green-8 text-uppercase text-weight-bold">par {{ product.unit.symbol }}
                    </div>
                  </div>
                </div>

                <div class="gt-sm">
                  <div class="text-weight-bold q-mb-sm text-grey-8">Quantité souhaitée</div>
                  <div class="row q-col-gutter-md items-center q-mb-lg">
                    <div class="col-6">
                      <q-input v-model.number="quantity" type="number" outlined dense class="custom-qty-input bg-grey-1"
                        @blur="validateQuantity">
                        <template v-slot:prepend>
                          <q-btn flat round dense icon="remove" size="sm" @click="decrement" />
                        </template>
                        <template v-slot:append>
                          <q-btn flat round dense icon="add" size="sm" @click="increment" />
                        </template>
                      </q-input>
                    </div>
                    <div class="col-6 text-right">
                      <div class="text-caption text-grey-7">Total estimé</div>
                      <div class="text-h6 text-weight-bold text-primary">{{ formatPrice(quantity *
                        Number(product.unit_price)) }}</div>
                    </div>
                  </div>

                  <q-btn unelevated color="primary" class="full-width q-py-md btn-main-cta shadow-2 border-radius-md"
                    @click="handleAddToCart">
                    <q-icon name="add_shopping_cart" class="q-mr-sm" />
                    {{ isAlreadyInCart ? 'AJOUTER PLUS' : 'AJOUTER AU PANIER' }}
                  </q-btn>
                </div>

                <div class="q-mt-lg q-pt-lg border-top-light">
                  <div class="row q-col-gutter-sm">
                    <div class="col-6">
                      <div class="info-tag bg-blue-grey-1 q-pa-sm rounded-borders flex items-center">
                        <q-icon name="location_on" color="primary" class="q-mr-xs" />
                        <span class="text-caption text-weight-medium">{{ product.location.city + ", " +
                          product.location.country
                          }}</span>
                      </div>
                    </div>
                    <div class="col-6">
                      <div class="info-tag bg-blue-grey-1 q-pa-sm rounded-borders flex items-center">
                        <q-icon name="shopping_bag" color="primary" class="q-mr-xs" />
                        <span class="text-caption text-weight-medium">Min: {{ product.min_order_quantity }} {{
                          product.unit.symbol
                        }}</span>
                      </div>
                    </div>
                  </div>
                </div>
              </q-card>
            </div>
          </div>
        </div>

        <section class="q-mt-xl">
          <div class="row items-center q-mb-md">
            <div class="text-h6 text-weight-bold">Offres similaires</div>
            <q-space />
            <q-btn flat color="primary" label="Voir plus" no-caps dense />
          </div>

          <div v-if="productStore.loadingSimilar" class="row q-col-gutter-md">
            <div v-for="i in 4" :key="i" class="col-6 col-sm-3">
              <q-skeleton height="200px" square class="border-radius-lg" />
            </div>
          </div>
          <div v-else class="row q-col-gutter-md">
            <div v-for="sim in similarProducts" :key="sim.id" class="col-6 col-sm-3 col-md-3">
              <ProductCard :product="sim" />
            </div>
          </div>
        </section>
      </div>

      <q-footer v-if="product && !productStore.loadingProduct" class="bg-white lt-md q-pa-md shadow-up-10" bordered>
        <div class="row q-col-gutter-md items-center no-wrap">
          <div class="col-5">
            <q-input v-model.number="quantity" type="number" outlined dense hide-bottom-space
              class="mobile-qty-field bg-grey-1" @blur="validateQuantity">
              <template v-slot:prepend>
                <q-btn flat round dense icon="remove" size="xs" @click="decrement" color="primary" />
              </template>
              <template v-slot:append>
                <q-btn flat round dense icon="add" size="xs" @click="increment" color="primary" />
              </template>
            </q-input>
          </div>

          <div class="col-7">
            <q-btn unelevated color="primary" class="full-width q-py-sm border-radius-md" @click="handleAddToCart"
              no-caps>
              <div class="column items-center">
                <span class="text-weight-bold">{{ isAlreadyInCart ? '+ Ajouter encore' : 'Ajouter au panier' }}</span>
                <span class="text-caption" style="opacity: 0.9">{{ formatPrice(quantity * Number(product.unit_price))
                }}</span>
              </div>
            </q-btn>
          </div>
        </div>
      </q-footer>
    </q-pull-to-refresh>
  </q-page>
</template>


<script setup lang="ts">
import { computed, ref, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProductStore } from 'src/stores/product'
import { useCartStore } from 'src/stores/cart'
import { useQuasar } from 'quasar'
import ProductCard from 'src/components/market/ProductCard.vue'

const route = useRoute()
const $router = useRouter()
const $q = useQuasar()
const productStore = useProductStore()
const cartStore = useCartStore()

const product = computed(() => productStore.currentProduct)
const similarProducts = computed(() => productStore.similarProducts)
const quantity = ref<number>(1)
const selectedImage = ref<string | null>(null)

const minOrder = computed(() => product.value ? Number(product.value.min_order_quantity) : 1)
const maxAvailable = computed(() => product.value ? Number(product.value.quantity) : 99999)

const isAlreadyInCart = computed(() =>
  cartStore.items.some(item => String(item.productId) === String(product.value?.id))
)

// Fonctions de contrôle de quantité
const increment = () => { if (quantity.value < maxAvailable.value) quantity.value++ }
const decrement = () => { if (quantity.value > minOrder.value) quantity.value-- }
const validateQuantity = () => {
  if (quantity.value < minOrder.value) quantity.value = minOrder.value
  if (quantity.value > maxAvailable.value) quantity.value = maxAvailable.value
}

watch(product, (newVal) => {
  if (newVal) {
    quantity.value = Number(newVal.min_order_quantity)
    selectedImage.value = newVal.images[0] ?? null
  }
}, { immediate: true })

watch(() => route.params.id as string, async (newId) => {
  if (newId) {
    productStore.currentProduct = null
    await Promise.all([
      productStore.fetchProductById(newId),
      productStore.fetchSimilar(newId)
    ])
    window.scrollTo(0, 0)
  }
})

const formatPrice = (price: string | number) =>
  new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF', maximumFractionDigits: 0 }).format(Number(price))

const handleAddToCart = () => {
  if (!product.value) return

  cartStore.addItem({
    productId: product.value.id,
    quantity: quantity.value,
    price: Number(product.value.unit_price),
    title: product.value.title,
    image: product.value.images[0] || null,
    unit: product.value.unit.symbol
  })

  $q.notify({
    message: 'Produit ajouté !',
    color: 'positive',
    icon: 'shopping_cart',
    position: 'top',
    actions: [
      {
        label: 'VOIR LE PANIER', color: 'white', handler: () => {
          $router.push('/cart').catch(() => { });
        }
      }
    ]
  })

}
const refreshPage = async (done: () => void) => {
  try {
    const id = route.params.id as string

    // On force le rafraîchissement de tous les stores nécessaires
    await Promise.allSettled([
      productStore.fetchProductById(id), productStore.fetchSimilar(id)
    ]);
  } catch (error) {
    console.error('Erreur lors du rafraîchissement:', error);
  } finally {
    // On cache l'icône de chargement de Quasar
    done();
  }
};
onMounted(async () => {
  const id = route.params.id as string
  await Promise.allSettled([productStore.fetchProductById(id), productStore.fetchSimilar(id)])
})
</script>

<style lang="scss" scoped>
.container {
  max-width: 1100px;
}

// Typographie adaptive
.product-title {
  font-size: clamp(1.5rem, 4vw, 2.2rem);
  font-weight: 800;
  line-height: 1.2;
}

.responsive-price {
  font-size: clamp(1.4rem, 5vw, 2.5rem);
  line-height: 1;
}

// Cards & Layout
.main-gallery-card {
  border-radius: 16px;

  .main-product-img {
    height: 500px;

    @media (max-width: 599px) {
      height: 300px;
    }
  }
}

.price-hero-modern {
  background: #f1fdf3;
  border-radius: 12px;
  border: 1px dashed #2e7d32;
}

// Inputs (Le coeur de la correction UX)
.custom-qty-input,
.mobile-qty-field {
  :deep(.q-field__control) {
    border-radius: 8px;
    background: #f5f5f5;
    padding: 0 4px;

    &:before {
      border: none;
    }
  }

  :deep(input) {
    text-align: center;
    font-weight: bold;
    font-size: 1.1rem;
    -moz-appearance: textfield;
  }
}

// Boutons
.btn-main-cta {
  border-radius: 12px;
  height: 56px;
  font-weight: bold;
}

.btn-mobile-action {
  border-radius: 10px;
  height: 50px;

  .btn-label {
    font-weight: 800;
    font-size: 0.9rem;
    line-height: 1;
  }

  .btn-sub-price {
    font-size: 0.75rem;
    opacity: 0.9;
  }
}

.small-stock {
  font-size: 0.75rem;
  color: #666;
}

.border-top-light {
  border-top: 1px solid #eee;
}

.info-tag {
  font-size: 0.85rem;
  display: flex;
  align-items: center;
  gap: 4px;
}

.description-text {
  white-space: pre-line;
  color: #444;
  line-height: 1.6;
}

.thumb-img {
  width: 60px;
  height: 60px;
  border-radius: 8px;

  &.thumb-active {
    border: 2px solid var(--q-primary);
  }
}

//last
.container {
  max-width: 1200px;
}

.border-radius-lg {
  border-radius: 16px;
}

.border-radius-md {
  border-radius: 8px;
}

.border-light {
  border: 1px solid rgba(0, 0, 0, 0.05);
}

.border-top-light {
  border-top: 1px solid rgba(0, 0, 0, 0.05);
}

.thumb-active {
  border: 2px solid var(--q-primary);
  transform: scale(1.05);
}

.price-hero-modern {
  border-left: 4px solid var(--q-green-8);
}

.main-product-img {
  height: 450px;
}

@media (max-width: 600px) {
  .main-product-img {
    height: 300px;
  }

  .responsive-price {
    font-size: 1.5rem;
  }
}

.sticky-column {
  position: sticky;
  top: 20px;
}
</style>
