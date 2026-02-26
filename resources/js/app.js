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

    let isValid = true;

    // Selecciona todos los inputs, selects y textareas con la clase "form-control" dentro del formulario
    const inputs = form.querySelectorAll('.form-control');

    inputs.forEach(input => {

        if (input.value.trim() === '') {

            // Si el campo está vacío, agrega la clase "invalid-input" y remueve "valid-input"
            input.classList.add('invalid-input');
            input.classList.remove('valid-input');
            isValid = false;

        } else {

            // Si el campo tiene valor, agrega la clase "valid-input" y remueve "invalid-input"
            input.classList.add('valid-input');
            input.classList.remove('invalid-input');

        }

    });

    return isValid; // Devuelve true si todos los campos son válidos, false si hay algún campo inválido

}
