@extends('layout')

@section('title', 'Daftar Usulan')

@section('content')

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <div class="container mt-4">

        <div class="d-flex justify-content-between mb-3">
            <h4 class="fw-bold">Daftar Usulan</h4>
            <button class="btn btn-primary" id="btnTambah">
                <i class="bi bi-plus-lg"></i> Tambah Usulan
            </button>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <table id="tabelUsulan" class="table table-bordered table-striped w-100">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Judul</th>
                            <th>Deskripsi</th>
                            <th>Jenis Usulan</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Form Usulan -->
    <div class="modal fade" id="modalUsulan" tabindex="-1" aria-labelledby="modalUsulanLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalUsulanLabel">Buat Usulan Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <form id="formUsulan" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">

                        <input type="hidden" name="id_usulan" id="id_usulan">
                        <input type="hidden" name="id_pengusul" id="id_pengusul">

                        <div class="mb-3">
                            <label for="id_jenis_usulan" class="form-label">Jenis Usulan</label>
                            <select name="id_jenis_usulan" id="id_jenis_usulan" class="form-select" required>
                                <option value="">-- Pilih Jenis Usulan --</option>
                                @foreach($jenisUsulan as $jenis)
                                    <option value="{{ $jenis->id }}">{{ $jenis->nama }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="judul" class="form-label">Judul Usulan</label>
                            <input type="text" class="form-control" id="judul" name="judul"
                                placeholder="Masukkan judul usulan" required>
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3"
                                placeholder="Tuliskan deskripsi singkat" required></textarea>
                        </div>

                        <div class="mb-3 position-relative">
                            <label for="alamat" class="form-label">Alamat Lokasi</label>
                            <input type="text" id="alamat" name="alamat" class="form-control" placeholder="Ketik alamat...">
                            <div id="alamat-list" class="list-group position-absolute w-100 shadow-sm"
                                style="z-index: 1000; max-height: 200px; overflow-y: auto; display: none;"></div>
                        </div>

                        <div id="map" style="height: 300px; border-radius: 10px;"></div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <label class="form-label">Latitude</label>
                                <input type="text" id="latitude" name="latitude" class="form-control" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Longitude</label>
                                <input type="text" id="longitude" name="longitude" class="form-control" readonly>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Apakah pengusul sama dengan pengguna saat ini?</label>
                            <div>
                                <input type="radio" name="pengusul_sama" value="1" checked> Ya
                                <input type="radio" name="pengusul_sama" value="0"> Tidak
                            </div>
                        </div>

                        <div id="formPengusulLain" class="mt-3" style="display: none;">
                            <label>Nama Pengusul</label>
                            <input type="text" name="nama_pengusul" class="form-control mb-2">
                            <label>Alamat Pengusul</label>
                            <textarea name="alamat_pengusul" class="form-control mb-2"></textarea>
                            <label>No. Telepon Pengusul</label>
                            <input type="text" name="no_telp_pengusul" class="form-control mb-2">
                        </div>

                        <div id="dokumenContainer" class="border p-3 rounded mb-3">
                            <label class="form-label">Unggah Dokumen Pendukung</label>
                            @foreach($dokumenJenis as $jenis)
                                <div class="mb-3">
                                    <label class="form-label">{{ ucfirst($jenis->nama) }}</label>
                                    <input type="file" name="dokumen[{{ $jenis->id }}]" class="form-control"
                                        accept=".pdf,.doc,.docx,.jpg,.png">
                                </div>
                            @endforeach
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Batal
                        </button>
                        <button type="submit" class="btn btn-primary" id="btn-submit">
                            <i class="bi bi-save"></i> Simpan Usulan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        const dokumenSection = $('#dokumenContainer');
        let table;
        let modal;

        $(function () {
            const map = L.map('map').setView([-6.200000, 106.816666], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a>'
            }).addTo(map);

            let marker;
            let typingTimer;

            map.on('click', function (e) {
                const lat = e.latlng.lat.toFixed(6);
                const lon = e.latlng.lng.toFixed(6);

                if (marker) marker.setLatLng(e.latlng);
                else marker = L.marker(e.latlng).addTo(map);

                $('#latitude').val(lat);
                $('#longitude').val(lon);
            });

            $('#alamat').on('input', function () {
                clearTimeout(typingTimer);
                const query = $(this).val().trim();
                if (query.length < 3) {
                    $('#alamat-list').hide();
                    return;
                }

                typingTimer = setTimeout(function () {
                    $.getJSON(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`, function (data) {
                        const list = $('#alamat-list');
                        list.empty();

                        if (data.length > 0) {
                            $.each(data.slice(0, 5), function (i, place) {
                                const item = $(`
                                                                                                                                                    <button type="button" class="list-group-item list-group-item-action">
                                                                                                                                                        ${place.display_name}
                                                                                                                                                    </button>
                                                                                                                                                `);
                                item.on('click', function () {
                                    const lat = parseFloat(place.lat);
                                    const lon = parseFloat(place.lon);

                                    $('#alamat').val(place.display_name);
                                    $('#latitude').val(lat.toFixed(6));
                                    $('#longitude').val(lon.toFixed(6));
                                    list.hide();

                                    map.setView([lat, lon], 15);
                                    if (marker) marker.setLatLng([lat, lon]);
                                    else marker = L.marker([lat, lon]).addTo(map);
                                });

                                list.append(item);
                            });
                            list.show();
                        } else {
                            list.hide();
                        }
                    });
                }, 400);
            });

            $(document).on('click', function (e) {
                if (!$(e.target).closest('#alamat, #alamat-list').length) {
                    $('#alamat-list').hide();
                }
            });

            modal = new bootstrap.Modal('#modalUsulan');

            table = $('#tabelUsulan').DataTable({
                ajax: '{{ route('usulan.data') }}',
                columns: [
                    { data: null, render: (data, type, row, meta) => meta.row + 1 },
                    { data: 'judul' },
                    { data: 'deskripsi' },
                    { data: 'jenis_usulan.nama', defaultContent: '-' },
                    { data: 'latitude' },
                    { data: 'longitude' },
                    {
                        data: null,
                        render: function (data) {
                            return `
                                                                                    <button class="btn btn-sm btn-info btnDetail" data-id="${data.id}">
                                                                                        <i class="bi bi-eye"></i> Detail
                                                                                    </button>
                                                                                    <button class="btn btn-sm btn-warning btnEdit" data-id="${data.id}">
                                                                                        <i class="bi bi-pencil"></i> Ubah
                                                                                    </button>
                                                                                    <button class="btn btn-sm btn-danger btnDelete" data-id="${data.id}">
                                                                                        <i class="bi bi-trash"></i> Hapus
                                                                                    </button>
                                                                                `;
                        }
                    }
                ],
                language: {
                    emptyTable: "Belum ada data usulan",
                    lengthMenu: "Tampilkan _MENU_ data per halaman",
                    zeroRecords: "Data tidak ditemukan",
                    info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                    infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
                    infoFiltered: "(disaring dari total _MAX_ data)",
                    search: "Cari:",
                    paginate: {
                        first: "Awal",
                        last: "Akhir",
                        next: "Berikutnya",
                        previous: "Sebelumnya"
                    },
                }
            });

            $('#btnTambah').on('click', function () {
                $('#formUsulan')[0].reset();
                $('#id_usulan').val('');
                $('#modalUsulanLabel').text('Buat Usulan Baru');
                renderDokumenForm();
                modal.show();
            });

            $('#formUsulan').on('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(this);
                const idUsulan = $('#id_usulan').val();

                if (idUsulan) {
                    url = `usulan/${idUsulan}`;
                    method = 'POST';
                    formData.append('_method', 'PUT');
                }


                $.ajax({
                    url: url,
                    method: method,
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: () => {
                        $('#btn-submit').prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Menyimpan...');
                    },
                    success: res => {
                        modal.hide();
                        table.ajax.reload();

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: 'Usulan berhasil disimpan.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    },
                    error: err => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal!',
                            text: 'Terjadi kesalahan saat menyimpan usulan.',
                        });
                    },
                    complete: () => {
                        $('.btn-submit').prop('disabled', false).html('<i class="bi bi-save"></i> Simpan Usulan');
                    }
                });
            });

            $('input[name="pengusul_sama"]').on('change', function () {
                if ($(this).val() == '1') {
                    $('#formPengusulLain').hide();
                } else {
                    $('#formPengusulLain').show();
                }
            });

            $('#tabelUsulan').on('click', '.btnEdit', function () {
                const id = $(this).data('id');
                $.get(`usulan/${id}`, function (data) {
                    $('#id_pengusul_hidden').remove();

                    if (data.id_pengusul) {
                        $('<input>').attr({
                            type: 'hidden',
                            id: 'id_pengusul_hidden',
                            name: 'id_pengusul',
                            value: data.id_pengusul
                        }).appendTo('#formUsulan');
                    }

                    $('#modalUsulanLabel').text('Edit Usulan');
                    $('#id_usulan').val(data.id);
                    $('#id_jenis_usulan').val(data.id_jenis_usulan);
                    $('#judul').val(data.judul);
                    $('#deskripsi').val(data.deskripsi);
                    $('#latitude').val(data.latitude);
                    $('#longitude').val(data.longitude);

                    if (data.id_pengusul) {
                        $('#id_pengusul').val(data.id_pengusul);
                        $('input[name="pengusul_sama"][value="0"]').prop('checked', true).trigger('change');
                        $('input[name="nama_pengusul"]').val(data.pengusul.nama_pengusul || '');
                        $('textarea[name="alamat_pengusul"]').val(data.pengusul.alamat_pengusul || '');
                        $('input[name="no_telp_pengusul"]').val(data.pengusul.no_telp_pengusul || '');
                    } else {
                        $('#id_pengusul').val('');
                        $('input[name="pengusul_sama"][value="1"]').prop('checked', true).trigger('change');
                    }

                    if (data.latitude && data.longitude) {
                        const lat = parseFloat(data.latitude);
                        const lon = parseFloat(data.longitude);
                        map.setView([lat, lon], 14);
                        if (marker) marker.setLatLng([lat, lon]);
                        else marker = L.marker([lat, lon]).addTo(map);
                    }

                    const dokumenSection = $('.border.p-3.rounded.mb-3');
                    let dokumenHtml = '<label class="form-label">Dokumen Pendukung</label>';

                    const dokumenMap = {};
                    if (data.dokumen && Array.isArray(data.dokumen)) {
                        data.dokumen.forEach(d => {
                            dokumenMap[d.id_jenis_dokumen] = d;
                        });
                    }

                    @foreach($dokumenJenis as $jenis)
                        d = dokumenMap[{{ $jenis->id }}];
                        namaJenis = "{{ ucfirst($jenis->nama) }}";
                        dokumenHtml += `<div class="mb-3">`;
                        dokumenHtml += `<label class="form-label">Dokumen ${namaJenis}</label>`;

                        if (d) {
                            dokumenHtml += `
                                                                                                                                            <div class="alert alert-info p-2 small">
                                                                                                                                                <i class="bi bi-file-earmark-check me-1"></i>
                                                                                                                                                <a href="/dokumen-usulan/${btoa(d.id)}" target="_blank" class="text-dark">
                                                                                                                                                    ${d.name_file}
                                                                                                                                                </a>
                                                                                                                                                <br>
                                                                                                                                                <small class="text-muted">Diunggah: ${new Date(d.created_at).toLocaleDateString()}</small>
                                                                                                                                            </div>
                                                                                                                                        `;
                        }

                        dokumenHtml += `
                                                                                                                                        <input type="file" name="dokumen[{{ $jenis->id }}]" class="form-control" accept=".pdf,.doc,.docx,.jpg,.png">
                                                                                                                                        ${d ? '<small class="text-muted">Biarkan kosong jika tidak ingin mengganti.</small>' : ''}
                                                                                                                                    </div>`;
                    @endforeach

                    dokumenSection.html(dokumenHtml);

                    modal.show();
                });
            });

            $('#tabelUsulan').on('click', '.btnDelete', function () {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Yakin?',
                    text: "Data yang dihapus tidak bisa dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `usulan/${id}`,
                            type: 'DELETE',
                            data: { _token: '{{ csrf_token() }}' },
                            success: res => {
                                table.ajax.reload();

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Berhasil!',
                                    text: res.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            },
                            error: err => {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal!',
                                    text: '❌ Gagal menghapus usulan.',
                                });
                            }
                        });
                    }
                });
            });

            $('#tabelUsulan').on('click', '.btnDetail', function () {
                const id = $(this).data('id');
                $.get(`usulan/${id}`, function (data) {

                    let pengusulNama = data.id_pengusul ? data.pengusul.nama_pengusul : data.created_by.name;
                    let pengusulAlamat = data.id_pengusul ? data.pengusul.alamat_pengusul : '-';
                    let pengusulTelp = data.id_pengusul ? data.pengusul.no_telp_pengusul : '-';

                    let html = `
                                                                                    <div class="mb-3">
                                                                                        <h6 class="text-primary mb-2">Judul Usulan</h6>
                                                                                        <p class="ms-3">${data.judul}</p>
                                                                                    </div>

                                                                                    <div class="mb-3">
                                                                                        <h6 class="text-primary mb-2">Deskripsi</h6>
                                                                                        <p class="ms-3">${data.deskripsi || '-'}</p>
                                                                                    </div>

                                                                                    <div class="row mb-3">
                                                                                        <div class="col-md-6">
                                                                                            <h6 class="text-primary mb-2">Jenis Usulan</h6>
                                                                                            <p class="ms-3">${data.jenis_usulan ? data.jenis_usulan.nama : '-'}</p>
                                                                                        </div>
                                                                                        <div class="col-md-6">
                                                                                            <h6 class="text-primary mb-2">Status</h6>
                                                                                            <p class="ms-3">
                                                                                                <span class="badge bg-secondary text-wrap">${data.status.nama}</span>
                                                                                            </p>
                                                                                        </div>
                                                                                    </div>

                                                                                    <div class="mb-3">
                                                                                        <h6 class="text-primary mb-2">Lokasi Usulan</h6>
                                                                                        <div class="ms-3">
                                                                                            <div id="mapDetail" style="height: 300px; border-radius: 8px; border: 1px solid #ddd;" class="mb-2"></div>

                                                                                            <div class="alert alert-info mb-2" id="alamatInfo">
                                                                                                <div class="d-flex align-items-center">
                                                                                                    <div class="spinner-border spinner-border-sm me-2" role="status">
                                                                                                        <span class="visually-hidden">Loading...</span>
                                                                                                    </div>
                                                                                                    <small>Memuat informasi alamat...</small>
                                                                                                </div>
                                                                                            </div>

                                                                                            <div class="row mb-2">
                                                                                                <div class="col-md-6">
                                                                                                    <small class="text-muted">
                                                                                                        <i class="bi bi-geo-alt-fill text-danger"></i> 
                                                                                                        <strong>Latitude:</strong> ${data.latitude}
                                                                                                    </small>
                                                                                                </div>
                                                                                                <div class="col-md-6">
                                                                                                    <small class="text-muted">
                                                                                                        <i class="bi bi-geo-alt-fill text-danger"></i> 
                                                                                                        <strong>Longitude:</strong> ${data.longitude}
                                                                                                    </small>
                                                                                                </div>
                                                                                            </div>

                                                                                            <div class="d-grid gap-2">
                                                                                                <a href="https://www.google.com/maps?q=${data.latitude},${data.longitude}" 
                                                                                                   target="_blank" 
                                                                                                   class="btn btn-success btn-sm">
                                                                                                    <i class="bi bi-map"></i> Buka di Google Maps
                                                                                                </a>
                                                                                            </div>
                                                                                        </div>
                                                                                    </div>

                                                                                    <div class="mb-3">
                                                                                        <h6 class="text-primary mb-2">Pengusul</h6>
                                                                                        <div class="ms-3">
                                                                                            <p class="mb-1"><strong>Nama:</strong> ${pengusulNama}</p>
                                                                                            <p class="mb-1"><strong>Alamat:</strong> ${pengusulAlamat}</p>
                                                                                            <p class="mb-0"><strong>No. Telepon:</strong> ${pengusulTelp}</p>
                                                                                        </div>
                                                                                    </div>
                                                                                `;

                    if (data.dokumen && data.dokumen.length > 0) {
                        html += `
                                                                            <div class="mb-3">
                                                                                <h6 class="text-primary mb-2">Dokumen Lampiran</h6>
                                                                                <div class="list-group ms-3">
                                                                        `;
                        data.dokumen.forEach(d => {
                            // Ambil nama jenis dokumen, fallback ke '-' jika tidak ada
                            const jenisDokumenNama = d.jenis_dokumen ? d.jenis_dokumen.nama : '-';

                            html += `
                                                                                <div class="list-group-item d-flex justify-content-between align-items-start">
                                                                                    <div class="flex-grow-1 me-3">
                                                                                        <div>
                                                                                            <i class="bi bi-file-earmark-text-fill text-primary me-2"></i>
                                                                                            <strong>${d.name_file}</strong>
                                                                                        </div>
                                                                                        <small class="text-muted">
                                                                                            Jenis: <span class="badge bg-light text-dark border">${jenisDokumenNama}</span>
                                                                                        </small>
                                                                                    </div>
                                                                                    <a href="/dokumen-usulan/${btoa(d.id)}" target="_blank" class="btn btn-sm btn-outline-primary flex-shrink-0">
                                                                                        <i class="bi bi-download"></i> Unduh
                                                                                    </a>
                                                                                </div>
                                                                            `;
                        });
                        html += `
                                                                                </div>
                                                                            </div>
                                                                        `;
                    }

                    const detailModal = `
                                                                                        <div class="modal fade" id="modalDetailUsulan" tabindex="-1" aria-labelledby="modalDetailUsulanLabel" aria-hidden="true">
                                                                                            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                                                                                                <div class="modal-content">
                                                                                                    <div class="modal-header bg-primary text-white">
                                                                                                        <h5 class="modal-title text-white" id="modalDetailUsulanLabel">
                                                                                                            <i class="bi bi-info-circle-fill me-2"></i>Detail Usulan
                                                                                                        </h5>
                                                                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                                                                    </div>
                                                                                                    <div class="modal-body">
                                                                                                        ${html}
                                                                                                    </div>
                                                                                                    <div class="modal-footer">
                                                                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>
                                                                                        </div>
                                                                                    `;

                    $('body').append(detailModal);
                    const modalDetail = new bootstrap.Modal('#modalDetailUsulan');

                    // Event ketika modal ditampilkan
                    $('#modalDetailUsulan').on('shown.bs.modal', function () {
                        const lat = parseFloat(data.latitude);
                        const lon = parseFloat(data.longitude);

                        const mapDetail = L.map('mapDetail').setView([lat, lon], 15);

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a>'
                        }).addTo(mapDetail);

                        const marker = L.marker([lat, lon]).addTo(mapDetail);

                        $.getJSON(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&zoom=18&addressdetails=1`, function (response) {
                            let alamatLengkap = '';

                            if (response && response.address) {
                                const addr = response.address;
                                const parts = [];

                                if (addr.road) parts.push(addr.road);
                                if (addr.suburb) parts.push(addr.suburb);
                                if (addr.village) parts.push(addr.village);
                                if (addr.city) parts.push(addr.city);
                                if (addr.state) parts.push(addr.state);
                                if (addr.postcode) parts.push(addr.postcode);

                                alamatLengkap = parts.join(', ');

                                $('#alamatInfo').removeClass('alert-info').addClass('alert-success').html(`
                                                                                                <div>
                                                                                                    <i class="bi bi-pin-map-fill me-2"></i>
                                                                                                    <strong>Alamat:</strong><br>
                                                                                                    <small>${alamatLengkap || response.display_name}</small>
                                                                                                </div>
                                                                                            `);

                                marker.bindPopup(`
                                                                                                <strong>${data.judul}</strong><br>
                                                                                                <small>${alamatLengkap || response.display_name}</small>
                                                                                            `).openPopup();
                            } else {
                                $('#alamatInfo').removeClass('alert-info').addClass('alert-warning').html(`
                                                                                                <small><i class="bi bi-exclamation-triangle me-2"></i>Alamat tidak dapat ditemukan</small>
                                                                                            `);

                                marker.bindPopup(`
                                                                                                <strong>${data.judul}</strong><br>
                                                                                                <small>${data.deskripsi || 'Lokasi usulan'}</small>
                                                                                            `).openPopup();
                            }
                        }).fail(function () {
                            $('#alamatInfo').removeClass('alert-info').addClass('alert-warning').html(`
                                                                                            <small><i class="bi bi-exclamation-triangle me-2"></i>Gagal memuat informasi alamat</small>
                                                                                        `);

                            marker.bindPopup(`
                                                                                            <strong>${data.judul}</strong><br>
                                                                                            <small>${data.deskripsi || 'Lokasi usulan'}</small>
                                                                                        `).openPopup();
                        });

                        setTimeout(function () {
                            mapDetail.invalidateSize();
                        }, 100);
                    });

                    modalDetail.show();

                    $('#modalDetailUsulan').on('hidden.bs.modal', function () {
                        $(this).remove();
                    });
                });
            });
        });

        function renderDokumenForm() {
            let html = '<label class="form-label">Unggah Dokumen Pendukung</label>';
            @foreach($dokumenJenis as $jenis)
                html += `
                                                                                                                        <div class="mb-3">
                                                                                                                            <label class="form-label">{{ ucfirst($jenis->nama) }}</label>
                                                                                                                            <input type="file" name="dokumen[{{ $jenis->id }}]" class="form-control" accept=".pdf,.doc,.docx,.jpg,.png">
                                                                                                                        </div>
                                                                                                                    `;
            @endforeach
            $('#dokumenContainer').html(html);
        }
    </script>
@endpush