window.Validator = window.Validator || {};

window.Validator.slugify = function (value) {
    return value
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim()
        .replace(/[^a-z0-9]+/g, '.')
        .replace(/^\.+|\.+$/g, '')
        .replace(/\.+/g, '.');
};

window.Validator.suggestUsername = function (fullName) {
    return window.Validator.slugify(fullName || '');
};

window.Validator.validatePasswordRules = function (password) {
    const checks = {
        length: password.length >= 8,
        letter: /[A-Za-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[^A-Za-z0-9]/.test(password),
    };

    if (!checks.length) {
        return { valid: false, message: 'La contraseña debe tener al menos 8 caracteres.', checks };
    }

    if (!checks.letter) {
        return { valid: false, message: 'La contraseña debe incluir al menos una letra.', checks };
    }

    if (!checks.number) {
        return { valid: false, message: 'La contraseña debe incluir al menos un número.', checks };
    }

    if (!checks.special) {
        return { valid: false, message: 'La contraseña debe incluir al menos un carácter especial.', checks };
    }

    return { valid: true, message: '', checks };
};

window.Validator.setInlineMessage = function (element, message, type) {
    if (!element) return;
    element.textContent = message || '';
    element.classList.toggle('text-danger', type === 'error');
    element.classList.toggle('text-success', type === 'success');
};

window.Validator.updateChecklist = function (listEl, checks) {
    if (!listEl || !checks) return;
    const items = listEl.querySelectorAll('[data-rule]');
    items.forEach(item => {
        const rule = item.getAttribute('data-rule');
        const passed = checks[rule];
        item.classList.toggle('is-passed', !!passed);
        item.classList.toggle('is-failed', passed === false);
    });
};

window.Validator.updateMatchChecklist = function (listEl, matches) {
    if (!listEl) return;
    const item = listEl.querySelector('[data-rule="match"]');
    if (!item) return;
    item.classList.toggle('is-passed', !!matches);
    item.classList.toggle('is-failed', matches === false);
};
