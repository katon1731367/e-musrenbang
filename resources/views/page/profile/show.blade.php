@extends('layout') {{-- Sesuaikan dengan layout utama Anda --}}

@section('title', 'Profil Saya')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center mb-5">
                    <h5 class="card-title mb-0">Profil Saya</h5>
                    <a href="{{ route('profile.edit') }}" class="btn btn-light btn-sm">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-4 text-center mb-3">
                            <div class="avatar avatar-xl">
                                <img src="{{ $biodata?->foto_url }}" alt="Avatar" class="avatar-img rounded-circle">
                            </div>
                            <h5 class="mt-2">{{ $user->name }}</h5>
                            <p class="text-muted">{{ $user->getRoleNames()->first() }}</p>
                        </div>
                        <div class="col-md-8">
                            <h6 class="text-muted">Informasi Akun</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <th>Nama Lengkap:</th>
                                    <td>{{ $user->name }}</td>
                                </tr>
                                <tr>
                                    <th>Email:</th>
                                    <td>{{ $user->email }}</td>
                                </tr>
                                <!-- Tambahkan baris lain jika perlu -->
                            </table>

                            <h6 class="text-muted mt-3">Biodata</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <th>NIK:</th>
                                    <td>{{ $biodata?->nik ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Jabatan:</th>
                                    <td>{{ $biodata?->jabatan ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Alamat:</th>
                                    <td>{{ $biodata?->alamat ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>No. HP:</th>
                                    <td>{{ $biodata?->no_hp ?? '-' }}</td>
                                </tr>
                                <!-- Tambahkan baris lain jika perlu -->
                            </table>

                            <h6 class="text-muted mt-3">Lokasi Terkait</h6>
                            <ul class="list-group list-group-flush">
                                @if($userRole === 'SUPER ADMIN – KECAMATAN')
                                    @if($location)
                                        <li class="list-group-item">
                                            <strong>Kecamatan:</strong> {{ $location->name }} ({{ $location->type }})
                                        </li>
                                        @if($desaTerkait)
                                            <li class="list-group-item">
                                                <strong>Desa Terkait (Otomatis):</strong> {{ $desaTerkait }}
                                            </li>
                                        @else
                                            <li class="list-group-item text-muted">Belum ada desa terkait (silakan edit profil)</li>
                                        @endif
                                    @else
                                        <li class="list-group-item text-muted">Kecamatan belum dipilih</li>
                                    @endif
                                @else
                                    @if($location)
                                        <li class="list-group-item">{{ $location->name }} ({{ $location->type }})</li>
                                    @else
                                        <li class="list-group-item text-muted">Tidak ada lokasi terkait</li>
                                    @endif
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection