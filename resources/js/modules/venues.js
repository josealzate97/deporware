document.addEventListener('DOMContentLoaded', () => {
    
});

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

    Alpine.data('venuesTable', ({ destroyUrlTemplate, activateUrlTemplate }) => ({
        isDeleting: false,
        isActivating: false,
        statusMap: {},
        init() {
            document.querySelectorAll('.section-table tbody tr[data-id]').forEach((row) => {
                this.statusMap[row.dataset.id] = row.dataset.status || '0';
            });
        },
        rowStatus(id) {
            return this.statusMap[id] || '0';
        },
        async deleteVenue(id) {
            if (this.isDeleting) return;
            this.isDeleting = true;

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const url = destroyUrlTemplate.replace('__ID__', id);

                const response = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(payload.message || 'No se pudo eliminar la sede.');
                }

                this.markRowInactive(id);
                this.notifySuccess(payload.message || 'Sede inactivada.');
            } catch (error) {
                this.notifyError(error.message || 'Error eliminando la sede.');
            } finally {
                this.isDeleting = false;
            }
        },

        async activateVenue(id) {
            if (this.isActivating) return;
            this.isActivating = true;

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const url = activateUrlTemplate.replace('__ID__', id);

                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(payload.message || 'No se pudo activar la sede.');
                }

                this.markRowActive(id);
                this.notifySuccess(payload.message || 'Sede activada.');
            } catch (error) {
                this.notifyError(error.message || 'Error activando la sede.');
            } finally {
                this.isActivating = false;
            }
        },

        markRowInactive(id) {
            const row = document.querySelector(`tr[data-id="${id}"]`);
            if (!row) return;
            row.dataset.status = '0';
            this.statusMap[id] = '0';
            const badge = row.querySelector('.status-pill');
            if (badge) {
                badge.textContent = 'Inactiva';
                badge.classList.remove('status-pill-success');
                badge.classList.add('status-pill-muted');
            }
        },

        markRowActive(id) {
            const row = document.querySelector(`tr[data-id="${id}"]`);
            if (!row) return;
            row.dataset.status = '1';
            this.statusMap[id] = '1';
            const badge = row.querySelector('.status-pill');
            if (badge) {
                badge.textContent = 'Activa';
                badge.classList.remove('status-pill-muted');
                badge.classList.add('status-pill-success');
            }
        },

        notifySuccess(message) {
            if (window.Notyf) {
                new window.Notyf().success(message);
            }
        },

        notifyError(message) {
            if (window.Notyf) {
                new window.Notyf().error(message);
            }
        },
    }));
});
