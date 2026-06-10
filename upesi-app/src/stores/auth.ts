import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { api } from 'boot/axios';
import { LocalStorage } from 'quasar';
import CryptoJS from 'crypto-js';
import type { ApiResponse } from 'src/types/api';
import type { User } from 'src/types/user';
import axios from 'axios';

// Interface pour la réponse structurée du login (Laravel Resource)
interface LoginData {
  user: User;
  token: string;
  token_type: string;
}
// Clé secrète pour le chiffrement (À placer dans un fichier quasar cofig section env ou créer un fichier .env idéalement)
const ENCRYPTION_KEY = process.env.ENCRYPTION_KEY || 'Your_encryption_key';

export const useAuthStore = defineStore(
  'auth',
  () => {
    // --- STATE ---
    const user = ref<User | null>(null);
    const token = ref<string | null>(null);
    const loading = ref(false);

    // --- GETTERS ---
    const isAuthenticated = computed(() => !!token.value);
    const userAvatar = computed(() => user.value?.profile_photo_url || '');

    // --- PRIVATE UTILS ---

    /**
     * Injecte ou retire le token des headers Axios
     */
    function syncAxiosHeader(newToken: string | null) {
      if (newToken) {
        api.defaults.headers.common['Authorization'] = `Bearer ${newToken}`;
      } else {
        delete api.defaults.headers.common['Authorization'];
      }
    }

    /**
     * Nettoie la session localement SANS appeler l'API logout
     * Utile pour éviter les boucles infinies lors des erreurs 401
     */
    function clearLocalSession() {
      token.value = null;
      user.value = null;
      syncAxiosHeader(null);
      // Note : ne pas appeler LocalStorage.remove ici car le persist le fait automatiquement
      // Le token sera effacé du localStorage via le système de persist

      // Supprimer manuellement le token du localStorage
      LocalStorage.remove('upesi_auth_vault');
    }

    // --- ACTIONS ---

    /**
     * Initialisation : Appelé au démarrage de l'app (App.vue ou Router)
     * On synchronise Axios avec le token persisté et on refresh les infos user
     */
    async function init() {
      if (token.value) {
        syncAxiosHeader(token.value);
        await fetchUser();
      }
    }

    /**
     * Login
     */
    async function login(credentials: object): Promise<ApiResponse<LoginData>> {
      loading.value = true;
      try {
        const response = await api.post<ApiResponse<LoginData>>('/auth/login', credentials);
        const { data } = response.data;

        token.value = data.token;
        user.value = data.user;
        syncAxiosHeader(data.token);

        return response.data;
      } finally {
        loading.value = false;
      }
    }

    /**
     * Register
     */
    async function register(formData: object): Promise<ApiResponse<LoginData>> {
      loading.value = true;
      try {
        const response = await api.post<ApiResponse<LoginData>>('/auth/register', formData);
        const { data } = response.data;

        token.value = data.token;
        user.value = data.user;
        syncAxiosHeader(data.token);

        return response.data;
      } finally {
        loading.value = false;
      }
    }

    /**
     * Récupérer les données fraîches de l'utilisateur
     */
    async function fetchUser() {
      // Ne pas essayer de récupérer l'utilisateur si pas de token
      if (!token.value) return;

      try {
        const response = await api.get<ApiResponse<User>>('/auth/me');
        user.value = response.data.data;
      } catch (error) {
        console.error('Fetch user error:', error);
        if (axios.isAxiosError(error)) {
          // Si le token est invalide/expiré côté Laravel, on nettoie localement SANS appeler logout
          if (error.response?.status === 401) {
            clearLocalSession();
          }
        }

        throw error;
      }
    }

    /**
     * Logout - Appelle l'API logout seulement si on a un token valide
     */
    async function logout() {
      // Ne pas appeler logout si le token est déjà null
      if (!token.value) {
        console.log('No token, skipping logout API call');
        clearLocalSession();
        return;
      }
      try {
        // Appeler l'API logout SEULEMENT si on a un token
        if (token.value) {
          await api.post('/auth/logout');
        }
      } catch (error) {
        console.error('Logout error:', error);
      } finally {
        // Toujours nettoyer localement
        clearLocalSession();
      }
    }

    /**
     * Magic Link
     */
    async function generateMagicLink(
      destination = '/app',
      origin = 'mobile',
    ): Promise<string | null> {
      try {
        const response = await api.post<ApiResponse<{ url: string }>>('/auth/generate-magic-link', {
          destination,
          origin,
        });
        return response.data.data.url;
      } catch (error) {
        if (process.env.DEV) {
          console.log(error);
        }
        return null;
      }
    }

    /**
     * Passwords & Verification
     */
    async function forgotPassword(email: string) {
      return (await api.post<ApiResponse<null>>('/auth/forgot-password', { email })).data;
    }

    async function resetPassword(form: object) {
      return (await api.post<ApiResponse<null>>('/auth/reset-password', form)).data;
    }

    async function verifyEmail(
      id: string,
      hash: string,
      params: Record<string, unknown>, // Ici le 'unknown' est acceptable car c'est un dictionnaire de paramètres
    ): Promise<ApiResponse<null>> {
      loading.value = true;
      try {
        const response = await api.get<ApiResponse<null>>(`/auth/verify-email/${id}/${hash}`, {
          params: params,
        });

        if (user.value) {
          user.value.status.email_verified_at = new Date().toISOString();
        }

        return response.data;
      } finally {
        loading.value = false;
      }
    }

    async function sendVerificationEmail(): Promise<ApiResponse<null>> {
      loading.value = true;
      try {
        const response = await api.post<ApiResponse<null>>('/auth/email/verification-notification');
        return response.data;
      } finally {
        loading.value = false;
      }
    }

    return {
      user,
      token,
      loading,
      isAuthenticated,
      userAvatar,
      init,
      login,
      register,
      fetchUser,
      logout,
      clearLocalSession, // Exporter pour utilisation dans l'intercepteur
      forgotPassword,
      resetPassword,
      generateMagicLink,
      sendVerificationEmail,
      verifyEmail,
    };
  },
  {
    // --- PERSISTENCE LAYER ---
    persist: {
      key: 'upesi_auth_vault',
      pick: ['token'], // On ne persiste QUE le token pour plus de sécurité
      storage: {
        getItem: (key) => {
          const raw = LocalStorage.getItem(key) as string;
          if (!raw) return null;
          try {
            // Déchiffrement AES
            const bytes = CryptoJS.AES.decrypt(raw, ENCRYPTION_KEY);
            return JSON.parse(bytes.toString(CryptoJS.enc.Utf8));
          } catch {
            return null;
          }
        },
        setItem: (key, value) => {
          // Chiffrement AES avant stockage dans le LocalStorage Quasar
          const encrypted = CryptoJS.AES.encrypt(JSON.stringify(value), ENCRYPTION_KEY).toString();
          LocalStorage.set(key, encrypted);
        },
      },
    },
  },
);
