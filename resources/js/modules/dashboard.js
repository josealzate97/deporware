const animateDashboardCounters = () => {
    const counters = document.querySelectorAll('[data-dashboard-counter]');
    if (!counters.length) return;

    counters.forEach((counter) => {
        const target = Number(counter.getAttribute('data-dashboard-counter') || '0');
        if (!Number.isFinite(target)) return;

        const duration = 700;
        const start = performance.now();

        const tick = (now) => {
            const progress = Math.min((now - start) / duration, 1);
            counter.textContent = Math.round(target * progress).toLocaleString('es-CO');

            if (progress < 1) {
                requestAnimationFrame(tick);
            }
        };

        requestAnimationFrame(tick);
    });
};

const renderDashboardActivity = () => {
    const root = document.querySelector('[data-dashboard-activity]');
    const chart = root?.querySelector('[data-dashboard-activity-chart]');
    if (!root || !chart) return;

    let items = [];
    try {
        items = JSON.parse(root.getAttribute('data-dashboard-activity') || '[]');
    } catch {
        items = [];
    }

    if (!items.length) {
        chart.innerHTML = '<div class="dashboard-empty-state">Sin actividad suficiente para graficar.</div>';
        return;
    }

    const maxValue = Math.max(...items.flatMap((item) => [Number(item.matches || 0), Number(item.trainings || 0)]), 1);

    chart.innerHTML = items.map((item) => {
        const matches = Number(item.matches || 0);
        const trainings = Number(item.trainings || 0);
        const matchesHeight = Math.max((matches / maxValue) * 100, matches > 0 ? 8 : 0);
        const trainingsHeight = Math.max((trainings / maxValue) * 100, trainings > 0 ? 8 : 0);

        return `
            <div class="dashboard-activity-bar" title="${item.monthLabel}: ${matches} partidos, ${trainings} entrenamientos">
                <div class="dashboard-activity-bar__tracks">
                    <span class="dashboard-activity-bar__track dashboard-activity-bar__track--matches" style="height:${matchesHeight}%"></span>
                    <span class="dashboard-activity-bar__track dashboard-activity-bar__track--trainings" style="height:${trainingsHeight}%"></span>
                </div>
                <span class="dashboard-activity-bar__label">${item.label}</span>
            </div>
        `;
    }).join('');
};

document.addEventListener('DOMContentLoaded', () => {
    animateDashboardCounters();
    renderDashboardActivity();
});
