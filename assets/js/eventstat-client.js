const eventstatClient = {
    emec: 0,
    listener: false,
    check: async (event, user, key) => {
        if (!eventstatClient.listener)
        {
            eventstatClient.listener = true;

            window.addEventListener('beforeunload', function(e) {
                e.preventDefault();

                eventstatClient.check(event, user, key);
            }, false);
        }

        const data = new FormData;

        data.append('eventstat-check-event', event);
        data.append('eventstat-check-user', user);
        data.append('eventstat-check-key', key);

        let byTimeout = true;

        const request = await fetch(
            '/wp-json/eventstat/v1/check',
            {
                method: 'POST',
                credentials: 'include',
                body: data
            }
        );

        if (request.ok)
        {
            const answer = await request.json();

            eventstatClient.emec = 0;

            if (answer.code == 0) console.log('eventstatClient.check(): success.');
            else console.error('eventstatClient.check(): API error. "'+answer.message+'"');
        }
        else
        {
            eventstatClient.emec = ++eventstatClient.emec;

            if (eventstatClient.emec < 3)
            {
                byTimeout = false;

                eventstatClient.check(event, user, key);
            }

            console.error('eventstatClient.check(): network error.');
        }

        if (byTimeout) setTimeout(eventstatClient.check, 300000, event, user, key);
    }
};
