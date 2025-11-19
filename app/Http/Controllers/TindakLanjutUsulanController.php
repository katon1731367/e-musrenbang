<?php

namespace App\Http\Controllers;

use App\Models\DokumenTindakLanjutUsulan;
use App\Models\Usulan;
use App\Models\TindakLanjutUsulan;
use App\Models\StatusTindakLanjut;
use App\Models\HistoryUsulan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TindakLanjutUsulanController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'id_usulan' => 'required|exists:usulan,id',
            'id_status_tindak_lanjut' => 'required|exists:status_tindak_lanjut,id',
            'keterangan' => 'nullable|string',
            'dokumen_tindak_lanjut' => 'nullable|array',
            'dokumen_tindak_lanjut.*' => 'file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240',
        ]);

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $roleNames = $user->getRoleNames();
            if ($roleNames->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'User tidak memiliki role.'], 403);
            }
            $role = $roleNames->first();

            $usulan = Usulan::findOrFail($request->id_usulan);

            $allowed = false;
            $newStatusUsulan = null;

            if ($role === 'ADMIN – DESA') {
                $userDesaIds = $user->locations()
                    ->where('locations.type', 'desa')
                    ->pluck('locations.id')
                    ->toArray();

                if (
                    $usulan->id_status_usulan == Usulan::STATUS_DIAJUKAN_RT &&
                    in_array($usulan->id_desa, $userDesaIds)
                ) {
                    $allowed = true;
                    switch ($request->id_status_tindak_lanjut) {
                        case StatusTindakLanjut::DISETUJUI:
                            $pembuatRole = optional($usulan->createdBy->getRoleNames())->first();
                            if ($pembuatRole === 'USER – RT/RW') {
                                $newStatusUsulan = Usulan::STATUS_DIAJUKAN_DESA;
                            } else {
                                $newStatusUsulan = Usulan::STATUS_APPROVE_DESA;
                            }
                            break;
                        case StatusTindakLanjut::DITOLAK:
                            $newStatusUsulan = Usulan::STATUS_REJECT_DESA;
                            break;
                        case StatusTindakLanjut::PROSES:
                            $newStatusUsulan = Usulan::STATUS_GAGAL_REVIEW_DESA;
                            break;
                    }
                }
            } elseif ($role === 'SUPER ADMIN – KECAMATAN') {
                $userDesaIds = $user->locations()
                    ->where('locations.type', 'desa')
                    ->pluck('locations.id')
                    ->toArray();

                if (
                    $usulan->id_status_usulan == Usulan::STATUS_DIAJUKAN_DESA &&
                    in_array($usulan->id_desa, $userDesaIds)
                ) {
                    $allowed = true;
                    switch ($request->id_status_tindak_lanjut) {
                        case StatusTindakLanjut::DISETUJUI:
                            $newStatusUsulan = Usulan::STATUS_APPROVE_KECAMATAN;
                            break;
                        case StatusTindakLanjut::DITOLAK:
                            $newStatusUsulan = Usulan::STATUS_REJECT_KECAMATAN;
                            break;
                        case StatusTindakLanjut::PROSES:
                            $newStatusUsulan = Usulan::STATUS_GAGAL_REVIEW_KECAMATAN;
                            break;
                    }
                }
            }

            if (!$allowed || !$newStatusUsulan) {
                throw new \Exception('Anda tidak berhak memberikan tindak lanjut pada usulan ini.');
            }

            $tindakLanjut = TindakLanjutUsulan::updateOrCreate(
                ['id_usulan' => $usulan->id],
                [
                    'id_status_tindak_lanjut' => $request->id_status_tindak_lanjut,
                    'keterangan' => $request->keterangan,
                    'created_by' => $user->id,
                ]
            );

            if ($request->hasFile('dokumen_tindak_lanjut')) {
                DokumenTindakLanjutUsulan::where('id_tindak_lanjut_usulan', $tindakLanjut->id)->delete();
                foreach ($request->file('dokumen_tindak_lanjut') as $file) {
                    if ($file && $file->isValid()) {
                        $path = $file->store('tindak-lanjut', 'private');
                        DokumenTindakLanjutUsulan::create([
                            'id_tindak_lanjut_usulan' => $tindakLanjut->id,
                            'name_file' => $file->getClientOriginalName(),
                            'path_file' => $path,
                            'created_at' => now(),
                        ]);
                    }
                }
            }

            // === SIMPAN HISTORY ===
            HistoryUsulan::create([
                'id_usulan' => $usulan->id,
                'id_status_usulan_lama' => $usulan->getOriginal('id_status_usulan'),
                'id_status_usulan_baru' => $newStatusUsulan,
                'created_by' => $user->id,
                'keterangan' => 'Tindak lanjut: ' . ($request->keterangan ?? 'Tanpa keterangan'),
            ]);

            $usulan->id_status_usulan = $newStatusUsulan;
            $usulan->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tindak lanjut berhasil disimpan.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Tindak Lanjut Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
        }
    }
}