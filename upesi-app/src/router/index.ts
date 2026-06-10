import { route } from 'quasar/wrappers';
import {
  createMemoryHistory,
  createRouter,
  createWebHashHistory,
  createWebHistory,
} from 'vue-router';
import routes from './routes';
import { useAuthStore } from 'src/stores/auth';

export default route(function (/* { store, ssrContext } */) {
  const createHistory = process.env.SERVER
    ? createMemoryHistory
    : process.env.VUE_ROUTER_MODE === 'history'
      ? createWebHistory
      : createWebHashHistory;

  const Router = createRouter({
    scrollBehavior: () => ({ left: 0, top: 0 }),
    routes,
    history: createHistory(process.env.VUE_ROUTER_BASE),
  });

  // --- LE ROUTER GUARD D'Upesi ---
  Router.beforeEach((to, from, next) => {
    const authStore = useAuthStore();

    const requiresAuth = to.matched.some((record) => record.meta.requiresAuth);
    const requiresVerification = to.matched.some((record) => record.meta.requiresVerified);

    // Utilisation de la structure exacte de ton interface : user -> status -> email_verified_at
    const isVerified = !!authStore.user?.status?.email_verified_at;
    const isAuthenticated = authStore.isAuthenticated;

    // 1. PAS CONNECTÉ -> LOGIN
    if (requiresAuth && !isAuthenticated) {
      return next('/auth/login');
    }

    // 2. CONNECTÉ MAIS NON VÉRIFIÉ
    // On ne bloque que si la route exige explicitement la vérification
    if (requiresVerification && isAuthenticated && !isVerified) {
      return next('/auth/verify-email');
    }

    // 3. LOGIQUE POUR ÉVITER LES BOUCLES SUR /AUTH/
    const isAuthPage = to.path.startsWith('/auth/');
    const isVerifyPage = to.path.includes('verify');

    // Si l'utilisateur est déjà connecté, on l'empêche d'aller sur Login/Register
    // SAUF s'il va sur une page de vérification
    if (isAuthPage && !isVerifyPage && isAuthenticated) {
      return next('/user/profile');
    }

    next();
  });

  return Router;
});
