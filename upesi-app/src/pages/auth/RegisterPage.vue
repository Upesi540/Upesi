<template>
  <q-page class="flex flex-center bg-grey-1">
    <q-card flat class="bg-transparent full-width no-shadow" style="max-width: 500px">
      <q-card-section class="text-center q-pb-md">
        <div class="lt-md q-mb-md">
          <img :src="logoUrl" height="40" alt="Upesi Logo">
        </div>
        <div class="text-h5 text-weight-bold text-primary">Créer un compte</div>
        <div class="text-subtitle2 text-grey-6">Rejoignez Upesi</div>
      </q-card-section>

      <q-card-section class="q-pt-none">
        <q-form @submit.prevent="handleRegister" class="q-gutter-y-sm">
          <div class="row q-col-gutter-sm">
            <div class="col-12 col-sm-6">
              <q-input v-model="form.first_name" label="Prénom" outlined rounded bg-color="white" class="modern-input"
                :rules="[val => !!val || 'Requis']">
                <template v-slot:prepend><q-icon name="person_outline" color="primary" /></template>
              </q-input>
            </div>
            <div class="col-12 col-sm-6">
              <q-input v-model="form.last_name" label="Nom" outlined rounded bg-color="white" class="modern-input"
                :rules="[val => !!val || 'Requis']">
                <template v-slot:prepend><q-icon name="person_outline" color="primary" /></template>
              </q-input>
            </div>
          </div>

          <q-input v-model="form.identifier" label="Email" type="email" outlined rounded bg-color="white"
            class="modern-input" :rules="[val => !!val || 'Email requis']">
            <template v-slot:prepend><q-icon name="mail_outline" color="primary" /></template>
          </q-input>

          <q-input v-model="form.password" label="Mot de passe" type="password" outlined rounded bg-color="white"
            class="modern-input"
            :rules="[val => !!val || 'Mot de passe requis', val => val.length >= 8 || '8 caractères minimum']">
            <template v-slot:prepend><q-icon name="lock_outline" color="primary" /></template>
          </q-input>

          <q-input v-model="form.password_confirmation" label="Confirmer le mot de passe" type="password" outlined
            rounded bg-color="white" class="modern-input"
            :rules="[val => val === form.password || 'Les mots de passe ne correspondent pas']"> <template
              v-slot:prepend><q-icon name="lock_outline" color="primary" /></template>
          </q-input>

          <div class="q-pt-lg">
            <q-btn label="Créer mon compte" type="submit" color="primary" rounded unelevated
              class="full-width q-py-md text-weight-bold shadow-2" :loading="auth.loading" />
          </div>
        </q-form>
      </q-card-section>

      <q-card-section class="text-center">
        <div class="text-grey-7">
          Déjà inscrit ?
          <q-btn to="/auth/login" flat no-caps label="Se connecter" color="secondary" class="text-weight-bolder" />
        </div>
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useAuthStore } from 'src/stores/auth';
import { useRouter } from 'vue-router';
import axios from 'axios';
import logoUrl from 'src/assets/logo.png';
import { useQuasar } from 'quasar';

const auth = useAuthStore();
const $q = useQuasar();
const router = useRouter();

const form = ref({
  first_name: '',
  last_name: '',
  identifier: '',
  password: '',
  password_confirmation: ''
});


async function handleRegister() {
  try {
    await auth.register(form.value);
    $q.notify({ color: 'positive', message: 'Bienvenue chez Upesi. Si vous voulez vendre sur la plateforme cliquer sur Vendre sur Upesi !  ', icon: 'done' });

    await router.push('/user/profile');

  } catch (error: unknown) {
    let msg = "Erreur lors de l'inscription.";
    if (axios.isAxiosError(error)) {
      msg = error.response?.data?.message || msg;
    }
    $q.notify({ color: 'negative', message: msg, icon: 'error' });
  }
}
// async function openFilament(destination: string) {
//   // 1. Affiche le loader Quasar
//   Loading.show({
//     spinner: QSpinnerFacebook,
//     spinnerColor: 'white',
//     backgroundColor: 'primary',
//     message: 'Connexion sécurisée à votre espace...',
//     messageColor: 'white'
//   });

//   try {
//     // 2. Appelle ton action Magic Link
//     // destination sera passée à Laravel pour rediriger au bon endroit après le login
//     const url = await auth.generateMagicLink(destination);

//     if (url) {
//       // 3. Ouvre l'URL (InAppBrowser sur mobile, nouvel onglet sur Web)
//       openURL(url);
//     } else {
//       throw new Error('Erreur de génération du lien');
//     }
//   } catch {
//     $q.notify({
//       color: 'negative',
//       message: 'Erreur technique lors de la redirection.',
//       icon: 'report_problem'
//     });
//   } finally {
//     // 4. Cache le loader
//     Loading.hide();
//   }
// }

</script>

<style lang="scss" scoped>
.modern-input {
  :deep(.q-field__control) {
    height: 56px;
    transition: all 0.3s;

    &:hover {
      background: white !important;
    }
  }

  :deep(.q-field__bottom) {
    padding-top: 4px;
  }
}

.account-card {
  cursor: pointer;
  transition: all 0.2s ease;
  border-radius: 12px !important;

  &:hover {
    border-color: var(--q-primary);
    transform: translateY(-2px);
  }
}

.account-card-active {
  cursor: pointer;
  border: 2px solid var(--q-primary);
  background: rgba(var(--q-primary), 0.05);
  border-radius: 12px !important;
  transition: all 0.2s ease;
}
</style>
