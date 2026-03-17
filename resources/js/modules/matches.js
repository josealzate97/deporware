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
    let selectedKey = toKey(new Date(monthDate.getFullYear(), monthDate.getMonth(), 1));

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
            button.className = `matches-calendar-day ${count ? 'has-events' : ''} ${key === selectedKey ? 'is-selected' : ''}`.trim();
            button.innerHTML = `
                <span class="matches-calendar-day-number">${day}</span>
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
        selectedKey = `${monthDate.getFullYear()}-${pad2(monthDate.getMonth() + 1)}-01`;
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

    validateFileInput(document.getElementById('match_file'), ['pdf', 'docx', 'xls', 'xlsx'], 'el informe del partido');
    validateFileInput(document.getElementById('team_photo'), ['jpg', 'png'], 'la foto del equipo');
});
