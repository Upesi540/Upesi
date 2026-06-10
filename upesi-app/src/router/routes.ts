import type { RouteRecordRaw } from 'vue-router';

const routes: RouteRecordRaw[] = [
  // {
  //   path: '/',
  //   component: () => import('layouts/MainLayout.vue'),
  //   children: [{ path: '', component: () => import('pages/IndexPage.vue') }],
  // },
  {
    path: '/auth',
    component: () => import('layouts/AuthLayout.vue'),
    children: [
      { path: 'login', component: () => import('pages/auth/LoginPage.vue') },
      { path: 'register', component: () => import('pages/auth/RegisterPage.vue') },
      { path: 'forgot-password', component: () => import('pages/auth/ForgotPasswordPage.vue') },
      // Route 1 : Doit être protégée par ton Gardien (beforeEach)
      {
        path: 'verify-email',
        component: () => import('pages/auth/VerifyEmailNotice.vue'),
        meta: { requiresAuth: true }, // Jean doit être logué pour voir ça
      },

    ],
  },
  {
    path: '/',
    component: () => import('layouts/MainLayout.vue'), // C'est ici qu'est le QLayout
    children: [
      { path: '', component: () => import('pages/IndexPage.vue') },
      // Cette ligne place AgriculturalBanking à l'intérieur du QPageContainer du MainLayout

      // Devient une simple page de confirmation
      {
        path: 'email-verified',
        component: () => import('pages/auth/VerifyEmailAction.vue'),
        name: 'verification.result', // On change le nom pour plus de clarté
        meta: { requiresAuth: false },
      },
      {
        path: 'search',
        name: 'search',
        component: () => import('pages/SearchPage.vue'),
        meta: {
          title: 'Recherche',
          requiresAuth: false, // Accessible même sans connexion
        },
      },
      // router/routes.ts
      {
        path: 'about',
        name: 'about',
        component: () => import('pages/AboutPage.vue'),
        meta: { title: 'À propos', requiresAuth: false },
      },
      // router/routes.ts

      {
        path: 'legal/:slug',
        name: 'legal',
        component: () => import('pages/LegalPage.vue'),
        meta: { title: 'Document légal', requiresAuth: false },
      },
      {
        path: 'user/profile',
        component: () => import('pages/auth/ProfilePage.vue'),
        meta: { requiresAuth: true },
      },

      {
        path: 'agricultural-banking',
        component: () => import('pages/landing/AgriculturalBanking.vue'),
      },
      {
        path: 'logistics',
        component: () => import('pages/landing/LogisticsPage.vue'), // Nom en anglais
        meta: { title: 'Logistique & Transport - Upesi' },
      },
      {
        path: '/services',
        component: () => import('pages/landing/ServicesPage.vue'),
        meta: { title: 'Prestations Agricoles - Upesi' },
      },
      //products
      {
        path: 'products/:id',
        name: 'product-detail',
        component: () => import('pages/products/ProductDetail.vue'),
        meta: { hideBottomNav: true }, // On dit à l'app de cacher le footer ici
      },
      {
        path: 'products',
        name: 'products',
        component: () => import('pages/products/ProductsPage.vue'),
      },
      {
        path: 'products/category/:id',
        name: 'products-by-category',
        component: () => import('pages/products/ProductsPage.vue'),
      },
      {
        path: 'products/crop/:id',
        name: 'products-by-crop',
        component: () => import('pages/products/ProductsPage.vue'),
      },
      {
        path: 'products/market/:id',
        name: 'products-by-market',
        component: () => import('pages/products/ProductsPage.vue'),
      },
      { path: 'cart', name: 'cart', component: () => import('pages/products/CartPage.vue') },
      // Dans ton fichier routes.ts, ajoute dans le MainLayout children :
      {
        path: 'checkout',
        name: 'checkout',
        component: () => import('pages/products/ProductCheckoutPage.vue'),
        meta: { requiresAuth: true, requiresVerified: true, title: 'Validation de commande' },
      },
      {
        path: 'order-confirmation/:id',
        name: 'order-confirmation',
        component: () => import('pages/products/OrderConfirmationPage.vue'),
        meta: { requiresAuth: true, title: 'Confirmation', showBottomNav: false },
      },
      // Liste des offres filtrées par CATEGORIE (ex: Logistique, Préparation du sol)
      {
        path: '/services/category/:slug',
        name: 'service-category',
        component: () => import('pages/services/ServiceOffersPage.vue'), // On réutilise la page de liste
        meta: { title: 'Catégorie de services' },
      },

      // Liste des offres filtrées par SERVICE spécifique (ex: Labour, Transport de bétail)
      {
        path: '/services/type/:slug',
        name: 'service-type',
        component: () => import('pages/services/ServiceOffersPage.vue'),
        meta: { title: 'Type de service' },
      },
      {
        path: '/services/offers',
        name: 'service-offers',
        component: () => import('pages/services/ServiceOffersPage.vue'),
        meta: { title: 'Toutes les offres' },
      },
      {
        path: '/services/offer/:id',
        name: 'service-offer-detail',
        component: () => import('pages/services/ServiceOfferDetailPage.vue'),
        meta: { title: "Détail de l'offre" },
      },
      {
        path: 'service-request/create/:offerId',
        name: 'service-request-create',
        component: () => import('pages/services/ServiceCheckoutPage.vue'),
        meta: { requiresAuth: true, requiresVerified: true, title: 'Demander ce service' },
      },
      {
        path: 'journal',
        name: 'journal',
        component: () => import('pages/news/JournalPage.vue'),
      },
      {
        path: 'journal/:slug',
        name: 'news-detail',
        component: () => import('pages/news/JournalDetail.vue'),
      },
      {
        path: 'projects',
        name: 'projects',
        component: () => import('pages/projects/ProjectsPage.vue'),
      },
      {
        path: 'bourse',
        name: 'bourse',
        component: () => import('pages/BoursePage.vue'),
      },
      {
        path: 'projets/:slug',
        name: 'project-detail',
        component: () => import('pages/projects/ProjectDetail.vue'),
      },
    ],
  },

  // Always leave this as last one,
  // but you can also remove it
  {
    path: '/:catchAll(.*)*',
    component: () => import('pages/ErrorNotFound.vue'),
  },
];

export default routes;
