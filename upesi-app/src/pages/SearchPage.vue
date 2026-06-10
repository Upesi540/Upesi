<template>
  <q-page class="bg-grey-2">
    <!-- Barre de recherche moderne -->
    <div class="search-header q-pa-md">
      <div class="row items-center">
        <q-btn flat round dense icon="arrow_back" @click="$router.back()" class="text-white" />
        <div class="col q-ml-sm">
          <q-input
            ref="searchInputRef"
            v-model="searchQuery"
            placeholder="Que cherchez-vous ?"
            outlined
            rounded
            dense
            bg-color="white"
            color="primary"
            class="search-input-modern"
            @keyup.enter="performSearch"
            @input="onSearchInput"
          >
            <template #append>
              <q-btn
                flat
                round
                icon="search"
                @click="performSearch"
                :loading="store.isLoading"
                class="search-btn-modern"
              />
            </template>
          </q-input>
        </div>
      </div>
    </div>

    <!-- Suggestions (autocomplete) -->
    <q-list v-if="store.suggestions && store.suggestions.length > 0 && !hasSearched" class="bg-white q-pa-sm" separator>
      <q-item
        v-for="suggestion in store.suggestions"
        :key="`${suggestion.type}-${suggestion.id}`"
        clickable
        v-close-popup
        @click="selectSuggestion(suggestion)"
      >
        <q-item-section avatar>
          <q-img
            :src="suggestion.image || '/icons/favicon-128x128.png'"
            style="width: 40px; height: 40px"
            fit="cover"
            class="rounded-10"
          />
        </q-item-section>
        <q-item-section>
          <q-item-label>{{ suggestion.title }}</q-item-label>
          <q-item-label caption>
            {{ suggestion.type === 'product' ? 'Produit' : 'Service' }}
          </q-item-label>
        </q-item-section>
      </q-item>
    </q-list>

    <!-- Chargement initial (avant tout résultat) -->
    <div
      v-if="store.isLoading && (!store.results || (!store.results.products?.data?.length && !store.results.services?.data?.length))"
      class="flex flex-center q-pa-xl"
    >
      <q-spinner color="primary" size="40px" />
      <div class="q-ml-sm">Recherche en cours...</div>
    </div>

    <!-- Résultats -->
    <div v-else-if="store.results" class="q-pa-md">
      <!-- Onglets -->
      <q-tabs v-model="activeTab" class="bg-white rounded-borders q-mb-md" dense>
        <q-tab name="all" label="Tous" :badge="totalResults" />
        <q-tab name="products" label="Produits" :badge="productsCount" />
        <q-tab name="services" label="Services" :badge="servicesCount" />
      </q-tabs>

      <q-tab-panels v-model="activeTab" animated>
        <!-- Onglet Tous -->
        <q-tab-panel name="all" class="q-pa-none">
          <div v-if="store.results.products?.data?.length" class="q-mb-md">
            <div class="row items-center justify-between q-mb-sm">
              <div class="text-subtitle1 text-weight-bold">Produits</div>
              <q-btn flat dense label="Voir plus" @click="activeTab = 'products'" />
            </div>
            <div class="row q-col-gutter-md">
              <div v-for="product in store.results.products.data.slice(0, 4)" :key="product.id" class="col-12 col-sm-6 col-md-3">
                <ProductCard :product="product" />
              </div>
            </div>
          </div>

          <div v-if="store.results.services?.data?.length" class="q-mt-lg">
            <div class="row items-center justify-between q-mb-sm">
              <div class="text-subtitle1 text-weight-bold">Services</div>
              <q-btn flat dense label="Voir plus" @click="activeTab = 'services'" />
            </div>
            <div class="row q-col-gutter-md">
              <div v-for="service in store.results.services.data.slice(0, 4)" :key="service.id" class="col-12 col-sm-6 col-md-3">
                <ServiceOfferCard :offer="service" />
              </div>
            </div>
          </div>

          <div v-if="!store.results.products?.data?.length && !store.results.services?.data?.length" class="text-center q-pa-xl">
            <q-icon name="search_off" size="60px" color="grey-5" />
            <div class="text-h6 text-grey-7 q-mt-md">Aucun résultat trouvé</div>
            <div class="text-caption text-grey-6">Essayez d'autres mots-clés</div>
          </div>
        </q-tab-panel>

        <!-- Onglet Produits -->
        <q-tab-panel name="products" class="q-pa-none">
          <div v-if="store.results.products?.data?.length" class="row q-col-gutter-md">
            <div v-for="product in store.results.products.data" :key="product.id" class="col-12 col-sm-6 col-md-3">
              <ProductCard :product="product" />
            </div>
          </div>
          <div v-else class="text-center q-pa-xl">
            <q-icon name="search_off" size="60px" color="grey-5" />
            <div class="text-h6 text-grey-7">Aucun produit trouvé</div>
          </div>

          <!-- Pagination produits -->
          <div v-if="store.results.products && store.results.products.last_page > 1" class="flex justify-center q-mt-md">
            <q-pagination v-model="productsPage" :max="store.results.products.last_page" direction-links @update:model-value="loadProductsPage" />
          </div>
        </q-tab-panel>

        <!-- Onglet Services -->
        <q-tab-panel name="services" class="q-pa-none">
          <div v-if="store.results.services?.data?.length" class="row q-col-gutter-md">
            <div v-for="service in store.results.services.data" :key="service.id" class="col-12 col-sm-6 col-md-4">
              <ServiceOfferCard :offer="service" />
            </div>
          </div>
          <div v-else class="text-center q-pa-xl">
            <q-icon name="search_off" size="60px" color="grey-5" />
            <div class="text-h6 text-grey-7">Aucun service trouvé</div>
          </div>

          <!-- Pagination services -->
          <div v-if="store.results.services && store.results.services.last_page > 1" class="flex justify-center q-mt-md">
            <q-pagination v-model="servicesPage" :max="store.results.services.last_page" direction-links @update:model-value="loadServicesPage" />
          </div>
        </q-tab-panel>
      </q-tab-panels>
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted, nextTick } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useSearchStore } from 'src/stores/search';
import ProductCard from 'src/components/market/ProductCard.vue';
import ServiceOfferCard from 'src/components/market/ServiceOfferCard.vue';

const router = useRouter();
const route = useRoute();
const store = useSearchStore();

const searchQuery = ref('');
const activeTab = ref<'all' | 'products' | 'services'>('all');
const productsPage = ref(1);
const servicesPage = ref(1);
const hasSearched = ref(false);
const searchInputRef = ref<{ focus: () => void } | null>(null);

const totalResults = computed(() => {
  const productTotal = store.results?.products?.total || 0;
  const serviceTotal = store.results?.services?.total || 0;
  return productTotal + serviceTotal;
});

const productsCount = computed(() => store.results?.products?.total || 0);
const servicesCount = computed(() => store.results?.services?.total || 0);

// Charger la recherche depuis l'URL
const loadFromUrl = async () => {
  const q = route.query.q as string;
  if (q) {
    searchQuery.value = q;
    await performSearch();
  }
};

// Exécuter la recherche
const performSearch = async () => {
  if (!searchQuery.value.trim()) return;

  hasSearched.value = true;
  productsPage.value = 1;
  servicesPage.value = 1;

  await router.replace({ query: { q: searchQuery.value } });
  await store.search(searchQuery.value, activeTab.value);
};

// Recharger la page produits avec pagination
const loadProductsPage = async () => {
  if (!searchQuery.value) return;
  await store.search(searchQuery.value, 'products', 20, productsPage.value);
};

// Recharger la page services avec pagination
const loadServicesPage = async () => {
  if (!searchQuery.value) return;
  await store.search(searchQuery.value, 'services', 20, servicesPage.value);
};

// Saisie utilisateur pour suggestions
const onSearchInput = async () => {
  if (searchQuery.value.length >= 2) {
    await store.fetchSuggestions(searchQuery.value);
  } else {
    store.suggestions = [];
  }
};

// Sélection d'une suggestion
const selectSuggestion = async (suggestion: { id: string; title: string; type: string }) => {
  searchQuery.value = suggestion.title;
  store.suggestions = [];
  await performSearch();
};

// Changer d'onglet
watch(activeTab, async (newTab) => {
  if (!searchQuery.value) return;
  productsPage.value = 1;
  servicesPage.value = 1;
  await store.search(searchQuery.value, newTab);
});

onMounted(async () => {
  await nextTick(() => {
    searchInputRef.value?.focus();
  });
  await loadFromUrl();
});
</script>

<style scoped lang="scss">
.search-header {
  background: linear-gradient(135deg, #1a5f2a, #0e3a1a);
}

.search-input-modern {
  :deep(.q-field__control) {
    border-radius: 48px;
    padding: 2px 8px;
    background: white;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: box-shadow 0.2s;

    &:focus-within {
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
    }
  }

  :deep(.q-field__native) {
    font-size: 0.95rem;
    padding-left: 12px;
  }
}

.search-btn-modern {
  background: white;
  margin-right: 4px;

  &:hover {
    background: #f5f5f5;
  }
}

.rounded-10 {
  border-radius: 10px;
}
</style>