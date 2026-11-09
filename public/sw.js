// Ancien service worker supprimé : il interceptait toutes les requêtes et ralentissait le navigateur.
// Ce script se désinstalle lui-même et vide ses caches chez les navigateurs qui l'avaient installé.
self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((noms) => Promise.all(noms.map((nom) => caches.delete(nom))))
            .then(() => self.registration.unregister())
    );
});
