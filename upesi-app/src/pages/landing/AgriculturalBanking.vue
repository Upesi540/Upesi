<template>
  <q-page class="bg-white">
    <div class="relative-position q-pa-xl bg-green-10 text-white overflow-hidden" style="min-height: 350px;">
      <div class="column justify-center full-height relative-z">
        <h1 class="text-h3 text-weight-bolder q-mb-md">Ma Banque Agricole</h1>
        <p class="text-h6 text-weight-light opacity-80 max-width-600">
          Le cœur financier de l'agriculture moderne en Afrique. Sécurisez vos transactions et investissez dans
          l'avenir.
        </p>
      </div>
      <q-icon name="account_balance" class="absolute-bottom-right opacity-10" size="300px"
        style="right: -50px; bottom: -50px;" />
    </div>

    <div class="q-pa-lg">
      <div class="text-center q-mb-xl">
        <h2 class="text-h4 text-weight-bold">Un système simple et sécurisé</h2>
        <div class="bg-secondary q-mx-auto q-mt-sm" style="height: 4px; width: 60px; border-radius: 2px;"></div>
      </div>

      <div class="row q-col-gutter-xl items-center">
        <div class="col-12 col-md-4 text-center">
          <q-avatar size="80px" color="green-1" text-color="green-9" icon="wallet" />
          <h3 class="text-h6 q-mt-md">1. Rechargez votre Wallet</h3>
          <p class="text-grey-7">
            Alimentez votre compte instantanément via vos solutions locales préférées.
          </p>

          <div class="row justify-center q-gutter-xs q-mt-sm">
            <q-badge color="orange" text-color="white" label="Orange Money" class="q-py-xs" />
            <q-badge color="yellow-8" text-color="black" label="MTN MoMo" class="q-py-xs" />
            <q-badge color="blue-10" text-color="white" label="Moov Money" class="q-py-xs" />
            <q-badge color="cyan-6" text-color="white" label="Wave" class="q-py-xs" />
            <div>etc...</div>
          </div>

          <div class="row justify-center q-gutter-x-sm q-mt-md items-center">
            <div class="text-caption text-weight-bold text-grey-6">Propulsé par</div>
            <q-chip outline color="indigo-7" text-color="indigo-7" size="sm" icon="bolt">
              FedaPay
            </q-chip>
            <q-icon name="credit_card" color="grey-7" size="xs">
              <q-tooltip>Visa / Mastercard</q-tooltip>
            </q-icon>
          </div>
        </div>

        <div class="col-12 col-md-4 text-center">
          <q-avatar size="80px" color="blue-1" text-color="blue-9" icon="sync_alt" />
          <h3 class="text-h6 q-mt-md">2. Vendez et Encaissez</h3>
          <p class="text-grey-7">
            <strong>Pour les marchands :</strong> Recevez vos paiements instantanément et retirez vos fonds vers votre
            Mobile Money sans délai.
            <br>
            <strong>Pour les acheteurs :</strong> Payez vos intrants et services en un clic.
          </p>
        </div>

        <div class="col-12 col-md-4 text-center">
          <q-avatar size="80px" color="orange-1" text-color="secondary" icon="trending_up" />
          <h3 class="text-h6 q-mt-md">3. Investissez en Bourse</h3>
          <p class="text-grey-7">
            Prenez des positions sur les récoltes futures et profitez de la croissance du marché agricole africain.
          </p>
        </div>
      </div>
    </div>

    <q-separator inset class="q-my-xl" />

    <div class="bg-grey-1 q-pa-xl">
      <div class="row q-col-gutter-lg items-center">
        <div class="col-12 col-md-6">
          <img src="https://cdn.quasar.dev/img/material.png" class="rounded-borders full-width shadow-10"
            style="max-height: 300px; object-fit: cover;">
        </div>
        <div class="col-12 col-md-6">
          <div class="text-h5 text-weight-bold q-mb-md">Plus qu'une banque, un partenaire de croissance</div>
          <q-list>
            <q-item class="q-px-none">
              <q-item-section avatar><q-icon name="check_circle" color="green" /></q-item-section>
              <q-item-section>Gestion transparente des comptes utilisateurs.</q-item-section>
            </q-item>
            <q-item class="q-px-none">
              <q-item-section avatar><q-icon name="check_circle" color="green" /></q-item-section>
              <q-item-section>Paiements sécurisés et traçabilité totale.</q-item-section>
            </q-item>
            <q-item class="q-px-none">
              <q-item-section avatar><q-icon name="check_circle" color="green" /></q-item-section>
              <q-item-section>Accès simplifié au crédit agricole pour les coopératives.</q-item-section>
            </q-item>
          </q-list>
        </div>
      </div>
    </div>

    <div class="q-pa-xl text-center">
      <div class="bg-green-1 text-green-10 q-pa-xl rounded-borders">
        <div class="text-h4 text-weight-bold q-mb-md">Prêt à dynamiser votre activité ?</div>
        <p class="text-body1 q-mb-lg">Rejoignez Upesi dès aujourd'hui et profitez de nos services financiers
          exclusifs.</p>
        <q-btn label="Créer mon compte" color="secondary" size="lg" unelevated no-caps class="text-weight-bold px-xl"
          to="/auth/register" />
        <div class="q-mt-md text-caption text-grey-7">Déjà membre ? <router-link to="/auth/login"
            class="text-green-9 text-weight-bold">Identifiez-vous</router-link></div>
      </div>
    </div>
  </q-page>
</template>

<script setup lang="ts">
// Page statique - Pas de logique complexe requise
</script>

<style lang="scss" scoped>
.max-width-600 {
  max-width: 600px;
}

.relative-z {
  position: relative;
  z-index: 2;
}

.opacity-80 {
  opacity: 0.8;
}

.opacity-10 {
  opacity: 0.1;
}

.px-xl {
  padding-left: 48px;
  padding-right: 48px;
}

h1,
h2,
h3 {
  margin: 0;
}
</style>
