<div class="matches-calendar" data-matches-calendar data-month="{{ $calendarMonth }}" data-events='@json($calendarMatchesData)'>
    <div class="matches-calendar-toolbar">
        <button type="button" class="btn btn-sm matches-calendar-nav-btn" data-calendar-nav="prev" aria-label="Mes anterior">
            <i class="fa-solid fa-chevron-left"></i>
        </button>
        <div class="matches-calendar-title" data-calendar-title>{{ $calendarMonthLabel }}</div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm matches-calendar-today-btn" data-calendar-nav="today">Hoy</button>
            <button type="button" class="btn btn-sm matches-calendar-nav-btn" data-calendar-nav="next" aria-label="Mes siguiente">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>
    </div>

    <div class="matches-calendar-grid" data-calendar-grid></div>

    <div class="matches-calendar-day-details" data-calendar-day-details>
        <div class="matches-calendar-day-title" data-calendar-day-title>Selecciona un dia</div>
        <div class="matches-calendar-events" data-calendar-events>
            <div class="matches-calendar-empty-state"><i class="fa-solid fa-calendar-xmark" aria-hidden="true"></i>Haz clic en un dia para ver los partidos.</div>
        </div>
    </div>
</div>
