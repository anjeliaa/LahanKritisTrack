/* LahanKritisTrack — grafik dashboard (Chart.js) */
(function () {
  if (!window.Chart || !window.APP || !window.APP.dashboard) return;
  const D = window.APP.dashboard;
  Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
  Chart.defaults.font.size = 11;
  const dark = document.documentElement.dataset.theme === 'dark';
  Chart.defaults.color = dark ? '#94a3b8' : '#64748b';
  const donut = document.getElementById('chart-donut');
  if (donut) new Chart(donut, {
    type: 'doughnut',
    data: { labels: D.donut.labels, datasets: [{ data: D.donut.values, backgroundColor: ['#ef4444', '#f97316', '#eab308', '#10b981'], borderColor: dark ? '#111a2b' : '#fff', borderWidth: 2 }] },
    options: { responsive: true, maintainAspectRatio: false, cutout: '50%', plugins: { legend: { position: 'right', labels: { usePointStyle: true, pointStyle: 'rect', padding: 14 } } } }
  });
  const bar = document.getElementById('chart-bar');
  if (bar) new Chart(bar, {
    type: 'bar',
    data: { labels: D.bars.labels, datasets: [{ label: 'Target Luas (Ha)', data: D.bars.target, backgroundColor: '#cbd5e1', borderRadius: 3 }, { label: 'Realisasi Reboisasi (Ha)', data: D.bars.real, backgroundColor: '#16a34a', borderRadius: 3 }] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'top', labels: { usePointStyle: true, pointStyle: 'rect' } } }, scales: { x: { grid: { display: false } }, y: { grid: { color: dark ? '#1f2a3d' : '#f1f5f9' } } } }
  });
})();
