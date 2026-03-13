document.addEventListener('alpine:init', () => {
    Alpine.data('infoModal', () => ({
        open: false,
        title: 'Detalle',
        content: '',
        async openModal(url) {
            this.open = true;
            this.content = '<div class="text-muted">Cargando...</div>';
            try {
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                this.content = await response.text();
            } catch (error) {
                this.content = '<div class="text-danger">No se pudo cargar la información.</div>';
            }
        },
        closeModal() {
            this.open = false;
            this.content = '';
        },
    }));
});

const initAutocomplete = (wrapper) => {
    const input = wrapper.querySelector('[data-autocomplete-input]');
    const hidden = wrapper.querySelector('[data-autocomplete-hidden]');
    const list = wrapper.querySelector('[data-autocomplete-list]');
    if (!input || !hidden || !list) return;

    let options = [];
    try {
        options = JSON.parse(wrapper.dataset.autocomplete || '[]');
    } catch {
        options = [];
    }

    const closeList = () => {
        list.innerHTML = '';
        list.classList.remove('is-open');
    };

    const render = (items) => {
        list.innerHTML = '';
        if (!items.length) {
            closeList();
            return;
        }
        list.classList.add('is-open');
        items.forEach((item) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'autocomplete-item';
            button.textContent = item.name;
            button.addEventListener('click', () => {
                input.value = item.name;
                hidden.value = item.id;
                closeList();
            });
            list.appendChild(button);
        });
    };

    const sync = () => {
        const query = input.value.trim().toLowerCase();
        if (!query) {
            hidden.value = '';
            closeList();
            return;
        }
        const filtered = options.filter((item) => item.name.toLowerCase().includes(query)).slice(0, 10);
        if (filtered.some((item) => item.name.toLowerCase() === query)) {
            const exact = filtered.find((item) => item.name.toLowerCase() === query);
            hidden.value = exact ? exact.id : '';
        } else {
            hidden.value = '';
        }
        render(filtered);
    };

    if (hidden.value) {
        const match = options.find((item) => item.id === hidden.value);
        if (match) input.value = match.name;
    }

    input.addEventListener('input', sync);
    input.addEventListener('focus', sync);
    input.addEventListener('blur', () => setTimeout(closeList, 150));
};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-autocomplete]').forEach(initAutocomplete);

    const dateInput = document.getElementById('match_date_date');
    const timeInput = document.getElementById('match_date_time');
    const hiddenInput = document.getElementById('match_date');
    if (dateInput && timeInput && hiddenInput) {
        const sync = () => {
            if (dateInput.value && timeInput.value) {
                hiddenInput.value = `${dateInput.value} ${timeInput.value}`;
            }
        };
        dateInput.addEventListener('input', sync);
        timeInput.addEventListener('input', sync);
        sync();
    }
});
