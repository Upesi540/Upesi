<template>
  <q-page class="bg-grey-1">
    <div v-if="store.loading && !news" class="flex flex-center q-pa-xl">
      <q-spinner-dots color="primary" size="40px" />
    </div>

    <div v-else-if="news" class="container-max q-pa-md">
      <!-- Fil d'Ariane -->
      <div class="q-mb-md">
        <q-breadcrumbs>
          <q-breadcrumbs-el label="Accueil" to="/" />
          <q-breadcrumbs-el label="Journal" to="/journal" />
          <q-breadcrumbs-el :label="news.title" />
        </q-breadcrumbs>
      </div>

      <div class="row q-col-gutter-lg">
        <!-- Contenu principal -->
        <div class="col-12 col-md-8">
          <q-card flat bordered>
            <q-img :src="news.featured_image || logoURL" :ratio="16/9" class="rounded-borders" />
            <q-card-section>
              <div class="text-overline text-grey-6">
                <q-chip :color="news.is_urgent ? 'negative' : 'primary'" size="sm" text-color="white">
                  {{ news.is_urgent ? 'Urgent' : news.type }}
                </q-chip>
                {{ news.category?.name }}
              </div>
              <div class="text-h4 text-weight-bold q-mt-sm">{{ news.title }}</div>
              <div class="row items-center q-mt-md text-grey-7">
                <q-icon name="schedule" size="16px" />
                <span class="q-ml-xs">{{ news.formatted_date }}</span>
                <q-icon name="access_time" size="16px" class="q-ml-md" />
                <span class="q-ml-xs">{{ news.reading_time }}</span>
                <q-icon name="person" size="16px" class="q-ml-md" />
                <span class="q-ml-xs">{{ news.author?.name || 'Bourse Agricole' }}</span>
              </div>
              <q-separator class="q-my-md" />
              <div class="q-mt-md">
                <json-renderer :content="news.content??{}" />

              </div>
            </q-card-section>
          </q-card>
        </div>

        <!-- Sidebar : infos complémentaires -->
        <div class="col-12 col-md-4">
          <q-card flat bordered>
            <q-card-section>
              <div class="text-h6">Partager cet article</div>
              <div class="row q-col-gutter-sm q-mt-md">
                <div class="col-auto">
                  <q-btn round color="primary" icon="facebook" @click="share('facebook')" />
                </div>
              </div>
            </q-card-section>
            <q-separator />
            <q-card-section v-if="news.tags?.length">
              <div class="text-h6">Tags</div>
              <div class="q-mt-md">
                <q-chip v-for="tag in news.tags" :key="tag" dense outline>{{ tag }}</q-chip>
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>
    </div>

    <div v-else class="flex flex-center q-pa-xl">
      <div class="text-center">
        <q-icon name="error_outline" size="64px" color="negative" />
        <div class="text-h6">Actualité introuvable</div>
        <q-btn to="/journal" label="Retour au journal" color="primary" flat class="q-mt-md" />
      </div>
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useMarketNewsStore } from 'src/stores/marketNews'
import { JsonRenderer } from '@leo91000/vue-tiptap-renderer'
import logoURL from 'src/assets/logo.png'

const route = useRoute()
const router = useRouter()
const store = useMarketNewsStore()

const news = computed(() => store.currentNews)

function share(platform: string) {
  const url = window.location.href
  let shareUrl = ''
  if (platform === 'facebook') shareUrl = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`
  if (shareUrl) window.open(shareUrl, '_blank')
}

async function loadNews() {
  const slug = route.params.slug as string
  if (slug) {
    await store.fetchNewsBySlug(slug)
    if (!store.currentNews) await router.replace('/journal')
  }
}

onMounted(() => {
 void loadNews()
})
</script>

<style scoped>
.container-max { max-width: 1280px; margin: 0 auto; }
</style>
