<template>
  <q-page class="bg-white">
    <div class="relative-position q-pa-xl bg-primary text-white overflow-hidden" style="min-height: 400px;">
      <div class="column justify-center full-height relative-z container-max">
        <q-badge color="secondary" label="Réseau Upesi Continental" class="q-mb-md q-py-xs self-start" />
        <h1 class="text-h3 text-weight-bolder q-mb-md">La Logistique sans frontières</h1>
        <p class="text-h6 text-weight-light opacity-80 max-width-600">
          Du champ de l'agriculteur aux marchés internationaux. Nous connectons les zones rurales aux hubs urbains
          grâce à notre flotte certifiée couvrant l'Afrique de l'Ouest et au-delà.
        </p>
        <div class="row q-gutter-sm q-mt-lg">
          <q-btn to="/user/profile" label="Devenir Transporteur" color="secondary" unelevated no-caps
            class="text-weight-bold btn-glow" size="lg" icon-right="rocket_launch" />
        </div>
      </div>
      <q-icon name="public" class="absolute-bottom-right opacity-10" size="350px"
        style="right: -40px; bottom: -40px;" />
    </div>

    <div class="q-pa-xl container-max">
      <div class="row q-col-gutter-xl items-center">
        <div class="col-12 col-md-5">
          <div class="text-secondary text-weight-bold q-mb-sm">NOTRE RÉSEAU</div>
          <h2 class="text-h4 text-weight-bold q-mb-md">Une couverture panafricaine, des ports à l'hinterland</h2>
          <p class="text-body1 text-grey-7 q-mb-lg">
            Nous avons digitalisé la chaîne de transport pour garantir la fluidité des corridors commerciaux. 
            Que ce soit par fret lourd inter-états ou tricycles motorisés pour le dernier kilomètre rural.
          </p>

          <q-list class="q-gutter-y-sm">
            <q-item class="bg-grey-1 rounded-borders">
              <q-item-section avatar><q-icon name="gps_fixed" color="indigo" /></q-item-section>
              <q-item-section>
                <q-item-label class="text-weight-bold">Traçabilité Transfrontalière</q-item-label>
                <q-item-label caption>Suivez votre cargaison en temps réel à travers toutes les frontières.</q-item-label>
              </q-item-section>
            </q-item>
            <q-item class="bg-grey-1 rounded-borders">
              <q-item-section avatar><q-icon name="severe_cold" color="blue" /></q-item-section>
              <q-item-section>
                <q-item-label class="text-weight-bold">Chaîne du Froid Certifiée</q-item-label>
                <q-item-label caption>Respect des normes internationales pour l'exportation de produits périssables.</q-item-label>
              </q-item-section>
            </q-item>
            <q-item class="bg-grey-1 rounded-borders">
              <q-item-section avatar><q-icon name="verified_user" color="green" /></q-item-section>
              <q-item-section>
                <q-item-label class="text-weight-bold">Assurance Multizone</q-item-label>
                <q-item-label caption>Protection garantie sur tous les corridors de transport d'Afrique.</q-item-label>
              </q-item-section>
            </q-item>
          </q-list>
        </div>

        <div class="col-12 col-md-7">
          <q-img src="https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?auto=format&fit=crop&q=80&w=1000"
            class="rounded-borders shadow-20" style="border-radius: 30px;">
            <div class="absolute-bottom text-subtitle2 text-center bg-transparent">
              <q-chip color="white" text-color="indigo-10" icon="language" class="text-weight-bold">
                Corridors CEDEAO - Hubs Logistiques Afrique
              </q-chip>
            </div>
          </q-img>
        </div>
      </div>
    </div>

    <div class="bg-grey-1 q-py-xl">
      <div class="container-max q-px-md">
        <div class="text-center q-mb-xl">
          <h2 class="text-h4 text-weight-bold text-grey-10">Le processus d'inscription transporteur</h2>
          <div class="h-underline bg-primary q-mx-auto q-mt-sm"></div>
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

        <div class="process-banner q-mt-xl q-pa-lg flex flex-center shadow-sm">
          <div class="row items-center full-width">
            <div class="col-12 col-md-8">
              <h3 class="text-h6 text-weight-bolder q-ma-none">Prêt à intégrer le réseau ?</h3>
              <p class="text-grey-7 q-mt-xs q-mb-none">
                Connectez-vous, allez dans <strong>Mon Upesi</strong> > <strong>Ajouter un profil marchand</strong>
                et choisissez <strong>Transporteur</strong>.
              </p>
            </div>
            <div class="col-12 col-md-4 text-right">
              <q-btn to="/user/profile" color="primary" label="Accéder au dashboard" no-caps unelevated rounded
                class="q-px-lg" />
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="q-pa-xl">
      <div class="text-center q-mb-xl">
        <h2 class="text-h4 text-weight-bold">Solutions adaptées aux flux africains</h2>
        <div class="bg-indigo-9 q-mx-auto q-mt-sm" style="height: 4px; width: 60px; border-radius: 2px;"></div>
      </div>

      <div class="row q-col-gutter-lg justify-center container-max">
        <div v-for="v in vehicles" :key="v.title" class="col-12 col-sm-6 col-md-3">
          <q-card flat class="text-center q-pa-lg full-height flex flex-center no-shadow border-light">
            <q-avatar size="60px" :color="v.color + '-1'" :text-color="v.color + '-9'" :icon="v.icon" />
            <div class="text-h6 q-mt-md text-weight-bold">{{ v.title }}</div>
            <p class="text-caption text-grey-7 q-mt-sm">{{ v.desc }}</p>
          </q-card>
        </div>
      </div>
    </div>

    <div class="q-pa-xl text-center">
      <div class="bg-primary text-white q-pa-xl rounded-borders shadow-10 relative-position overflow-hidden">
        <div class="relative-z">
          <div class="text-h4 text-weight-bold q-mb-md">Besoin de transporter votre stock ?</div>
          <p class="text-body1 q-mb-lg opacity-80">Obtenez un devis instantané pour vos trajets nationaux, inter-urbains
            ou transfrontaliers.</p>
          <q-btn to="/services/category/logistique" label="Demander un devis" color="secondary" size="lg" unelevated
            no-caps class="text-weight-bold q-px-xl" />
        </div>
        <q-icon name="map" class="absolute opacity-10" size="200px" style="top: -20px; left: -20px;" />
      </div>
    </div>
  </q-page>
</template>

<script setup lang="ts">
const vehicles = [
  { icon: 'moped', title: 'Collecte Locale', desc: 'Tricycles motorisés pour collecter directement dans les zones de production isolées.', color: 'orange' },
  { icon: 'local_shipping', title: 'Flotte Inter-États', desc: 'Poids lourds configurés pour les longs trajets et les corridors régionaux.', color: 'indigo' },
  { icon: 'warehouse', title: 'Hubs de Stockage', desc: 'Points de transit sécurisés stratégiquement placés pour vos stocks.', color: 'green' },
  { icon: 'public', title: 'Import & Export', desc: 'Connexion directe avec les principaux ports autonomes de la sous-région.', color: 'blue' }
];

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
    icon: 'local_shipping',
    title: 'Devenir Transporteur',
    desc: "Sélectionnez le profil 'Transporteur' et enregistrez votre flotte."
  }
];
</script>

<style lang="scss" scoped>
.container-max { max-width: 1200px; margin: 0 auto; }
.max-width-600 { max-width: 600px; }
.relative-z { position: relative; z-index: 2; }
.opacity-80 { opacity: 0.8; }
.opacity-10 { opacity: 0.1; }
.h-underline { width: 60px; height: 4px; border-radius: 2px; }

.step-icon-wrapper {
  width: 90px;
  height: 90px;
  background: #e8eaf6;
  border-radius: 30px;
  display: flex;
  align-items: center;
  justify-content: center;
  position: relative;
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

.process-banner {
  background: white;
  border-radius: 20px;
  border-left: 8px solid var(--q-primary);
}

.border-light {
  border: 1px solid rgba(0, 0, 0, 0.05);
  border-radius: 20px;
  transition: transform 0.3s ease;
  &:hover {
    transform: translateY(-5px);
    border-color: var(--q-indigo);
  }
}

.btn-glow {
  box-shadow: 0 10px 20px rgba(var(--q-secondary), 0.3);
}

h1, h2, h3 { margin: 0; }
</style>