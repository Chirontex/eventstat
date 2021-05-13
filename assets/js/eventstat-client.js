const eventstatClient = {
    emec: 0,
    availableClicks: 0,
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
    },
    click: async (buttonId, event, user, key) => {
        const button = document.getElementById(buttonId);

        const span = document.createElement('span');
        span.setAttribute('id', buttonId+'-wait');

        span.innerHTML = 'Подождите...';

        document.getElementById(buttonId+'-content-0').setAttribute('style', 'display: none;');
        document.getElementById(buttonId+'-content-1').setAttribute('style', 'display: none;');

        button.appendChild(span);

        const data = new FormData;

        data.append('eventstat-button-event', event);
        data.append('eventstat-button-user', user);
        data.append('eventstat-button-key', key);

        const request = await fetch(
            '/wp-json/eventstat/v1/click',
            {
                method: 'POST',
                credentials: 'include',
                body: data
            }
        );

        if (request.ok)
        {
            const answer = await request.json();

            if (answer.code == 0) console.log('eventstatClient.click(): success.');
            else console.error('eventstatClient.click(): API error.');
        }
        else console.error('eventstatClient.click(): network error.');

        button.removeChild(span);

        document.getElementById(buttonId+'-content-1').removeAttribute('style');

        button.setAttribute('disabled', 'true');

        eventstatClient.availableClicks -= 1;

        if (eventstatClient.availableClicks > 0)
        {
            setTimeout(
                (buttonId) => {
                    const button = document.getElementById(buttonId);

                    if (button.hasAttribute('disabled')) button.removeAttribute('disabled');

                    document.getElementById(buttonId+'-content-0').removeAttribute('style');
                    document.getElementById(buttonId+'-content-1').setAttribute('style', 'display: none;');
                },
                900000,
                buttonId
            );
        }
    }
};
