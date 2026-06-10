declare const self: ServiceWorkerGlobalScope &
  typeof globalThis & { skipWaiting: () => void };

import { clientsClaim } from 'workbox-core';
import {
  precacheAndRoute,
  cleanupOutdatedCaches,
  createHandlerBoundToURL,
} from 'workbox-precaching';
import { registerRoute, NavigationRoute } from 'workbox-routing';

// 1. ON ÉCOUTE LE MESSAGE "SKIP_WAITING" ENVOYÉ PAR LE BOUTON ACTUALISER
self.addEventListener('message', (event) => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    // Ajout de void ici pour skipWaiting()
    void self.skipWaiting();
  }
});

// 2. On garde clientsClaim pour prendre le contrôle des pages ouvertes
void clientsClaim();

// 3. Précachage et nettoyage
void precacheAndRoute(self.__WB_MANIFEST);
void cleanupOutdatedCaches();

// 4. Gestion du routage en production
if (process.env.PROD) {
  // Ajout de void ici pour registerRoute()
  void registerRoute(
    new NavigationRoute(
      createHandlerBoundToURL(process.env.PWA_FALLBACK_HTML),
      {
        denylist: [
          new RegExp(process.env.PWA_SERVICE_WORKER_REGEX),
          /workbox-(.)*\.js$/
        ]
      }
    )
  );
}
