<template>
  <q-page class="bg-grey-1">
    <div v-if="store.isLoading" class="flex flex-center q-pa-xl">
      <q-spinner color="primary" size="40px" />
      <span class="q-ml-sm">Chargement...</span>
    </div>

    <div v-else-if="store.error" class="flex flex-center q-pa-xl">
      <div class="text-center">
        <q-icon name="error_outline" size="60px" color="red" />
        <div class="text-h6 q-mt-md">{{ store.error }}</div>
        <q-btn flat label="Retour" @click="$router.back()" class="q-mt-md" />
      </div>
    </div>

    <div v-else-if="store.document" class="container-narrow q-mx-auto q-pa-lg">
      <div class="text-h4 text-weight-bold text-primary q-mb-sm">
        {{ store.document.title }}
      </div>
      <div v-if="store.document.version" class="text-caption text-grey-7 q-mb-md">
        Version {{ store.document.version }} – Dernière mise à jour : {{ formatDate(store.document.updated_at) }}
      </div>
      <q-separator class="q-mb-lg" />

      <!-- Rendu du contenu rich text (Tiptap) -->
      <div class="legal-content">
        <JsonRenderer :content="store.document.content??{}" />
      </div>
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import { useLegalStore } from 'src/stores/legal';
import { JsonRenderer } from '@leo91000/vue-tiptap-renderer';

const route = useRoute();
const store = useLegalStore();

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('fr-FR', {
    year: 'numeric', month: 'long', day: 'numeric'
  });
};

const loadDocument = async() => {
  const slug = route.params.slug as string;
  if (slug) {
    await store.fetchDocument(slug);
  }
};

// Chargement initial
onMounted(async () => {
  await loadDocument();
} );

// Réagir aux changements d'URL (si on navigue d'un document à un autre)
watch(() => route.params.slug, () => {
  void loadDocument();
});
</script>

<style scoped lang="scss">
.container-narrow {
  max-width: 900px;
  margin: 0 auto;
}

.legal-content {
  :deep(h1) {
    font-size: 1.8rem;
    font-weight: 700;
    margin-top: 1.5rem;
    margin-bottom: 1rem;
  }
  :deep(h2) {
    font-size: 1.5rem;
    font-weight: 600;
    margin-top: 1.2rem;
    margin-bottom: 0.8rem;
  }
  :deep(p) {
    margin-bottom: 1rem;
    line-height: 1.6;
    color: #374151;
  }
  :deep(ul), :deep(ol) {
    margin: 1rem 0;
    padding-left: 2rem;
  }
  :deep(li) {
    margin-bottom: 0.5rem;
  }
}
</style>