document.addEventListener('alpine:init', () => {
    Alpine.data('infoModal', () => ({
        open: false,
        title: 'Detalle',
        content: '',
        init() {
            window.addEventListener('open-info-modal', (e) => {
                this.openModal(e.detail.url);
            });
        },
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

window._openObservationsModal = (url) => {
    window.dispatchEvent(new CustomEvent('open-info-modal', { detail: { url } }));
};

const pad2 = (value) => String(value).padStart(2, '0');
const toKey = (date) => `${date.getFullYear()}-${pad2(date.getMonth() + 1)}-${pad2(date.getDate())}`;

const buildMonthDate = (value) => {
    if (typeof value === 'string' && /^\d{4}-\d{2}$/.test(value)) {
        const [year, month] = value.split('-').map(Number);
        if (!Number.isNaN(year) && !Number.isNaN(month)) {
            return new Date(year, month - 1, 1);
        }
    }

    const now = new Date();
    return new Date(now.getFullYear(), now.getMonth(), 1);
};

const capitalizeFirst = (text) => {
    if (!text) return '';
    return text.charAt(0).toUpperCase() + text.slice(1);
};

const initTrainingsCalendar = () => {
    const root = document.querySelector('[data-trainings-calendar]');
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
    const isCoordinator = root.dataset.isCoordinator === '1';

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
            eventsEl.innerHTML = '<div class="trainings-calendar-empty-state"><i class="fa-solid fa-calendar-xmark" aria-hidden="true"></i>No hay entrenamientos para este día.</div>';
            return;
        }

        eventsEl.innerHTML = dayEvents.map((event) => {
            const statusClass = Number(event.statusCode) === 1 ? 'is-active' : 'is-inactive';
            return `
            <div class="trainings-calendar-event-item">
                <div class="trainings-calendar-event-main">
                    <div class="fw-semibold">${event.name || 'Entrenamiento'}</div>
                    <div class="text-muted small d-flex align-items-center gap-2 flex-wrap">
                        <span><i class="fa-solid fa-users me-1"></i>${event.team || '-'}</span>
                        <span><i class="fa-solid fa-clock me-1"></i>${event.time || '-'}</span>
                        <span><i class="fa-regular fa-hourglass-half me-1"></i>${event.duration || '-'}</span>
                        <span><i class="fa-solid fa-note-sticky me-1"></i>${event.observationsCount || 0} observaciones</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap mt-2">
                        <a class="btn btn-sm training-attendance-open-btn" href="${event.editUrl || '#'}">
                            <i class="fa-solid fa-pen me-1"></i>Editar
                        </a>
                        ${isCoordinator ? `
                            <a class="btn btn-sm training-attendance-open-btn" href="${event.observationsUrl || event.editUrl || '#'}">
                                <i class="fa-solid fa-note-sticky me-1"></i>Observaciones
                            </a>
                        ` : ''}
                    </div>
                </div>
                <div>
                    <span class="trainings-calendar-status-badge ${statusClass}">${event.status || '-'}</span>
                </div>
            </div>
        `;
        }).join('');
    };

    const renderGrid = () => {
        grid.innerHTML = '';

        const header = document.createElement('div');
        header.className = 'trainings-calendar-weekdays';
        header.innerHTML = weekdayLabels.map((label) => `<span>${label}</span>`).join('');
        grid.appendChild(header);

        const daysWrap = document.createElement('div');
        daysWrap.className = 'trainings-calendar-days';

        const year = monthDate.getFullYear();
        const month = monthDate.getMonth();
        const firstDay = new Date(year, month, 1);
        const daysInMonth = new Date(year, month + 1, 0).getDate();
        const startOffset = (firstDay.getDay() + 6) % 7;

        for (let i = 0; i < startOffset; i += 1) {
            const emptyCell = document.createElement('span');
            emptyCell.className = 'trainings-calendar-day is-empty';
            daysWrap.appendChild(emptyCell);
        }

        for (let day = 1; day <= daysInMonth; day += 1) {
            const date = new Date(year, month, day);
            const key = toKey(date);
            const dayEvents = eventsByDay[key] || [];
            const count = dayEvents.length;
            const isToday = key === todayKey;

            const button = document.createElement('button');
            button.type = 'button';
            button.className = `trainings-calendar-day ${count ? 'has-events' : ''} ${key === selectedKey ? 'is-selected' : ''} ${isToday ? 'is-today' : ''}`.trim();
            button.innerHTML = `
                <span class="trainings-calendar-day-number">${day}</span>
                ${isToday ? '<span class="trainings-calendar-day-today-badge">Hoy</span>' : ''}
                ${count ? `<span class="trainings-calendar-day-count">${count}</span>` : ''}
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

document.addEventListener('DOMContentLoaded', () => {
    initTrainingsCalendar();

    const dateInput = document.getElementById('training_date_date');
    const timeInput = document.getElementById('training_date_time');
    const hiddenInput = document.getElementById('training_date');

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

    const trainingDocumentInput = document.getElementById('training_document');
    const removeDocumentInput = document.getElementById('remove_document');

    if (trainingDocumentInput) {
        trainingDocumentInput.addEventListener('change', () => {
            if (trainingDocumentInput.files?.length && removeDocumentInput) {
                removeDocumentInput.value = '0';
            }

            const assetCard = document.querySelector('[data-training-asset="document"]');
            const uploadWrap = document.querySelector('[data-training-upload="document"]');
            if (assetCard) assetCard.classList.add('d-none');
            if (uploadWrap) uploadWrap.classList.remove('d-none');
        });
    }

    document.addEventListener('click', (event) => {
        const removeBtn = event.target.closest('[data-training-remove]');
        if (!removeBtn) return;

        const assetCard = document.querySelector('[data-training-asset="document"]');
        const uploadWrap = document.querySelector('[data-training-upload="document"]');
        const replacementHint = uploadWrap?.querySelector('[data-training-replacement-label]');

        if (assetCard) assetCard.classList.add('d-none');
        if (uploadWrap) uploadWrap.classList.remove('d-none');
        if (removeDocumentInput) removeDocumentInput.value = '1';
        if (trainingDocumentInput) trainingDocumentInput.value = '';
        if (replacementHint) {
            replacementHint.textContent = 'Selecciona el nuevo informe para reemplazar el anterior.';
        }
    });
});
