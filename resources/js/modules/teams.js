document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('teamsSearch');
    const statusSelect = document.getElementById('teamsStatusFilter');
    const table = document.querySelector('.section-table');

    const seasonInput = document.getElementById('teamsSeasonFilter');
    const yearInput = document.getElementById('teamsYearFilter');
    const seasonField = document.querySelector('input[name="season"]');
    const yearField = document.querySelector('input[name="year"]');

    const sanitizeYearSeason = (event) => {
        const input = event.target;
        const cleaned = input.value.replace(/[^0-9-]/g, '');
        if (cleaned !== input.value) {
            const pos = input.selectionStart;
            input.value = cleaned;
            if (typeof pos === 'number') {
                const nextPos = Math.max(0, pos - 1);
                input.setSelectionRange(nextPos, nextPos);
            }
        }
    };

    [seasonField, yearField].forEach((field) => {
        if (!field) return;
        field.addEventListener('input', sanitizeYearSeason);
        field.addEventListener('paste', sanitizeYearSeason);
    });

    if (searchInput && statusSelect && seasonInput && yearInput && table) {
        const rows = Array.from(table.querySelectorAll('tbody tr'));

        const filterRows = () => {
            const query = searchInput.value.trim().toLowerCase();
            const status = statusSelect.value;
            const season = seasonInput.value.trim().toLowerCase();
            const year = yearInput.value.trim().toLowerCase();

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const seasonText = row.children[1]?.textContent.toLowerCase() || '';
                const yearText = row.children[2]?.textContent.toLowerCase() || '';
                const matchesQuery = !query || text.includes(query);
                const matchesStatus = !status || row.dataset.status === status;
                const matchesSeason = !season || seasonText.includes(season);
                const matchesYear = !year || yearText.includes(year);
                row.style.display = matchesQuery && matchesStatus && matchesSeason && matchesYear ? '' : 'none';
            });
        };

        searchInput.addEventListener('input', filterRows);
        statusSelect.addEventListener('change', filterRows);
        seasonInput.addEventListener('input', filterRows);
        yearInput.addEventListener('input', filterRows);
    }

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

    Alpine.data('teamsTable', ({ destroyUrlTemplate, activateUrlTemplate }) => ({
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
        async deleteTeam(id) {
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
                    throw new Error(payload.message || 'No se pudo eliminar la plantilla.');
                }

                this.markRowInactive(id);
                this.notifySuccess(payload.message || 'Plantilla inactivada.');
            } catch (error) {
                this.notifyError(error.message || 'Error eliminando la plantilla.');
            } finally {
                this.isDeleting = false;
            }
        },

        async activateTeam(id) {
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
                    throw new Error(payload.message || 'No se pudo activar la plantilla.');
                }

                this.markRowActive(id);
                this.notifySuccess(payload.message || 'Plantilla activada.');
            } catch (error) {
                this.notifyError(error.message || 'Error activando la plantilla.');
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
