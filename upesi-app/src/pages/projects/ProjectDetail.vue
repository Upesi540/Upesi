<template>
  <q-page class="bg-grey-1">
    <div v-if="projectStore.loading && !project" class="flex flex-center q-pa-xl">
      <q-spinner-dots color="primary" size="40px" />
    </div>

    <div v-else-if="project" class="container-max q-pa-md">
      <!-- Fil d'Ariane -->
      <div class="q-mb-md">
        <q-breadcrumbs>
          <q-breadcrumbs-el label="Accueil" to="/" />
          <q-breadcrumbs-el label="Projets" to="/projets" />
          <q-breadcrumbs-el :label="project.title" />
        </q-breadcrumbs>
      </div>

      <!-- En-tête du projet -->
      <div class="row q-col-gutter-lg">
        <div class="col-12 col-md-8">
          <q-img
            :src="project.image_path || logoUrl"
            :ratio="16/9"
            class="rounded-borders"
            fit="cover"
          />
        </div>
        <div class="col-12 col-md-4">
          <q-card flat bordered class="full-height">
            <q-card-section>
              <div class="text-overline text-grey-6">Projet</div>
              <div class="text-h4 text-weight-bold">{{ project.title }}</div>

              <q-separator class="q-my-md" />

              <div class="row q-col-gutter-sm">
                <div class="col-6">
                  <div class="text-caption text-grey-6">Statut</div>
                  <q-badge :color="project.is_ongoing ? 'positive' : 'grey-7'">
                    {{ project.is_ongoing ? 'En cours' : 'Terminé' }}
                  </q-badge>
                </div>
                <div class="col-6">
                  <div class="text-caption text-grey-6">Durée</div>
                  <div>{{ project.duration || '—' }}</div>
                </div>
                <div class="col-12">
                  <div class="text-caption text-grey-6">Client / Partenaire</div>
                  <div class="text-weight-medium">{{ project.client || 'Non spécifié' }}</div>
                </div>
                <div class="col-12">
                  <div class="text-caption text-grey-6">Localisation</div>
                  <div class="row items-center">
                    <q-icon name="location_on" size="16px" class="q-mr-xs" />
                    {{ project.location || 'Non spécifiée' }}
                  </div>
                </div>
                <div class="col-12">
                  <div class="text-caption text-grey-6">Période</div>
                  <div>
                    {{ formatDate(project.start_date) }}
                    {{ project.end_date ? ` - ${formatDate(project.end_date)}` : ' - Aujourd’hui' }}
                  </div>
                </div>
              </div>
            </q-card-section>
          </q-card>
        </div>
      </div>

      <!-- Description -->
      <div class="q-mt-xl">
        <q-card flat bordered>
          <q-card-section>
            <div class="text-h6 text-weight-bold">À propos du projet</div>
          <json-renderer :content="project.description??{}" />
          </q-card-section>
        </q-card>
      </div>

      <!-- Galerie (si existante) -->
      <div v-if="project.gallery && project.gallery.length" class="q-mt-xl">
        <div class="text-h6 text-weight-bold q-mb-md">Galerie</div>
        <div class="row q-col-gutter-sm">
          <div
            v-for="(img, idx) in project.gallery"
            :key="idx"
            class="col-6 col-sm-4 col-md-3"
          >
            <q-img
              :src="img"
              :ratio="1"
              class="rounded-borders cursor-pointer gallery-img"
              @click="openGallery(idx)"
            />
          </div>
        </div>
      </div>

      <!-- Témoignages -->
      <div v-if="testimonials.length" class="q-mt-xl q-mb-xl">
        <div class="text-h6 text-weight-bold text-center q-mb-md">Ils parlent de ce projet</div>
        <div class="row justify-center">
          <div class="col-12 col-md-8">
            <q-carousel
              v-model="slide"
              animated
              infinite
              arrows
              navigation
              class="testimonial-carousel"
            >
              <q-carousel-slide
                v-for="(testimonial, idx) in testimonials"
                :key="idx"
                :name="idx"
                class="text-center"
              >
                <q-icon name="format_quote" size="40px" color="primary" />
                <div class="text-h6 q-mt-md">{{ testimonial.content }}</div>
                <div class="text-subtitle2 q-mt-md text-primary">
                  — {{ testimonial.author }}, {{ testimonial.role }}
                </div>
              </q-carousel-slide>
            </q-carousel>
          </div>
        </div>
      </div>
    </div>

    <!-- Erreur -->
    <div v-else class="flex flex-center q-pa-xl">
      <div class="text-center">
        <q-icon name="error_outline" size="64px" color="negative" />
        <div class="text-h6">Projet introuvable</div>
        <q-btn to="/projets" label="Retour aux projets" color="primary" flat class="q-mt-md" />
      </div>
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useProjectStore } from 'src/stores/project'
import logoUrl from 'src/assets/logo.png'
import type{ Testimonial } from 'src/types/project'
import { JsonRenderer } from '@leo91000/vue-tiptap-renderer'

const route = useRoute()
const router = useRouter()
const projectStore = useProjectStore()

const slide = ref(0)

const project = computed(() => projectStore.currentProject)

// Extraire les témoignages (si stockés dans un champ 'testimonials' de type array)
const testimonials = computed(() => {
  const t = project.value?.testimonials
  if (!t || !Array.isArray(t)) return []
  return (t).map((item: Testimonial) => ({
    content: item.content || item.text || '',
    author: item.author || '',
    role: item.role || ''
  }))
})

// Description formatée (HTML si contenu en JSON)


function formatDate(dateStr: string | null): string {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleDateString('fr-FR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

function openGallery(startIndex: number) {
  // Implémentez un lightbox (ex: quasar QDialog avec galerie)
  // Pour l'exemple, on ouvre simplement la première image
  if (project.value?.gallery) {
    window.open(project.value.gallery[startIndex], '_blank')
  }
}

async function loadProject() {
  const slug = route.params.slug as string
  if (slug) {
    await projectStore.fetchProjectBySlug(slug)
    if (!projectStore.currentProject) {
      // Rediriger ou afficher erreur
     void router.replace('/projects')
    }
  }
}

onMounted(() => {
  void loadProject()
})
</script>

<style lang="scss" scoped>
.container-max {
  max-width: 1280px;
  margin: 0 auto;
}

.gallery-img {
  transition: transform 0.2s;
  &:hover {
    transform: scale(1.02);
  }
}

.testimonial-carousel {
  background: white;
  border-radius: 24px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
</style>
