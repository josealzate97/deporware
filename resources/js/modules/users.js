document.addEventListener("DOMContentLoaded", () => {

    console.log("Users Js Loaded!");

    const searchInput = document.getElementById('usersSearch');
    const roleSelect = document.getElementById('usersRoleFilter');
    const table = document.querySelector('.section-table');

    if (searchInput && roleSelect && table) {
        const rows = Array.from(table.querySelectorAll('tbody tr'));

        const filterRows = () => {
            const query = searchInput.value.trim().toLowerCase();
            const role = roleSelect.value;

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const matchesQuery = !query || text.includes(query);
                const matchesRole = !role || row.dataset.role === role;
                row.style.display = matchesQuery && matchesRole ? '' : 'none';
            });
        };

        searchInput.addEventListener('input', filterRows);
        roleSelect.addEventListener('change', filterRows);
    }

    const toggles = document.querySelectorAll('[data-password-toggle]');
    toggles.forEach(toggle => {
        toggle.addEventListener('click', () => {
            const targetId = toggle.getAttribute('data-target');
            const target = document.getElementById(targetId);
            if (!target) return;

            const isPassword = target.getAttribute('type') === 'password';
            target.setAttribute('type', isPassword ? 'text' : 'password');

            const icon = toggle.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-eye', !isPassword);
                icon.classList.toggle('fa-eye-slash', isPassword);
            }
        });
    });

    const createForm = document.querySelector('form[data-user-form="create"]');
    if (createForm) {
        const nameInput = document.getElementById('user-name');
        const usernameInput = document.getElementById('user-username');
        const passwordInput = document.getElementById('user-password');
        const passwordConfirmInput = document.getElementById('user-password-confirm');
        const passwordMessage = document.getElementById('user-password-message');
        const confirmMessage = document.getElementById('user-password-confirm-message');
        const checklist = document.getElementById('user-password-checklist');
        const confirmChecklist = document.getElementById('user-password-confirm-checklist');
        let usernameManuallyEdited = false;

        const updateCreatePasswordMessages = () => {
            if (!window.Validator) return;
            const password = passwordInput ? passwordInput.value : '';
            const confirm = passwordConfirmInput ? passwordConfirmInput.value : '';
            const validation = window.Validator.validatePasswordRules(password);

            if (password.length === 0) {
                window.Validator.setInlineMessage(passwordMessage, '', 'error');
                window.Validator.updateChecklist(checklist, validation.checks || {});
            } else if (!validation.valid) {
                window.Validator.setInlineMessage(passwordMessage, validation.message, 'error');
                window.Validator.updateChecklist(checklist, validation.checks || {});
            } else {
                window.Validator.setInlineMessage(passwordMessage, 'Contraseña válida.', 'success');
                window.Validator.updateChecklist(checklist, validation.checks || {});
            }

            if (confirm.length === 0) {
                window.Validator.setInlineMessage(confirmMessage, '', 'error');
                window.Validator.updateMatchChecklist(confirmChecklist, false);
            } else if (password !== confirm) {
                window.Validator.setInlineMessage(confirmMessage, 'Las contraseñas no coinciden.', 'error');
                window.Validator.updateMatchChecklist(confirmChecklist, false);
            } else {
                window.Validator.setInlineMessage(confirmMessage, 'Las contraseñas coinciden.', 'success');
                window.Validator.updateMatchChecklist(confirmChecklist, true);
            }
        };

        if (usernameInput) {
            usernameInput.addEventListener('input', () => {
                usernameManuallyEdited = usernameInput.value.trim().length > 0;
            });
        }

        if (nameInput && usernameInput) {
            nameInput.addEventListener('input', () => {
                if (usernameManuallyEdited) return;
                if (!window.Validator) return;
                const suggestion = window.Validator.suggestUsername(nameInput.value);
                if (suggestion) {
                    usernameInput.value = suggestion;
                }
            });
        }

        if (passwordInput) {
            passwordInput.addEventListener('blur', updateCreatePasswordMessages);
        }

        if (passwordConfirmInput) {
            passwordConfirmInput.addEventListener('blur', updateCreatePasswordMessages);
        }

        createForm.addEventListener('submit', (event) => {
            const password = passwordInput ? passwordInput.value : '';
            const confirm = passwordConfirmInput ? passwordConfirmInput.value : '';
            const validation = window.Validator ? window.Validator.validatePasswordRules(password) : { valid: true, message: '' };
            updateCreatePasswordMessages();

            if (!validation.valid) {
                event.preventDefault();
                return;
            }

            if (password !== confirm) {
                event.preventDefault();
            }
        });
    }
    
});

const notyf = new Notyf();

// Hacer que la función esté disponible globalmente
window.deleteUser = deleteUser;
window.activateUser = activateUser;

/**
 * Función para manejar el formulario de usuario
 * @param {Object} userData - Datos del usuario a editar
 * @returns {Object} - Objeto con métodos y propiedades para manejar el formulario
*/
window.userForm = function (userData) {
    
    return {
        editMode: false,
        isSaving: false,
        isPasswordValid: true, // Estado inicial de la validación de la contraseña
        isPasswordMatch: true,
        adminRoles: userData.adminRoles || [],
        form: { ...userData, venues: userData.venues || [], new_password: '' },
        original: { ...userData, venues: userData.venues || [], new_password: '' },
        confirmNewPassword: '',
        init() {
            this.$watch('form.role', (value) => {
                if (this.adminRoles.includes(parseInt(value))) {
                    this.form.venues = [];
                }
            });
        },
        enableEdit() {
            this.editMode = true;
        },
        cancelEdit() {
            this.form = { ...this.original, venues: [...(this.original.venues || [])], new_password: '' };
            this.editMode = false;
            this.isPasswordValid = true;
            this.isPasswordMatch = true;
            this.confirmNewPassword = '';
        },
        async saveUser() {
            if (this.isSaving) return;
            this.isSaving = true;

            // const form = document.getElementsByClassName('form')[0];

            /*if (!validateForm(form)) {
                alert('Por favor, completa todos los campos obligatorios.');
                return false;
            }*/

            if (this.form.new_password.length > 0 && (!this.isPasswordValid || !this.isPasswordMatch)) {
                notyf.error('La contraseña no es válida.');
                return;
            }

            try {

                const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                const response = await fetch(`/users/update/${this.form.id}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify(this.form)
                });

                // Si la respuesta es exitosa, actualizamos el estado
                // y mostramos un mensaje de éxito
                if (response.ok == true) {

                    this.original = { ...this.form, new_password: '' };
                    this.form = { ...this.original };
                    this.editMode = false;

                    notyf.success('Usuario actualizado correctamente');

                } else {

                    notyf.error('Error al actualizar');

                }
                
            } catch (e) {

                notyf.error('Error de red');
                
            } finally {
                this.isSaving = false;
            }
        },
        validatePassword() {
            const validation = window.Validator
                ? window.Validator.validatePasswordRules(this.form.new_password)
                : { valid: true, message: '' };
            this.isPasswordValid = validation.valid;
            this.isPasswordMatch = this.form.new_password === this.confirmNewPassword;
            const passwordMessage = document.getElementById('user-new-password-message');
            const confirmMessage = document.getElementById('user-new-password-confirm-message');
            const checklist = document.getElementById('user-new-password-checklist');
            const confirmChecklist = document.getElementById('user-new-password-confirm-checklist');

            if (this.form.new_password.length === 0) {
                this.isPasswordValid = true;
                this.isPasswordMatch = true;
                if (window.Validator) {
                    window.Validator.setInlineMessage(passwordMessage, '', 'error');
                    window.Validator.setInlineMessage(confirmMessage, '', 'error');
                    window.Validator.updateChecklist(checklist, validation.checks || {});
                    window.Validator.updateMatchChecklist(confirmChecklist, false);
                }
                return;
            }

            if (!this.isPasswordValid) {
                if (window.Validator) {
                    window.Validator.setInlineMessage(passwordMessage, validation.message, 'error');
                    window.Validator.updateChecklist(checklist, validation.checks || {});
                }
                return;
            }

            if (!this.isPasswordMatch) {
                if (window.Validator) {
                    window.Validator.setInlineMessage(confirmMessage, 'Las contraseñas no coinciden.', 'error');
                    window.Validator.updateMatchChecklist(confirmChecklist, false);
                }
                return;
            }

            if (window.Validator) {
                window.Validator.setInlineMessage(passwordMessage, 'Contraseña válida.', 'success');
                window.Validator.setInlineMessage(confirmMessage, 'Las contraseñas coinciden.', 'success');
                window.Validator.updateChecklist(checklist, validation.checks || {});
                window.Validator.updateMatchChecklist(confirmChecklist, true);
            }
        }
    }

}

/**
 * Función para eliminar un usuario
 * @param {number} userId - ID del usuario a eliminar
 * @returns {Promise<void>}
*/
async function deleteUser(userId) {

    try {

        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        const response = await fetch(`/users/delete/${userId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            }
        });

        if (response.ok) {

            const payload = await response.json();
            notyf.success(payload.message || 'Usuario marcado como inactivo correctamente');

            if (payload.logout && payload.redirect) {
                setTimeout(() => {
                    window.location.href = payload.redirect;
                }, 800);
                return;
            }

            const badge = document.querySelector(`tr[data-id="${userId}"] .badge`);
            
            badge.textContent = 'Inactivo';
            badge.classList.remove('bg-success');
            badge.classList.add('bg-danger');
            
            setTimeout(() => {
                location.reload();
            }, 3000);

        } else {

            notyf.error('Error al marcar como inactivo');

        }

    } catch (e) {
        notyf.error('Error de red');
    }
}

/** * Función para activar un usuario
 * @param {number} userId - ID del usuario a activar
 * @returns {Promise<void>}
*/
async function activateUser(userId) {

    try {

        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        const response = await fetch(`/users/activate/${userId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token
            }
        });

        if (response.ok) {

            notyf.success('Usuario activado correctamente');
            
            // Cambia dinámicamente el badge del usuario
            const badge = document.querySelector(`tr[data-id="${userId}"] .badge`);

            badge.textContent = 'Activo';
            badge.classList.remove('bg-danger');
            badge.classList.add('bg-success');

            setTimeout(() => {
                location.reload();
            }, 3000);

        } else {

            notyf.error('Error al activar el usuario');

        }

    } catch (e) {

        notyf.error('Error de red');

    }
}
