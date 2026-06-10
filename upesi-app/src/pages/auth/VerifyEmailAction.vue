<template>
  <q-page class="flex flex-center bg-grey-1">
    <q-card flat class="text-center bg-transparent">
      <!-- ÉTAT : CHARGEMENT -->
      <div v-if="status === 'loading'">
        <q-spinner-dots color="primary" size="60px" />
        <div class="text-h6 q-mt-md text-primary">Vérification en cours...</div>
        <p class="text-grey-7">Nous confirmons vos informations auprès du serveur Upesi.</p>
      </div>

      <!-- ÉTAT : SUCCÈS -->
      <q-card-section v-if="status === 'success'" class="animate-fade">
        <q-icon name="check_circle" color="positive" size="100px" />
        <div class="text-h4 text-weight-bold q-mt-md">Compte activé !</div>
        <p class="text-subtitle1 text-grey-8 q-mt-sm">
          Votre adresse email a été validée avec succès.
        </p>
        <p class="text-grey-6 italic">Redirection automatique dans quelques secondes...</p>
        <q-btn color="primary" label="Accéder à mon compte" to="/user/profile" unelevated class="q-mt-md" />
      </q-card-section>

      <!-- ÉTAT : ERREUR -->
      <q-card-section v-if="status === 'error'">
        <q-icon name="report_problem" color="negative" size="100px" />
        <div class="text-h5 text-weight-bold q-mt-md">Lien invalide ou expiré</div>
        <p class="text-grey-8 q-mt-sm">
          Ce lien de vérification n'est plus valable. Veuillez vous connecter pour demander un nouveau lien.
        </p>
        <q-btn outline color="primary" label="Retour au Login" to="/auth/login" class="q-mt-md" />
      </q-card-section>
    </q-card>
  </q-page>
</template>
<script setup lang="ts">
import { useAuthStore } from 'src/stores/auth';
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';

const route = useRoute();
const router = useRouter();
const status = ref<'loading' | 'success' | 'error'>('loading');
const authStore = useAuthStore();

onMounted(() => {
  // On récupère le statut envoyé par la redirection Laravel (?status=verified)
  const result = route.query.status;

  if (result === 'verified' || result === 'already_verified') {
    status.value = 'success';

    // On attend 3.5s pour que l'utilisateur voit le message de succès
    setTimeout(() => {
      // S'il est déjà logué sur Quasar, on l'envoie sur son profil
      // Sinon, on le laisse aller au login pour finir sa session
      if (authStore.isAuthenticated) {
        void router.push('/user/profile');
      } else {
        void router.push('/auth/login');
      }
    }, 10000);
  } else {
    // Si status est 'error' ou vide
    status.value = 'error';
  }
});
</script>

<style scoped>
.animate-fade {
  animation: fadeIn 0.8s ease-in;
}
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}
</style>
