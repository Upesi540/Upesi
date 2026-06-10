<template>
  <q-page class="bg-white">
    <div class="header-gradient q-py-xl">
      <div class="container-max q-pa-lg text-center">
        <div class="text-overline text-primary-light text-weight-bolder tracking-widest q-mb-md">
          REJOIGNEZ LE RÉSEAU UPESI
        </div>
        <h1 class="text-h2 text-weight-bolder text-white q-ma-none leading-tight">
          Monétisez votre <br /><span class="text-orange-4">expertise & matériel</span>
        </h1>
        <p class="text-h6 text-white opacity-80 q-mt-lg q-mx-auto" style="max-width: 700px">
          Vous avez un tracteur, un drone ou une expertise agronomique ?
          Inscrivez-vous pour servir des milliers d'agriculteurs et sécuriser vos revenus.
        </p>
        <div class="q-mt-xl">
          <q-btn to="/user/profile"
            unelevated
            rounded
            color="secondary"
            label="Devenir prestataire maintenant"
            size="xl"
            class="q-px-xl btn-glow text-weight-bold"
            no-caps
            icon-right="rocket_launch"
          />
        </div>
      </div>
    </div>

    <div class="container-max q-py-xl q-px-md">
      <div class="row justify-center q-mb-xl">
        <div class="text-center">
          <h2 class="text-h4 text-weight-bold text-grey-10">Le processus d'inscription</h2>
          <div class="h-underline bg-primary q-mx-auto q-mt-sm"></div>
        </div>
      </div>

      <div class="row q-col-gutter-xl">
        <div v-for="(step, index) in steps" :key="index" class="col-12 col-md-3">
          <div class="process-step text-center">
            <div class="step-icon-wrapper q-mx-auto q-mb-lg">
              <q-icon :name="step.icon" size="40px" color="primary" />
              <div class="step-badge">{{ index + 1 }}</div>
            </div>
            <div class="text-h6 text-weight-bold q-mb-sm text-grey-9">{{ step.title }}</div>
            <p class="text-body2 text-grey-7">{{ step.desc }}</p>
          </div>
        </div>
      </div>
    </div>

    <div class="bg-grey-1 q-py-xl">
      <div class="container-max q-px-md">
        <div class="process-banner q-pa-xl flex flex-center shadow-xl">
          <div class="row items-center full-width">
            <div class="col-12 col-md-8">
              <h3 class="text-h5 text-weight-bolder q-ma-none">Prêt à configurer votre profil ?</h3>
              <p class="text-grey-7 q-mt-sm q-mb-none">
                Connectez-vous, allez dans <strong>Mon Upesi</strong> > <strong>Ajouter un profil marchand</strong>
                et choisissez <strong>Prestataire</strong>.
              </p>
            </div>
            <div class="col-12 col-md-4 text-right sm-text-left sm-q-mt-md">
              <q-btn
                to="/user/profile"
                color="primary"
                label="Aller sur mon tableau de bord"
                no-caps
                unelevated
                rounded
                size="lg"
                class="q-px-lg"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </q-page>
</template>

<script setup lang="ts">
const steps = [
  {
    icon: 'person_add_alt_1',
    title: 'Compte Upesi',
    desc: "Connectez-vous ou créez votre compte utilisateur pour démarrer."
  },
  {
    icon: 'dashboard_customize',
    title: 'Mon Upesi',
    desc: "Accédez à votre espace personnel pour gérer vos activités."
  },
  {
    icon: 'storefront',
    title: 'Profil Marchand',
    desc: "Cliquez sur 'Ajouter un profil marchand' dans votre menu."
  },
  {
    icon: 'engineering',
    title: 'Devenir Prestataire',
    desc: "Choisissez le profil 'Prestataire' et listez vos services."
  }
];
</script>

<style lang="scss" scoped>
.container-max { max-width: 1200px; margin: 0 auto; }

// Hero Style
.header-gradient {
  background: linear-gradient(135deg, #1b5e20 0%, #004d40 100%);
  min-height: 60vh;
  display: flex;
  align-items: center;
  border-radius: 0 0 80px 80px;
}

.text-primary-light { color: #a8ff78; }
.tracking-widest { letter-spacing: 0.25em; }
.leading-tight { line-height: 1.1; }

// Process Steps
.step-icon-wrapper {
  width: 90px;
  height: 90px;
  background: #f1f8e9;
  border-radius: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
  transition: transform 0.3s ease;

  &:hover {
    transform: rotate(5deg) scale(1.05);
  }
}

.step-badge {
  position: absolute;
  top: -5px;
  right: -5px;
  background: var(--q-primary);
  color: white;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: bold;
  font-size: 0.8rem;
  border: 3px solid white;
}

.h-underline {
  width: 60px;
  height: 4px;
  border-radius: 2px;
}

// Banner CTA
.process-banner {
  background: white;
  border-radius: 30px;
  border-left: 8px solid var(--q-primary);
}

.btn-glow {
  box-shadow: 0 10px 30px rgba(230, 81, 0, 0.4);
  &:hover {
    box-shadow: 0 15px 40px rgba(230, 81, 0, 0.6);
  }
}

@media (max-width: $breakpoint-xs-max) {
  .header-gradient { border-radius: 0 0 40px 40px; }
  .text-h2 { font-size: 2.5rem; }
}
</style>
