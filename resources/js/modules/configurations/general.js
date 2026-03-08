document.addEventListener('alpine:init', () => {
    Alpine.data('configurationGeneralForm', ({ initial, hasConfig, indexUrl, updateUrl }) => ({
        hasConfig: !!hasConfig,
        isEditing: !hasConfig,
        isLoading: false,
        isSaving: false,
        form: {},
        original: {},

        init() {
            this.applyConfig(initial);
            this.fetchConfig(indexUrl);
            this.$watch('form.country', () => this.updateAppConfig());
            this.$watch('form.currency', () => this.updateAppConfig());
        },

        defaultForm() {
            return {
                id: '',
                name: '',
                legal_name: '',
                legal_id: '',
                country: '',
                city: '',
                address: '',
                phone: '',
                email: '',
                website: '',
                logo: '',
                currency: '',
                timezone: '',
                locale: '',
                sport: '',
            };
        },

        applyConfig(config) {
            const base = this.defaultForm();
            if (!config) {
                this.form = { ...base };
                this.original = { ...this.form };
                this.hasConfig = false;
                return;
            }

            this.form = { ...base, ...config };
            this.original = { ...this.form };
            this.hasConfig = true;
        },

        async fetchConfig(url) {
            if (!url) return;
            this.isLoading = true;

            try {
                const response = await fetch(url, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    throw new Error('No se pudo cargar la configuracion.');
                }

                const payload = await response.json();
                this.applyConfig(payload.config || null);
            } catch (error) {
                this.notifyError(error.message || 'Error cargando la configuracion.');
            } finally {
                this.isLoading = false;
            }
        },

        enableEdit() {
            this.isEditing = true;
        },

        cancelEdit() {
            this.form = { ...this.original };
            this.isEditing = !this.hasConfig;
        },

        async save() {
            if (this.isSaving) return;
            this.isSaving = true;

            try {
                const formData = new FormData();
                Object.entries(this.form).forEach(([key, value]) => {
                    formData.append(key, value ?? '');
                });

                formData.append('_method', 'PUT');

                const response = await fetch(updateUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.getCsrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });

                if (!response.ok) {
                    throw new Error('No se pudo guardar la configuracion.');
                }

                const payload = await response.json();
                this.applyConfig(payload.config || this.form);
                this.updateAppConfig();
                this.isEditing = false;
                this.hasConfig = true;
                this.notifySuccess(payload.message || 'Configuracion actualizada.');
            } catch (error) {
                this.notifyError(error.message || 'Error guardando la configuracion.');
            } finally {
                this.isSaving = false;
            }
        },

        updateAppConfig() {
            if (document.body) {
                document.body.dataset.country = this.form.country || '';
                document.body.dataset.currency = this.form.currency || '';
            }
            window.dispatchEvent(
                new CustomEvent('app:config-updated', {
                    detail: {
                        country: this.form.country || '',
                        currency: this.form.currency || '',
                    },
                })
            );
        },

        getCsrfToken() {
            const tokenTag = document.querySelector('meta[name="csrf-token"]');
            return tokenTag ? tokenTag.getAttribute('content') : '';
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
