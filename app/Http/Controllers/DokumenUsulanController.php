<?php

namespace App\Http\Controllers;

use App\Models\DokumenUsulan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DokumenUsulanController extends Controller
{
    public function download($encodedId)
    {
        $id = base64_decode($encodedId);

        $dokumen = DokumenUsulan::with('usulan')->findOrFail($id);

        if (Auth::id() !== $dokumen->usulan->created_by && !Auth::user()->hasRole('admin')) {
            abort(403, 'Anda tidak memiliki izin untuk mengunduh dokumen ini.');
        }

        if (!Storage::disk('private')->exists($dokumen->path_file)) {
            abort(404, 'File tidak ditemukan.');
        }

        // Ganti nama file sesuai keinginan saat diunduh
        $namaDownload = $dokumen->name_file ?? 'dokumen_usulan.pdf';

        return Storage::disk('private')->download($dokumen->path_file, $namaDownload);
    }
}
