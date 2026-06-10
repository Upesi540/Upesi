<template>
  <q-page class="bg-grey-1">
    <div class="container-max q-pa-md">
      <!-- En-tête -->
      <div class="text-center q-mb-xl">
        <h1 class="text-h4 text-weight-bold">Journal de la bourse</h1>
        <p class="text-subtitle1 text-grey-7">
          Toute l’actualité agricole et les tendances des marchés
        </p>
      </div>

      <!-- Filtres : recherche et catégorie -->
      <div class="row q-col-gutter-md q-mb-lg">
        <div class="col-12 col-md-6">
          <q-input
            v-model="search"
            label="Rechercher un article"
            dense
            outlined
            clearable
            @keyup.enter="resetAndFetch"
            @clear="resetAndFetch"
          >
            <template v-append>
              <q-icon name="search" class="cursor-pointer" @click="resetAndFetch" />
            </template>
          </q-input>
        </div>
        <div class="col-12 col-md-4">
          <q-select
            v-model="selectedCategory"
            :options="categoryOptions"
            label="Catégorie"
            dense
            outlined
            clearable
            emit-value
            map-options
            @update:model-value="resetAndFetch"
          />
        </div>
        <div class="col-12 col-md-2">
          <q-btn color="primary" label="Filtrer" @click="resetAndFetch" class="full-width" />
        </div>
      </div>

      <!-- Chargement -->
      <div v-if="store.loading" class="row q-col-gutter-md">
        <div v-for="n in 6" :key="n" class="col-12 col-md-4">
          <q-card flat bordered>
            <q-skeleton height="180px" square />
            <q-card-section>
              <q-skeleton type="text" width="60%" />
              <q-skeleton type="text" width="80%" class="q-mt-sm" />
            </q-card-section>
          </q-card>
        </div>
      </div>

      <!-- Grille d'actualités -->
      <div v-else>
        <div v-if="news.length" class="row q-col-gutter-md">
          <div v-for="item in news" :key="item.id" class="col-12 col-md-4">
            <q-card flat bordered class="news-card cursor-pointer" @click="goToDetail(item.slug)">
              <q-img :src="item.featured_image || logoUrl" :ratio="16/9" class="rounded-borders">
                <div class="absolute-bottom-left q-ma-sm">
                  <q-chip :color="item.is_urgent ? 'negative' : 'primary'" text-color="white" size="sm">
                    {{ item.is_urgent ? 'Urgent' : item.type === 'flash' ? 'Flash' : 'Actu' }}
                  </q-chip>
                </div>
              </q-img>
              <q-card-section>
                <div class="text-h6 text-weight-bold">{{ item.title }}</div>
                <div class="text-caption text-grey-7 q-mt-sm" v-html="item.excerpt"></div>
                <div class="row items-center justify-between q-mt-md">
                  <div class="text-caption text-grey">
                    <q-icon name="schedule" size="12px" />
                    {{ item.formatted_date }}
                  </div>
                  <div class="text-caption text-grey">
                    <q-icon name="access_time" size="12px" />
                    {{ item.reading_time }}
                  </div>
                </div>
              </q-card-section>
            </q-card>
          </div>
        </div>

        <div v-else class="text-center q-pa-xl text-grey-6">
          <q-icon name="newspaper" size="64px" />
          <div class="text-h6">Aucune actualité trouvée</div>
        </div>

        <div v-if="pagination && pagination.last_page > 1" class="flex flex-center q-mt-xl">
          <q-pagination
            v-model="currentPage"
            :max="pagination.last_page"
            :max-pages="5"
            direction-links
            boundary-links
            color="primary"
            @update:model-value="fetchNews"
          />
        </div>
      </div>
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useMarketNewsStore } from 'src/stores/marketNews'
import logoUrl from 'src/assets/logo.png'
import type{ NewsCategory } from 'src/types/marketNews'

const router = useRouter()
const store = useMarketNewsStore()

const currentPage = ref(1)
const search = ref('')
const selectedCategory = ref<string | null>(null)

const news = computed(() => store.newsList)
const pagination = computed(() => store.pagination)

const categoryOptions = computed(() =>
  store.categories.map((c: NewsCategory) => ({ label: c.name, value: c.slug }))
)

async function fetchNews() {
  const filters: Record<string, unknown> = {}
  if (search.value) filters.search = search.value
  if (selectedCategory.value) filters.category_slug = selectedCategory.value
  await store.fetchNews(currentPage.value, 9, filters)
}

function resetAndFetch() {
  currentPage.value = 1
 void fetchNews()
}

function goToDetail(slug: string) {
 void router.push({ name: 'news-detail', params: { slug } })
}

watch(currentPage, () => fetchNews())

onMounted(async () => {
  await store.fetchCategories()
  await fetchNews()
})
</script>

<style scoped>
.container-max { max-width: 1280px; margin: 0 auto; }
.news-card { transition: transform 0.2s; }
.news-card:hover { transform: translateY(-4px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
</style>
