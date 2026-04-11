/**
 * tenants.js — Lógica del formulario de creación de escuelas (tenants).
 *
 * - Rellena automáticamente el campo slug (base) a partir del nombre en tiempo real.
 * - Muestra una vista previa del slug FINAL incluyendo el sufijo numérico (_001).
 * - Si el usuario edita el slug manualmente, se desvincula del nombre.
 * - El botón reset vuelve a enlazar nombre → slug.
 * - Bloquea caracteres especiales en el campo nombre.
 * - Bloquea caracteres inválidos en el campo slug (solo a-z, 0-9, _).
 */

document.addEventListener('DOMContentLoaded', () => {
    const nameInput      = document.getElementById('tenantName');
    const slugInput      = document.getElementById('tenantSlug');
    const slugResetBtn   = document.getElementById('slugResetBtn');
    const slugFinalPreview = document.getElementById('slugFinalPreview');

    if (!nameInput || !slugInput) return;

    // Indica si el usuario ha editado el slug manualmente.
    let userEditedSlug = slugInput.value.length > 0;

    // -----------------------------------------------------------------------
    // Convierte texto al formato de slug del servidor (base, sin número).
    // -----------------------------------------------------------------------
    function toSlug(str) {
        return str
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^_+|_+$/g, '');
    }

    // -----------------------------------------------------------------------
    // Actualiza el preview del slug final: base + _###
    // -----------------------------------------------------------------------
    function updateFinalPreview() {
        if (!slugFinalPreview) return;
        const base = slugInput.value.trim();
        slugFinalPreview.textContent = base ? base + '_###' : '…';
    }

    // -----------------------------------------------------------------------
    // Sincroniza el slug desde el nombre (solo cuando no fue editado manualmente).
    // -----------------------------------------------------------------------
    function syncSlugFromName() {
        if (userEditedSlug) {
            updateFinalPreview();
            return;
        }
        const base = toSlug(nameInput.value.trim());
        slugInput.value = base;
        updateFinalPreview();
    }

    // -----------------------------------------------------------------------
    // Nombre: solo letras (incluidas acentuadas/ñ), dígitos, espacio y - . , & '
    // -----------------------------------------------------------------------
    function sanitizeName(value) {
        return value.replace(/[^\p{L}\p{N} \-.,&']/gu, '');
    }

    nameInput.addEventListener('input', function () {
        const raw   = this.value;
        const clean = sanitizeName(raw);

        if (clean !== raw) {
            const pos = this.selectionStart - (raw.length - clean.length);
            this.value = clean;
            this.setSelectionRange(pos, pos);
            this.classList.add('is-invalid');
            clearTimeout(this._warnTimer);
            this._warnTimer = setTimeout(() => this.classList.remove('is-invalid'), 1500);
        }

        syncSlugFromName();
    });

    // -----------------------------------------------------------------------
    // Slug: solo a-z, 0-9 y guión bajo. Se elimina cualquier otro carácter.
    // -----------------------------------------------------------------------
    slugInput.addEventListener('input', function () {
        const raw   = this.value;
        const clean = raw.toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9_]/g, '');

        if (clean !== raw) {
            const pos = Math.max(0, this.selectionStart - (raw.length - clean.length));
            this.value = clean;
            this.setSelectionRange(pos, pos);
        }

        userEditedSlug = clean.length > 0;
        updateFinalPreview();
    });

    slugInput.addEventListener('keydown', function (e) {
        const nav = ['ArrowLeft','ArrowRight','ArrowUp','ArrowDown','Home','End',
                     'Shift','Control','Alt','Meta','Tab','CapsLock'];
        if (!nav.includes(e.key)) {
            setTimeout(() => { userEditedSlug = slugInput.value.length > 0; }, 0);
        }
    });

    // -----------------------------------------------------------------------
    // Botón reset: vuelve a generar el slug desde el nombre.
    // -----------------------------------------------------------------------
    if (slugResetBtn) {
        slugResetBtn.addEventListener('click', () => {
            userEditedSlug = false;
            syncSlugFromName();
            slugInput.focus();
        });
    }

    // Inicializar (p.ej. old() tras error de validación).
    syncSlugFromName();
});
