<template>
  <q-page class="bg-blue-grey-1">
    <div class="bg-primary text-white q-pa-xl flex flex-center">
      <div class="text-center">
        <h1 class="text-h3 text-weight-bold q-mb-xs">Prestations & Logistique</h1>
        <p class="text-subtitle1 opacity-80">Trouvez les meilleurs experts pour vos travaux agricoles</p>
      </div>
    </div>

    <div class="container-max q-pa-md">
      <div class="text-center">
        <h1 class="text-h3  text-capitalize text-weight-bold q-mb-xs">
          {{ pageTitle }}
        </h1>
        <p class="text-subtitle1 text-capitalize opacity-80">{{ pageSubtitle }}</p>
      </div>
      <div class="row q-col-gutter-lg">

        <div class="col-12 col-md-3">
          <q-card flat bordered class="rounded-borders sticky-filters">
            <q-card-section class="flex justify-between items-center">
              <div class="text-subtitle1 text-weight-bold">Filtres</div>
              <q-btn flat round dense icon="tune" color="primary" />
            </q-card-section>

            <q-separator />

            <q-card-section class="q-gutter-y-md">
              <div>
                <div class="text-caption text-grey-7 q-mb-sm">Catégorie</div>
                <q-list dense>
                  <q-item v-for="cat in categories" :key="cat.id" clickable v-ripple
                    :active="selectedCategorySlug === cat.slug" active-class="bg-blue-1 text-primary text-weight-bold"
                    class="rounded-borders" @click="toggleCategory(cat.slug)">
                    <q-avatar size="28px" color="blue-1" text-color="blue-8" class="text-weight-bold text-caption">
                      <img :src="cat.icon ?? ''" alt="" srcset="">
                    </q-avatar>
                    <q-item-section class="q-pl-xs">{{ cat.name }}</q-item-section>
                  </q-item>
                </q-list>
              </div>

              <!-- <q-select v-model="filters.zone" :options="serviceStore.zones" label="Zone d'intervention" outlined dense
                emit-value map-options @update:model-value="applyFilters">
                <template v-slot:prepend><q-icon name="place" /></template>
</q-select> -->

              <div>
                <div class="text-caption text-grey-7 q-mb-sm">Budget max (FCFA)</div>
                <q-slider v-model="filters.max_price" :min="0" :max="1000000" :step="10000" label label-always
                  color="primary" @change="applyFilters" />
              </div>

              <q-btn label="Réinitialiser" flat color="grey-7" class="full-width" no-caps @click="resetFilters" />
            </q-card-section>
          </q-card>
        </div>

        <div class="col-12 col-md-9">
          <div class="row items-center justify-between q-mb-lg bg-white q-pa-sm rounded-borders shadow-sm">
            <div class="flex items-center q-gutter-x-sm">
              <q-badge color="primary" class="q-pa-xs">{{ pagination.total }}</q-badge>
              <span class="text-weight-medium">Offres disponibles</span>
            </div>

            <q-select v-model="sortOption" :options="sortOptions" dense borderless options-dense
              @update:model-value="applyFilters" />
          </div>

          <div v-if="serviceStore.loading" class="row q-col-gutter-md">
            <div v-for="n in 4" :key="n" class="col-12">
              <q-skeleton height="150px" class="rounded-borders" />
            </div>
          </div>

          <div v-else>
            <div class="row q-col-gutter-md">
              <div v-for="offer in serviceStore.offers" :key="offer.id" class="col-12">
                <ServiceOfferCard :offer="offer" />
              </div>
            </div>

            <div v-if="serviceStore.offers.length === 0" class="text-center q-pa-xl">
              <q-icon name="hail" size="80px" color="grey-4" />
              <div class="text-h6 text-grey-5">Aucun prestataire ne correspond à vos critères</div>
            </div>

            <div class="flex justify-center q-mt-xl">
              <q-pagination v-model="pagination.current_page" :max="pagination.last_page" @update:model-value="loadPage"
                color="primary" outline direction-links />
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
import { useServiceOfferStore } from 'src/stores/service-offer';
import { api } from 'src/boot/axios';
import ServiceOfferCard from 'src/components/market/ServiceOfferCard.vue';
import type { ServiceCategory } from 'src/types';
import { useRouter } from 'vue-router';

const router = useRouter();
const route = useRoute();
const serviceStore = useServiceOfferStore();

// State local
const selectedCategorySlug = ref<string | null>(null);
const categories = ref<ServiceCategory[]>([]);
const sortOption = ref('created_at|desc');
const pageTitle = computed(() => {
  if (isServiceTypeRoute.value) return slugParam.value?.replace(/-/g, ' ') || 'Prestation';
  if (isCategoryRoute.value) return slugParam.value?.replace(/-/g, ' ') || 'Catégorie';
  return 'Prestations & Logistique';
});

const pageSubtitle = computed(() => {
  if (isServiceTypeRoute.value) return 'Trouvez un professionnel pour ce service';
  if (isCategoryRoute.value) return 'Explorez toutes les offres de cette catégorie';
  return 'Trouvez les meilleurs experts pour vos travaux agricoles';
});
const filters = reactive({
  zone: null as string | null,
  max_price: 1000000,
});

const sortOptions = [
  { label: 'Plus récents', value: 'created_at|desc' },
  { label: 'Prix croissant', value: 'price|asc' },
  { label: 'Prix décroissant', value: 'price|desc' },
];

const pagination = computed(() => serviceStore.pagination);

// Détermine le type de vue en fonction de la route
const isServiceTypeRoute = computed(() => route.name === 'service-type');
const isCategoryRoute = computed(() => route.name === 'service-category');
const slugParam = computed(() => route.params.slug as string);

// Charger les catégories (pour le menu latéral)
const loadCategories = async () => {
  try {
    const { data } = await api.get('/service-categories');
    categories.value = data.data;
  } catch (e) { console.error(e); }
};

// Construire les paramètres communs (hors filtre spécifique)
const buildCommonParams = () => {
  const [sortBy, sortOrder] = sortOption.value.split('|');
  return {
    page: pagination.value.current_page,
    sort_by: sortBy,
    sort_order: sortOrder,
    zone: filters.zone,
    max_price: filters.max_price,
  };
};

const toggleCategory = async (slug: string) => {
  if (selectedCategorySlug.value === slug) {
    await router.push({ name: 'service-offers' });
  } else {
    await router.push({ name: 'service-category', params: { slug } });
  }
};

// Dans loadOffers, plus besoin du paramètre 'category'
const loadOffers = async () => {
  const commonParams = buildCommonParams();
  if (isServiceTypeRoute.value && slugParam.value) {
    await serviceStore.fetchByService(slugParam.value, commonParams);
  } else if (isCategoryRoute.value && slugParam.value) {
    await serviceStore.fetchByCategory(slugParam.value, commonParams);
  } else {
    await serviceStore.fetchOffers(commonParams);
  }
};

// Appliquer les filtres (réinitialise la page)
const applyFilters = async () => {
  pagination.value.current_page = 1;
  await loadOffers();
};

const loadPage = async (page: number) => {
  pagination.value.current_page = page;
  await loadOffers();
};


const resetFilters = async () => {
  filters.zone = null;
  filters.max_price = 1000000;
  selectedCategorySlug.value = null;
  await applyFilters();
};

// Réagir aux changements de route (quand on navigue entre catégorie/type)
watch(() => route.params.slug, async () => {
  pagination.value.current_page = 1;
  await loadOffers();
}, { immediate: true });

// Initialisation
onMounted(async () => {
  await loadCategories();
  await serviceStore.fetchZones();
  await loadOffers();
});
</script>
<style lang="scss" scoped>
.container-max {
  max-width: 1200px;
  margin: 0 auto;
}

.sticky-filters {
  position: sticky;
  top: 80px;
}

.rounded-borders {
  border-radius: 12px;
}

.shadow-sm {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.opacity-80 {
  opacity: 0.8;
}
</style>
