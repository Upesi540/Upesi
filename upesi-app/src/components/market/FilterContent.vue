<template>
  <div>
    <!-- Desktop : Sidebar classique -->
    <q-card v-if="$q.screen.gt.md" flat class="sidebar-card" :class="{ 'mobile-sidebar': $q.screen.lt.md }">
      <!-- TOUT LE CONTENU ORIGINAL DES FILTRES (recopié une fois) -->
      <div class="card-header">
        <div class="header-title">
          <q-icon name="tune" size="20px" class="q-mr-sm" />
          <span class="text-weight-bold">Filtres</span>
          <q-badge v-if="activeFiltersCount > 0" color="primary" rounded class="q-ml-sm">
            {{ activeFiltersCount }}
          </q-badge>
        </div>
        <q-btn v-if="activeFiltersCount > 0" flat dense label="Réinitialiser" size="sm" color="primary"
          @click="$emit('reset')" />
      </div>

      <q-separator />

      <div class="markets-section">
        <div class="section-header" @click="toggleMarkets">
          <div class="section-title">
            <q-icon name="storefront" size="20px" class="q-mr-sm text-primary" />
            <span class="text-weight-bold">Marchés</span>
          </div>
          <q-icon :name="marketsExpanded ? 'expand_less' : 'expand_more'" size="20px" color="grey-6" />
        </div>

        <q-slide-transition>
          <div v-show="marketsExpanded">
            <div class="markets-list">
              <div v-for="market in markets" :key="market.id" class="market-item"
                :class="{ active: selectedMarket === market.id }" @click="$emit('select-market', market.id)">
                <div class="market-avatar">
                  <img :src="market.image" alt="" />
                </div>
                <div class="market-info">
                  <div class="market-name">{{ market.name }}</div>
                  <div class="market-categories">{{ market.categories?.length || 0 }} catégories</div>
                </div>
                <q-icon name="chevron_right" size="16px" class="market-arrow"
                  :class="{ rotated: selectedMarket === market.id }" />
              </div>

              <q-slide-transition>
                <div v-show="selectedMarket" class="categories-wrapper">
                  <div v-for="cat in selectedMarketCategories" :key="cat.id" class="category-item"
                    :class="{ active: selectedCategory === cat.id }" @click="$emit('select-category', cat.id)">
                    <span class="category-icon">{{ cat.icon || '📦' }}</span>
                    <span class="category-name">{{ cat.name }}</span>
                    <q-icon v-if="selectedCategory === cat.id" name="check_circle" size="16px" color="primary" />
                  </div>
                </div>
              </q-slide-transition>
            </div>
          </div>
        </q-slide-transition>
      </div>

      <q-separator />

      <div class="filters-section">
        <div class="section-header" @click="toggleFilters">
          <div class="section-title">
            <q-icon name="filter_alt" size="20px" class="q-mr-sm text-primary" />
            <span class="text-weight-bold">Filtres avancés</span>
          </div>
          <q-icon :name="filtersExpanded ? 'expand_less' : 'expand_more'" size="20px" color="grey-6" />
        </div>

        <q-slide-transition>
          <div v-show="filtersExpanded" class="filters-content">
            <div class="filter-group">
              <div class="filter-label">
                <q-icon name="attach_money" size="16px" />
                <span>Prix (FCFA)</span>
              </div>
              <div class="price-range">
                <q-input v-model.number="localFilters.min_price" type="number" label="Min" dense outlined
                  class="price-input" @update:model-value="emitUpdate" />
                <span class="price-separator">—</span>
                <q-input v-model.number="localFilters.max_price" type="number" label="Max" dense outlined
                  class="price-input" @update:model-value="emitUpdate" />
              </div>
            </div>

            <div class="filter-group">
              <div class="filter-label">
                <q-icon name="visibility" size="16px" />
                <span>Disponibilité</span>
              </div>
              <div class="availability-options">
                <div class="chip-option" :class="{ active: localFilters.is_featured }" @click="
                  localFilters.is_featured = !localFilters.is_featured;
                emitUpdate();
                ">
                  <q-icon name="star" size="16px" />
                  <span>En vedette</span>
                </div>
              </div>
            </div>

            <div class="filter-group">
              <div class="filter-label">
                <q-icon name="location_on" size="16px" />
                <span>Localisation</span>
              </div>

              <q-select v-model="localFilters.country_id" :options="countryOptions" label="Pays" dense emit-value
                map-options outlined clearable class="q-mb-sm" @update:model-value="emitCountryChange" />

              <!-- <q-select
                v-model="localFilters.state_id"
                :options="stateOptions"
                label="Région"
                dense
                outlined
                clearable
                :disable="!localFilters.country_id"
                class="q-mb-sm"
                @update:model-value="emitStateChange"
              /> -->
            </div>

            <div class="apply-button" v-if="$q.screen.lt.md">
              <q-btn color="primary" label="Appliquer les filtres" class="full-width" rounded @click="emit('apply')" />
            </div>
          </div>
        </q-slide-transition>
      </div>
    </q-card>

    <!-- Mobile : Bouton + Drawer -->
    <div v-else>
      <q-btn color="primary" icon="filter_alt" label="Filtres" outline rounded class="filter-btn"
        @click="drawerOpen = true" />

      <q-drawer v-model="drawerOpen" side="left" elevated class="filter-drawer">
        <div class="drawer-header">
          <span class="text-h6">Filtres</span>
          <q-btn flat round icon="close" @click="drawerOpen = false" />
        </div>
        <q-separator />
        <q-scroll-area class="drawer-content">
          <!-- ICI ON RECOPIE EXACTEMENT LE MÊME CONTENU QUE DANS LA SIDEBAR -->
          <div class="filter-content-mobile">
            <!-- Copie identique du bloc filtres (mêmes classes, mêmes éléments) -->
            <div class="card-header">
              <div class="header-title">
                <q-icon name="tune" size="20px" class="q-mr-sm" />
                <span class="text-weight-bold">Filtres</span>
                <q-badge v-if="activeFiltersCount > 0" color="primary" rounded class="q-ml-sm">
                  {{ activeFiltersCount }}
                </q-badge>
              </div>
              <q-btn v-if="activeFiltersCount > 0" flat dense label="Réinitialiser" size="sm" color="primary"
                @click="$emit('reset')" />
            </div>

            <q-separator />

            <div class="markets-section">
              <div class="section-header" @click="toggleMarkets">
                <div class="section-title">
                  <q-icon name="storefront" size="20px" class="q-mr-sm text-primary" />
                  <span class="text-weight-bold">Marchés</span>
                </div>
                <q-icon :name="marketsExpanded ? 'expand_less' : 'expand_more'" size="20px" color="grey-6" />
              </div>

              <q-slide-transition>
                <div v-show="marketsExpanded">
                  <div class="markets-list">
                    <div v-for="market in markets" :key="market.id" class="market-item"
                      :class="{ active: selectedMarket === market.id }" @click="$emit('select-market', market.id)">
                      <div class="market-avatar">
                        <img :src="market.image" alt="" />
                      </div>
                      <div class="market-info">
                        <div class="market-name">{{ market.name }}</div>
                        <div class="market-categories">{{ market.categories?.length || 0 }} catégories</div>
                      </div>
                      <q-icon name="chevron_right" size="16px" class="market-arrow"
                        :class="{ rotated: selectedMarket === market.id }" />
                    </div>

                    <q-slide-transition>
                      <div v-show="selectedMarket" class="categories-wrapper">
                        <div v-for="cat in selectedMarketCategories" :key="cat.id" class="category-item"
                          :class="{ active: selectedCategory === cat.id }" @click="$emit('select-category', cat.id)">
                          <span class="category-icon">{{ cat.icon || '📦' }}</span>
                          <span class="category-name">{{ cat.name }}</span>
                          <q-icon v-if="selectedCategory === cat.id" name="check_circle" size="16px" color="primary" />
                        </div>
                      </div>
                    </q-slide-transition>
                  </div>
                </div>
              </q-slide-transition>
            </div>

            <q-separator />

            <div class="filters-section">
              <div class="section-header" @click="toggleFilters">
                <div class="section-title">
                  <q-icon name="filter_alt" size="20px" class="q-mr-sm text-primary" />
                  <span class="text-weight-bold">Filtres avancés</span>
                </div>
                <q-icon :name="filtersExpanded ? 'expand_less' : 'expand_more'" size="20px" color="grey-6" />
              </div>

              <q-slide-transition>
                <div v-show="filtersExpanded" class="filters-content">
                  <div class="filter-group">
                    <div class="filter-label">
                      <q-icon name="attach_money" size="16px" />
                      <span>Prix (FCFA)</span>
                    </div>
                    <div class="price-range">
                      <q-input v-model.number="localFilters.min_price" type="number" label="Min" dense outlined
                        class="price-input" @update:model-value="emitUpdate" />
                      <span class="price-separator">—</span>
                      <q-input v-model.number="localFilters.max_price" type="number" label="Max" dense outlined
                        class="price-input" @update:model-value="emitUpdate" />
                    </div>
                  </div>

                  <div class="filter-group">
                    <div class="filter-label">
                      <q-icon name="visibility" size="16px" />
                      <span>Disponibilité</span>
                    </div>
                    <div class="availability-options">
                      <div class="chip-option" :class="{ active: localFilters.is_featured }" @click="
                        localFilters.is_featured = !localFilters.is_featured;
                      emitUpdate();
                      ">
                        <q-icon name="star" size="16px" />
                        <span>En vedette</span>
                      </div>
                    </div>
                  </div>

                  <div class="filter-group">
                    <div class="filter-label">
                      <q-icon name="location_on" size="16px" />
                      <span>Localisation</span>
                    </div>

                    <q-select v-model="localFilters.country_id" :options="countryOptions" label="Pays" dense emit-value
                      map-options outlined clearable class="q-mb-sm" @update:model-value="emitCountryChange" />

                    <!-- <q-select
                      v-model="localFilters.state_id"
                      :options="stateOptions"
                      label="Région"
                      dense
                      outlined
                      clearable
                      :disable="!localFilters.country_id"
                      class="q-mb-sm"
                      @update:model-value="emitStateChange"
                    /> -->

                  </div>

                  <div class="apply-button" v-if="$q.screen.lt.md">
                    <q-btn color="primary" label="Appliquer les filtres" class="full-width" rounded
                      @click="emit('apply')" />
                  </div>
                </div>
              </q-slide-transition>
            </div>
          </div>
        </q-scroll-area>
        <div class="drawer-footer">
          <q-btn flat label="Réinitialiser" @click="$emit('reset')" />
          <q-btn color="primary" label="Appliquer" @click="applyFilters" />
        </div>
      </q-drawer>
    </div>
  </div>
</template>

<script setup lang="ts">
import type { Market } from 'src/types';
import { ref, reactive, watch, computed } from 'vue';
import { useQuasar } from 'quasar';

const $q = useQuasar();
const drawerOpen = ref(false);

interface Option {
  label: string;
  value: string | number | null;
}

interface FilterState {
  min_price: number | null;
  max_price: number | null;
  is_featured: boolean;
  country_id: string | null;
  state_id: string | null;
}

const props = defineProps<{
  markets: Market[];
  selectedMarket: string | null;
  selectedCategory: string | null;
  filters: FilterState;
  expandedMarkets: boolean;
  countryOptions: Option[];
  stateOptions: Option[];
}>();

const emit = defineEmits([
  'update:filters',
  'update:expandedMarkets',
  'select-market',
  'select-category',
  'country-change',
  'state-change',
  'apply',
  'reset',
]);

const localFilters = reactive({ ...props.filters });
const marketsExpanded = ref(props.expandedMarkets);
const filtersExpanded = ref($q.screen.gt.sm);

const selectedMarketCategories = computed(() => {
  const market = props.markets.find((m) => m.id === props.selectedMarket);
  return market?.categories || [];
});

const activeFiltersCount = computed(() => {
  let count = 0;
  if (localFilters.min_price) count++;
  if (localFilters.max_price) count++;
  if (localFilters.is_featured) count++;
  if (localFilters.country_id) count++;
  if (localFilters.state_id) count++;
  return count;
});

watch(
  () => props.filters,
  (newVal) => {
    Object.assign(localFilters, newVal);
  },
  { deep: true }
);

watch(
  () => props.expandedMarkets,
  (val) => {
    marketsExpanded.value = val;
  }
);

const toggleMarkets = () => {
  marketsExpanded.value = !marketsExpanded.value;
  emit('update:expandedMarkets', marketsExpanded.value);
};

const toggleFilters = () => {
  filtersExpanded.value = !filtersExpanded.value;
};

const emitUpdate = () => {
  emit('update:filters', { ...localFilters });
  emit('apply');
};

const emitCountryChange = () => {
  emit('update:filters', { ...localFilters });
  emit('country-change');
};

// const emitStateChange = () => {
//   emit('update:filters', { ...localFilters });
//   emit('state-change');
// };

const applyFilters = () => {
  emit('apply');
  drawerOpen.value = false;
};
</script>

<style lang="scss" scoped>
/* TOUS LES STYLES ORIGINAUX (aucune modification) */
.sidebar-card {
  border-radius: 24px;
  background: white;
  border: 1px solid rgba(0, 0, 0, 0.05);
  overflow: hidden;

  &.mobile-sidebar {
    border-radius: 20px;
    margin: 0 0 16px 0;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.06);
  }
}

.card-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;

  .header-title {
    display: flex;
    align-items: center;
    font-size: 16px;
    color: #1a1a1a;
  }
}

.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  cursor: pointer;
  transition: background 0.2s ease;

  &:hover {
    background: rgba(0, 0, 0, 0.02);
  }

  .section-title {
    display: flex;
    align-items: center;
    font-size: 15px;
    color: #1a1a1a;
  }
}

.markets-section .markets-list {
  padding: 0 12px 16px 12px;
}

.market-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px;
  border-radius: 16px;
  cursor: pointer;
  transition: all 0.2s ease;

  &:hover {
    background: rgba(33, 150, 243, 0.05);
  }

  &.active {
    background: rgba(33, 150, 243, 0.1);

    .market-name {
      color: var(--q-primary);
      font-weight: 600;
    }
  }

  .market-avatar {
    width: 40px;
    height: 40px;
    border-radius: 12px;
    background: #f5f5f5;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;

    img {
      width: 24px;
      height: 24px;
      object-fit: contain;
    }
  }

  .market-info {
    flex: 1;

    .market-name {
      font-size: 14px;
      font-weight: 500;
      color: #2c3e50;
      margin-bottom: 2px;
    }

    .market-categories {
      font-size: 11px;
      color: #95a5a6;
    }
  }

  .market-arrow {
    color: #bdc3c7;
    transition: transform 0.2s ease;

    &.rotated {
      transform: rotate(90deg);
      color: var(--q-primary);
    }
  }
}

.categories-wrapper {
  margin-left: 52px;
  margin-top: 4px;
  margin-bottom: 8px;

  .category-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s ease;

    &:hover {
      background: rgba(0, 0, 0, 0.03);
    }

    &.active {
      background: rgba(33, 150, 243, 0.08);

      .category-name {
        color: var(--q-primary);
        font-weight: 500;
      }
    }

    .category-icon {
      font-size: 20px;
    }

    .category-name {
      flex: 1;
      font-size: 13px;
      color: #5a6c7e;
    }
  }
}

.filters-section .filters-content {
  padding: 0 20px 20px 20px;
}

.filter-group {
  margin-bottom: 24px;

  .filter-label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 12px;
  }
}

.price-range {
  display: flex;
  align-items: center;
  gap: 12px;

  .price-input {
    flex: 1;

    :deep(.q-field__control) {
      border-radius: 12px;
    }
  }

  .price-separator {
    color: #95a5a6;
    font-size: 14px;
  }
}

.availability-options .chip-option {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  border-radius: 30px;
  background: #f5f5f5;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 13px;

  &:hover {
    background: #e0e0e0;
  }

  &.active {
    background: var(--q-primary);
    color: white;

    .q-icon {
      color: white;
    }
  }
}

.apply-button {
  margin-top: 20px;
}

// Styles supplémentaires pour le drawer
.filter-btn {
  width: 100%;
  margin-bottom: 16px;
}

.filter-drawer {
  border-radius: 20px 20px 0 0;
  max-height: 90vh;
}

.drawer-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
}

.drawer-content {
  height: calc(90vh - 120px);
  padding: 16px;
}

.drawer-footer {
  display: flex;
  gap: 12px;
  padding: 16px;
  border-top: 1px solid #edf2f7;

  .q-btn {
    flex: 1;
  }
}

@media (min-width: 1024px) {
  .sidebar-card {
    position: sticky;
    top: 90px;
  }

  .section-header {
    padding: 20px;
  }

  .markets-section .markets-list {
    padding: 0 16px 20px 16px;
  }

  .filters-section .filters-content {
    padding: 0 20px 24px 20px;
  }
}

@media (max-width: 599px) {
  .card-header {
    padding: 14px 16px;
  }

  .section-header {
    padding: 14px 16px;
  }

  .markets-section .markets-list {
    padding: 0 8px 12px 8px;
  }

  .market-item {
    padding: 10px;
  }

  .filters-section .filters-content {
    padding: 0 16px 16px 16px;
  }
}
</style>
