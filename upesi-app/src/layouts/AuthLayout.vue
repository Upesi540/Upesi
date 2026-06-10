<template>
  <q-layout view="lHh Lpr lFf">
    <div class="fixed-top-right z-max q-pa-md">
      <q-btn to="/" flat round color="grey-9" icon="close" class="back-home-btn">
        <q-tooltip class="bg-primary">Retour à l'accueil</q-tooltip>
      </q-btn>
    </div>

    <q-page-container>
      <div class="row auth-wrapper">

        <div class="col-12 col-md-7 flex flex-center bg-primary gt-sm relative-position">
          <div class="absolute-full low-overlay"></div>
          <div class="z-top text-white q-pa-xl text-center">
            <img :src="logoUrl" height="90" alt="Upesi Logo" class="q-mb-lg">
            <h1 class="text-h4 text-weight-bolder q-mb-md">Upesi</h1>
            <p class="text-h6 text-weight-light opacity-80">
              La bourse agricole numérique au bout de vos doigts.
            </p>
          </div>
          <q-img src="https://images.unsplash.com/photo-1523348837708-15d4a09cfac2?auto=format&fit=crop&w=1470&q=80"
            class="absolute-full" style="opacity: 0.3;" />
        </div>

        <div class="col-12 col-md-5 bg-grey-1 column items-center">
          <div class="auth-card-container full-width q-px-lg q-my-auto q-py-xl">
            <router-view v-slot="{ Component }">
              <transition appear enter-active-class="animated fadeInRight" leave-active-class="animated fadeOutLeft"
                mode="out-in">
                <component :is="Component" />
              </transition>
            </router-view>
          </div>
        </div>

      </div>
    </q-page-container>
  </q-layout>
</template>

<script setup lang="ts">
import logoUrl from 'src/assets/logo.png';
</script>

<style lang="scss" scoped>
.auth-wrapper {
  min-height: 100vh;
}

.auth-card-container {
  max-width: 480px;
}

.low-overlay {
  background: linear-gradient(135deg, rgba($primary, 0.8) 0%, rgba(0, 0, 0, 0.4) 100%);
}

.opacity-80 {
  opacity: 0.8;
}

// Style du bouton de retour
.back-home-btn {
  background: rgba(255, 255, 255, 0.6);
  backdrop-filter: blur(4px);
  border: 1px solid rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;

  &:hover {
    background: white;
    transform: rotate(90deg);
  }
}

/* Correction spécifique pour mobile */
@media (max-width: 1023px) {
  .auth-wrapper {
    display: block;
  }

  // Sur mobile, on le rend un peu plus visible
  .back-home-btn {
    background: white;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
  }
}
</style>
