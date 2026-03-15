import * as bootstrap from 'bootstrap';
import { Notyf } from 'notyf';
import Alpine from 'alpinejs';
import IMask from 'imask';
import './sidebar-toggle';

window.Notyf = Notyf;
window.IMask = IMask;
window.Alpine = Alpine;
window.bootstrap = bootstrap;

// Loader animation handled by CSS (no JS needed)

// Crear y mostrar overlay de cargando
document.addEventListener("DOMContentLoaded", () => {
    
    Alpine.start();

    // console.log("App Js loadead!");

    const overlay = document.getElementById('loading-overlay');

    // Ocultar el overlay con transición
    if (overlay) {
        const hideDelay = 1000;

        setTimeout(() => {
            overlay.classList.add('is-hidden');
        }, hideDelay);

        setTimeout(() => {
            overlay.remove();
        }, hideDelay + 450);
    }

    
    // Obtén la URL actual sin parámetros ni hash
    const currentPath = window.location.pathname.replace(/^\//, ''); // quita el slash inicial

    // Selecciona todos los enlaces del sidebar
    const links = document.querySelectorAll('.sidebar-link');

    // Llamado a la funcion de marcar el nav-link actual
    getActiveNav(currentPath, links);

    // Toggle de tema (modo noche)
    const themeSwitch = document.getElementById('theme-switch');

    if (themeSwitch) {
        const savedTheme = localStorage.getItem('theme-mode');

        if (savedTheme === 'dark') {
            document.body.classList.add('theme-dark');
            themeSwitch.checked = true;
        }

        themeSwitch.addEventListener('change', () => {
            document.body.classList.toggle('theme-dark', themeSwitch.checked);
            localStorage.setItem('theme-mode', themeSwitch.checked ? 'dark' : 'light');
        });
    }


    const getCountry = () => document.body?.dataset?.country || '';
    const getCurrency = () => document.body?.dataset?.currency || '';

    const phoneMaskByCountry = (country) => {
        if (country === 'CO') return '+{57} 000 000 0000';
        if (country === 'ES') return '+{34} 000 000 000';
        return '+{34} 000 000 000';
    };

    const moneyMaskConfig = (currency) => {
        if (currency === 'COP') {
            return {
                mask: Number,
                scale: 0,
                signed: false,
                thousandsSeparator: '.',
                radix: ',',
                padFractionalZeros: false,
                normalizeZeros: true,
                min: 0,
                max: 9999999999,
            };
        }

        return {
            mask: Number,
            scale: 2,
            signed: false,
            thousandsSeparator: ',',
            radix: '.',
            mapToRadix: ['.'],
            padFractionalZeros: true,
            normalizeZeros: true,
            min: 0,
            max: 9999999999.99,
        };
    };

    const applyPhoneMasks = () => {
        const mask = phoneMaskByCountry(getCountry());
        document.querySelectorAll('.mask-phone').forEach(input => {
            if (input._imask) {
                input._imask.destroy();
            }
            input._imask = IMask(input, { mask });
        });
    };

    const applyMoneyMasks = () => {
        const config = moneyMaskConfig(getCurrency());
        document.querySelectorAll('.mask-money').forEach(input => {
            if (input._imask) {
                input._imask.destroy();
            }
            input._imask = IMask(input, config);
        });
    };

    applyPhoneMasks();
    applyMoneyMasks();

    window.addEventListener('app:config-updated', (event) => {
        if (event?.detail?.country) {
            document.body.dataset.country = event.detail.country;
        }
        if (event?.detail?.currency) {
            document.body.dataset.currency = event.detail.currency;
        }
        applyPhoneMasks();
        applyMoneyMasks();
    });

    document.querySelectorAll('form[data-validate="app"]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.validateForm(form)) {
                event.preventDefault();
            }
        });
    });

});

/**
 * Funcion encargada de marcar activo el nav-link segun la URL
 * @param {*} currentPath - Actual Path
 * @param {*} links - Modulos mostrados en el sidebar
 */
function getActiveNav(currentPath, links) {

    links.forEach(link => {

        link.classList.remove('active');

        let linkPath = link.getAttribute('url'); // ya no necesitas replace
        
        if (linkPath === currentPath || currentPath.startsWith(`${linkPath}/`)) {
            link.classList.add('active');
            localStorage.setItem('sidebar-active', linkPath);
        }

    });

}


/**
 * Función para validar un formulario
 * @param {HTMLFormElement} form - El formulario a validar
 * @returns {boolean} - Devuelve true si el formulario es válido, false si hay campos inválidos
 */
window.validateForm = function (form) {
    const fields = Array.from(form.querySelectorAll('input, select, textarea'));
    let firstInvalid = null;

    const clearFieldError = (field) => {
        field.classList.remove('is-invalid');
        field.classList.remove('invalid-input');
        field.classList.remove('valid-input');

        const wrapper = field.closest('.col-12, .col-7, .col-5, .col-12.col-lg-4, .col-12.col-md-4, .col-12.col-md-6, .col-12.col-lg-3, .col-12.col-lg-5') || field.parentElement;
        if (!wrapper) return;
        wrapper.querySelectorAll('.client-invalid-feedback').forEach((node) => node.remove());
    };

    const showFieldError = (field) => {
        clearFieldError(field);

        field.classList.add('is-invalid');
        field.classList.add('invalid-input');

        const wrapper = field.closest('.col-12, .col-7, .col-5, .col-12.col-lg-4, .col-12.col-md-4, .col-12.col-md-6, .col-12.col-lg-3, .col-12.col-lg-5') || field.parentElement;
        if (!wrapper) return;

        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback d-block client-invalid-feedback';
        feedback.textContent = field.validationMessage || 'Completa este campo.';
        wrapper.appendChild(feedback);
    };

    const syncAutocompleteValidity = () => {
        form.querySelectorAll('[data-autocomplete]').forEach((wrapper) => {
            const visibleInput = wrapper.querySelector('[data-autocomplete-input]');
            const hiddenInput = wrapper.querySelector('[data-autocomplete-hidden]');

            if (!visibleInput || !hiddenInput) return;

            if (visibleInput.required && visibleInput.value.trim() !== '' && !hiddenInput.value) {
                visibleInput.setCustomValidity('Selecciona una opcion valida de la lista.');
            } else {
                visibleInput.setCustomValidity('');
            }
        });
    };

    syncAutocompleteValidity();

    fields.forEach((field) => {
        const isVisible = !!field.offsetParent || field.type === 'hidden';
        if (!isVisible) {
            clearFieldError(field);
            return;
        }

        if (field.checkValidity()) {
            clearFieldError(field);
            field.classList.add('valid-input');
            return;
        }

        showFieldError(field);
        if (!firstInvalid) {
            firstInvalid = field;
        }
    });

    if (!firstInvalid) {
        return true;
    }

    firstInvalid.reportValidity();
    if (firstInvalid.type !== 'hidden') {
        firstInvalid.focus();
    }

    return false;

}
