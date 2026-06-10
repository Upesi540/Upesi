<template>
  <q-page class="bg-grey-1">
    <!-- Hero section -->
    <div class="hero-section q-pa-xl text-white" style="background: linear-gradient(135deg, #1a5f2a, #0e3a1a);">
      <div class="container-narrow q-mx-auto text-center">
        <h1 class="text-h2 text-weight-bolder q-mb-md">À propos de Upesi</h1>
        <p class="text-h6 text-weight-light">Ensemble, bâtissons une agriculture durable et innovante pour l'Afrique</p>
      </div>
    </div>

    <div class="container-narrow q-mx-auto q-pa-lg">
      <!-- Qui sommes-nous -->
      <section class="q-mb-xl">
        <h2 class="text-h4 text-weight-bold text-primary q-mb-md">Qui sommes-nous ?</h2>
        <p class="text-body1 text-grey-8">
          Le Groupe d'Investissement Agricole et du Commerce (IAC) est un acteur clé dans le développement d'une agriculture moderne et durable à travers l'Afrique de l'Ouest et certains pays d'Afrique Centrale. Notre mission est de proposer des offres stratégiques qui répondent aux besoins variés du secteur agricole, tout en considérant les défis environnementaux actuels.
        </p>
      </section>

      <!-- Ambition, Vision et Objectifs -->
      <section class="q-mb-xl">
        <h2 class="text-h4 text-weight-bold text-primary q-mb-md">Ambition, Vision et Objectifs</h2>
        <div class="row q-col-gutter-md">
          <div class="col-12 col-md-4">
            <q-card flat bordered class="rounded-20 h-full">
              <q-card-section>
                <q-icon name="rocket_launch" size="40px" color="primary" class="q-mb-sm" />
                <div class="text-h6 text-weight-bold">Ambition</div>
                <p class="text-grey-8">Devenir un leader incontournable dans l'innovation agricole en Afrique, en promouvant des pratiques agricoles durables et technologiquement avancées.</p>
              </q-card-section>
            </q-card>
          </div>
          <div class="col-12 col-md-4">
            <q-card flat bordered class="rounded-20 h-full">
              <q-card-section>
                <q-icon name="visibility" size="40px" color="primary" class="q-mb-sm" />
                <div class="text-h6 text-weight-bold">Vision</div>
                <p class="text-grey-8">Créer un écosystème agricole où chaque acteur, du producteur au consommateur final, bénéficie de solutions adaptées et efficientes.</p>
              </q-card-section>
            </q-card>
          </div>
          <div class="col-12 col-md-4">
            <q-card flat bordered class="rounded-20 h-full">
              <q-card-section>
                <q-icon name="flag" size="40px" color="primary" class="q-mb-sm" />
                <div class="text-h6 text-weight-bold">Objectifs</div>
                <ul class="q-mt-sm q-pl-md text-grey-8">
                  <li>Faciliter l'accès aux ressources et technologies agricoles modernes.</li>
                  <li>Améliorer la chaîne de valeur agricole pour maximiser les profits des producteurs.</li>
                  <li>Promouvoir des pratiques agricoles durables et respectueuses de l'environnement.</li>
                </ul>
              </q-card-section>
            </q-card>
          </div>
        </div>
      </section>

      <!-- Programme et Projets -->
      <section class="q-mb-xl">
        <h2 class="text-h4 text-weight-bold text-primary q-mb-md">Programme et Projets</h2>
        <p class="text-body1 text-grey-8">
          Nous avons mis en place plusieurs programmes et projets visant à renforcer la capacité des producteurs agricoles et à améliorer l'accès au marché pour les produits agricoles. Ces initiatives incluent la formation des agriculteurs, le financement de projets innovants, et la mise en place de systèmes logistiques efficaces.
        </p>
      </section>

      <!-- La Plateforme Numérique Upesi -->
      <section class="q-mb-xl">
        <h2 class="text-h4 text-weight-bold text-primary q-mb-md">La Plateforme Numérique Upesi</h2>
        <p class="text-body1 text-grey-8">
          Upesi est notre réponse aux défis technologiques du secteur agricole. Conçue comme une plateforme numérique intégrée, Upesi offre des solutions adaptées aux besoins des producteurs, prestataires, fournisseurs, et consommateurs finaux. Elle permet :
        </p>
        <ul class="q-mt-md q-pl-lg text-grey-8">
          <li>L'accès à des informations en temps réel sur les marchés agricoles.</li>
          <li>La gestion optimisée des ressources agricoles grâce à des outils numériques avancés.</li>
          <li>La mise en relation directe entre producteurs et consommateurs pour réduire les coûts et améliorer l'efficacité.</li>
        </ul>
      </section>

      <!-- À propos de M. Silah Kpatra -->
      <section class="q-mb-xl">
        <h2 class="text-h4 text-weight-bold text-primary q-mb-md">À propos de M. Silah Kpatra</h2>
        <div class="row items-center q-col-gutter-md">
          <div class="col-12 col-md-3 text-center">
            <q-avatar size="150px" class="shadow-10">
              <img :src="ceoImage" alt="Silah Kpatra">
            </q-avatar>
          </div>
          <div class="col-12 col-md-9">
            <p class="text-body1 text-grey-8">
              M. Silah Kpatra, Président du groupe IAC, est un visionnaire et un leader charismatique. Animé par une passion pour le développement agricole, il a joué un rôle crucial dans la création et le développement de la plateforme Upesi. Ses qualités incluent :
            </p>
            <div class="row q-col-gutter-sm q-mt-md">
              <div class="col-12 col-md-4">
                <q-badge outline color="primary" class="full-width text-center q-py-sm">Leadership</q-badge>
                <p class="text-caption text-grey-7 q-mt-xs">Inspire et motive son équipe avec une vision claire.</p>
              </div>
              <div class="col-12 col-md-4">
                <q-badge outline color="primary" class="full-width text-center q-py-sm">Innovation</q-badge>
                <p class="text-caption text-grey-7 q-mt-xs">Constamment à la recherche de nouvelles idées et technologies.</p>
              </div>
              <div class="col-12 col-md-4">
                <q-badge outline color="primary" class="full-width text-center q-py-sm">Engagement</q-badge>
                <p class="text-caption text-grey-7 q-mt-xs">Dévoué à la cause de l'agriculture durable.</p>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Notre équipe (dynamique depuis l'API) -->
      <section class="q-mb-xl">
        <h2 class="text-h4 text-weight-bold text-primary q-mb-md">Notre équipe dirigeante</h2>
        <p class="text-body1 text-grey-8 q-mb-lg">
          Composée de compétences diversifiées, l'équipe dirigeante du Groupe est douée, expérimentée, dynamique, motivée.
        </p>

        <div v-if="aboutStore.isLoading" class="flex flex-center q-pa-xl">
          <q-spinner color="primary" size="40px" />
          <span class="q-ml-sm">Chargement de l'équipe...</span>
        </div>
        <div v-else-if="aboutStore.error" class="text-center text-negative">
          {{ aboutStore.error }}
        </div>
        <div v-else class="row q-col-gutter-md">
          <div v-for="member in aboutStore.teamMembers" :key="member.id" class="col-12 col-sm-6 col-md-4 col-lg-3">
            <q-card flat bordered class="rounded-20 text-center team-card">
              <q-card-section class="q-pt-lg">
                <q-avatar size="100px" class="shadow-3">
                  <img :src="member.photo_path || 'https://cdn.quasar.dev/img/avatar.png'" :alt="member.full_name">
                </q-avatar>
                <div class="text-h6 text-weight-bold q-mt-md">{{ member.full_name }}</div>
                <div class="text-subtitle2 text-primary">{{ member.role }}</div>
                <div v-if="member.bio" class="text-caption text-grey-7 q-mt-sm">{{ member.bio }}</div>
              </q-card-section>
              <q-card-section v-if="member.email || member.phone" class="q-pt-none">
                <div v-if="member.email" class="text-caption">
                  <q-icon name="email" size="xs" /> {{ member.email }}
                </div>
                <div v-if="member.phone" class="text-caption">
                  <q-icon name="phone" size="xs" /> {{ member.phone }}
                </div>
              </q-card-section>
              <q-card-actions v-if="member.social_links && (member.social_links.linkedin || member.social_links.twitter || member.social_links.facebook)" class="justify-center">
                <q-btn v-if="member.social_links.linkedin" flat round icon="linkedin" size="sm" color="blue-7" :href="member.social_links.linkedin" target="_blank" />
                <q-btn v-if="member.social_links.twitter" flat round icon="twitter" size="sm" color="blue-5" :href="member.social_links.twitter" target="_blank" />
                <q-btn v-if="member.social_links.facebook" flat round icon="facebook" size="sm" color="blue-8" :href="member.social_links.facebook" target="_blank" />
              </q-card-actions>
            </q-card>
          </div>
        </div>
      </section>

      <!-- Organigramme -->
      <section class="q-mb-xl">
        <h2 class="text-h4 text-weight-bold text-primary q-mb-md">Organigramme du Groupe IAC & Upesi</h2>
        <div class="org-chart bg-white q-pa-md rounded-20 shadow-2">
          <div class="text-center">
            <div class="org-node ceo">
              <div class="node-title text-weight-bold">Présidence</div>
              <div class="node-name">M. Silah Kpatra, Président</div>
              <div class="node-desc text-caption">Leader visionnaire, orientation stratégique</div>
            </div>
            <q-icon name="arrow_downward" size="24px" class="q-my-md" />
            <div class="org-node dg">
              <div class="node-title text-weight-bold">Direction Générale</div>
              <div class="node-name">Directeur Général</div>
              <div class="node-desc text-caption">Gestion opérationnelle quotidienne</div>
            </div>
            <div class="row justify-center q-mt-md q-col-gutter-md">
              <div class="col-12 col-md-6">
                <div class="org-node">
                  <div class="node-title text-weight-bold">Direction Administrative et Financière</div>
                  <div class="node-name">Directeur Administratif et Financier</div>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="org-node">
                  <div class="node-title text-weight-bold">Direction Technique</div>
                  <div class="node-name">Directeur Technique</div>
                </div>
              </div>
            </div>
            <div class="row justify-center q-mt-md q-col-gutter-md">
              <div class="col-12 col-md-6">
                <div class="org-node">
                  <div class="node-title text-weight-bold">Direction Commerciale et Marketing</div>
                  <div class="node-name">Directrice Commerciale et Marketing</div>
                </div>
              </div>
              <div class="col-12 col-md-6">
                <div class="org-node">
                  <div class="node-title text-weight-bold">Administration Upesi</div>
                  <div class="node-name">Responsables Marché et Services (Admin Délégué)</div>
                </div>
              </div>
            </div>
            <div class="row justify-center q-mt-md">
              <div class="col-12">
                <div class="org-node">
                  <div class="node-title text-weight-bold">Représentants Pays</div>
                  <div class="node-name">Liaison siège / opérations locales</div>
                </div>
              </div>
            </div>
            <div class="row justify-center q-mt-md">
              <div class="col-12">
                <div class="org-node">
                  <div class="node-title text-weight-bold">Support Technique et Opérations de Terrain</div>
                  <div class="node-name">Techniciens de Maintenance et de Terrain</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Expérience Internationale -->
      <section class="q-mb-xl">
        <h2 class="text-h4 text-weight-bold text-primary q-mb-md">Expérience Internationale</h2>
        <p class="text-body1 text-grey-8">
          Depuis 2019, GIAC a accumulé plusieurs expériences au Togo, en Centrafrique, au Cameroun, en Guinée Conakry, au Maroc et en Croatie, dans la recherche des solutions technologiques, digitales pour moderniser et mécaniser notre agriculture.
        </p>
      </section>

      <!-- Présence en Afrique -->
      <section class="q-mb-xl">
        <h2 class="text-h4 text-weight-bold text-primary q-mb-md">Présence en Afrique</h2>
        <div class="row q-col-gutter-sm">
          <div v-for="country in africanCountries" :key="country" class="col-6 col-sm-4 col-md-3">
            <q-chip outline color="primary" icon="place">{{ country }}</q-chip>
          </div>
        </div>
      </section>

      <div class="text-center text-grey-7 q-mt-xl">
        Ensemble, nous travaillons à bâtir un avenir agricole prospère et durable pour l'Afrique.
      </div>
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useAboutStore } from 'src/stores/about';
import ceoImage from 'src/assets/ceo.jpeg';

const aboutStore = useAboutStore();

const africanCountries = ref([
  'Guinée Conakry', 'Togo', 'Côte d\'Ivoire', 'Burkina Faso', 'Niger', 'Ghana',
  'Bénin', 'Nigeria', 'Liberia', 'Sierra Leone', 'Gambie', 'Centrafrique',
  'Burundi', 'Congo Brazzaville', 'RDC', 'Gabon', 'Cameroun'
]);

onMounted(async () => {
 await aboutStore.fetchTeamMembers();
});
</script>

<style scoped lang="scss">
.container-narrow {
  max-width: 1200px;
  margin: 0 auto;
}

.hero-section {
  border-radius: 0 0 30px 30px;
}

.rounded-20 {
  border-radius: 20px;
}

.h-full {
  height: 100%;
}

.org-chart {
  .org-node {
    background: #f5f7fa;
    border-radius: 16px;
    padding: 16px;
    margin: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    transition: transform 0.2s;
    &:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .node-title {
      color: var(--q-primary);
      font-size: 1rem;
    }
    .node-name {
      font-weight: 500;
      margin-top: 4px;
    }
    .node-desc {
      margin-top: 4px;
      color: #6c757d;
    }
  }
  .ceo {
    background: #e8f5e9;
    border-left: 4px solid var(--q-primary);
  }
  .dg {
    background: #e3f2fd;
    border-left: 4px solid #42a5f5;
  }
}

.team-card {
  transition: all 0.2s;
  &:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
  }
}
</style>
