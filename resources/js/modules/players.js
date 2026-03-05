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
        confirmOpen: false,
        confirmTitle: 'Confirmar',
        confirmMessage: '',
        confirmAction: '',
        confirmMethod: 'POST',
        confirmSuccess: '',
        isConfirming: false,
        openConfirm({ title, message, action, method = 'POST', successMessage }) {
            this.confirmTitle = title || 'Confirmar';
            this.confirmMessage = message || '';
            this.confirmAction = action || '';
            this.confirmMethod = method || 'POST';
            this.confirmSuccess = successMessage || '';
            this.confirmOpen = true;
        },
        closeConfirm() {
            this.confirmOpen = false;
            this.confirmMessage = '';
            this.confirmAction = '';
            this.confirmMethod = 'POST';
            this.confirmSuccess = '';
        },
        async runConfirm() {
            if (this.isConfirming || !this.confirmAction) return;
            this.isConfirming = true;
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const payload = new URLSearchParams();
                payload.append('_token', token);
                if (this.confirmMethod && this.confirmMethod !== 'POST') {
                    payload.append('_method', this.confirmMethod);
                }

                const response = await fetch(this.confirmAction, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
                    },
                    body: payload.toString(),
                });

                if (!response.ok) {
                    throw new Error('No se pudo completar la acción.');
                }
                if (this.confirmSuccess && window.Notyf) {
                    new window.Notyf().success(this.confirmSuccess);
                }
                window.location.reload();
            } catch (error) {
                if (window.Notyf) {
                    new window.Notyf().error(error.message || 'Error realizando la acción.');
                }
            } finally {
                this.isConfirming = false;
                this.closeConfirm();
            }
        },
    }));
});
