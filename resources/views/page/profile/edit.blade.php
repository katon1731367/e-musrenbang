@extends('layout') {{-- Sesuaikan dengan layout utama Anda --}}

@section('title', 'Edit Profil')

@section('content')
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center mb-5">
                        <h5 class="card-title mb-0">Edit Profil</h5>
                        <a href="{{ route('profile.show') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row mb-3">
                                <div class="col-md-4 text-center">
                                    <div class="avatar avatar-xl mb-2">
                                        <img src="{{ $biodata?->foto_url }}" alt="Foto Profil"
                                            class="avatar-img rounded-circle">
                                    </div>
                                    <label for="foto" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-upload"></i> Ganti Foto
                                    </label>
                                    <input type="file" id="foto" name="foto"
                                        class="form-control @error('foto') is-invalid @enderror" accept="image/*"
                                        style="display: none;">
                                    @error('foto')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Nama Lengkap</label>
                                        <input type="text" class="form-control @error('name') is-invalid @enderror"
                                            id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                            id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password Baru (Kosongkan jika tidak ingin
                                            diubah)</label>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                                            id="password" name="password">
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="password_confirmation" class="form-label">Konfirmasi Password
                                            Baru</label>
                                        <input type="password" class="form-control" id="password_confirmation"
                                            name="password_confirmation">
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="mb-3">
                                <label for="nik" class="form-label">NIK</label>
                                <input type="text" class="form-control @error('nik') is-invalid @enderror" id="nik"
                                    name="nik" value="{{ old('nik', $biodata?->nik) }}">
                                @error('nik')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="jabatan" class="form-label">Jabatan</label>
                                <input type="text" class="form-control @error('jabatan') is-invalid @enderror" id="jabatan"
                                    name="jabatan" value="{{ old('jabatan', $biodata?->jabatan) }}">
                                @error('jabatan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat</label>
                                <textarea class="form-control @error('alamat') is-invalid @enderror" id="alamat"
                                    name="alamat" rows="3">{{ old('alamat', $biodata?->alamat) }}</textarea>
                                @error('alamat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="no_hp" class="form-label">No. HP</label>
                                <input type="text" class="form-control @error('no_hp') is-invalid @enderror" id="no_hp"
                                    name="no_hp" value="{{ old('no_hp', $biodata?->no_hp) }}">
                                @error('no_hp')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Dropdown Lokasi berdasarkan Role -->
                            @if($userRole === 'SUPER ADMIN – KECAMATAN')
                                {{-- Hanya Pilih Kecamatan --}}
                                <div class="mb-3">
                                    <label for="kecamatan" class="form-label">Kecamatan</label>
                                    <select class="form-select" name="kecamatan" id="kecamatan" required>
                                        <option value="">-- Pilih Kecamatan --</option>
                                        @foreach($kecamatans as $kec)
                                            <option value="{{ $kec->id }}" {{ $userKecamatanId == $kec->id ? 'selected' : '' }}>
                                                {{ $kec->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @else
                                {{-- Role selain Admin Kecamatan HARUS pilih Kecamatan terlebih dahulu --}}
                                <div class="mb-3">
                                    <label for="kecamatan" class="form-label">Kecamatan</label>
                                    <select class="form-select" name="kecamatan" id="kecamatan" required>
                                        <option value="">-- Pilih Kecamatan --</option>
                                        @foreach($kecamatans as $kec)
                                            <option value="{{ $kec->id }}" {{ $userKecamatanId == $kec->id ? 'selected' : '' }}>
                                                {{ $kec->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="locations" class="form-label">Desa</label>
                                    <select multiple class="form-select" name="locations[]" id="desa-select">
                                        @foreach($desas as $desa)
                                            <option value="{{ $desa->id }}" {{ in_array($desa->id, $userDesaIds) ? 'selected' : '' }}>
                                                {{ $desa->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted">Pilih desa berdasarkan kecamatan.</small>
                                </div>
                            @endif


                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="{{ route('profile.show') }}" class="btn btn-secondary me-md-2">Batal</a>
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Script untuk preview gambar (jika diperlukan)
        document.getElementById('foto').addEventListener('change', function (event) {
            const [file] = event.target.files;
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    document.querySelector('.avatar-img').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        document.getElementById('kecamatan').addEventListener('change', function() {
            let kecamatanId = this.value;
            let desaSelect = document.getElementById('desa-select');

            if (!desaSelect) return;

            desaSelect.innerHTML = '<option>Loading...</option>';

            fetch(`/ajax/desa-by-kecamatan/${kecamatanId}`)
                .then(res => res.json())
                .then(data => {
                    desaSelect.innerHTML = '';
                    data.forEach(desa => {
                        desaSelect.innerHTML += `
                            <option value="${desa.id}">${desa.name}</option>
                        `;
                    });
                });
        });

        document.addEventListener('DOMContentLoaded', function() {

            const kecSelect = document.getElementById('kecamatan');
            const desaSelect = document.getElementById('desa-select');

            // Jika tidak ada desa (role kecamatan), tidak perlu load desa
            if (!kecSelect || !desaSelect) return;

            // Preselected desa dari backend
            let userDesaIds = @json($userDesaIds);

            function loadDesa(kecamatanId, preselected = []) {
                if (!kecamatanId) return;

                desaSelect.innerHTML = '<option>Memuat...</option>';

                fetch(`/ajax/desa-by-kecamatan/${kecamatanId}`)
                    .then(res => res.json())
                    .then(data => {
                        desaSelect.innerHTML = '';

                        data.forEach(desa => {
                            let selected = preselected.includes(String(desa.id)) ? 'selected' : '';
                            desaSelect.innerHTML += `
                                <option value="${desa.id}" ${selected}>
                                    ${desa.name}
                                </option>
                            `;
                        });
                    });
            }

            // 🔥 1. AUTO LOAD SAAT HALAMAN PERTAMA KALI DIBUKA
            @if($userKecamatanId)
                loadDesa("{{ $userKecamatanId }}", userDesaIds);
            @endif

            // 🔥 2. AUTO CHANGE SAAT PILIH KECAMATAN DI UBAH
            kecSelect.addEventListener('change', function() {
                loadDesa(this.value);
            });

        });
    </script>
@endpush