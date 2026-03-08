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

    Alpine.data('playersPage', (config = {}) => ({
        ...infoModal(),
        ...observationModal(),
        search: config.search || '',
        position: config.position || '',
        team: config.team || '',
        baseUrl: config.baseUrl || window.location.pathname,
        isLoading: false,
        confirmOpen: false,
        confirmTitle: 'Confirmar',
        confirmMessage: '',
        confirmAction: '',
        confirmMethod: 'POST',
        confirmSuccess: '',
        isConfirming: false,
        init() {
            this.open = false;
            this.content = '';
            this.title = 'Detalle';
            this.observationOpen = false;
            this.confirmOpen = false;
            this.bindPagination();
        },
        buildUrl(overrideUrl) {
            if (overrideUrl) return overrideUrl;
            const params = new URLSearchParams();
            if (this.search) params.set('search', this.search);
            if (this.position) params.set('position', this.position);
            if (this.team) params.set('team', this.team);
            const query = params.toString();
            return query ? `${this.baseUrl}?${query}` : this.baseUrl;
        },
        bindPagination() {
            const wrap = document.getElementById('playersTableWrap');
            if (!wrap) return;
            wrap.querySelectorAll('.pagination a.page-link').forEach((link) => {
                link.addEventListener('click', (event) => {
                    const href = link.getAttribute('href');
                    if (!href || href === '#') return;
                    event.preventDefault();
                    this.fetchPlayers(href);
                });
            });
        },
        async fetchPlayers(overrideUrl) {
            if (this.isLoading) return;
            const url = this.buildUrl(overrideUrl);
            this.isLoading = true;
            try {
                const response = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (!response.ok) {
                    throw new Error('No se pudo actualizar la lista.');
                }
                const html = await response.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const newMeta = doc.getElementById('playersResultsMeta');
                const newWrap = doc.getElementById('playersTableWrap');

                if (newMeta && newWrap) {
                    const currentMeta = document.getElementById('playersResultsMeta');
                    const currentWrap = document.getElementById('playersTableWrap');
                    if (currentMeta) currentMeta.innerHTML = newMeta.innerHTML;
                    if (currentWrap) {
                        currentWrap.innerHTML = newWrap.innerHTML;
                        if (window.Alpine && typeof window.Alpine.initTree === 'function') {
                            window.Alpine.initTree(currentWrap);
                        }
                    }
                    this.bindPagination();
                }

                window.history.replaceState({}, '', url);
            } catch (error) {
                if (window.Notyf) {
                    new window.Notyf().error(error.message || 'Error al actualizar la lista.');
                }
            } finally {
                this.isLoading = false;
            }
        },
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
