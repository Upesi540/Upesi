// src/boot/axios.ts

import { defineBoot } from '#q-app/wrappers';
import axios, { type AxiosError } from 'axios';
import { useAuthStore } from 'src/stores/auth';

// Configuration de l'API en fonction de l'environnement
const API_URL = process.env.API_URL || 'http://127.0.0.1:8000/api/v1';
const FILAMENT_URL = process.env.FILAMENT_URL || 'http://127.0.0.1:8000';

const api = axios.create({
  baseURL: API_URL,
  timeout: 15000,
});

export default defineBoot(({ app, router }) => {
  let isRedirecting = false;

  api.interceptors.response.use(
    (response) => response,
    async (error: AxiosError) => {
      const authStore = useAuthStore();

      if (error.response?.status === 401 && !isRedirecting) {
        isRedirecting = true;

        if (router.currentRoute.value.path !== '/auth/login') {
          console.log('401 intercepted, redirecting to login');

          if (authStore.token) {
            await authStore.logout();
          } else {
            authStore.clearLocalSession();
          }

          await router.push('/auth/login');
        }

        isRedirecting = false;
      }

      return Promise.reject(error);
    }
  );

  app.config.globalProperties.$axios = axios;
  app.config.globalProperties.$api = api;
});

export { api, FILAMENT_URL };
