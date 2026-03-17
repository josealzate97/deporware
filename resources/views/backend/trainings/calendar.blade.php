<div class="trainings-calendar" data-trainings-calendar data-month="{{ $calendarMonth }}" data-events='@json($calendarTrainingsData)'>
    <div class="trainings-calendar-toolbar">
        <button type="button" class="btn btn-sm trainings-calendar-nav-btn" data-calendar-nav="prev" aria-label="Mes anterior">
            <i class="fa-solid fa-chevron-left"></i>
        </button>
        <div class="trainings-calendar-title" data-calendar-title>{{ $calendarMonthLabel }}</div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm trainings-calendar-today-btn" data-calendar-nav="today">Hoy</button>
            <button type="button" class="btn btn-sm trainings-calendar-nav-btn" data-calendar-nav="next" aria-label="Mes siguiente">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>
    </div>

    <div class="trainings-calendar-grid" data-calendar-grid></div>

    <div class="trainings-calendar-day-details" data-calendar-day-details>
        <div class="trainings-calendar-day-title" data-calendar-day-title>Selecciona un día</div>
        <div class="trainings-calendar-events" data-calendar-events>
            <div class="trainings-calendar-empty-state"><i class="fa-solid fa-calendar-xmark" aria-hidden="true"></i>Haz clic en un día para ver los entrenamientos.</div>
        </div>
    </div>
</div>
