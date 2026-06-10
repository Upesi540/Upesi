<template>
  <q-footer class="bg-transparent footer-container">
    <div class="q-px-md relative-position">

      <div class="upesi-fab-search" @click="openSearchDialog">
        <div class="fab-inner shadow-18">
          <q-icon name="search" size="30px" color="white" />
        </div>
      </div>

      <div class="nav-wrapper">
        <q-tabs v-model="activeTab" active-color="primary" indicator-color="transparent" class="main-nav-tabs" no-caps
          :dense="$q.screen.lt.md" :breakpoint="0">
          <q-route-tab to="/" class="modern-tab">
            <template #default>
              <q-icon name="home" :size="$q.screen.lt.md ? '20px' : '24px'" />
              <div class="tab-label" :class="{ 'tablet-label': $q.screen.gt.sm }">Accueil</div>
            </template>
          </q-route-tab>

          <q-route-tab to="/products" class="modern-tab">
            <template #default>
              <q-icon name="grid_view" :size="$q.screen.lt.md ? '20px' : '24px'" />
              <div class="tab-label" :class="{ 'tablet-label': $q.screen.gt.sm }">Rayons</div>
            </template>
          </q-route-tab>

          <q-route-tab to="/services/offers" class="modern-tab central-item">
            <template #default>
              <q-icon name="bolt" :size="$q.screen.lt.md ? '20px' : '24px'" />
              <div class="tab-label" :class="{ 'tablet-label': $q.screen.gt.sm }">Services</div>
            </template>
          </q-route-tab>

          <q-route-tab to="/cart" class="modern-tab">
            <template #default>
              <div class="relative-position">
                <q-icon name="shopping_basket" :size="$q.screen.lt.md ? '20px' : '24px'" />
                <q-badge v-if="cartStore.uniqueItemsCount > 0" color="secondary" floating rounded class="upesi-badge">
                  {{ cartStore.uniqueItemsCount }}
                </q-badge>
              </div>
              <div class="tab-label" :class="{ 'tablet-label': $q.screen.gt.sm }">Panier</div>
            </template>
          </q-route-tab>

          <q-route-tab :to="authStore.isAuthenticated ? '/user/profile' : '/auth/login'" class="modern-tab">
            <template #default>
              <q-avatar v-if="authStore.isAuthenticated" :size="$q.screen.lt.md ? '20px' : '24px'"
                class="avatar-border">
                <q-img :src="authStore.userAvatar || 'https://cdn.quasar.dev/img/avatar.png'" />
              </q-avatar>
              <q-icon v-else name="person" :size="$q.screen.lt.md ? '20px' : '24px'" />
              <div class="tab-label q-pt-xs" :class="{ 'tablet-label': $q.screen.gt.sm }">Mon upesi</div>
            </template>
          </q-route-tab>
        </q-tabs>
      </div>
    </div>

    <!-- Dialog de recherche avec suggestions réelles -->
    <q-dialog v-model="showSearch" :position="$q.screen.gt.md ? 'top' : 'bottom'"
      :transition-show="$q.screen.gt.md ? 'slide-down' : 'slide-up'"
      :transition-hide="$q.screen.gt.md ? 'slide-up' : 'slide-down'">
      <q-card class="search-sheet" :class="{ 'tablet-search': $q.screen.gt.sm, 'desktop-search': $q.screen.gt.md }">
        <div class="sheet-handle q-mx-auto q-mt-sm" v-if="$q.screen.lt.md"></div>
        <q-card-section class="q-pa-lg">
          <div class="row items-center q-mb-md">
            <span class="text-h6 text-weight-bolder">Recherche Upesi</span>
            <q-spacer />
            <q-btn icon="close" flat round v-close-popup class="text-grey-5" />
          </div>

          <q-input 
            v-model="searchQuery" 
            placeholder="Que cherchez-vous?" 
            outlined 
            rounded 
            autofocus 
            bg-color="grey-2"
            :size="$q.screen.gt.sm ? 'lg' : 'md'" 
            @keyup.enter="performSearch"
            @update:model-value="onSearchInput"
            :loading="searchStore.isLoading"
          >
            <template #prepend>
              <q-icon name="search" color="primary" :size="$q.screen.gt.sm ? '24px' : '20px'" />
            </template>
            <template v-if="searchQuery" #append>
              <q-icon name="close" class="cursor-pointer" @click="clearSearch" />
            </template>
          </q-input>

          <!-- Suggestions réelles depuis l'API -->
          <div v-if="searchSuggestions?.length > 0 && !hasSearched" class="q-mt-md">
            <q-separator class="q-mb-md" />
            <div class="text-subtitle2 text-grey-7 q-mb-sm">Suggestions</div>
            <div class="row q-col-gutter-sm">
              <div v-for="suggestion in searchSuggestions" :key="suggestion.id" class="col-12 col-sm-6 col-md-4">
                <q-item clickable v-close-popup @click="selectSuggestion(suggestion)" class="q-pa-sm">
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
              </div>
            </div>
          </div>

          <!-- Message de chargement -->
          <div v-else-if="searchStore.isLoading && searchQuery?.length > 2" class="flex flex-center q-mt-md">
            <q-spinner color="primary" size="24px" />
            <span class="q-ml-sm text-grey-6">Recherche en cours...</span>
          </div>
        </q-card-section>
      </q-card>
    </q-dialog>
  </q-footer>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useQuasar } from 'quasar';
import { useAuthStore } from 'src/stores/auth';
import { useCartStore } from 'src/stores/cart';
import { useSearchStore } from 'src/stores/search';

const router = useRouter();
const $q = useQuasar();
const authStore = useAuthStore();
const cartStore = useCartStore();
const searchStore = useSearchStore();

const activeTab = ref('home');
const showSearch = ref(false);
const searchQuery = ref('');
const hasSearched = ref(false);

// Suggestions depuis le store
const searchSuggestions = computed(() => searchStore.suggestions);

// Ouvrir le dialog et reset
const openSearchDialog = () => {
  showSearch.value = true;
  hasSearched.value = false;
  searchQuery.value = '';
  searchStore.suggestions = [];
};

// Nettoyer la recherche
const clearSearch = () => {
  searchQuery.value = '';
  searchStore.suggestions = [];
  hasSearched.value = false;
};

// Saisie utilisateur - fetch suggestions
const onSearchInput = async () => {
  if (searchQuery.value?.length >= 2) {
    await searchStore.fetchSuggestions(searchQuery.value);
  } else {
    searchStore.suggestions = [];
  }
};

// Sélectionner une suggestion
const selectSuggestion = async (suggestion: { id: string; title: string; type: string }) => {
  searchQuery.value = suggestion.title;
  await performSearch();
};

// Exécuter la recherche et rediriger
const performSearch = async () => {
  if (!searchQuery.value.trim()) return;

  hasSearched.value = true;
  showSearch.value = false;
  searchStore.suggestions = [];

  await router.push({
    name: 'search',
    query: { q: searchQuery.value.trim() }
  });

  searchQuery.value = '';
};

// Fermer le dialog avec la touche ESC
watch(showSearch, (newVal) => {
  if (!newVal) {
    searchQuery.value = '';
    searchStore.suggestions = [];
    hasSearched.value = false;
  }
});
</script>

<style lang="scss" scoped>
.footer-container {
  overflow: visible !important;
  pointer-events: none;
  padding-bottom: calc(20px + env(safe-area-inset-bottom));
  padding-top: 60px;
  z-index: 2000;

  @media (min-width: 768px) and (max-width: 1023px) {
    padding-bottom: calc(30px + env(safe-area-inset-bottom));
    padding-top: 70px;
  }

  @media (min-width: 1024px) {
    padding-bottom: calc(40px + env(safe-area-inset-bottom));
    padding-top: 80px;
  }
}

.nav-wrapper {
  pointer-events: auto;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
  border-radius: 40px;
  width: 100%;
  margin: 0 auto;
  display: flex;
  align-items: center;
  border: 1px solid rgba(255, 255, 255, 0.5);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
  overflow: visible !important;
  height: 65px;
  max-width: 550px;

  @media (min-width: 768px) and (max-width: 1023px) {
    height: 75px;
    max-width: 650px;
    border-radius: 50px;
  }

  @media (min-width: 1024px) {
    height: 80px;
    max-width: 750px;
    border-radius: 60px;
  }
}

.main-nav-tabs {
  width: 100%;
  padding: 0 8px;

  :deep(.q-tabs__content) {
    justify-content: space-evenly;
    gap: 4px;

    @media (min-width: 768px) {
      gap: 8px;
    }
  }
}

.upesi-fab-search {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  z-index: 2100;
  pointer-events: auto;
  cursor: pointer;
  transition: all 0.3s ease;
  top: -35px;

  @media (min-width: 768px) and (max-width: 1023px) {
    top: -40px;
  }

  @media (min-width: 1024px) {
    top: -45px;
  }

  .fab-inner {
    background: linear-gradient(135deg, var(--q-primary), #2a5298);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 5px solid #ffffff;
    box-shadow: 0 10px 25px rgba(var(--q-primary-rgb), 0.5);
    transition: transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    width: 55px;
    height: 55px;

    @media (min-width: 768px) and (max-width: 1023px) {
      width: 65px;
      height: 65px;
    }

    @media (min-width: 1024px) {
      width: 70px;
      height: 70px;
      border-width: 6px;
    }

    &:active {
      transform: scale(0.9);
    }

    &:hover {
      transform: scale(1.05);
    }
  }
}

.modern-tab {
  flex: 1;
  color: #000000 !important;
  padding: 0;
  transition: all 0.3s ease;
  border-radius: 30px;
  min-height: 60px;

  @media (min-width: 768px) and (max-width: 1023px) {
    min-height: 70px;
    padding: 8px 0;
  }

  @media (min-width: 1024px) {
    min-height: 75px;
    padding: 10px 0;
  }

  &:hover {
    background: rgba(0, 0, 0, 0.05);
  }

  &.q-tab--active {
    color: var(--q-primary) !important;
    background: rgba(var(--q-primary-rgb), 0.1);

    .tab-label {
      font-weight: 800;
      opacity: 1;
    }
  }
}

:deep(.q-tab__content) {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 4px;
}

.central-item {
  :deep(.q-tab__content) {
    @media (max-width: 767px) {
      padding-top: 12px;
    }

    @media (min-width: 768px) {
      padding-top: 15px;
    }
  }
}

.tab-label {
  font-weight: 700;
  line-height: 1;
  opacity: 0.8;
  transition: all 0.3s ease;
  font-size: 9px !important;
  margin-top: 2px;

  @media (min-width: 768px) and (max-width: 1023px) {
    font-size: 11px !important;
    margin-top: 4px;
  }

  @media (min-width: 1024px) {
    font-size: 12px !important;
    margin-top: 5px;
  }

  &.tablet-label {
    @media (min-width: 768px) {
      font-size: 12px !important;
    }
  }
}

.upesi-badge {
  top: -6px !important;
  right: -10px !important;
  border: 2px solid white;
  font-size: 10px;

  @media (min-width: 768px) {
    top: -8px !important;
    right: -12px !important;
    font-size: 11px;
  }
}

.avatar-border {
  border: 2px solid transparent;
  transition: border-color 0.3s;
}

:deep(.q-tab--active) .avatar-border {
  border-color: var(--q-primary);
}

.search-sheet {
  border-radius: 30px 30px 0 0;

  &.tablet-search {
    border-radius: 20px;
    margin: 20px;
    width: 90%;
    max-width: 600px;
  }

  &.desktop-search {
    border-radius: 20px;
    margin: 30px auto;
    width: 80%;
    max-width: 800px;
  }
}

.sheet-handle {
  width: 40px;
  height: 4px;
  background: #e0e0e0;
  border-radius: 2px;
}

.rounded-10 {
  border-radius: 10px;
}

:deep(.q-icon) {
  transition: all 0.3s ease;
}

@keyframes pulse {
  0% {
    box-shadow: 0 10px 25px rgba(var(--q-primary-rgb), 0.5);
  }

  50% {
    box-shadow: 0 10px 30px rgba(var(--q-primary-rgb), 0.8);
  }

  100% {
    box-shadow: 0 10px 25px rgba(var(--q-primary-rgb), 0.5);
  }
}

.upesi-fab-search {
  animation: pulse 2s infinite;

  &:hover {
    animation: none;
  }
}
</style>