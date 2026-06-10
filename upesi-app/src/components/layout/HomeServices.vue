<template>
  <div class="q-px-md q-py-lg">
    <div class="row items-center q-mb-md">
      <div class="title-line q-mr-sm"></div>
      <div class="text-h6 text-weight-bolder text-grey-9">Écosystème Upesi</div>
    </div>

    <div class="row q-col-gutter-md">
      <div v-for="service in allServices" :key="service.name" class="col-6 col-sm-3 col-md-3">
        <q-card flat bordered class="service-card cursor-pointer full-height" @click="$router.push(service.link)">
          <q-card-section class="column items-center text-center q-pa-md">
            <q-avatar :color="service.bgColor" :text-color="service.iconColor" size="56px" font-size="32px"
              :icon="service.icon" class="q-mb-sm shadow-1" />

            <div class="text-subtitle2 text-weight-bold text-grey-10">{{ service.name }}</div>

            <div v-if="$q.screen.gt.xs" class="text-caption text-grey-7 q-mt-xs">
              {{ service.desc }}
            </div>
          </q-card-section>

          <q-badge v-if="service.tag" floating color="red" label="Live" class="q-ma-xs" />
        </q-card>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
const allServices = [
  {
    name: 'Bourse Agricole',
    icon: 'trending_up',
    bgColor: 'lime-1',
    iconColor: 'lime-10',
    desc: 'Tendances et cours des marchés',
    link: '/bourse',
    tag: true
  },
  {
    name: 'Ma Banque',
    icon: 'account_balance',
    bgColor: 'blue-1',
    iconColor: 'blue-8',
    desc: 'Gestion de vos comptes et paiements',
    link: '/agricultural-banking'
  },
  {
    name: 'Logistique',
    icon: 'local_shipping',
    bgColor: 'orange-1',
    iconColor: 'secondary',
    desc: 'Transport national & international',
    link: '/logistics'
  },
  {
    name: 'Prestations',
    icon: 'handyman',
    bgColor: 'brown-1',
    iconColor: 'brown-8',
    desc: 'Labour, semis et entretien',
    link: '/services'
  }
];
</script>

<style scoped>
.title-line {
  width: 6px;
  height: 24px;
  background: var(--q-lime-14);
  border-radius: 4px;
}

.service-card {
  transition: all 0.3s ease;
  border-radius: 16px;
  border: 1px solid #edf2f7;
}

.service-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08) !important;
  border-color: var(--q-lime-14);
}

/* Optimisation pour le toucher sur mobile */
@media (max-width: 599px) {
  .service-card:active {
    transform: scale(0.95);
    background-color: #f8fafc;
  }
}
</style>