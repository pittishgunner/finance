self.addEventListener('push', function (event) {
    if (!(self.Notification && self.Notification.permission === 'granted')) {
        return;
    }
    console.log(event);
    const sendNotification = response => {
        const {title, options} = JSON.parse(response);
        console.log(title, options);

        return self.registration.showNotification(title, options);
    };

    if (event.data) {
        const message = event.data.text();
        event.waitUntil(sendNotification(message));
    }

    onfetch = async (event) => {
        if (event.request.method !== 'POST') return;

        /* This is to fix the issue Jake found */
        event.respondWith(Response.redirect('/share/image/'));

        event.waitUntil(async function () {
            const data = await event.request.formData();
            const client = await self.clients.get(event.resultingClientId || event.clientId);
            // Get the data from the named element 'file'
            const file = data.get('file');

            console.log('file', file);
            client.postMessage({ file, action: 'load-image' });
        }());
    };
});
