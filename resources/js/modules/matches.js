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
});

const pad2 = (value) => String(value).padStart(2, '0');

const toKey = (date) => `${date.getFullYear()}-${pad2(date.getMonth() + 1)}-${pad2(date.getDate())}`;

const buildMonthDate = (monthValue) => {
    const [yearStr, monthStr] = String(monthValue || '').split('-');
    const year = Number(yearStr);
    const month = Number(monthStr);
    if (!Number.isInteger(year) || !Number.isInteger(month) || month < 1 || month > 12) {
        const now = new Date();
        return new Date(now.getFullYear(), now.getMonth(), 1);
    }
    return new Date(year, month - 1, 1);
};

const capitalizeFirst = (text) => {
    if (!text) return '';
    return text.charAt(0).toUpperCase() + text.slice(1);
};

const initMatchesCalendar = () => {
    const root = document.querySelector('[data-matches-calendar]');
    if (!root) return;

    let events = [];
    try {
        events = JSON.parse(root.dataset.events || '[]');
    } catch {
        events = [];
    }

    const eventsByDay = events.reduce((acc, item) => {
        const key = item.date;
        if (!key) return acc;
        if (!acc[key]) acc[key] = [];
        acc[key].push(item);
        return acc;
    }, {});

    const weekdayLabels = ['Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab', 'Dom'];
    const grid = root.querySelector('[data-calendar-grid]');
    const titleEl = root.querySelector('[data-calendar-title]');
    const dayTitleEl = root.querySelector('[data-calendar-day-title]');
    const eventsEl = root.querySelector('[data-calendar-events]');
    const navButtons = root.querySelectorAll('[data-calendar-nav]');

    if (!grid || !titleEl || !dayTitleEl || !eventsEl) return;

    const monthDate = buildMonthDate(root.dataset.month);
    const today = new Date();
    const todayKey = toKey(today);
    const isCurrentMonth = monthDate.getFullYear() === today.getFullYear() && monthDate.getMonth() === today.getMonth();
    let selectedKey = isCurrentMonth
        ? todayKey
        : toKey(new Date(monthDate.getFullYear(), monthDate.getMonth(), 1));

    const monthFormatter = new Intl.DateTimeFormat('es-CO', {
        month: 'long',
        year: 'numeric',
    });
    const dayFormatter = new Intl.DateTimeFormat('es-CO', {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });

    const renderDetails = (dayKey) => {
        const dayEvents = eventsByDay[dayKey] || [];
        const dateForTitle = new Date(`${dayKey}T00:00:00`);
        dayTitleEl.textContent = capitalizeFirst(dayFormatter.format(dateForTitle));

        if (!dayEvents.length) {
            eventsEl.innerHTML = '<div class="matches-calendar-empty-state"><i class="fa-solid fa-calendar-xmark" aria-hidden="true"></i>No hay partidos para este dia.</div>';
            return;
        }

        eventsEl.innerHTML = dayEvents.map((event) => {
            const statusClass = event.statusCode === 2
                ? 'is-completed'
                : event.statusCode === 3
                    ? 'is-cancelled'
                    : 'is-scheduled';

            const resultClass = event.resultCode === 1
                ? 'is-win'
                : event.resultCode === 2
                    ? 'is-loss'
                    : event.resultCode === 3
                        ? 'is-draw'
                        : '';

            const resultBadge = event.statusCode === 2 && resultClass !== ''
                ? `<span class="match-calendar-result-badge ${resultClass}">${event.resultLabel || '-'}</span>`
                : '';

            return `
            <div class="matches-calendar-event-item">
                <div class="matches-calendar-event-main">
                    <div class="fw-semibold">${event.team} vs ${event.rival}</div>
                    <div class="text-muted small d-flex align-items-center gap-2 flex-wrap">
                        <span><i class="fa-solid fa-clock me-1"></i>${event.time}</span>
                        <span class="match-calendar-status-badge ${statusClass}">${event.status}</span>
                    </div>
                </div>
                <div class="matches-calendar-event-badges">
                    ${resultBadge}
                    <span class="meta-badge">${event.score}</span>
                </div>
            </div>
        `;
        }).join('');
    };

    const renderGrid = () => {
        grid.innerHTML = '';
        const header = document.createElement('div');
        header.className = 'matches-calendar-weekdays';
        header.innerHTML = weekdayLabels.map((label) => `<span>${label}</span>`).join('');
        grid.appendChild(header);

        const daysWrap = document.createElement('div');
        daysWrap.className = 'matches-calendar-days';

        const year = monthDate.getFullYear();
        const month = monthDate.getMonth();
        const firstDay = new Date(year, month, 1);
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const startOffset = (firstDay.getDay() + 6) % 7;

        for (let i = 0; i < startOffset; i += 1) {
            const emptyCell = document.createElement('span');
            emptyCell.className = 'matches-calendar-day is-empty';
            daysWrap.appendChild(emptyCell);
        }

        for (let day = 1; day <= daysInMonth; day += 1) {
            const date = new Date(year, month, day);
            const key = toKey(date);
            const dayEvents = eventsByDay[key] || [];
            const count = dayEvents.length;
            const resultBadges = dayEvents
                .filter((event) => event.statusCode === 2 && [1, 2, 3].includes(Number(event.resultCode)))
                .slice(0, 2)
                .map((event) => {
                    const cls = Number(event.resultCode) === 1
                        ? 'is-win'
                        : Number(event.resultCode) === 2
                            ? 'is-loss'
                            : 'is-draw';
                    const letter = Number(event.resultCode) === 1
                        ? 'G'
                        : Number(event.resultCode) === 2
                            ? 'P'
                            : 'E';
                    return `<span class="matches-calendar-day-result ${cls}">${letter}</span>`;
                })
                .join('');

            const button = document.createElement('button');
            button.type = 'button';
            const isToday = key === todayKey;
            button.className = `matches-calendar-day ${count ? 'has-events' : ''} ${key === selectedKey ? 'is-selected' : ''} ${isToday ? 'is-today' : ''}`.trim();
            button.innerHTML = `
                <span class="matches-calendar-day-number">${day}</span>
                ${isToday ? '<span class="matches-calendar-day-today-badge">Hoy</span>' : ''}
                ${count ? `<span class="matches-calendar-day-count">${count}</span>` : ''}
                ${resultBadges ? `<span class="matches-calendar-day-results">${resultBadges}</span>` : ''}
            `;
            button.addEventListener('click', () => {
                selectedKey = key;
                renderGrid();
                renderDetails(key);
            });
            daysWrap.appendChild(button);
        }

        grid.appendChild(daysWrap);
        titleEl.textContent = capitalizeFirst(monthFormatter.format(monthDate));
    };

    navButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const action = button.dataset.calendarNav;
            let target = new Date(monthDate.getFullYear(), monthDate.getMonth(), 1);
            if (action === 'prev') target.setMonth(target.getMonth() - 1);
            if (action === 'next') target.setMonth(target.getMonth() + 1);
            if (action === 'today') {
                const now = new Date();
                target = new Date(now.getFullYear(), now.getMonth(), 1);
            }

            const params = new URLSearchParams(window.location.search);
            params.set('view', 'calendar');
            params.set('month', `${target.getFullYear()}-${pad2(target.getMonth() + 1)}`);
            params.delete('page');
            window.location.search = params.toString();
        });
    });

    renderGrid();
    if (!(eventsByDay[selectedKey] || []).length) {
        selectedKey = isCurrentMonth
            ? todayKey
            : `${monthDate.getFullYear()}-${pad2(monthDate.getMonth() + 1)}-01`;
    }
    renderDetails(selectedKey);
};

const initAutocomplete = (wrapper) => {
    const input = wrapper.querySelector('[data-autocomplete-input]');
    const hidden = wrapper.querySelector('[data-autocomplete-hidden]');
    const list = wrapper.querySelector('[data-autocomplete-list]');
    if (!input || !hidden || !list) return;

    let options = [];
    try {
        options = JSON.parse(wrapper.dataset.autocomplete || '[]');
    } catch {
        options = [];
    }

    const closeList = () => {
        list.innerHTML = '';
        list.classList.remove('is-open');
    };

    const render = (items) => {
        list.innerHTML = '';
        if (!items.length) {
            closeList();
            return;
        }
        list.classList.add('is-open');
        items.forEach((item) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'autocomplete-item';
            button.textContent = item.name;
            button.addEventListener('click', () => {
                input.value = item.name;
                hidden.value = item.id;
                closeList();
            });
            list.appendChild(button);
        });
    };

    const sync = () => {
        const query = input.value.trim().toLowerCase();
        if (!query) {
            hidden.value = '';
            closeList();
            return;
        }
        const filtered = options.filter((item) => item.name.toLowerCase().includes(query)).slice(0, 10);
        if (filtered.some((item) => item.name.toLowerCase() === query)) {
            const exact = filtered.find((item) => item.name.toLowerCase() === query);
            hidden.value = exact ? exact.id : '';
        } else {
            hidden.value = '';
        }
        render(filtered);
    };

    if (hidden.value) {
        const match = options.find((item) => item.id === hidden.value);
        if (match) input.value = match.name;
    }

    input.addEventListener('input', sync);
    input.addEventListener('focus', sync);
    input.addEventListener('blur', () => setTimeout(closeList, 150));
};

document.addEventListener('DOMContentLoaded', () => {
    let lightboxRoot = null;
    let lightboxImage = null;

    const closeLightbox = () => {
        if (!lightboxRoot) return;
        lightboxRoot.classList.remove('is-open');
        lightboxRoot.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('match-lightbox-open');
    };

    const ensureLightbox = () => {
        if (lightboxRoot) return;
        const wrapper = document.createElement('div');
        wrapper.className = 'match-lightbox';
        wrapper.setAttribute('aria-hidden', 'true');
        wrapper.innerHTML = `
            <div class="match-lightbox__backdrop" data-lightbox-close></div>
            <div class="match-lightbox__dialog" role="dialog" aria-modal="true" aria-label="Vista ampliada de archivo">
                <button type="button" class="match-lightbox__close" data-lightbox-close aria-label="Cerrar vista previa">
                    <i class="fa-solid fa-xmark"></i>
                </button>
                <img class="match-lightbox__image" alt="Imagen ampliada">
            </div>
        `;
        document.body.appendChild(wrapper);
        lightboxRoot = wrapper;
        lightboxImage = wrapper.querySelector('.match-lightbox__image');

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
        document.body.classList.add('match-lightbox-open');
    };

    document.querySelectorAll('[data-autocomplete]').forEach(initAutocomplete);
    initMatchesCalendar();

    const dateInput = document.getElementById('match_date_date');
    const timeInput = document.getElementById('match_date_time');
    const hiddenInput = document.getElementById('match_date');
    if (dateInput && timeInput && hiddenInput) {
        const sync = () => {
            if (dateInput.value && timeInput.value) {
                hiddenInput.value = `${dateInput.value} ${timeInput.value}`;
            }
        };
        dateInput.addEventListener('input', sync);
        timeInput.addEventListener('input', sync);
        sync();
    }

    const showFileError = (message) => {
        if (window.Notyf) {
            new window.Notyf().error(message);
        } else {
            alert(message);
        }
    };

    const validateFileInput = (input, allowedExts, label) => {
        if (!input) return;
        input.addEventListener('change', () => {
            const file = input.files?.[0];
            if (!file) return;
            const ext = file.name.split('.').pop()?.toLowerCase() || '';
            if (!allowedExts.includes(ext)) {
                showFileError(`Formato no válido para ${label}.`);
                input.value = '';
            }
        });
    };

    const matchFileInput = document.getElementById('match_file');
    const teamPhotoInput = document.getElementById('team_photo');

    validateFileInput(matchFileInput, ['pdf', 'docx', 'xls', 'xlsx'], 'el informe del partido');
    validateFileInput(teamPhotoInput, ['jpg', 'jpeg', 'png'], 'la foto del equipo');

    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-match-photo-trigger], [data-lightbox-src]');
        if (trigger) {
            const src = trigger.getAttribute('data-lightbox-src') || '';
            if (src) {
                event.preventDefault();
                openLightbox(src, trigger.getAttribute('data-lightbox-alt') || 'Foto del equipo');
            }
            return;
        }

        const removeBtn = event.target.closest('[data-match-remove]');
        if (!removeBtn) return;

        const assetCard = removeBtn.closest('[data-match-asset]');
        if (!assetCard) return;

        const assetType = assetCard.getAttribute('data-match-asset');
        const uploadWrap = document.querySelector(`[data-match-upload="${assetType}"]`);
        const hiddenRemove = document.getElementById(assetType === 'report' ? 'remove_match_file' : 'remove_team_photo');
        const input = document.getElementById(assetType === 'report' ? 'match_file' : 'team_photo');
        const replacementHint = uploadWrap?.querySelector('[data-match-replacement-label]');
        const alpineState = input?.closest('form')?._x_dataStack?.[0];

        assetCard.classList.add('d-none');
        uploadWrap?.classList.remove('d-none');

        if (hiddenRemove) hiddenRemove.value = '1';
        if (alpineState) {
            if (assetType === 'report') alpineState.hasExistingMatchFile = false;
            if (assetType === 'photo') alpineState.hasExistingTeamPhoto = false;
        }
        if (input) {
            input.value = '';
            input.dispatchEvent(new Event('change'));
        }

        if (replacementHint) {
            replacementHint.textContent = assetType === 'report'
                ? 'Selecciona el nuevo informe para reemplazar el anterior.'
                : 'Selecciona la nueva foto para reemplazar la anterior.';
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeLightbox();
        }
    });

    if (teamPhotoInput) {
        const photoStage = document.querySelector('[data-match-photo-stage]');
        const photoPreview = document.querySelector('[data-match-photo-preview]');
        const removePhotoInput = document.getElementById('remove_team_photo');

        const showPreview = (src) => {
            if (!photoStage || !photoPreview) return;
            photoPreview.src = src;
            photoStage.classList.remove('d-none');
        };

        const hidePreview = () => {
            if (!photoStage || !photoPreview) return;
            photoPreview.removeAttribute('src');
            photoStage.classList.add('d-none');
        };

        teamPhotoInput.addEventListener('change', () => {
            const file = teamPhotoInput.files?.[0];
            if (!file) {
                hidePreview();
                return;
            }

            const ext = file.name.split('.').pop()?.toLowerCase() || '';
            if (!['jpg', 'jpeg', 'png'].includes(ext)) {
                hidePreview();
                return;
            }

            if (removePhotoInput) removePhotoInput.value = '0';
            showPreview(URL.createObjectURL(file));
        });
    }

    if (matchFileInput) {
        const removeMatchFileInput = document.getElementById('remove_match_file');
        matchFileInput.addEventListener('change', () => {
            if (matchFileInput.files?.length && removeMatchFileInput) {
                removeMatchFileInput.value = '0';
            }
            const alpineState = matchFileInput.closest('form')?._x_dataStack?.[0];
            if (alpineState && matchFileInput.files?.length) {
                alpineState.hasExistingMatchFile = false;
            }
        });
    }
});
