document.addEventListener('alpine:init', () => {
    Alpine.data('configurationGeneralForm', ({ initial, hasConfig, indexUrl, updateUrl }) => ({
        hasConfig: !!hasConfig,
        isEditing: !hasConfig,
        isLoading: false,
        isSaving: false,
        logoFile: null,
        logoPreviewUrl: '',
        logoObjectUrl: null,
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
                this.resetLogoState();
                return;
            }

            this.form = { ...base, ...config };
            this.original = { ...this.form };
            this.hasConfig = true;
            this.revokeLogoObjectUrl();
            this.logoFile = null;
            this.logoPreviewUrl = this.resolveLogoUrl(this.form.logo);
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
            this.revokeLogoObjectUrl();
            this.logoFile = null;
            this.logoPreviewUrl = this.resolveLogoUrl(this.form.logo);
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
                formData.append('remove_logo', this.form.logo === '' ? '1' : '0');
                if (this.logoFile) {
                    formData.append('logo_file', this.logoFile);
                }

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

        onLogoSelected(event) {
            const file = event?.target?.files?.[0];
            if (!file) return;

            this.logoFile = file;
            this.form.logo = file.name;
            this.revokeLogoObjectUrl();
            this.logoObjectUrl = URL.createObjectURL(file);
            this.logoPreviewUrl = this.logoObjectUrl;
        },

        removeLogo() {
            this.logoFile = null;
            this.form.logo = '';
            this.revokeLogoObjectUrl();
            this.logoPreviewUrl = '';

            const input = document.getElementById('configuration_logo_file');
            if (input) input.value = '';
        },

        resolveLogoUrl(value) {
            const raw = String(value || '').trim();
            if (!raw) return '';
            if (/^https?:\/\//i.test(raw)) return raw;
            if (raw.startsWith('/')) return raw;
            return `/storage/${raw}`;
        },

        revokeLogoObjectUrl() {
            if (this.logoObjectUrl) {
                URL.revokeObjectURL(this.logoObjectUrl);
                this.logoObjectUrl = null;
            }
        },

        resetLogoState() {
            this.logoFile = null;
            this.logoPreviewUrl = '';
            this.revokeLogoObjectUrl();
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
