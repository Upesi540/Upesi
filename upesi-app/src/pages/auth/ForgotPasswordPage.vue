<template>
  <q-page class="flex flex-center">
    <q-card class="q-pa-md" style="width: 400px">
      <q-card-section>
        <div class="text-h6">Récupération</div>
        <div class="text-caption">Entrez votre email pour recevoir un lien.</div>
      </q-card-section>
      <q-card-section>
        <q-input v-model="email" label="Email" filled />
        <q-btn label="Envoyer le lien" color="primary" class="full-width q-mt-md" @click="onSubmit" />
      </q-card-section>
    </q-card>
  </q-page>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { useAuthStore } from 'src/stores/auth';
import { useQuasar } from 'quasar';

const email = ref('');
const auth = useAuthStore();
const $q = useQuasar();

async function onSubmit() {
  await auth.forgotPassword(email.value);
  $q.notify('Lien envoyé ! Vérifiez vos emails.');
}
</script>
