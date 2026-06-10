<template>
  <q-page class="bg-grey-1">
    <div class="container-max q-pa-md">
      <div class="row q-col-gutter-lg">

        <div class="col-12 col-md-3">
          <FilterContent v-model:filters="filters" v-model:expanded-markets="expandedMarkets"
            :markets="navigationStore.markets" :selected-market="selectedMarket" :selected-category="selectedCategory"
            :country-options="locationStore.countryOptions" :state-options="stateOptions" @select-market="selectMarket"
            @select-category="selectCategory" @country-change="onCountryChange" @state-change="onStateChange"
            @apply="applyFilters" @reset="resetFilters" />
        </div>

        <div class="col-12 col-md-9">
          <div class="row items-center justify-between q-mb-md">
            <div class="text-h6 text-weight-bold">{{ pageTitle }}</div>
            <q-select v-model="sortOption" :options="sortOptions" label="Trier par" dense outlined style="width: 160px"
              @update:model-value="applyFilters" emit-value map-options />
          </div>

          <div v-if="productStore.loading" class="row q-col-gutter-md">
            <div v-for="n in 6" :key="n" class="col-6 col-sm-6 col-md-4">
              <q-card flat bordered>
                <q-skeleton height="150px" square />
                <q-card-section>
                  <q-skeleton type="text" class="text-subtitle1" width="60%" />
                  <q-skeleton type="rect" class="q-mt-md" height="30px" />
                </q-card-section>
              </q-card>
            </div>
          </div>

          <div v-else>
            <div class="row q-col-gutter-md">
              <div v-for="product in products" :key="product.id" class="col-6 col-sm-6 col-md-4">
                <ProductCard :product="product" />
              </div>
            </div>

            <div v-if="products.length === 0" class="text-center q-pa-xl text-grey-6">
              <q-icon name="inventory_2" size="64px" />
              <div class="text-h6">Aucun produit trouvé</div>
            </div>

            <div class="flex justify-center q-mt-xl">
              <q-pagination v-model="pagination.current_page" :max="pagination.last_page" :max-pages="3"
                @update:model-value="loadPage" color="primary" flat boundary-links direction-links />
            </div>
          </div>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useProductStore } from 'src/stores/product';
import { useNavigationStore } from 'src/stores/navigation';
import { useLocationStore } from 'src/stores/location';
import ProductCard from 'src/components/market/ProductCard.vue';
import FilterContent from 'src/components/market/FilterContent.vue';

const route = useRoute();
const productStore = useProductStore();
const navigationStore = useNavigationStore();
const locationStore = useLocationStore();

const expandedMarkets = ref(true);
const selectedMarket = ref<string | null>(null);
const selectedCategory = ref<string | null>(null);
const selectedCrop = ref<string | null>(null);
const sortOption = ref('created_at|desc');

const filters = reactive({
  min_price: null as number | null,
  max_price: null as number | null,
  is_featured: false,
  country_id: null as string | null,
  state_id: null as string | null,
});

const stateOptions = computed(() => {
  if (!filters.country_id) return [];
  return locationStore.getStateOptions(filters.country_id);
});

const sortOptions = [
  { label: 'Plus récent', value: 'created_at|desc' },
  { label: 'Plus ancien', value: 'created_at|asc' },
  { label: 'Prix croissant', value: 'unit_price|asc' },
  { label: 'Prix décroissant', value: 'unit_price|desc' },
  { label: 'Popularité', value: 'popularity|desc' },
];

const products = computed(() => productStore.products);
const pagination = computed(() => productStore.pagination);

const pageTitle = computed(() => {
  if (selectedCategory.value) {
    const cat = navigationStore.markets.flatMap(m => m.categories).find(c => c?.id === selectedCategory.value);
    return cat ? `Produits – ${cat.name}` : 'Produits';
  }
  if (selectedMarket.value) {
    const market = navigationStore.markets.find(m => m.id === selectedMarket.value);
    return market ? `Produits – ${market.name}` : 'Produits';
  }
  return 'Produits agricoles';
});

const loadCountries = async () => {
  await locationStore.fetchCountries();
};

const onCountryChange = async () => {
  if (filters.country_id) {
    await locationStore.fetchStatesByCountry(filters.country_id);
  }
  await applyFilters();
};

const onStateChange = async () => {
  await applyFilters();
};

const buildParams = () => {
  const params: Record<string, unknown> = {
    per_page: 12,
    page: pagination.value.current_page
  };

  const [sortBy, sortOrder] = sortOption.value.split('|');
  params.sort_by = sortBy;
  params.sort_order = sortOrder;

  if (filters.min_price) params.min_price = filters.min_price;
  if (filters.max_price) params.max_price = filters.max_price;
  if (filters.is_featured) params.is_featured = true;
  if (filters.country_id) params.country_id = filters.country_id;
  if (filters.state_id) params.state_id = filters.state_id;

  if (selectedCrop.value) params.crop_id = selectedCrop.value;
  else if (selectedCategory.value) params.category_id = selectedCategory.value;
  else if (selectedMarket.value) params.market_id = selectedMarket.value;

  return params;
};

const fetchProducts = async () => {
  await productStore.fetchProducts(buildParams());
};

const applyFilters = async () => {
  pagination.value.current_page = 1;
  await fetchProducts();
};

const loadPage = async (page: number) => {
  pagination.value.current_page = page;
  await fetchProducts();
};

const resetFilters = async () => {
  Object.assign(filters, {
    min_price: null,
    max_price: null,
    is_featured: false,
    country_id: null,
    state_id: null
  });
  selectedMarket.value = null;
  selectedCategory.value = null;
  selectedCrop.value = null;
  await applyFilters();
};

const selectMarket = async (marketId: string) => {
  selectedMarket.value = selectedMarket.value === marketId ? null : marketId;
  selectedCategory.value = null;
  selectedCrop.value = null;
  await applyFilters();
};

const selectCategory = async (categoryId: string) => {
  selectedCategory.value = categoryId;
  selectedCrop.value = null;
  await applyFilters();
};

// Logique de synchronisation Route -> Filtres
const syncRouteToFilters = () => {
  const paramId = route.params.id as string;

  // Reset temporaire des IDs pour éviter les mélanges
  selectedMarket.value = null;
  selectedCategory.value = null;
  selectedCrop.value = null;

  if (paramId) {
    if (route.name === 'products-by-category') {
      selectedCategory.value = paramId;
      const parent = navigationStore.markets.find(m => m.categories?.some(c => c.id === paramId));
      if (parent) selectedMarket.value = parent.id;
    } else if (route.name === 'products-by-market') {
      selectedMarket.value = paramId;
    } else if (route.name === 'products-by-crop') {
      selectedCrop.value = paramId;
    }
  }
};

// Surveille uniquement le changement d'ID dans l'URL
watch(() => route.params.id, async (newId, oldId) => {
  if (newId !== oldId) {
    syncRouteToFilters();
    await applyFilters();
  }
});

onMounted(async () => {
  // Chargement initial des données de base
  await Promise.all([
    navigationStore.fetchNavigationData(),
    loadCountries()
  ]);

  if (filters.country_id) {
    await locationStore.fetchStatesByCountry(filters.country_id);
  }

  // Appliquer la route actuelle et charger les produits
  syncRouteToFilters();
  await fetchProducts();
});
</script>
<style lang="scss" scoped>
.container-max {
  max-width: 1440px;
  margin: 0 auto;
}
</style>
