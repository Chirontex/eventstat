const eventstat = {
    matchDelete: (matchId) => {
        const form = document.getElementById('eventstat-match-delete-form');

        const input = document.createElement('input');

        input.setAttribute('type', 'hidden');
        input.setAttribute('name', 'eventstat-match-delete-id');
        input.setAttribute('value', matchId);

        form.appendChild(input);

        form.submit();
    }
};
