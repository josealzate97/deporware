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

document.addEventListener('DOMContentLoaded', () => {
    let lightboxRoot = null;
    let lightboxImage = null;

    const closeLightbox = () => {
        if (!lightboxRoot) return;
        lightboxRoot.classList.remove('is-open');
        lightboxRoot.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('player-lightbox-open');
    };

    const ensureLightbox = () => {
        if (lightboxRoot) return;
        const wrapper = document.createElement('div');
        wrapper.className = 'player-lightbox';
        wrapper.setAttribute('aria-hidden', 'true');
        wrapper.innerHTML = `
            <div class="player-lightbox__backdrop" data-lightbox-close></div>
            <div class="player-lightbox__dialog" role="dialog" aria-modal="true" aria-label="Vista ampliada de imagen">
                <button type="button" class="player-lightbox__close" data-lightbox-close aria-label="Cerrar imagen">
                    <i class="fa-solid fa-xmark"></i>
                </button>
                <img class="player-lightbox__image" alt="Imagen ampliada">
            </div>
        `;
        document.body.appendChild(wrapper);
        lightboxRoot = wrapper;
        lightboxImage = wrapper.querySelector('.player-lightbox__image');

        wrapper.addEventListener('click', (event) => {
            if (event.target.closest('[data-lightbox-close]')) {
                closeLightbox();
            }
        });
    };

    const openLightbox = (src, alt = 'Imagen') => {
        if (!src) return;
        ensureLightbox();
        lightboxImage.src = src;
        lightboxImage.alt = alt;
        lightboxRoot.classList.add('is-open');
        lightboxRoot.setAttribute('aria-hidden', 'false');
        document.body.classList.add('player-lightbox-open');
    };

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-lightbox-src]');
        if (trigger) {
            event.preventDefault();
            const src = trigger.getAttribute('data-lightbox-src') || '';
            const alt = trigger.getAttribute('data-lightbox-alt') || trigger.getAttribute('alt') || 'Imagen';
            openLightbox(src, alt);
            return;
        }

        const previewImage = event.target.closest('[data-photo-img][data-open-lightbox="true"]');
        if (!previewImage) return;
        if (!previewImage.classList.contains('is-visible')) return;

        event.preventDefault();
        openLightbox(previewImage.currentSrc || previewImage.src, previewImage.getAttribute('alt') || 'Foto del jugador');
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeLightbox();
        }
    });

    const input = document.getElementById('player_photo');
    const preview = document.querySelector('[data-photo-preview]');
    const img = preview?.querySelector('[data-photo-img]');
    const empty = preview?.querySelector('[data-photo-empty]');
    const removeInput = document.getElementById('remove_photo');
    const uploadBtn = document.querySelector('[data-photo-action="upload"]');
    const removeBtn = document.querySelector('[data-photo-action="remove"]');
    if (!input || !preview || !img || !empty) return;

    const existingUrl = preview.dataset.photoUrl || '';

    const showImage = (url) => {
        img.src = url;
        img.classList.add('is-visible');
        img.setAttribute('data-lightbox-src', url);
        img.setAttribute('data-lightbox-alt', img.getAttribute('alt') || 'Foto del jugador');
        empty.style.display = 'none';
    };

    const showEmpty = () => {
        img.removeAttribute('src');
        img.removeAttribute('data-lightbox-src');
        img.removeAttribute('data-lightbox-alt');
        img.classList.remove('is-visible');
        empty.style.display = 'flex';
    };

    if (existingUrl) {
        showImage(existingUrl);
    } else {
        showEmpty();
    }

    if (uploadBtn) {
        uploadBtn.addEventListener('click', () => input.click());
    }

    if (removeBtn) {
        removeBtn.addEventListener('click', () => {
            input.value = '';
            if (removeInput) removeInput.value = '1';
            showEmpty();
        });
    }

    input.addEventListener('change', () => {
        const file = input.files?.[0];
        if (!file) {
            if (existingUrl) {
                showImage(existingUrl);
            } else {
                showEmpty();
            }
            return;
        }
        const ext = file.name.split('.').pop()?.toLowerCase() || '';
        if (!['jpg', 'jpeg', 'png'].includes(ext)) {
            if (window.Notyf) {
                new window.Notyf().error('Formato no válido para la foto.');
            } else {
                alert('Formato no válido para la foto.');
            }
            input.value = '';
            showEmpty();
            return;
        }
        if (removeInput) removeInput.value = '0';
        const url = URL.createObjectURL(file);
        showImage(url);
    });
});
