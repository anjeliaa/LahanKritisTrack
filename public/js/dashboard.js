
/* LahanKritisTrack — grafik dashboard (Chart.js) */
(function () {
    'use strict';

    function initDashboardCharts() {
        const bar = document.getElementById('chart-bar');
        const donut = document.getElementById('chart-donut');

        // Pastikan Chart.js sudah dimuat.
        if (typeof window.Chart === 'undefined') {
            console.error(
                'Chart.js belum dimuat. Periksa file vendor/chartjs/chart.umd.js.'
            );
            return;
        }

        // Ambil data dari Laravel.
        const app = window.APP || {};
        const dashboard = app.dashboard || {};
        const dark = document.documentElement.dataset.theme === 'dark';

        const Chart = window.Chart;

        Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
        Chart.defaults.font.size = 11;
        Chart.defaults.color = dark ? '#cbd5e1' : '#475569';

        // Grafik donat.
        if (donut) {
            // Hapus instance lama jika sudah ada.
            const oldDonut = Chart.getChart(donut);
            if (oldDonut) {
                oldDonut.destroy();
            }

            const data = dashboard.donut || {};

            if (
                Array.isArray(data.labels) &&
                Array.isArray(data.values) &&
                Array.isArray(data.colors) &&
                data.labels.length === data.values.length &&
                data.labels.length === data.colors.length
            ) {
                new Chart(donut, {
                    type: 'doughnut',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            data: data.values,
                            backgroundColor: data.colors,
                            borderColor: dark ? '#111a2b' : '#ffffff',
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
                                labels: {
                                    color: dark ? '#cbd5e1' : '#475569',
                                    usePointStyle: true,
                                    pointStyle: 'rect',
                                    padding: 14
                                }
                            }
                        }
                    }
                });
            } else {
                console.error('Data grafik donat tidak valid:', data);
            }
        }

        // Grafik batang rehabilitasi.
        if (bar) {
            // Hapus instance lama sebelum membuat grafik baru.
            const oldBar = Chart.getChart(bar);
            if (oldBar) {
                oldBar.destroy();
            }

            // Pastikan canvas tidak diberi latar abu-abu.
            bar.style.backgroundColor = 'transparent';
            bar.style.display = 'block';

            const data = dashboard.bars || {};

            if (
                Array.isArray(data.labels) &&
                Array.isArray(data.target) &&
                Array.isArray(data.real) &&
                data.labels.length === data.target.length &&
                data.labels.length === data.real.length
            ) {
                new Chart(bar, {
                    type: 'bar',
                    data: {
                        labels: data.labels,
                        datasets: [
                            {
                                label: 'Target Luas (Ha)',
                                data: data.target,
                                backgroundColor: '#94a3b8',
                                borderColor: '#64748b',
                                borderWidth: 1,
                                borderRadius: 3,
                                maxBarThickness: 48
                            },
                            {
                                label: 'Realisasi Reboisasi (Ha)',
                                data: data.real,
                                backgroundColor: '#16a34a',
                                borderColor: '#15803d',
                                borderWidth: 1,
                                borderRadius: 3,
                                maxBarThickness: 48
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        animation: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                                labels: {
                                    color: dark ? '#cbd5e1' : '#475569',
                                    usePointStyle: true,
                                    pointStyle: 'rect',
                                    padding: 12
                                }
                            },
                            tooltip: {
                                enabled: true
                            }
                        },
                        scales: {
                            x: {
                                display: true,
                                ticks: {
                                    display: true,
                                    color: dark ? '#cbd5e1' : '#475569'
                                },
                                grid: {
                                    display: false
                                },
                                border: {
                                    display: true,
                                    color: dark ? '#475569' : '#cbd5e1'
                                }
                            },
                            y: {
                                display: true,
                                beginAtZero: true,
                                ticks: {
                                    display: true,
                                    color: dark ? '#cbd5e1' : '#475569',
                                    precision: 2
                                },
                                grid: {
                                    display: true,
                                    color: dark ? '#334155' : '#e2e8f0'
                                },
                                border: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            } else {
                console.error(
                    'Data grafik batang tidak valid:',
                    data
                );
            }
        }

        console.log('Data dashboard:', dashboard);
    }

    // Jalankan setelah dokumen siap.
    if (document.readyState === 'loading') {
        document.addEventListener(
            'DOMContentLoaded',
            initDashboardCharts
        );
    } else {
        initDashboardCharts();
    }
})();
