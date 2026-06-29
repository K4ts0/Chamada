// ============================================================
// SERVICE WORKER — Sistema Hospitalar UPABJ
// Coloque na RAIZ do projeto (mesmo nível de painel.php)
//
// COMO A VIBRAÇÃO FUNCIONA:
//   A página chama swRegistration.showNotification(título, { vibrate: [...] })
//   Isso acorda este SW e exibe a notificação via SO (Android).
//   O padrão vibrate=[600,200,600,200,600] vibra 3x mesmo com Chrome minimizado.
// ============================================================

self.addEventListener('install', () => self.skipWaiting());
self.addEventListener('activate', (e) => e.waitUntil(clients.claim()));

// Clique na notificação ? foca a aba do painel (ou abre uma nova)
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((lista) => {
            for (const client of lista) {
                if (client.url.includes('painel') && 'focus' in client) {
                    return client.focus();
                }
            }
            const url = (event.notification.data && event.notification.data.url)
                ? event.notification.data.url
                : '/painel.php';
            return clients.openWindow(url);
        })
    );
});

self.addEventListener('notificationclose', (event) => {
    console.log('[SW] Notificação fechada:', event.notification.tag);
});

// Responde ao ping de keep-alive enviado pela página a cada 25s
// Isso impede que o browser mate o SW por inatividade
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'KEEPALIVE') {
        // Apenas responde para manter o SW vivo
        event.source && event.source.postMessage({ type: 'KEEPALIVE_ACK' });
    }
});