@extends('layout')

@section('title', 'Daftar Usulan')

@section('content')

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <div class="container mt-4">

        <!-- Di dalam container Anda, mungkin setelah judul atau sebelum tabel -->
        <div class="d-flex justify-content-between mb-3">
            <h4 class="fw-bold">Daftar Usulan</h4>
            <div>
                <a href="{{ route('usulan.export.excel') }}" class="btn btn-success">
                    <i class="bi bi-file-earmark-excel"></i> Export ke Excel
                </a>
                <!-- Tombol Tambah Usulan Anda -->
                @php
                    $userRole = Auth::user()->getRoleNames()->first() ?? '';
                @endphp
                <?php if (in_array($userRole, ['USER – RT/RW', 'ADMIN – DESA'])) { ?>
                <button class="btn btn-primary" id="btnTambah">
                    <i class="bi bi-plus-lg"></i> Tambah Usulan
                </button>
                <?php } ?>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tabelUsulan" class="table table-bordered table-striped w-100">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Judul</th>
                                <th>Deskripsi</th>
                                <th>Jenis Usulan</th>
                                <th>Status Usulan</th>
                                <th>Alamat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
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

    <!-- Modal Tindak Lanjut -->
    <div class="modal fade" id="modalTindakLanjut" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tindak Lanjut Usulan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="formTindakLanjut" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id_usulan" id="tl_id_usulan">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Tindakan</label>
                            <select class="form-select" name="id_status_tindak_lanjut" id="tl_status" required>
                                <option value="">-- Pilih --</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea class="form-control" name="keterangan" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dokumen Pendukung (opsional)</label>
                            <input type="file" class="form-control" name="dokumen_tindak_lanjut[]" multiple
                                accept=".pdf,.doc,.docx,.jpg,.png">
                            <small class="text-muted">Maksimal 10 MB per file</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Tindak Lanjut</button>
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
                                const item = $(`<button type="button" class="list-group-item list-group-item-action">
                                                                                                                ${place.display_name}
                                                                                                            </button>`);
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
                    {
                        data: 'judul',
                        width: '15%'
                    },
                    {
                        data: 'deskripsi',
                        width: '25%',
                        render: function (data, type, row) {
                            if (type === 'display' && data.length > 50) {
                                return data.substr(0, 50) + '...';
                            }
                            return data;
                        }
                    },
                    {
                        data: 'jenis_usulan.nama',
                        defaultContent: '-',
                        width: '10%'
                    },
                    {
                        data: 'html_status',
                        width: '10%'
                    },
                    {
                        data: 'alamat_tampil',
                        width: '20%'
                    },
                    {
                        data: null,
                        width: '15%',
                        render: function (data) {
                            let btns = '';
                            if (data.bisa_edit) {
                                btns += `<button class="btn btn-sm btn-warning btnEdit" data-id="${data.id}"><i class="bi bi-pencil"></i> Ubah</button> `;
                            }
                            if (data.bisa_ajukan) {
                                btns += `<button class="btn btn-sm btn-success btnAjukan" data-id="${data.id}"><i class="bi bi-send"></i> Ajukan</button> `;
                            }
                            if (data.bisa_hapus) {
                                btns += `<button class="btn btn-sm btn-danger btnDelete" data-id="${data.id}"><i class="bi bi-trash"></i> Hapus</button> `;
                            }
                            if (data.bisa_tindak_lanjut) {
                                btns += `<button class="btn btn-sm btn-secondary btnTindakLanjut" data-id="${data.id}"><i class="bi bi-journal-check"></i> Tindak Lanjut</button> `;
                            }
                            btns += `<button class="btn btn-sm btn-info btnDetail" data-id="${data.id}"><i class="bi bi-eye"></i> Detail</button>`;
                            return btns;
                        }
                    }
                ],
                responsive: true,
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

                let url;
                let method = 'POST';

                if (idUsulan) {
                    // Edit: gunakan PUT ke /usulan/{id}
                    url = `/usulan/${idUsulan}`;
                    formData.append('_method', 'PUT');
                } else {
                    // Tambah: gunakan POST ke route 'usulan.store'
                    url = '{{ route("usulan.store") }}';
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
                        $('#btn-submit').prop('disabled', false).html('<i class="bi bi-save"></i> Simpan Usulan');
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
                    $('#alamat').val(data.alamat);

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
                            dokumenHtml += `<div class="alert alert-info p-2 small">
                                                                                                                                                                        <i class="bi bi-file-earmark-check me-1"></i>
                                                                                                                                                                        <a href="/dokumen-usulan/${btoa(d.id)}" target="_blank" class="text-dark">
                                                                                                                                                                            ${d.name_file}
                                                                                                                                                                        </a>
                                                                                                                                                                        <br>
                                                                                                                                                                        <small class="text-muted">Diunggah: ${new Date(d.created_at).toLocaleDateString()}</small>
                                                                                                                                                                    </div>`;
                        }

                        dokumenHtml += `<input type="file" name="dokumen[{{ $jenis->id }}]" class="form-control" accept=".pdf,.doc,.docx,.jpg,.png">
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

                    let html = `<div class="mb-3">
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
                                                                                    <span class="badge ${getBadgeClass(data)} text-wrap">${data.status_label_tampil}</span>
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
                                                                    </div>`;

                    if (data.dokumen && data.dokumen.length > 0) {
                        html += ` <div class="mb-3">
                                                                                        <h6 class="text-primary mb-2">Dokumen Lampiran</h6>
                                                                                        <div class="list-group ms-3">`;

                        data.dokumen.forEach(d => {
                            // Ambil nama jenis dokumen, fallback ke '-' jika tidak ada
                            const jenisDokumenNama = d.jenis_dokumen ? d.jenis_dokumen.nama : '-';

                            html += `<div class="list-group-item d-flex justify-content-between align-items-start">
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
                                                                        </div>`;
                        });

                        html += `</div>
                                                                </div>`;
                    }

                    const detailModal = `<div class="modal fade" id="modalDetailUsulan" tabindex="-1" aria-labelledby="modalDetailUsulanLabel" aria-hidden="true">
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
                                                                            </div>`;

                    $('body').append(detailModal);

                    // Ambil history
                    $.get(`/usulan/${id}/history`, function (historyRes) {
                        let historyHtml = '<div class="mb-3"><h6 class="text-primary">Riwayat Status</h6><ul class="list-group list-group-flush">';

                        if (historyRes.data.length > 0) {
                            historyRes.data.forEach(h => {
                                const waktu = new Date(h.created_at).toLocaleString('id-ID');
                                const dari = h.status_lama ? h.status_lama.nama : 'Awal';
                                const ke = h.status_baru.nama;
                                const user = h.created_by.name;

                                historyHtml += `
                                                                                    <li class="list-group-item">
                                                                                        <small>
                                                                                            <i class="bi bi-clock"></i> ${waktu}<br>
                                                                                            <strong>${dari}</strong> → <strong>${ke}</strong><br>
                                                                                            Oleh: ${user}
                                                                                        </small>
                                                                                    </li>
                                                                                `;
                            });
                        } else {
                            historyHtml += '<li class="list-group-item text-muted">Belum ada riwayat</li>';
                        }

                        historyHtml += '</ul></div>';
                        $('#modalDetailUsulan .modal-body').append(historyHtml);
                    });

                    $.get(`/usulan/${id}/tindak-lanjut`, function (tlRes) {
                        if (tlRes.data) {
                            let tlHtml = `<div class="mb-3"><h6 class="text-primary">Tindak Lanjut</h6>`;
                            tlHtml += `<p><strong>Status:</strong> ${tlRes.data.status_tindak_lanjut.nama}</p>`;
                            tlHtml += `<p><strong>Oleh:</strong> ${tlRes.data.created_by.name}</p>`;
                            tlHtml += `<p><strong>Keterangan:</strong> ${tlRes.data.keterangan || '-'}</p>`;

                            if (tlRes.data.dokumen && tlRes.data.dokumen.length > 0) {
                                tlHtml += `<h6 class="mt-3">Dokumen Tindak Lanjut</h6><ul class="list-group">`;
                                tlRes.data.dokumen.forEach(d => {
                                    tlHtml += `
                                                                        <li class="list-group-item">
                                                                            <a href="/dokumen-tindak-lanjut/${btoa(d.id)}" target="_blank">
                                                                                ${d.name_file}
                                                                            </a>
                                                                        </li>
                                                                    `;
                                });
                                tlHtml += `</ul>`;
                            }
                            tlHtml += `</div>`;
                            $('#modalDetailUsulan .modal-body').append(tlHtml);
                        }
                    });

                    const modalDetail = new bootstrap.Modal('#modalDetailUsulan');

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

            $('#tabelUsulan').on('click', '.btnAjukan', function () {
                const id = $(this).data('id');
                const usulan = table.row($(this).closest('tr')).data();

                let newStatus;
                if (usulan.id_status_usulan == {{ \App\Models\Usulan::STATUS_DRAFT_RT }}) {
                    newStatus = {{ \App\Models\Usulan::STATUS_DIAJUKAN_RT }};
                } else if (usulan.id_status_usulan == {{ \App\Models\Usulan::STATUS_DRAFT_DESA }}) {
                    newStatus = {{ \App\Models\Usulan::STATUS_DIAJUKAN_DESA }};
                } else {
                    Swal.fire('Error', 'Tidak bisa mengajukan usulan ini.', 'error');
                    return;
                }

                $.ajax({
                    url: `usulan/${id}/status`,
                    method: 'PUT',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id_status_usulan: newStatus
                    },
                    beforeSend: () => {
                        $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Mengajukan...');
                    },
                    success: () => {
                        table.ajax.reload();
                        Swal.fire('Berhasil!', 'Usulan telah diajukan.', 'success');
                    },
                    error: (err) => {
                        const msg = err.responseJSON?.message || 'Gagal mengajukan usulan.';
                        Swal.fire('Gagal!', msg, 'error');
                    },
                    complete: () => {
                        $(this).prop('disabled', false).html('<i class="bi bi-send"></i> Ajukan');
                    }
                });
            });

            $('#tabelUsulan').on('click', '.btnTindakLanjut', function () {
                const id = $(this).data('id');
                const usulan = table.row($(this).closest('tr')).data();

                $('#formTindakLanjut')[0].reset();
                $('#tl_id_usulan').val(id);

                const select = $('#tl_status');
                select.empty().append('<option value="">-- Pilih --</option>');

                if (usulan.id_status_usulan == {{ \App\Models\Usulan::STATUS_DIAJUKAN_RT }}) {
                    select.append(`<option value="2">✅ Setujui</option>`);
                    select.append(`<option value="1">❌ Tolak</option>`);
                    select.append(`<option value="3">🔄 Minta Revisi</option>`);
                } else if (usulan.id_status_usulan == {{ \App\Models\Usulan::STATUS_DIAJUKAN_DESA }}) {
                    select.append(`<option value="2">✅ Setujui</option>`);
                    select.append(`<option value="1">❌ Tolak</option>`);
                    select.append(`<option value="3">🔄 Minta Revisi</option>`);
                }

                const modal = new bootstrap.Modal('#modalTindakLanjut');
                modal.show();
            });

            $('#formTindakLanjut').on('submit', function (e) {
                e.preventDefault();
                const formData = new FormData(this);

                $.ajax({
                    url: '{{ route("tindak-lanjut.store") }}',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: () => {
                        $('#formTindakLanjut button[type="submit"]').prop('disabled', true).text('Menyimpan...');
                    },
                    success: (res) => {
                        $('#modalTindakLanjut').modal('hide');
                        table.ajax.reload();
                        Swal.fire('Berhasil!', res.message, 'success');
                    },
                    error: (err) => {
                        Swal.fire('Gagal!', err.responseJSON?.message || 'Gagal menyimpan tindak lanjut.', 'error');
                    },
                    complete: () => {
                        $('#formTindakLanjut button[type="submit"]').prop('disabled', false).text('Simpan Tindak Lanjut');
                    }
                });
            });

        });

        function renderDokumenForm() {
            let html = '<label class="form-label">Unggah Dokumen Pendukung</label>';
            @foreach($dokumenJenis as $jenis)
                html += `<div class="mb-3">
                                                                                                    <label class="form-label">{{ ucfirst($jenis->nama) }}</label>
                                                                                                    <input type="file" name="dokumen[{{ $jenis->id }}]" class="form-control" accept=".pdf,.doc,.docx,.jpg,.png">
                                                                                                </div>`;
            @endforeach
            $('#dokumenContainer').html(html);
        }

        function getBadgeClass(data) {
            let color = 'secondary';
            if (data.id_status_usulan == {{ \App\Models\Usulan::STATUS_DRAFT_RT }} ||
                data.id_status_usulan == {{ \App\Models\Usulan::STATUS_DRAFT_DESA }}) {
                color = data.is_revisi ? 'warning' : 'primary';
            } else if ([{{ \App\Models\Usulan::STATUS_DIAJUKAN_RT }}, {{ \App\Models\Usulan::STATUS_DIAJUKAN_DESA }}].includes(data.id_status_usulan)) {
                color = 'info';
            } else if ([{{ \App\Models\Usulan::STATUS_APPROVE_DESA }}, {{ \App\Models\Usulan::STATUS_APPROVE_KECAMATAN }}].includes(data.id_status_usulan)) {
                color = 'success';
            } else if ([{{ \App\Models\Usulan::STATUS_REJECT_DESA }}, {{ \App\Models\Usulan::STATUS_REJECT_KECAMATAN }}].includes(data.id_status_usulan)) {
                color = 'danger';
            }
            return `bg-${color}`;
        }
    </script>
@endpush