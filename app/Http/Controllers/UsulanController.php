<?php

namespace App\Http\Controllers;

use App\Models\DokumenUsulan;
use App\Models\Pengusul;
use App\Models\Usulan;
use App\Models\JenisUsulan;
use App\Models\JenisDokumenUsulan;

use Auth;
use DB;
use Illuminate\Http\Request;

class UsulanController extends Controller
{
    public function index()
    {
        $jenisUsulan = JenisUsulan::all();
        $dokumenJenis = JenisDokumenUsulan::all();

        return view('page.usulan.index', compact('jenisUsulan', 'dokumenJenis'));
    }

    public function getData()
    {
        $usulan = Usulan::with('dokumen', 'jenisUsulan')->get();

        return response()->json(['data' => $usulan]);
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $idPengusul = null;
            if ($request->pengusul_sama != 1) {
                $pengusul = Pengusul::create([
                    'nama_pengusul' => $request->nama_pengusul,
                    'alamat_pengusul' => $request->alamat_pengusul,
                    'no_telp_pengusul' => $request->no_telp_pengusul,
                ]);
                $idPengusul = $pengusul->id;
            }

            $usulan = Usulan::create([
                'id_jenis_usulan' => $request->id_jenis_usulan,
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'id_pengusul' => $idPengusul,
                'created_by' => Auth::id(),
            ]);

            if ($request->hasFile('dokumen')) {
                foreach ($request->file('dokumen') as $idJenisDokumen => $file) {
                    if ($file) {
                        $path = $file->store('usulan', 'private');

                        DokumenUsulan::create([
                            'id_usulan' => $usulan->id,
                            'id_jenis_dokumen' => $idJenisDokumen,
                            'name_file' => $file->getClientOriginalName(),
                            'path_file' => $path,
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->back()->with('success', 'Usulan berhasil dibuat.');

        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage()); // debug
        }
    }

    public function show($id)
    {
        $usulan = Usulan::with(['jenisUsulan', 'pengusul', 'dokumen.jenisDokumen', 'status', 'pengusul', 'createdBy'])->findOrFail($id);
        return response()->json($usulan);
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $usulan = Usulan::with('pengusul')->findOrFail($id);

            // Update data usulan dasar
            $usulan->update([
                'id_jenis_usulan' => $request->id_jenis_usulan,
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ]);

            // === PENANGANAN PENGAJU (PENGUSUL) ===
            if ($request->pengusul_sama == 1) {
                if ($usulan->id_pengusul) {
                    $usulan->id_pengusul = null;
                    $usulan->save();
                }
            } else {
                // Data pengusul diisi manual
                if ($usulan->id_pengusul) {
                    // Update pengusul yang sudah ada
                    $usulan->pengusul->update([
                        'nama_pengusul' => $request->nama_pengusul,
                        'alamat_pengusul' => $request->alamat_pengusul,
                        'no_telp_pengusul' => $request->no_telp_pengusul,
                    ]);
                } else {
                    // Buat pengusul baru
                    $pengusul = Pengusul::create([
                        'nama_pengusul' => $request->nama_pengusul,
                        'alamat_pengusul' => $request->alamat_pengusul,
                        'no_telp_pengusul' => $request->no_telp_pengusul,
                    ]);
                    $usulan->id_pengusul = $pengusul->id;
                    $usulan->save();
                }
            }

            // === PENANGANAN DOKUMEN ===
            if ($request->hasFile('dokumen')) {
                foreach ($request->file('dokumen') as $idJenisDokumen => $file) {
                    // 🔥 Konversi ke integer! 🔥
                    $idJenisDokumen = (int) $idJenisDokumen;

                    if ($file && $file->isValid() && $file->getSize() > 0) {
                        // Cari dokumen lama dengan jenis yang sama
                        $dokumenLama = DokumenUsulan::where('id_usulan', $usulan->id)
                            ->where('id_jenis_dokumen', $idJenisDokumen)
                            ->first();

                        if ($dokumenLama) {
                            \Storage::disk('private')->delete($dokumenLama->path_file);
                            $dokumenLama->delete();
                        }

                        $path = $file->store('usulan', 'private');
                        DokumenUsulan::create([
                            'id_usulan' => $usulan->id,
                            'id_jenis_dokumen' => $idJenisDokumen, // pastikan integer
                            'name_file' => $file->getClientOriginalName(),
                            'path_file' => $path,
                        ]);
                    }
                }
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Usulan berhasil diperbarui.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Update Usulan Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui usulan.'
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $usulan = Usulan::with('dokumen')->findOrFail($id);

            foreach ($usulan->dokumen as $dok) {
                \Storage::disk('private')->delete($dok->path_file);
                $dok->delete();
            }

            $usulan->delete();
            return response()->json(['success' => true, 'message' => 'Usulan berhasil dihapus.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
