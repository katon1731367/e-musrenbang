@extends('layout')

@section('title', 'Dashboard')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between mb-3">
        <h4 class="fw-bold">Dashboard E-MUSRENBANG</h4>
        <div>
            <!-- Tambahkan tombol atau elemen lain di sini jika diperlukan -->
        </div>
    </div>

    <div class="row mb-4">
        <!-- Jumlah Total Usulan -->
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Total Usulan</h6>
                            <h3 class="card-text" id="totalUsulanCount">0</h3>
                        </div>
                        <div class="bg-primary bg-opacity-25 p-3 rounded">
                            <i class="bi bi-clipboard-data" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Usulan Draft -->
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Draft</h6>
                            <h3 class="card-text" id="draftUsulanCount">0</h3>
                        </div>
                        <div class="bg-warning bg-opacity-25 p-3 rounded">
                            <i class="bi bi-pencil-square" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Usulan Diajukan -->
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Diajukan</h6>
                            <h3 class="card-text" id="diajukanUsulanCount">0</h3>
                        </div>
                        <div class="bg-info bg-opacity-25 p-3 rounded">
                            <i class="bi bi-send" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Usulan Disetujui -->
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title">Disetujui</h6>
                            <h3 class="card-text" id="approveUsulanCount">0</h3>
                        </div>
                        <div class="bg-success bg-opacity-25 p-3 rounded">
                            <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Grafik atau Tabel Ringkas -->
        <div class="col-md-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Statistik Usulan (Beranda)</h5>
                </div>
                <div class="card-body">
                    <canvas id="usulanChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Aktivitas Terbaru -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Aktivitas Terbaru</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush" id="aktivitasList">
                        <!-- Aktivitas akan dimuat di sini -->
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold">Memuat...</div>
                                <small class="text-muted">Mengambil data aktivitas...</small>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(function() {
        function loadDashboardData() {
            $.get('{{ route("dashboard.data") }}', function(data) {
                $('#totalUsulanCount').text(data.total_usulan || 0);
                $('#draftUsulanCount').text(data.draft_usulan || 0);
                $('#diajukanUsulanCount').text(data.diajukan_usulan || 0);
                $('#approveUsulanCount').text(data.approve_usulan || 0);

                updateChart(data.chart_data || []);

                updateActivityLog(data.aktivitas_terbaru || []);
            }).fail(function() {
                console.error('Gagal memuat data dashboard.');
            });
        }

        function updateChart(chartData) {
            const ctx = document.getElementById('usulanChart').getContext('2d');
            if (window.usulanChartInstance) {
                window.usulanChartInstance.destroy();
            }
            window.usulanChartInstance = new Chart(ctx, {
                type: 'bar', // Bisa juga 'line', 'pie', dll.
                data: {
                    labels: chartData.labels || [],
                    datasets: [{
                        label: 'Jumlah Usulan',
                        data: chartData.data || [],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.2)',
                            'rgba(255, 205, 86, 0.2)',
                            'rgba(75, 192, 192, 0.2)',
                            'rgba(153, 102, 255, 0.2)',
                            'rgba(255, 159, 64, 0.2)'
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 205, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)',
                            'rgba(255, 159, 64, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: 'Statistik Usulan Berdasarkan Status'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        }

        function updateActivityLog(activities) {
            const list = $('#aktivitasList');
            list.empty();
            if (activities.length > 0) {
                activities.forEach(activity => {
                    const waktu = new Date(activity.created_at).toLocaleString('id-ID');
                    list.append(`
                        <li class="list-group-item d-flex justify-content-between align-items-start">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold">${activity.action}</div>
                                <small class="text-muted">Usulan: ${activity.usulan_judul || 'N/A'} | ${waktu}</small>
                            </div>
                            <span class="badge bg-light text-dark rounded-pill">${activity.user_name}</span>
                        </li>
                    `);
                });
            } else {
                list.append(`
                    <li class="list-group-item d-flex justify-content-between align-items-start">
                        <div class="ms-2 me-auto">
                            <div class="fw-bold">Tidak Ada Aktivitas</div>
                            <small class="text-muted">Belum ada aktivitas terbaru.</small>
                        </div>
                    </li>
                `);
            }
        }

        loadDashboardData();
    });
</script>
@endpush