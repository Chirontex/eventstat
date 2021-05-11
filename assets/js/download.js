const eventstat = {
    matchDelete: (matchId) => {
        const form = document.getElementById('eventstat-match-delete-form');

        const input = document.createElement('input');

        input.setAttribute('type', 'hidden');
        input.setAttribute('name', 'eventstat-match-delete-id');
        input.setAttribute('value', matchId);

        form.appendChild(input);

        form.submit();
    },
    matchUpdateOpen: (matchId) => {
        const placeCell = document.getElementById('eventstat-match-'+matchId+'-place');
        const keyCell = document.getElementById('eventstat-match-'+matchId+'-key');
        const aliasCell = document.getElementById('eventstat-match-'+matchId+'-alias');
        const editCell = document.getElementById('eventstat-match-'+matchId+'-edit');

        let input = document.createElement('input');

        input.setAttribute('type', 'number');
        input.setAttribute('id', 'eventstat-match-'+matchId+'-place-input');
        input.setAttribute('class', 'form-control form-control-sm');
        input.setAttribute('value', placeCell.innerHTML);

        placeCell.innerHTML = '';
        placeCell.appendChild(input);

        input = document.createElement('input');

        input.setAttribute('type', 'text');
        input.setAttribute('id', 'eventstat-match-'+matchId+'-key-input');
        input.setAttribute('class', 'form-control form-control-sm');
        input.setAttribute('value', keyCell.innerHTML);

        keyCell.innerHTML = '';
        keyCell.appendChild(input);

        input = document.createElement('input');

        input.setAttribute('type', 'text');
        input.setAttribute('id', 'eventstat-match-'+matchId+'-alias-input');
        input.setAttribute('class', 'form-control form-control-sm');
        input.setAttribute('value', aliasCell.innerHTML);

        aliasCell.innerHTML = '';
        aliasCell.appendChild(input);

        editCell.innerHTML = '<a href="javascript:void(0)" onclick="eventstat.matchUpdate('+matchId+');">Сохранить</a>';
    },
    matchUpdate: (matchId) => {
        const form = document.getElementById('eventstat-match-update-form');

        const props = ['place', 'key', 'alias'];

        let input;

        for (let i = 0; i < props.length; i++)
        {
            input = document.createElement('input');

            input.setAttribute('type', 'hidden');
            input.setAttribute('name', 'eventstat-match-update-'+props[i]);
            input.setAttribute(
                'value',
                document.
                    getElementById('eventstat-match-'+matchId+'-'+props[i]+'-input').value
            );

            form.appendChild(input);
        }

        input = document.createElement('input');

        input.setAttribute('type', 'hidden');
        input.setAttribute('name', 'eventstat-match-update-id');
        input.setAttribute('value', matchId);

        form.appendChild(input);

        form.submit();
    }
};
