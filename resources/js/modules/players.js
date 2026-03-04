document.addEventListener('alpine:init', () => {
    const observationModal = () => ({
        observationOpen: false,
        observationPlayerId: null,
        observationPlayerName: '',
        openObservation(id, name) {
            this.observationPlayerId = id;
            this.observationPlayerName = name;
            this.observationOpen = true;
        },
        closeObservation() {
            this.observationOpen = false;
            this.observationPlayerId = null;
            this.observationPlayerName = '';
        },
    });

    const infoModal = () => ({
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
    });

    Alpine.data('infoModal', infoModal);

    Alpine.data('playersPage', () => ({
        ...infoModal(),
        ...observationModal(),
    }));
});
