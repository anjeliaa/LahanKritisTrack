/* LahanKritisTrack — grafik dashboard (Chart.js) */
(function () {
    'use strict';

    function init() {
        if (typeof window.Chart === 'undefined') {
            console.error('Chart.js belum dimuat. Periksa file vendor/chartjs/chart.umd.js.');
            return;
        }
        const Chart = window.Chart;
        const dash = (window.APP || {}).dashboard || {};
        const fmt = (n) => Number(n).toLocaleString('id-ID', { maximumFractionDigits: 2 });

        Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
        Chart.defaults.font.size = 11;
        Chart.defaults.color = '#66796e';

        const tooltip = {
            backgroundColor: '#0c3a2b',
            titleColor: '#fff',
            bodyColor: '#d7efe0',
            padding: 12,
            cornerRadius: 10,
            boxPadding: 4
        };

        /* ---------- donat (bentuk awal): legenda di kanan, bisa diklik untuk menyembunyikan irisan ---------- */
        const donut = document.getElementById('chart-donut');
        const d = dash.donut || {};
        if (donut) {
            const old = Chart.getChart(donut);
            if (old) old.destroy();

            if (
                Array.isArray(d.labels) && Array.isArray(d.values) && Array.isArray(d.colors) &&
                d.labels.length === d.values.length && d.labels.length === d.colors.length
            ) {
                new Chart(donut, {
                    type: 'doughnut',
                    data: {
                        labels: d.labels,
                        datasets: [{
                            data: d.values,
                            backgroundColor: d.colors,
                            borderColor: '#ffffff',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '50%',
                        plugins: {
                            legend: {
                                display: true,
                                position: 'right',
                                labels: { usePointStyle: true, pointStyle: 'rect', padding: 14 }
                            },
                            tooltip: { ...tooltip, callbacks: { label: (c) => ' ' + c.label + ': ' + fmt(c.parsed) + ' Ha' } }
                        }
                    }
                });
            } else {
                console.error('Data grafik donat tidak valid:', d);
            }
        }

        /* ---------- batang: target (belakang) vs realisasi (depan) ---------- */
        const bar = document.getElementById('chart-bar');
        const b = dash.bars || {};
        if (bar && Array.isArray(b.labels)) {
            const old = Chart.getChart(bar);
            if (old) old.destroy();
            new Chart(bar, {
                type: 'bar',
                data: {
                    labels: b.labels,
                    datasets: [
                        {
                            label: 'Target Luas (Ha)',
                            data: b.target,
                            backgroundColor: '#d9e8de',
                            borderRadius: 10,
                            borderSkipped: false,
                            grouped: false,
                            order: 2,
                            barPercentage: 0.9,
                            categoryPercentage: 0.7,
                            maxBarThickness: 64
                        },
                        {
                            label: 'Realisasi (Ha)',
                            data: b.real,
                            backgroundColor: '#1f8f5a',
                            borderRadius: 10,
                            borderSkipped: false,
                            grouped: false,
                            order: 1,
                            barPercentage: 0.5,
                            categoryPercentage: 0.7,
                            maxBarThickness: 40
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'end',
                            labels: { usePointStyle: true, pointStyle: 'rectRounded', boxWidth: 10, boxHeight: 10, padding: 16 }
                        },
                        tooltip: { ...tooltip, callbacks: { label: (c) => ' ' + c.dataset.label + ': ' + fmt(c.parsed.y) } }
                    },
                    scales: {
                        x: { grid: { display: false }, border: { display: false } },
                        y: { beginAtZero: true, border: { display: false }, grid: { color: '#ece9dc' }, ticks: { callback: (v) => fmt(v) } }
                    }
                }
            });
        }
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
})();