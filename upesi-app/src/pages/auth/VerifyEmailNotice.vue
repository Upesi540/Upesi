<template>
  <q-page class="flex flex-center bg-grey-2">
    <q-card flat bordered class="q-pa-lg text-center" style="max-width: 450px; border-radius: 12px">
      <q-card-section>
        <q-icon name="mark_email_read" color="primary" size="80px" />
        <div class="text-h5 q-mt-md text-weight-bold">Validez votre compte</div>
        <p class="text-grey-8 q-mt-md">
          Bienvenue sur <strong>Upesi Market</strong> ! <br>
          Pour commencer à publier des offres ou effectuer des achats, vous devez cliquer sur le lien de confirmation
          envoyé à votre adresse email.
        </p>
      </q-card-section>

      <q-card-section class="q-pt-none">
        <q-banner rounded class="bg-blue-1 text-blue-9 text-left">
          <template v-slot:avatar>
            <q-icon name="info" color="blue" />
          </template>
          Vous n'avez rien reçu ? Vérifiez votre dossier <strong>spams</strong> ou demandez un nouveau lien ci-dessous.
        </q-banner>
      </q-card-section>

      <q-card-actions vertical align="center" class="q-gutter-y-sm">
        <q-btn color="primary" label="Renvoyer l'email de validation" class="full-width" unelevated :loading="loading"
          @click="resendEmail" />
        <q-btn flat color="grey-7" label="Retour à l'accueil" to="/" class="full-width" />
      </q-card-actions>
    </q-card>
  </q-page>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useAuthStore } from 'src/stores/auth';
import { useQuasar } from 'quasar';

const $q = useQuasar();
const authStore = useAuthStore();
const loading = ref(false);
onMounted(async() => {
  await resendEmail();
});

const resendEmail = async () => {
  loading.value = true;
  try {
    // On appelle l'action du store
    await authStore.sendVerificationEmail();

    // Si on arrive ici, c'est que c'est réussi (pas d'erreur 4xx ou 5xx)
    $q.notify({
      type: 'positive',
      message: 'Un nouveau lien a été envoyé !',
      position: 'top'
    });
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: "Impossible d'envoyer l'email.",
      position: 'top'
    });
    console.error('Erreur de validation email:', error);

  } finally {
    loading.value = false;
  }
};
</script>
