
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Visualisasi Temporal - Lahan Kritis Track</title>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
        }

        .navbar {
            background: #14532d;
            color: white;
            padding: 20px 6%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .navbar h2 {
            font-size: 22px;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            background: #166534;
            padding: 10px 18px;
            border-radius: 8px;
        }

        .navbar a:hover {
            background: #15803d;
        }

        .container {
            max-width: 1300px;
            margin: 35px auto;
            padding: 0 20px;
        }

        .header {
            margin-bottom: 25px;
        }

        .header h1 {
            font-size: 30px;
            color: #14532d;
            margin-bottom: 10px;
        }

        .header p {
            color: #64748b;
            line-height: 1.6;
        }

        .filter-card {
            background: white;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.04);
            margin-bottom: 25px;
        }

        .filter-card h3 {
            margin-bottom: 20px;
            color: #14532d;
        }

        .filter-form {
            display: flex;
            flex-wrap: wrap;
            align-items: end;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex: 1;
            min-width: 200px;
        }

        .form-group label {
            font-weight: 600;
            font-size: 14px;
        }

        .form-group select {
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: white;
            font-size: 14px;
        }

        .btn-filter {
            background: #15803d;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
        }

        .btn-filter:hover {
            background: #166534;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.04);
            border-left: 5px solid #16a34a;
        }

        .stat-card h4 {
            color: #64748b;
            font-size: 14px;
            margin-bottom: 12px;
        }

        .stat-card .value {
            color: #14532d;
            font-size: 30px;
            font-weight: 700;
        }

        .stat-card .unit {
            font-size: 14px;
            color: #64748b;
            margin-top: 8px;
        }

        .chart-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
            margin-bottom: 25px;
        }

        .chart-card {
            background: white;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.04);
        }

        .chart-card h3 {
            color: #14532d;
            margin-bottom: 10px;
            font-size: 18px;
        }

        .chart-card p {
            color: #64748b;
            font-size: 13px;
            margin-bottom: 20px;
        }

        .chart-wrapper {
            position: relative;
            height: 350px;
        }

        .table-card {
            background: white;
            padding: 25px;
            border-radius: 14px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.04);
            margin-bottom: 25px;
        }

        .table-card h3 {
            color: #14532d;
            margin-bottom: 20px;
        }

        .table-responsive {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            background: #f0fdf4;
            color: #14532d;
            font-size: 14px;
        }

        td {
            font-size: 14px;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .badge-dummy {
            background: #fef3c7;
            color: #92400e;
        }

        .badge-asli {
            background: #dcfce7;
            color: #166534;
        }

        .empty-state {
            text-align: center;
            padding: 30px;
            color: #64748b;
        }

        .footer {
            text-align: center;
            padding: 25px;
            color: #64748b;
            font-size: 13px;
        }

        @media (max-width: 900px) {
            .chart-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 600px) {
            .navbar {
                flex-direction: column;
                align-items: flex-start;
            }

            .header h1 {
                font-size: 24px;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .stat-card .value {
                font-size: 25px;
            }

            .chart-wrapper {
                height: 280px;
            }

            .filter-form {
                flex-direction: column;
                align-items: stretch;
            }

            .btn-filter {
                width: 100%;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <h2>🌿 Lahan Kritis Track</h2>

        <a href="{{ route('home') }}">
            ← Kembali ke Beranda
        </a>
    </nav>

    <main class="container">

        <div class="header">
            <h1>Visualisasi Temporal Lahan Kritis</h1>

            <p>
                Dashboard untuk melihat perubahan luas dan kondisi lahan kritis
                berdasarkan tahun serta sumber data.
            </p>
        </div>

        <!-- Filter -->
        <div class="filter-card">

            <h3>Filter Data Temporal</h3>

            <form action="{{ route('temporal.index') }}"
                  method="GET"
                  class="filter-form">

                <div class="form-group">
                    <label for="tahun">Pilih Tahun</label>

                    <select name="tahun" id="tahun">

                        @foreach($tahunList as $tahunItem)
                            <option value="{{ $tahunItem }}"
                                {{ (string) $tahunItem === (string) $tahun ? 'selected' : '' }}>
                                {{ $tahunItem }}
                            </option>
                        @endforeach

                    </select>
                </div>

                <div class="form-group">
                    <label for="sumber">Sumber Data</label>

                    <select name="sumber" id="sumber">

                        <option value="asli"
                            {{ $sumber === 'asli' ? 'selected' : '' }}>
                            Data Asli
                        </option>

                        <option value="dummy"
                            {{ $sumber === 'dummy' ? 'selected' : '' }}>
                            Data Dummy
                        </option>

                    </select>
                </div>

                <button type="submit" class="btn-filter">
                    Tampilkan Data
                </button>

            </form>

        </div>

        <!-- Statistik -->
        <div class="stats">

            <div class="stat-card">
                <h4>Total Luas Lahan Kritis Tahun {{ $tahun }}</h4>

                <div class="value">
                    {{ number_format((float) $totalLuas, 2, ',', '.') }}
                </div>

                <div class="unit">
                    Hektare (Ha)
                </div>
            </div>

            <div class="stat-card">
                <h4>Jumlah Data Lahan Tahun {{ $tahun }}</h4>

                <div class="value">
                    {{ number_format($jumlahLahan, 0, ',', '.') }}
                </div>

                <div class="unit">
                    Data lahan terdaftar
                </div>
            </div>

        </div>

        <!-- Grafik -->
        <div class="chart-grid">

            <div class="chart-card">

                <h3>Grafik Perubahan Luas Lahan</h3>

                <p>
                    Perubahan total luas lahan berdasarkan tahun
                    untuk sumber data {{ ucfirst($sumber) }}.
                </p>

                <div class="chart-wrapper">
                    <canvas id="temporalChart"></canvas>
                </div>

            </div>

            <div class="chart-card">

                <h3>Distribusi Tingkat Kekritisan</h3>

                <p>
                    Distribusi luas lahan menurut kategori kekritisan
                    pada tahun {{ $tahun }}.
                </p>

                <div class="chart-wrapper">
                    <canvas id="kategoriChart"></canvas>
                </div>

            </div>

        </div>

        <!-- Tabel Rekap -->
        <div class="table-card">

            <h3>Rekapitulasi Kategori Lahan Tahun {{ $tahun }}</h3>

            <div class="table-responsive">

                <table>

                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kategori Kekritisan</th>
                            <th>Total Luas (Ha)</th>
                            <th>Persentase</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($rekap as $index => $item)

                            <tr>
                                <td>{{ $index + 1 }}</td>

                                <td>
                                    {{ $item->kritis ?? 'Tidak diketahui' }}
                                </td>

                                <td>
                                    {{ number_format((float) $item->total_luas, 2, ',', '.') }}
                                </td>

                                <td>
                                    @if((float) $totalLuas > 0)
                                        {{ number_format(((float) $item->total_luas / (float) $totalLuas) * 100, 2, ',', '.') }}%
                                    @else
                                        0%
                                    @endif
                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="4" class="empty-state">
                                    Tidak ada data kategori lahan untuk tahun ini.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

        <!-- Informasi Sumber -->
        <div class="table-card">

            <h3>Informasi Sumber Data</h3>

            <p style="margin-bottom: 15px; color: #64748b;">
                Data yang sedang ditampilkan:
            </p>

            @if($sumber === 'asli')

                <span class="badge badge-asli">
                    Data Asli
                </span>

                <p style="margin-top: 12px; color: #64748b;">
                    Data berasal dari dataset lahan kritis yang tersedia.
                </p>

            @else

                <span class="badge badge-dummy">
                    Data Dummy / Simulasi
                </span>

                <p style="margin-top: 12px; color: #64748b;">
                    Data digunakan untuk simulasi perubahan temporal.
                    Nilainya bukan hasil pengukuran aktual pada tahun tersebut.
                </p>

            @endif

        </div>

    </main>

    <footer class="footer">
        &copy; {{ date('Y') }} Lahan Kritis Track
        — Sistem Monitoring dan Rehabilitasi Lahan Kritis
    </footer>

    <script>
        // Data grafik dari Laravel
        const grafikData = @json($grafik);

        const kategoriData = @json($rekap);

        // Grafik perubahan luas lahan temporal
        const temporalLabels = grafikData.map(item => item.tahun);

        const temporalValues = grafikData.map(item =>
            Number(item.total_luas)
        );

        const temporalCtx = document
            .getElementById('temporalChart')
            .getContext('2d');

        new Chart(temporalCtx, {
            type: 'line',

            data: {
                labels: temporalLabels,

                datasets: [{
                    label: 'Total Luas Lahan (Ha)',

                    data: temporalValues,

                    borderColor: '#16a34a',

                    backgroundColor: 'rgba(22, 163, 74, 0.15)',

                    borderWidth: 3,

                    fill: true,

                    tension: 0.4,

                    pointRadius: 5,

                    pointBackgroundColor: '#15803d'
                }]
            },

            options: {
                responsive: true,

                maintainAspectRatio: false,

                interaction: {
                    intersect: false,
                    mode: 'index'
                },

                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },

                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' +
                                    Number(context.raw).toLocaleString('id-ID', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    }) + ' Ha';
                            }
                        }
                    }
                },

                scales: {
                    y: {
                        beginAtZero: true,

                        title: {
                            display: true,
                            text: 'Luas Lahan (Ha)'
                        }
                    },

                    x: {
                        title: {
                            display: true,
                            text: 'Tahun'
                        }
                    }
                }
            }
        });

        // Grafik kategori kekritisan
        const kategoriLabels = kategoriData.map(item =>
            item.kritis ?? 'Tidak diketahui'
        );

        const kategoriValues = kategoriData.map(item =>
            Number(item.total_luas)
        );

        const kategoriCtx = document
            .getElementById('kategoriChart')
            .getContext('2d');

        new Chart(kategoriCtx, {
            type: 'doughnut',

            data: {
                labels: kategoriLabels,

                datasets: [{
                    label: 'Luas Lahan (Ha)',

                    data: kategoriValues,

                    borderWidth: 2
                }]
            },

            options: {
                responsive: true,

                maintainAspectRatio: false,

                plugins: {
                    legend: {
                        position: 'bottom'
                    },

                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' +
                                    Number(context.raw).toLocaleString('id-ID', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    }) + ' Ha';
                            }
                        }
                    }
                }
            }
        });
    </script>

</body>
</html>
