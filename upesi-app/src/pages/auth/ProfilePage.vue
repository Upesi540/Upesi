<template>
  <q-page class="profile-page">
    <!-- Hero Section avec parallaxe -->
    <div class="hero-section bg-primary overflow-hidden">
      <div class="hero-bg"></div>
      <div v-if="$q.screen.gt.sm" class="decorative-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
      </div>

      <div class="hero-container container q-py-lg">
        <div class="row items-center q-col-gutter-xl" :class="$q.screen.lt.md ? 'column text-center' : 'row text-left'">

          <div class="col-auto">
            <div class="avatar-wrapper">
              <div class="avatar-glow"></div>
              <q-avatar @click="openFilament('/app/profile')" :size="$q.screen.lt.sm ? '110px' : '140px'"
                class="avatar shadow-15 cursor-pointer transition-bounce">
                <q-img :src="auth.userAvatar || 'https://cdn.quasar.dev/img/avatar.png'" />
                <q-badge v-if="auth?.user?.status?.email_verified_at" floating color="positive" rounded
                  class="verified-badge shadow-2">
                  <q-icon name="check" size="14px" />
                </q-badge>
              </q-avatar>

              <!-- <div class="merchant-badge bg-white text-primary shadow-5" v-if="auth.user?.roles?.includes('merchant')">
              <q-icon name="verified" size="16px" />
              <span>Marchand</span>
            </div> -->
            </div>
          </div>

          <div class="col">
            <div class="user-main-info q-mt-sm">
              <div class="name-row row no-wrap items-center" :class="{ 'justify-center': $q.screen.lt.md }">
                <h1 class="user-name q-ma-none text-weight-bold text-white">
                  {{ auth.user?.first_name }} {{ auth.user?.last_name }}
                </h1>
                <q-chip v-if="Number(auth.user?.wallet?.available_balance) > 100000" color="white" text-color="primary"
                  icon="stars" label="Premium" class="q-ml-sm shadow-1" size="sm" dense />
              </div>

              <div class="contact-row row q-gutter-md q-mt-xs" :class="{ 'justify-center': $q.screen.lt.md }">
                <div class="contact-item text-white-8"><q-icon name="mail_outline" /> {{ auth.user?.email }}</div>
                <div v-if="auth.user?.phone" class="contact-item text-white-8"><q-icon name="phone" /> {{
                  auth.user?.phone }}</div>
              </div>
            </div>

            <div class="wallet-card q-mt-lg shadow-20" :class="{ 'mx-auto': $q.screen.lt.md }" v-ripple>
              <div class="wallet-content row items-center justify-between no-wrap">
                <div class="row items-center no-wrap col">
                  <div class="wallet-icon-box q-mr-md bg-white-1">
                    <q-icon name="account_balance_wallet" color="white" size="24px" />
                  </div>
                  <div class="col">
                    <div class="text-caption text-white-7 text-weight-bold text-uppercase letter-spacing-1">SOLDE
                      DISPONIBLE</div>
                    <div class="wallet-amount text-white text-weight-bolder no-wrap ellipsis">
                      <span class="text-caption q-mr-xs opacity-70">XOF</span>{{
                        (auth.user?.wallet?.formatted_available)
                      }}
                    </div>
                  </div>
                </div>
                <q-btn flat round color="white" icon="chevron_right" @click="openFilament('/app/my-wallet')"
                  class="q-ml-sm" />
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- Stats Cards (optionnel - ajoute de la valeur) -->
    <div class="stats-section">
      <div class="stats-grid">
        <div class="stat-card">
          <q-icon name="shopping_bag" size="28px" color="primary" />
          <div class="stat-value">{{ auth.user?.stats?.orders_as_buyer_count }}</div>
          <div class="stat-label">Mes achats</div>
        </div>
        <div class="stat-card">
          <q-icon name="trending_up" size="28px" color="primary" />
          <div class="stat-value">{{ auth.user?.stats?.orders_as_seller_count }}</div>
          <div class="stat-label">Mes ventes</div>
        </div>
        <div class="stat-card">
          <q-icon name="storefront" size="28px" color="green" />
          <div class="stat-value">{{ auth.user?.stats?.merchant_profiles_count }}</div>
          <div class="stat-label">Boutiques</div>
        </div>
        <div class="stat-card">
          <q-icon name="local_offer" size="28px" color="primary" />
          <div class="stat-value">{{ auth.user?.stats?.products_count }}</div>
          <div class="stat-label">Mes Offres de produits </div>
        </div>
      </div>
    </div>

    <!-- Menu Items -->
    <div class="menu-section">
      <!-- Section principale -->
      <div class="menu-group">
        <h3 class="menu-group-title" v-if="$q.screen.gt.sm">Navigation principale</h3>

        <q-card class="menu-card" flat>
          <q-list padding>
            <q-item v-for="item in mainMenuItems" :key="item.route" clickable v-ripple @click="openFilament(item.route)"
              class="menu-item">
              <q-item-section avatar>
                <div class="menu-icon-wrapper" :style="{ backgroundColor: item.bgColor }">
                  <q-icon :name="item.icon" :color="item.iconColor" size="20px" />
                </div>
              </q-item-section>
              <q-item-section>
                <div class="menu-item-title">{{ item.label }}</div>
                <div class="menu-item-desc" v-if="$q.screen.gt.sm">{{ item.description }}</div>
              </q-item-section>
              <q-item-section side>
                <q-icon name="chevron_right" size="18px" color="grey-5" />
              </q-item-section>
            </q-item>
          </q-list>
        </q-card>
      </div>

      <!-- Section secondaire -->
      <div class="menu-group">
        <h3 class="menu-group-title">Autres</h3>

        <q-card class="menu-card" flat>
          <q-list padding>
            <q-item clickable v-ripple @click="openFilament('/app/my-wallet')" class="menu-item">
              <q-item-section avatar>
                <div class="menu-icon-wrapper" style="background-color: rgba(76, 175, 80, 0.1);">
                  <q-icon name="receipt_long" color="green" size="20px" />
                </div>
              </q-item-section>
              <q-item-section>
                <div class="menu-item-title">Historique des transactions</div>
                <div class="menu-item-desc" v-if="$q.screen.gt.sm">Consultez vos mouvements financiers</div>
              </q-item-section>
              <q-item-section side>
                <q-icon name="chevron_right" size="18px" color="grey-5" />
              </q-item-section>
            </q-item>
          </q-list>
        </q-card>
      </div>

      <!-- Logout Button -->
      <div class="logout-section">
        <q-btn flat color="negative" icon="logout" label="Déconnexion" class="logout-btn" @click="handleLogout" />
      </div>
    </div>
  </q-page>
</template>

<script setup lang="ts">
import { useAuthStore } from 'src/stores/auth';
import { useQuasar, Loading, QSpinnerFacebook } from 'quasar';
import { useRouter } from 'vue-router';
import { Browser } from '@capacitor/browser';
import { computed } from 'vue';

const auth = useAuthStore();
const $q = useQuasar();
const router = useRouter();

const mainMenuItems = computed(() => {
  // 1. Menus communs à tous (Dashboard, Profil, etc.)
  const commonItems = [
    {
      label: 'Mon Dashboard',
      description: 'Vue d\'ensemble de votre activité',
      icon: 'dashboard',
      route: '/app',
      bgColor: 'rgba(33, 150, 243, 0.1)',
      iconColor: 'primary'
    },
    {
      label: 'Mes Achats',
      description: 'Commandes passées',
      icon: 'shopping_basket',
      route: '/app/purchase-orders',
      bgColor: 'rgba(255, 152, 0, 0.1)',
      iconColor: 'orange'
    },
    {
      label: 'Vendre sur Upesi',
      description: 'Gérez vos produits et services',
      icon: 'storefront',
      route: '/app/profiles',
      bgColor: 'rgba(156, 39, 176, 0.1)',
      iconColor: 'purple'
    },
    // ... d'autres menus communs si besoin
  ];

  // 2. Menus pour les profils "vendeurs de produits" (producer, supplier, trader)
  const productSellerItems = [
    {
      label: 'Mes Ventes',
      description: 'Commandes reçues',
      icon: 'trending_up',
      route: '/app/sale-orders',
      bgColor: 'rgba(76, 175, 80, 0.1)',
      iconColor: 'green'
    },
    {
      label: 'Mes Offres de produits',
      description: 'Produits',
      icon: 'local_offer',
      route: '/app/products',
      bgColor: 'rgba(33, 150, 243, 0.1)',
      iconColor: 'blue'
    }
  ];

  // 3. Menus pour les profils "prestataires" (provider, transporter)
  const serviceProviderItems = [
    {
      label: 'Mes Offres de services',
      description: 'Services',
      icon: 'miscellaneous_services',
      route: '/app/service-offers',
      bgColor: 'rgba(156, 39, 176, 0.1)',
      iconColor: 'purple'
    },
    {
      label: 'Demandes de services',
      description: 'Demandes reçues',
      icon: 'request_quote',
      route: '/app/merchant-service-requests',
      bgColor: 'rgba(255, 152, 0, 0.1)',
      iconColor: 'orange',
      badge: auth.user?.stats?.service_requests_count || 0
    }
  ];

  // 4. Déterminer quels types de profils possède l'utilisateur
  const profiles = auth.user?.merchant_profiles || [];
  const hasProductProfile = profiles.some(p => ['producer', 'supplier', 'trader'].includes(p.type));
  const hasServiceProfile = profiles.some(p => ['provider', 'transporter'].includes(p.type));

  // 5. Construire le menu final
  const items = [...commonItems];
  if (hasProductProfile) items.push(...productSellerItems);
  if (hasServiceProfile) items.push(...serviceProviderItems);

  // 6. Ajouter les autres menus (ex: Historique des transactions, Déconnexion) en dehors du computed
  return items;
});

async function openFilament(destination: string) {
  Loading.show({
    spinner: QSpinnerFacebook,
    spinnerColor: 'white',
    backgroundColor: 'primary',
    message: 'Connexion sécurisée...',
    messageColor: 'white'
  });

  try {
    const url = await auth.generateMagicLink(destination);
    if (url) {
      await Browser.open({ url: url });
    } else {
      throw new Error('Erreur de génération du lien');
    }
  } catch {
    $q.notify({
      color: 'negative',
      message: 'Erreur technique lors de la redirection.',
      icon: 'report_problem'
    });
  } finally {
    Loading.hide();
  }
}

function handleLogout() {
  $q.dialog({
    title: 'Déconnexion',
    message: 'Voulez-vous vraiment quitter Upesi ?',
    cancel: { label: 'Annuler', color: 'grey' },
    ok: { label: 'Déconnexion', color: 'negative' },
    persistent: true
  }).onOk(() => {
    void (async () => {
      try {
        await auth.logout();
        await router.push('/auth/login');
      } catch (error) {
        console.error('Erreur déconnexion:', error);
        await router.push('/auth/login');
      }
    })();
  });
}
</script>

<style lang="scss" scoped>
.profile-page {
  background: #f5f7fa;
  min-height: 100vh;
}

.hero-section {
  position: relative;
  min-height: 300px;
  display: flex;
  align-items: center;
  border-radius: 0 0 40px 40px; // Arrondi élégant en bas de section

  // S'assurer que bg-primary est bien défini dans tes variables Quasar
  &.bg-primary {
    background-color: var(--q-primary);
  }
}

.hero-container {
  z-index: 1;
  width: 100%;
}

//--- AVATAR ---
.avatar-wrapper {
  position: relative;

  .avatar {
    border: 4px solid white; // Contour blanc fort pour détacher du vert
    background: white;
    transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);

    &:hover {
      transform: scale(1.05);
    }
  }

  // Lueur douce blanche/jaune au lieu de primaire
  .avatar-glow {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 130%;
    height: 130%;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.2) 0%, transparent 70%);
    pointer-events: none;
  }
}

.merchant-badge {
  position: absolute;
  bottom: -12px;
  left: 50%;
  transform: translateX(-50%);
  padding: 6px 16px;
  border-radius: 20px;
  font-size: 11px;
  font-weight: 800;
  display: flex;
  align-items: center;
  gap: 5px;
  white-space: nowrap;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

//--- TEXTES ---
.user-name {
  font-size: clamp(1.6rem, 6vw, 2.4rem); // Un peu plus grand sur mobile
  line-height: 1.2;
}

.contact-item {
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 7px;
}

//--- CLASSES UTILITAIRES DE COULEUR ---
.text-white-8 {
  color: rgba(255, 255, 255, 0.85) !important;
}

.text-white-7 {
  color: rgba(255, 255, 255, 0.7) !important;
  font-size: 10px;
}

.bg-white-1 {
  background: rgba(255, 255, 255, 0.1) !important;
}

.opacity-70 {
  opacity: 0.7;
}

.letter-spacing-1 {
  letter-spacing: 1px;
}

//--- WALLET CARD (Look Fintech Premium) ---
.wallet-card {
  background: #1d1d1f; // Noir profond pour trancher avec le vert
  border-radius: 24px;
  padding: 18px 22px;
  max-width: 420px;
  position: relative;
  overflow: hidden;
  cursor: pointer;
  transition: transform 0.2s ease, box-shadow 0.2s ease;

  &:active {
    transform: scale(0.97); // Effet de clic
  }

  // Reflet subtil sur la carte
  &::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.03) 0%, transparent 60%);
    pointer-events: none;
  }

  .wallet-icon-box {
    width: 52px;
    height: 52px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .wallet-amount {
    font-size: clamp(1.3rem, 5vw, 1.6rem);
    letter-spacing: -0.7px;
    line-height: 1.1;
  }
}

.mx-auto {
  margin-left: auto;
  margin-right: auto;
}

//--- SHAPES ---
.decorative-shapes {
  .shape {
    position: absolute;
    filter: blur(50px);
    opacity: 0.2; // Très discret sur le vert
    z-index: 0;
  }

  .shape-1 {
    top: -10%;
    right: 10%;
    width: 250px;
    height: 250px;
    background: #ffffff;
  }

  .shape-2 {
    bottom: 0%;
    left: 5%;
    width: 180px;
    height: 180px;
    background: #beffc8;
  }
}

// Stats Section
.stats-section {
  padding: 20px;

  .stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    max-width: 800px;
    margin: 0 auto;
  }

  .stat-card {
    background: white;
    border-radius: 20px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;

    &:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
    }

    .stat-value {
      font-size: 28px;
      font-weight: 700;
      color: #2c3e50;
      margin: 8px 0 4px;
    }

    .stat-label {
      font-size: 12px;
      color: #7f8c8d;
      font-weight: 500;
    }
  }

  @media (max-width: 600px) {
    .stats-grid {
      grid-template-columns: repeat(2, 1fr);
      /* Une seule colonne sur mobile */
      gap: 10px;
    }

    .stat-card {
      background: white;
      border-radius: 20px;
      padding: 5px;
      text-align: center;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
      transition: all 0.3s ease;

      &:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
      }

      .stat-value {
        font-size: 15px;
        font-weight: 700;
        color: #2c3e50;
        margin: 8px 0 4px;
      }

      .stat-label {
        font-size: 12px;
        color: #7f8c8d;
        font-weight: 500;
      }
    }
  }
}

// Menu Section
.menu-section {
  padding: 0 16px 24px;

  @media (min-width: 768px) {
    padding: 0 32px 32px;
    max-width: 800px;
    margin: 0 auto;
  }

  .menu-group {
    margin-bottom: 24px;

    .menu-group-title {
      font-size: 13px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 1px;
      color: #95a5a6;
      margin: 0 0 12px 12px;
    }
  }

  .menu-card {
    border-radius: 20px;
    overflow: hidden;
    transition: all 0.3s ease;

    &:hover {
      box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
    }
  }

  .menu-item {
    padding: 12px 16px;
    transition: background-color 0.2s ease;

    &:hover {
      background-color: rgba(0, 0, 0, 0.02);
    }

    .menu-icon-wrapper {
      width: 40px;
      height: 40px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .menu-item-title {
      font-weight: 600;
      font-size: 15px;
      color: #2c3e50;
      margin-bottom: 2px;
    }

    .menu-item-desc {
      font-size: 12px;
      color: #95a5a6;
    }
  }

  .logout-section {
    margin-top: 32px;

    .logout-btn {
      width: 100%;
      padding: 12px;
      border-radius: 12px;
      font-weight: 600;
      transition: all 0.2s ease;

      &:hover {
        background-color: rgba(244, 67, 54, 0.05);
      }
    }
  }
}
</style>
