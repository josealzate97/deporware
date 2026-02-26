document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('venuesSearch');
    const statusSelect = document.getElementById('venuesStatusFilter');
    const table = document.querySelector('.section-table');

    if (searchInput && statusSelect && table) {
        const rows = Array.from(table.querySelectorAll('tbody tr'));

        const filterRows = () => {
            const query = searchInput.value.trim().toLowerCase();
            const status = statusSelect.value;

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const matchesQuery = !query || text.includes(query);
                const matchesStatus = !status || row.dataset.status === status;
                row.style.display = matchesQuery && matchesStatus ? '' : 'none';
            });
        };

        searchInput.addEventListener('input', filterRows);
        statusSelect.addEventListener('change', filterRows);
    }
});

document.addEventListener('alpine:init', () => {
    Alpine.data('venuesTable', ({ destroyUrlTemplate }) => ({
        isDeleting: false,
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

                if (!response.ok) {
                    throw new Error('No se pudo eliminar la sede.');
                }

                const payload = await response.json();
                this.markRowInactive(id);
                this.notifySuccess(payload.message || 'Sede inactivada.');
            } catch (error) {
                this.notifyError(error.message || 'Error eliminando la sede.');
            } finally {
                this.isDeleting = false;
            }
        },

        markRowInactive(id) {
            const row = document.querySelector(`tr[data-id="${id}"]`);
            if (!row) return;
            row.dataset.status = '0';
            const badge = row.querySelector('.status-pill');
            if (badge) {
                badge.textContent = 'Inactiva';
                badge.classList.remove('status-pill-success');
                badge.classList.add('status-pill-muted');
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
