<template>
  <q-page class="flex flex-center bg-grey-1">
    <q-card flat class="bg-transparent full-width no-shadow">

      <q-card-section class="text-center q-pb-xl">
        <div class="lt-md q-mb-md">
          <img :src="logoUrl" height="40" alt="Upesi Logo">
        </div>
        <div class="text-h5 text-weight-bold text-primary">Connexion</div>
        <div class="text-subtitle2 text-grey-6">Heureux de vous revoir !</div>
      </q-card-section>

      <q-card-section class="q-pt-none">
        <q-form @submit="handleLogin" class="q-gutter-y-lg">

          <q-input
            v-model="form.identifier"
            label="Email"
            type="email"
            outlined
            rounded
            bg-color="white"
            class="modern-input"
            lazy-rules
            :rules="[val => !!val || 'Email requis']"
          >
            <template v-slot:prepend>
              <q-icon name="mail_outline" color="primary" />
            </template>
          </q-input>

          <q-input
            v-model="form.password"
            label="Mot de passe"
            type="password"
            outlined
            rounded
            bg-color="white"
            class="modern-input"
            lazy-rules
            :rules="[val => !!val || 'Mot de passe requis']"
          >
            <template v-slot:prepend>
              <q-icon name="lock_open" color="primary" />
            </template>
          </q-input>

          <div class="q-pt-md">
            <q-btn
              label="Se connecter"
              type="submit"
              color="primary"
              rounded
              unelevated
              class="full-width q-py-md text-weight-bold shadow-2"
              :loading="authStore.loading"
            />
          </div>
        </q-form>
      </q-card-section>

      <q-card-section class="text-center">
        <q-btn :href="filamentPasswordResetUrl" target="_blank" flat no-caps label="Mot de passe oublié ?" color="primary" size="sm" class="text-weight-bold" />

        <div class="q-mt-xl text-grey-7">
          Vous n'avez pas de compte ?
          <q-btn to="/auth/register" flat no-caps label="S'inscrire" color="secondary" class="text-weight-bolder" />
        </div>
      </q-card-section>
    </q-card>
  </q-page>
</template>


<script setup lang="ts">
import { computed, ref } from 'vue';
import { useAuthStore } from 'src/stores/auth';
import { useRouter } from 'vue-router';
import { useQuasar } from 'quasar';
import axios from 'axios'; // Importe l'objet axios ET le type
import logoUrl from 'src/assets/logo.png';
import { FILAMENT_URL } from 'src/boot/axios';

const authStore = useAuthStore();
const router = useRouter();
const $q = useQuasar();

const form = ref({
  identifier: '',
  password: ''
});

const filamentPasswordResetUrl = computed(() => {
  return `${FILAMENT_URL}/app/password-reset/request`;
});

async function handleLogin() {
  try {
    await authStore.login(form.value);
    $q.notify({ color: 'positive', message: 'Bienvenue sur Upesi !', icon: 'check' });
    // Correction de la route : ajoute le slash initial pour éviter une route relative cassée
    await router.push('/user/profile');

  } catch (error: unknown) {
    let errorMessage = 'Identifiants incorrects';

    // 2. Utilise axios.isAxiosError pour un typage sécurisé
    if (axios.isAxiosError(error)) {
      // Maintenant TypeScript sait que 'error' est un AxiosError
      // On récupère le message d'erreur envoyé par Laravel (ex: validation ou 401)
      errorMessage = error.response?.data?.message || errorMessage;
    } else if (error instanceof Error) {
      errorMessage = error.message;
    }

    $q.notify({
      color: 'negative',
      message: errorMessage,
      icon: 'report_problem'
    });

    if (process.env.DEV) {
      console.error('[Login Error]:', error);
    }
  }
}
</script>

<style lang="scss" scoped>
.modern-input {
  :deep(.q-field__control) {
    height: 60px; // Un peu plus haut pour le confort mobile
    transition: all 0.3s;
    &:hover {
      background: white !important;
    }
  }
}

// Supprime les ombres par défaut pour un look flat sur fond gris
.q-card {
  @media (max-width: $breakpoint-sm-max) {
    background: transparent !important;
  }
}
</style>
