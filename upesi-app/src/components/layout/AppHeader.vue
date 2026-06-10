<template>
  <q-header reveal :elevated="!$q.screen.lt.md" class="bg-white text-grey-9 flat-header" height-hint="110">
    <q-toolbar class="q-py-xs q-px-md row no-wrap items-center">
      <div class="row items-center q-mr-lg">
        <div class="cursor-pointer row items-center q-mr-md" @click="$router.push('/')">
          <img :src="logoUrl" alt="Upesi Logo" class="logo-img">
        </div>
      </div>

      <div class="search-container col">
        <q-input v-if="$q.screen.gt.sm" v-model="searchQuery" dense borderless rounded
          placeholder="Rechercher un produit, une culture, un service ..." class="custom-search-modern"
          @keyup.enter="performSearch" @update:model-value="onSearchInput" :loading="searchStore.isLoading">
          <template v-slot:prepend>
            <q-icon name="search" color="primary" size="22px" class="q-ml-md" />
          </template>

          <template v-slot:append v-if="searchQuery">
            <q-icon name="close" @click="clearSearch" class="cursor-pointer text-grey-5 hover-red" size="18px" />
          </template>

          <template v-slot:after>
            <q-btn color="primary" label="Rechercher" unelevated rounded class="search-btn q-px-xl text-weight-bolder"
              no-caps @click="performSearch" />
          </template>
        </q-input>
      </div>

      <q-space v-if="$q.screen.lt.md" />

      <div class="row no-wrap items-center q-ml-md q-gutter-x-sm">
        <!-- <q-btn flat round icon="notifications_none" color="grey-6" size="12px">
          <q-badge floating color="red-5" rounded label="" />
        </q-btn>
        -->
        <q-btn v-if="$q.screen.gt.sm" flat round :class="{ 'animate-shake': cartStore.isShaking }" icon="shopping_cart"
          color="grey-6" size="12px" to="/cart">
          <q-badge floating color="red" rounded v-if="cartStore.uniqueItemsCount > 0"
            :label="cartStore.uniqueItemsCount" />
        </q-btn>
        <div v-if="$q.screen.gt.sm">
          <template v-if="!authStore.isAuthenticated">
            <q-separator vertical inset class="q-mx-xs" color="grey-3" />
            <q-btn flat color="secondary" label="S'identifier" to="/auth/login" class="text-weight-bold" no-caps />
          </template>
          <q-avatar v-else size="34px" class="cursor-pointer q-ml-xs" @click="$router.push('/auth/profile')">
            <img :src="authStore.userAvatar ?? 'https://cdn.quasar.dev/img/avatar.png'">
          </q-avatar>
        </div>

      </div>
    </q-toolbar>

    <q-separator color="grey-2" />

    <div class="row items-center q-px-md bg-white sub-nav no-wrap scroll-x" style="padding-top: 0; padding-bottom: 0;">
      <q-btn flat no-caps label="Tous les Marchés" :size="$q.screen.lt.sm ? '12px' : ''"
        :padding="$q.screen.lt.sm ? '4px 8px' : '8px 16px'" icon="storefront" color="primary"
        class="text-weight-bold flex-none">
        <q-menu transition-show="jump-down" transition-hide="jump-up" flat bordered class="nav-menu">
          <q-list style="min-width: 250px" class="q-py-sm">
            <q-item v-for="market in navigation.markets" :key="market.id" clickable>
              <q-item-section avatar>
                <img :src="market.image" style="height: 24px; object-fit: contain;">
              </q-item-section>
              <q-item-section>
                <div>{{ market.name }}</div>
              </q-item-section>
              <q-item-section side>
                <q-icon name="chevron_right" size="xs" />
              </q-item-section>

              <q-menu :anchor="$q.screen.gt.sm ? 'top end' : 'bottom start'"
                :self="$q.screen.gt.sm ? 'top start' : 'top start'" flat bordered>
                <q-list style="min-width: 200px">
                  <q-item v-for="cat in market.categories" :key="cat.id" clickable
                    :to="{ name: 'products-by-category', params: { id: cat.id } }">
                    <q-item-section avatar>{{ cat.icon }}</q-item-section>
                    <q-item-section>
                      <div>{{ cat.name }}</div>
                    </q-item-section>
                  </q-item>
                </q-list>
              </q-menu>
            </q-item>
          </q-list>
        </q-menu>
      </q-btn>

      <q-separator vertical inset class="q-mx-sm" />

      <div class="row items-center q-gutter-x-md no-wrap">
        <q-btn flat no-caps label="Prestations" :size="$q.screen.lt.sm ? '12px' : ''"
          :padding="$q.screen.lt.sm ? '4px 8px' : '8px 16px'" icon="engineering" class="flex-none">
          <q-menu flat bordered>
            <q-list style="min-width: 220px">
              <q-item v-for="sCat in navigation.service_categories" :key="sCat.id" clickable>
                <q-item-section avatar>
                  <q-avatar size="28px" color="blue-1" text-color="blue-8" class="text-weight-bold text-caption">
                    <img :src="sCat.icon ?? ''" alt="" srcset="">
                  </q-avatar>
                </q-item-section>
                <q-item-section>{{ sCat.name }}</q-item-section>
                <q-item-section side><q-icon name="chevron_right" size="xs" /></q-item-section>

                <q-menu :anchor="$q.screen.gt.sm ? 'top end' : 'bottom start'"
                  :self="$q.screen.gt.sm ? 'top start' : 'top start'" flat bordered>
                  <q-list style="min-width: 180px">
                    <q-item v-for="service in sCat.services" :key="service.id" clickable
                      :to="`/services/type/${service.slug}`">
                      <q-item-section>{{ service.name }}</q-item-section>
                    </q-item>
                  </q-list>
                </q-menu>
              </q-item>
            </q-list>
          </q-menu>
        </q-btn>

        <q-btn :size="$q.screen.lt.sm ? '12px' : ''" :padding="$q.screen.lt.sm ? '4px 8px' : '8px 16px'" flat no-caps
          label="Banque agricole" to="/agricultural-banking" icon="account_balance" color="grey-7" class="flex-none" />
        <q-btn :size="$q.screen.lt.sm ? '12px' : ''" :padding="$q.screen.lt.sm ? '4px 8px' : '8px 16px'" flat no-caps
          label="Journal de la bourse" to="/journal" icon="newspaper" color="grey-7" class="flex-none" />
        <q-btn :size="$q.screen.lt.sm ? '12px' : ''" :padding="$q.screen.lt.sm ? '4px 8px' : '8px 16px'" flat no-caps
          label="Nos projets" to="/projects" icon="folder" color="grey-7" class="flex-none" />

        <q-btn :size="$q.screen.lt.sm ? '12px' : ''" :padding="$q.screen.lt.sm ? '4px 8px' : '8px 16px'" flat no-caps
          label="À propos" to="/about" icon="info" color="grey-7" class="flex-none" />

      </div>
    </div>
    <slot name="bottom"></slot>
  </q-header>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import logoUrl from 'src/assets/logo.png';
import type { appInitData } from 'src/types/appInit';
import { useCartStore } from 'src/stores/cart';
import { useAuthStore } from 'src/stores/auth';
import { useSearchStore } from 'src/stores/search';

const router = useRouter();
const cartStore = useCartStore();
const authStore = useAuthStore();
const searchStore = useSearchStore();

const searchQuery = ref('');

// Props
defineProps<{
  navigation: appInitData;
  loading: boolean;
}>();

// Saisie utilisateur - fetch suggestions
const onSearchInput = async () => {
  if (searchQuery.value.length >= 2) {
    await searchStore.fetchSuggestions(searchQuery.value);
  } else {
    searchStore.suggestions = [];
  }
};

// Nettoyer la recherche
const clearSearch = () => {
  searchQuery.value = '';
  searchStore.suggestions = [];
};

// Exécuter la recherche et rediriger
const performSearch = async () => {
  if (!searchQuery.value.trim()) {
    await router.push({ name: 'search' });
    return;
  }

  // Nettoyer les suggestions
  searchStore.suggestions = [];

  await router.push({
    name: 'search',
    query: { q: searchQuery.value.trim() }
  });

  searchQuery.value = '';
};
</script>

<style scoped lang="scss">
/* Réduire la taille des textes dans les menus (Marchés et Prestations) */
.q-menu .q-item {
  min-height: 32px; // Réduit aussi la hauteur pour un look plus compact
  padding: 8px 12px;

  .text-weight-bold {
    font-size: 0.9rem; // Nom du marché (au lieu de 1rem)
  }

  .q-item__section {
    font-size: 0.85rem; // Sous-menus et catégories
    line-height: 1.2;
  }
}

/* Réduire spécifiquement les icônes dans les menus pour garder l'équilibre */
.q-menu .q-item__section--avatar {
  min-width: 40px;

  .q-icon {
    font-size: 18px;
  }
}

@keyframes basket-shake {
  0% {
    transform: rotate(0deg);
  }

  25% {
    transform: rotate(15deg);
  }

  50% {
    transform: rotate(-15deg);
  }

  75% {
    transform: rotate(10deg);
  }

  100% {
    transform: rotate(0deg);
  }
}

.animate-shake {
  animation: basket-shake 0.5s cubic-bezier(.36, .07, .19, .97) both;
  display: inline-block;
}

.scroll-x {
  overflow-x: auto;
  white-space: nowrap;
  scrollbar-width: none;

  &::-webkit-scrollbar {
    display: none;
  }
}

.flex-none {
  flex: 0 0 auto;
}

.flat-header {
  border-bottom: 1px solid #f0f0f0;
  box-shadow: none !important;
}

.nav-menu {
  border-radius: 12px !important;
  overflow: hidden;
  margin-top: 8px;
}

.logo-img {
  height: 30px;
  width: auto;
  display: block;
}

.search-container {
  max-width: 700px;
  margin: 0 auto;
}

.custom-search-modern {
  background: #ffffff;
  border: 1px solid rgba(0, 0, 0, 0.08);
  border-radius: 50px !important;
  padding: 4px;
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.04);
}

.custom-search-modern:hover,
.custom-search-modern:focus-within {
  border-color: var(--q-primary);
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
  transform: translateY(-1px);
}

.search-btn {
  height: 100%;
  margin-left: -12px;
  font-size: 15px;
  letter-spacing: 0.5px;
  transition: transform 0.2s ease;
}

.search-btn:active {
  transform: scale(0.96);
}

.hover-red:hover {
  color: #f44336 !important;
}

:deep(.q-placeholder) {
  color: #9e9e9e;
  font-weight: 500;
  padding-left: 8px;
}
</style>
