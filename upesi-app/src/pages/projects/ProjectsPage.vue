<template>
  <q-page class="bg-grey-1">
    <div class="container-max q-pa-md">
      <!-- En-tête -->
      <div class="text-center q-mb-xl">
        <h1 class="text-h4 text-weight-bold">Nos projets</h1>
        <p class="text-subtitle1 text-grey-7">
          Découvrez les réalisations qui transforment l'agriculture africaine
        </p>
      </div>

      <!-- Filtres rapides (optionnel) -->
      <div class="row justify-center q-mb-lg">
        <q-btn-toggle
          v-model="statusFilter"
          :options="statusOptions"
          flat
          rounded
          toggle-color="primary"
          @update:model-value="resetAndFetch"
        />
      </div>

      <!-- Skelettons pendant le chargement -->
      <div v-if="projectStore.loading" class="row q-col-gutter-md">
        <div v-for="n in 6" :key="n" class="col-12 col-sm-6 col-md-4">
          <q-card flat bordered>
            <q-skeleton height="200px" square />
            <q-card-section>
              <q-skeleton type="text" width="60%" />
              <q-skeleton type="text" width="40%" class="q-mt-sm" />
            </q-card-section>
          </q-card>
        </div>
      </div>

      <!-- Grille des projets -->
      <div v-else>
        <div v-if="projects.length" class="row q-col-gutter-md">
          <div
            v-for="project in projects"
            :key="project.id"
            class="col-12 col-sm-6 col-md-4"
          >
            <q-card
              flat
              bordered
              class="project-card cursor-pointer"
              @click="goToDetail(project.slug)"
            >
              <q-img
                :src="project.image_path || logoUrl"
                :ratio="16/9"
                class="rounded-borders"
              >
                <div class="absolute-bottom-right q-ma-sm">
                  <q-chip
                    :color="project.is_ongoing ? 'positive' : 'grey-7'"
                    text-color="white"
                    size="sm"
                  >
                    {{ project.is_ongoing ? 'En cours' : 'Terminé' }}
                  </q-chip>
                </div>
              </q-img>

              <q-card-section>
                <div class="text-h6 text-weight-bold">{{ project.title }}</div>
                <div class="row items-center q-mt-xs text-grey-7">
                  <q-icon name="location_on" size="14px" />
                  <span class="text-caption q-ml-xs">{{ project.location || 'Lieu non précisé' }}</span>
                </div>
                <div class="row items-center q-mt-xs text-grey-7">
                  <q-icon name="schedule" size="14px" />
                  <span class="text-caption q-ml-xs">{{ project.duration || 'Durée indéterminée' }}</span>
                </div>
                <div class="q-mt-sm text-primary text-weight-medium">
                  {{ project.client || 'Partenariat' }}
                </div>
              </q-card-section>
            </q-card>
          </div>
        </div>

        <!-- Aucun résultat -->
        <div v-else class="text-center q-pa-xl text-grey-6">
          <q-icon name="construction" size="64px" />
          <div class="text-h6">Aucun projet trouvé</div>
          <div class="text-caption">Revenez plus tard pour découvrir nos réalisations.</div>
        </div>

        <!-- Pagination -->
        <div v-if="pagination && pagination.last_page > 1" class="flex flex-center q-mt-xl">
          <q-pagination
            v-model="currentPage"
            :max="pagination.last_page"
            :max-pages="5"
            direction-links
            boundary-links
            color="primary"
            @update:model-value="fetchProjects"
          />
        </div>
      </div>
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useProjectStore } from 'src/stores/project'
import logoUrl from 'src/assets/logo.png'

const router = useRouter()
const projectStore = useProjectStore()

// Données réactives
const currentPage = ref(1)
const statusFilter = ref<string | null>(null)

const projects = computed(() => projectStore.projects)
const pagination = computed(() => projectStore.pagination)

// Options de filtre
const statusOptions = [
  { label: 'Tous', value: null },
  { label: 'En cours', value: 'ongoing' },
  { label: 'Terminés', value: 'completed' }
]

// Récupération des projets
async function fetchProjects() {
  const filters: Record<string, unknown> = {}
  if (statusFilter.value) filters.status = statusFilter.value

  await projectStore.fetchProjects(currentPage.value, 9, filters)
}

function resetAndFetch() {
  currentPage.value = 1
  void fetchProjects()
}

function goToDetail(slug: string) {
 void router.push({ name: 'project-detail', params: { slug } })
}

// Recharger quand la page change
watch(currentPage, () => void fetchProjects())

onMounted(() => {
 void fetchProjects()
})
</script>

<style lang="scss" scoped>
.container-max {
  max-width: 1280px;
  margin: 0 auto;
}

.project-card {
  transition: transform 0.2s ease, box-shadow 0.2s ease;

  &:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
  }
}
</style>
