<?php

namespace App\Http\Controllers;

use App\Models\DokumenUsulan;
use App\Models\HistoryUsulan;
use App\Models\Pengusul;
use App\Models\Usulan;
use App\Models\JenisUsulan;
use App\Models\JenisDokumenUsulan;
use App\Models\TindakLanjutUsulan;

use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use Session;

class UsulanController extends Controller
{
    public function index()
    {
        Session::put('page_title', 'Usulan');
        Session::put('menu', 'Usulan');

        $jenisUsulan = JenisUsulan::all();
        $dokumenJenis = JenisDokumenUsulan::all();

        return view('page.usulan.index', compact('jenisUsulan', 'dokumenJenis'));
    }

    public function show($id)
    {
        $usulan = Usulan::with([
            'jenisUsulan',
            'pengusul',
            'dokumen.jenisDokumen',
            'status',
            'createdBy'
        ])->findOrFail($id);

        $isRevisi = false;
        if (in_array($usulan->id_status_usulan, [Usulan::STATUS_DRAFT_RT, Usulan::STATUS_DRAFT_DESA])) {
            $hasRevisi = HistoryUsulan::where('id_usulan', $usulan->id)
                ->whereIn('id_status_usulan_baru', [
                    Usulan::STATUS_REJECT_DESA,
                    Usulan::STATUS_GAGAL_REVIEW_DESA,
                    Usulan::STATUS_REJECT_KECAMATAN,
                    Usulan::STATUS_GAGAL_REVIEW_KECAMATAN,
                ])->exists();
            $isRevisi = $hasRevisi;
        }
        $usulan->is_revisi = $isRevisi;

        $statusLabel = $usulan->status->nama ?? 'Tidak ada status';
        if (in_array($usulan->id_status_usulan, [Usulan::STATUS_DRAFT_RT, Usulan::STATUS_DRAFT_DESA])) {
            if ($isRevisi) {
                $statusLabel = '(Revisi) - ' . $statusLabel;
            }
        }
        $usulan->status_label_tampil = $statusLabel;

        return response()->json($usulan);
    }

    public function getData()
    {
        $user = Auth::user();
        $roleNames = $user->getRoleNames();
        if ($roleNames->isEmpty()) {
            return response()->json(['error' => 'User tidak memiliki role.'], 403);
        }
        $role = $roleNames->first();

        // Cek apakah user adalah admin atau bappeda
        $isAdminOrBappeda = in_array($role, ['admin', 'MASTER – BAPPEDA']);

        // Ambil ID desa yang terkait dengan user (kecuali admin/bappeda)
        $userDesaIds = [];
        if (!$isAdminOrBappeda) {
            $userDesaIds = $user->locations()
                ->where('locations.type', 'desa')
                ->pluck('locations.id')
                ->toArray();
        }

        $query = Usulan::with('dokumen', 'jenisUsulan', 'status', 'createdBy');

        // Terapkan filter berdasarkan role
        if ($isAdminOrBappeda) {
            // Tidak ada filter, ambil semua
        } elseif ($role === 'USER – RT/RW') {
            $query->where('created_by', $user->id);
        } elseif ($role === 'ADMIN – DESA') {
            $query->where(function ($q) use ($user, $userDesaIds) {
                $q->where('created_by', $user->id)
                    ->whereIn('id_status_usulan', [
                        Usulan::STATUS_DRAFT_DESA,
                        Usulan::STATUS_DIAJUKAN_DESA,
                        Usulan::STATUS_APPROVE_DESA,
                        Usulan::STATUS_REJECT_DESA,
                        Usulan::STATUS_GAGAL_REVIEW_DESA,
                        Usulan::STATUS_APPROVE_KECAMATAN,
                        Usulan::STATUS_REJECT_KECAMATAN,
                        Usulan::STATUS_GAGAL_REVIEW_KECAMATAN,
                        Usulan::STATUS_DIAJUKAN_KECAMATAN,
                    ]);
                $q->orWhere(function ($q2) use ($userDesaIds) {
                    $q2->whereIn('id_desa', $userDesaIds)
                        ->whereIn('id_status_usulan', [
                            Usulan::STATUS_DIAJUKAN_RT,
                            Usulan::STATUS_DRAFT_DESA,
                            Usulan::STATUS_DIAJUKAN_DESA,
                            Usulan::STATUS_APPROVE_DESA,
                            Usulan::STATUS_REJECT_DESA,
                            Usulan::STATUS_GAGAL_REVIEW_DESA,
                            Usulan::STATUS_APPROVE_KECAMATAN,
                            Usulan::STATUS_REJECT_KECAMATAN,
                            Usulan::STATUS_GAGAL_REVIEW_KECAMATAN,
                            Usulan::STATUS_DIAJUKAN_KECAMATAN,
                        ]);
                });
            });
        } elseif ($role === 'SUPER ADMIN – KECAMATAN') {
            $query->whereIn('id_desa', $userDesaIds)
                ->whereIn('id_status_usulan', [
                    Usulan::STATUS_DIAJUKAN_DESA,
                    Usulan::STATUS_APPROVE_KECAMATAN,
                    Usulan::STATUS_REJECT_KECAMATAN,
                    Usulan::STATUS_GAGAL_REVIEW_KECAMATAN,
                    Usulan::STATUS_DIAJUKAN_KECAMATAN,
                ]);
        }

        $usulan = $query->get()->map(function ($item) use ($user, $role, $userDesaIds, $isAdminOrBappeda) {
            $item->bisa_edit = false;
            $item->bisa_ajukan = false;
            $item->bisa_hapus = false;
            $item->bisa_tindak_lanjut = false;

            $item->bisa_hapus = (
                in_array($item->id_status_usulan, [Usulan::STATUS_DRAFT_RT, Usulan::STATUS_DRAFT_DESA])
                && $item->created_by == $user->id
            );

            if ($item->created_by == $user->id) {
                if ($role === 'USER – RT/RW' && $item->id_status_usulan == Usulan::STATUS_DRAFT_RT) {
                    $item->bisa_edit = true;
                    $item->bisa_ajukan = true;
                } elseif (
                    $role === 'USER – RT/RW' && in_array($item->id_status_usulan, [
                        Usulan::STATUS_GAGAL_REVIEW_DESA,
                        Usulan::STATUS_GAGAL_REVIEW_KECAMATAN,
                    ])
                ) {
                    $item->bisa_edit = true;
                    $item->bisa_ajukan = false;
                } elseif (
                    $role === 'ADMIN – DESA' && in_array($item->id_status_usulan, [
                        Usulan::STATUS_DRAFT_DESA,
                        Usulan::STATUS_GAGAL_REVIEW_DESA,
                    ])
                ) {
                    $item->bisa_edit = true;
                    if ($item->id_status_usulan == Usulan::STATUS_DRAFT_DESA) {
                        $item->bisa_ajukan = true;
                    }
                }
            }

            if ($role === 'ADMIN – DESA' && $item->id_status_usulan == Usulan::STATUS_DIAJUKAN_RT) {
                $item->bisa_tindak_lanjut = true;
            } elseif ($role === 'SUPER ADMIN – KECAMATAN' && $item->id_status_usulan == Usulan::STATUS_DIAJUKAN_DESA) {
                $item->bisa_tindak_lanjut = true;
            }

            $item->alamat_tampil = $item->alamat ?? '-';

            $isRevisi = false;
            if (in_array($item->id_status_usulan, [Usulan::STATUS_DRAFT_RT, Usulan::STATUS_DRAFT_DESA])) {
                $hasRevisi = HistoryUsulan::where('id_usulan', $item->id)
                    ->whereIn('id_status_usulan_baru', [
                        Usulan::STATUS_REJECT_DESA,
                        Usulan::STATUS_GAGAL_REVIEW_DESA,
                        Usulan::STATUS_REJECT_KECAMATAN,
                        Usulan::STATUS_GAGAL_REVIEW_KECAMATAN,
                    ])->exists();
                $isRevisi = $hasRevisi;
            }

            $statusLabel = $item->status->nama ?? 'Tidak ada status';
            $color = 'secondary';

            if (in_array($item->id_status_usulan, [Usulan::STATUS_DRAFT_RT, Usulan::STATUS_DRAFT_DESA])) {
                if ($isRevisi) {
                    $statusLabel = '(Revisi) - ' . $statusLabel;
                    $color = 'warning';
                } else {
                    $color = 'primary';
                }
            } elseif (in_array($item->id_status_usulan, [Usulan::STATUS_DIAJUKAN_RT, Usulan::STATUS_DIAJUKAN_DESA])) {
                $color = 'info';
            } elseif (in_array($item->id_status_usulan, [Usulan::STATUS_APPROVE_DESA, Usulan::STATUS_APPROVE_KECAMATAN])) {
                $color = 'success';
            } elseif (in_array($item->id_status_usulan, [Usulan::STATUS_REJECT_DESA, Usulan::STATUS_REJECT_KECAMATAN])) {
                $color = 'danger';
            }

            $item->html_status = "<span class='badge bg-{$color}'>{$statusLabel}</span>";

            return $item;
        });

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

            $user = Auth::user();
            $role = $user->getRoleNames()->first();

            $initialStatus = match ($role) {
                'USER – RT/RW' => Usulan::STATUS_DRAFT_RT,
                'ADMIN – DESA' => Usulan::STATUS_DRAFT_DESA,
                default => Usulan::STATUS_DRAFT_RT,
            };

            $lokasi = $user->locations()
                ->where('locations.type', 'desa')
                ->first();

            if (!$lokasi) {
                throw new \Exception('User tidak terhubung dengan desa.');
            }

            $usulan = Usulan::create([
                'id_jenis_usulan' => $request->id_jenis_usulan,
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'id_pengusul' => $idPengusul,
                'created_by' => $user->id,
                'id_status_usulan' => $initialStatus,
                'id_desa' => $lokasi->id,
                'id_kecamatan' => $lokasi->parent_id,
                'alamat' => $request->alamat,
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

            HistoryUsulan::create([
                'id_usulan' => $usulan->id,
                'id_status_usulan_lama' => null,
                'id_status_usulan_baru' => $initialStatus,
                'created_by' => $user->id,
                'keterangan' => 'Usulan dibuat',
            ]);
            return redirect()->back()->with('success', 'Usulan berhasil dibuat.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Store Usulan Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal membuat usulan: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $usulan = Usulan::with('pengusul', 'createdBy')->findOrFail($id);

            $statusSaatIni = $usulan->id_status_usulan;
            $harusResetKeDraft = in_array($statusSaatIni, [
                Usulan::STATUS_REJECT_DESA,
                Usulan::STATUS_GAGAL_REVIEW_DESA,
                Usulan::STATUS_REJECT_KECAMATAN,
                Usulan::STATUS_GAGAL_REVIEW_KECAMATAN,
            ]);

            $statusBaru = $statusSaatIni;
            if ($harusResetKeDraft) {
                $pembuatRole = optional($usulan->createdBy->getRoleNames())->first();
                if ($pembuatRole === 'USER – RT/RW') {
                    $statusBaru = Usulan::STATUS_DRAFT_RT;
                } elseif ($pembuatRole === 'ADMIN – DESA') {
                    $statusBaru = Usulan::STATUS_DRAFT_DESA;
                }
                $usulan->id_status_usulan = $statusBaru;
            }

            $usulan->update([
                'id_jenis_usulan' => $request->id_jenis_usulan,
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'alamat' => $request->alamat,
            ]);

            if ($request->pengusul_sama == 1) {
                if ($usulan->id_pengusul) {
                    $usulan->id_pengusul = null;
                    $usulan->save();
                }
            } else {
                if ($usulan->id_pengusul) {
                    $usulan->pengusul->update([
                        'nama_pengusul' => $request->nama_pengusul,
                        'alamat_pengusul' => $request->alamat_pengusul,
                        'no_telp_pengusul' => $request->no_telp_pengusul,
                    ]);
                } else {
                    $pengusul = Pengusul::create([
                        'nama_pengusul' => $request->nama_pengusul,
                        'alamat_pengusul' => $request->alamat_pengusul,
                        'no_telp_pengusul' => $request->no_telp_pengusul,
                    ]);
                    $usulan->id_pengusul = $pengusul->id;
                    $usulan->save();
                }
            }

            if ($request->hasFile('dokumen')) {
                foreach ($request->file('dokumen') as $idJenisDokumen => $file) {
                    $idJenisDokumen = (int) $idJenisDokumen;
                    if ($file && $file->isValid() && $file->getSize() > 0) {
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
                            'id_jenis_dokumen' => $idJenisDokumen,
                            'name_file' => $file->getClientOriginalName(),
                            'path_file' => $path,
                        ]);
                    }
                }
            }

            $usulan->save();

            if ($statusBaru != $statusSaatIni) {
                HistoryUsulan::create([
                    'id_usulan' => $usulan->id,
                    'id_status_usulan_lama' => $statusSaatIni,
                    'id_status_usulan_baru' => $statusBaru,
                    'created_by' => Auth::id(),
                    'keterangan' => 'Status direset ke draft setelah revisi',
                ]);
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Usulan berhasil diperbarui.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update Usulan Error: ' . $e->getMessage());
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
            Log::error('Destroy Usulan Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Gagal menghapus usulan.'], 500);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'id_status_usulan' => 'required|integer|exists:status_usulan,id',
            'keterangan' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $usulan = Usulan::findOrFail($id);
            $user = Auth::user();

            $roleNames = $user->getRoleNames();
            if ($roleNames->isEmpty()) {
                throw new \Exception('User tidak memiliki role.');
            }
            $currentRole = $roleNames->first();

            $currentStatus = $usulan->id_status_usulan;
            $newStatus = $request->id_status_usulan;

            $allowed = $this->canChangeStatus($currentStatus, $newStatus, $currentRole);
            if (!$allowed) {
                throw new \Exception('Anda tidak berhak mengubah status ini.');
            }

            HistoryUsulan::create([
                'id_usulan' => $usulan->id,
                'id_status_usulan_lama' => $currentStatus ?: null,
                'id_status_usulan_baru' => $newStatus,
                'created_by' => $user->id,
                'keterangan' => $request->keterangan ?? 'Perubahan status usulan',
            ]);

            $usulan->id_status_usulan = $newStatus;
            if ($currentStatus == Usulan::STATUS_DRAFT_RT && $newStatus == Usulan::STATUS_DIAJUKAN_RT) {
                $desa = $user->locations()->where('locations.type', 'desa')->first();
                if ($desa) {
                    $usulan->id_desa = $desa->id;
                    $usulan->id_kecamatan = $desa->parent_id;
                }
            }

            $usulan->save();

            if (in_array($newStatus, Usulan::getStatusRejected())) {
                TindakLanjutUsulan::updateOrCreate(
                    ['id_usulan' => $usulan->id],
                    [
                        'id_status_tindak_lanjut' => 1,
                        'keterangan' => 'Usulan ditolak pada tahap: ' . optional($usulan->status)->nama_status,
                        'created_by' => $user->id,
                    ]
                );
            } elseif (in_array($newStatus, Usulan::getStatusApproved())) {
                TindakLanjutUsulan::updateOrCreate(
                    ['id_usulan' => $usulan->id],
                    [
                        'id_status_tindak_lanjut' => 3,
                        'keterangan' => 'Menunggu tindak lanjut implementasi.',
                        'created_by' => $user->id,
                    ]
                );
            }

            DB::commit();

            $pesan = match ($newStatus) {
                Usulan::STATUS_DIAJUKAN_RT => 'Usulan berhasil diajukan ke Admin Desa.',
                Usulan::STATUS_DIAJUKAN_DESA => 'Usulan berhasil diajukan ke Admin Kecamatan.',
                default => 'Status usulan diperbarui.'
            };

            return response()->json(['success' => true, 'message' => $pesan]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update Status Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 403);
        }
    }

    private function canChangeStatus($currentStatus, $newStatus, $role)
    {
        $roleMap = [
            'USER – RT/RW' => 'rtrw',
            'ADMIN – DESA' => 'desa',
            'SUPER ADMIN – KECAMATAN' => 'kecamatan',
            'MASTER – BAPPEDA' => 'bappeda',
            'admin' => 'admin',
        ];

        $userRole = $roleMap[$role] ?? 'unknown';

        $transitions = [
            Usulan::STATUS_DRAFT_RT => [
                'next' => [Usulan::STATUS_DIAJUKAN_RT],
                'role' => 'rtrw'
            ],
            Usulan::STATUS_DIAJUKAN_RT => [
                'next' => [
                    Usulan::STATUS_REJECT_DESA,
                    Usulan::STATUS_GAGAL_REVIEW_DESA,
                    Usulan::STATUS_APPROVE_DESA
                ],
                'role' => 'desa'
            ],
            Usulan::STATUS_APPROVE_DESA => [
                'next' => [
                    Usulan::STATUS_DRAFT_DESA,
                    Usulan::STATUS_DIAJUKAN_DESA
                ],
                'role' => 'desa'
            ],
            Usulan::STATUS_DRAFT_DESA => [
                'next' => [Usulan::STATUS_DIAJUKAN_DESA],
                'role' => 'desa'
            ],
            Usulan::STATUS_DIAJUKAN_DESA => [
                'next' => [
                    Usulan::STATUS_REJECT_KECAMATAN,
                    Usulan::STATUS_GAGAL_REVIEW_KECAMATAN,
                    Usulan::STATUS_APPROVE_KECAMATAN
                ],
                'role' => 'kecamatan'
            ],
            Usulan::STATUS_APPROVE_KECAMATAN => [
                'next' => [Usulan::STATUS_DIAJUKAN_KECAMATAN],
                'role' => 'kecamatan'
            ],
            Usulan::STATUS_NOTIF_KE_DESA => [
                'next' => [
                    Usulan::STATUS_NOTIF_KE_RT,
                    Usulan::STATUS_DRAFT_DESA
                ],
                'role' => 'desa'
            ],
            Usulan::STATUS_NOTIF_KE_RT => [
                'next' => [Usulan::STATUS_DRAFT_RT],
                'role' => 'rtrw'
            ],
        ];

        if (isset($transitions[$currentStatus])) {
            $rule = $transitions[$currentStatus];
            return in_array($newStatus, $rule['next']) && $userRole === $rule['role'];
        }

        if (
            in_array($currentStatus, [
                Usulan::STATUS_REJECT_DESA,
                Usulan::STATUS_GAGAL_REVIEW_DESA
            ])
        ) {
            return $userRole === 'rtrw';
        }

        if (
            in_array($currentStatus, [
                Usulan::STATUS_REJECT_KECAMATAN,
                Usulan::STATUS_GAGAL_REVIEW_KECAMATAN
            ])
        ) {
            return $userRole === 'desa';
        }

        return false;
    }

    public function getHistory($id)
    {
        $history = HistoryUsulan::with('statusLama', 'statusBaru', 'createdBy')
            ->where('id_usulan', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['data' => $history]);
    }

    public function getTindakLanjut($id)
    {
        $tindakLanjut = TindakLanjutUsulan::with('statusTindakLanjut', 'createdBy', 'dokumen')
            ->where('id_usulan', $id)
            ->first();

        return response()->json(['data' => $tindakLanjut]);
    }

    public function exportExcel()
    {
        $user = Auth::user();
        $roleNames = $user->getRoleNames();
        if ($roleNames->isEmpty()) {
            abort(403, 'User tidak memiliki role yang valid untuk mengekspor data.');
        }
        $role = $roleNames->first();

        $isAdminOrBappeda = in_array($role, ['admin', 'MASTER – BAPPEDA']);

        $userDesaIds = [];
        if (!$isAdminOrBappeda) {
            $userDesaIds = $user->locations()
                ->where('locations.type', 'desa')
                ->pluck('locations.id')
                ->toArray();
        }

        $query = Usulan::with(['createdBy', 'jenisUsulan', 'status', 'dokumen']);

        if ($isAdminOrBappeda) {
            // Tidak ada filter, ambil semua
        } elseif ($role === 'USER – RT/RW') {
            $query->where('created_by', $user->id);
        } elseif ($role === 'ADMIN – DESA') {
            $query->where(function ($q) use ($user, $userDesaIds) {
                $q->where('created_by', $user->id)
                    ->whereIn('id_status_usulan', [
                        Usulan::STATUS_DRAFT_DESA,
                        Usulan::STATUS_DIAJUKAN_DESA,
                        Usulan::STATUS_APPROVE_DESA,
                        Usulan::STATUS_REJECT_DESA,
                        Usulan::STATUS_GAGAL_REVIEW_DESA,
                        Usulan::STATUS_APPROVE_KECAMATAN,
                        Usulan::STATUS_REJECT_KECAMATAN,
                        Usulan::STATUS_GAGAL_REVIEW_KECAMATAN,
                        Usulan::STATUS_DIAJUKAN_KECAMATAN,
                    ]);
                $q->orWhere(function ($q2) use ($userDesaIds) {
                    $q2->whereIn('id_desa', $userDesaIds)
                        ->whereIn('id_status_usulan', [
                            Usulan::STATUS_DIAJUKAN_RT,
                            Usulan::STATUS_DRAFT_DESA,
                            Usulan::STATUS_DIAJUKAN_DESA,
                            Usulan::STATUS_APPROVE_DESA,
                            Usulan::STATUS_REJECT_DESA,
                            Usulan::STATUS_GAGAL_REVIEW_DESA,
                            Usulan::STATUS_APPROVE_KECAMATAN,
                            Usulan::STATUS_REJECT_KECAMATAN,
                            Usulan::STATUS_GAGAL_REVIEW_KECAMATAN,
                            Usulan::STATUS_DIAJUKAN_KECAMATAN,
                        ]);
                });
            });
        } elseif ($role === 'SUPER ADMIN – KECAMATAN') {
            $query->whereIn('id_desa', $userDesaIds)
                ->whereIn('id_status_usulan', [
                    Usulan::STATUS_DIAJUKAN_DESA,
                    Usulan::STATUS_APPROVE_KECAMATAN,
                    Usulan::STATUS_REJECT_KECAMATAN,
                    Usulan::STATUS_GAGAL_REVIEW_KECAMATAN,
                    Usulan::STATUS_DIAJUKAN_KECAMATAN,
                ]);
        }

        $usulanData = $query->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Daftar Usulan');

        $headers = [
            'ID',
            'Judul',
            'Deskripsi',
            'Jenis Usulan',
            'Status Usulan',
            'Alamat',
            'Latitude',
            'Longitude',
            'Tanggal Dibuat',
            'Tanggal Diupdate',
            'Dibuat Oleh (Nama)',
            'Dibuat Oleh (Email)',
            'Jumlah Dokumen',
            'Bisa Edit',
            'Bisa Ajukan',
            'Bisa Hapus',
            'Bisa Tindak Lanjut', // Catatan: Lihat komentar di bawah
        ];

        foreach ($headers as $index => $header) {
            $column = Coordinate::stringFromColumnIndex($index + 1);
            $sheet->setCellValue("{$column}1", $header);
        }

        $styleArray = [
            'font' => [
                'bold' => true,
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E6E6E6'],
            ],
        ];
        $lastColumnIndex = count($headers);
        $lastColumn = Coordinate::stringFromColumnIndex($lastColumnIndex);
        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray($styleArray);

        $rowIndex = 2;
        foreach ($usulanData as $usulan) {
            $colIndex = 0;

            $sheet->setCellValue(Coordinate::stringFromColumnIndex(++$colIndex) . $rowIndex, $usulan->id);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex(++$colIndex) . $rowIndex, $usulan->judul);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex(++$colIndex) . $rowIndex, strip_tags($usulan->deskripsi));
            $sheet->setCellValue(Coordinate::stringFromColumnIndex(++$colIndex) . $rowIndex, $usulan->jenisUsulan ? $usulan->jenisUsulan->nama : '-');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex(++$colIndex) . $rowIndex, $usulan->status ? $usulan->status->nama : '-');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex(++$colIndex) . $rowIndex, $usulan->alamat ?? '-');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex(++$colIndex) . $rowIndex, $usulan->latitude);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex(++$colIndex) . $rowIndex, $usulan->longitude);
            $sheet->setCellValue(Coordinate::stringFromColumnIndex(++$colIndex) . $rowIndex, $usulan->created_at ? $usulan->created_at->format('Y-m-d H:i:s') : '-');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex(++$colIndex) . $rowIndex, $usulan->updated_at ? $usulan->updated_at->format('Y-m-d H:i:s') : '-');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex(++$colIndex) . $rowIndex, $usulan->createdBy ? $usulan->createdBy->name : '-');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex(++$colIndex) . $rowIndex, $usulan->createdBy ? $usulan->createdBy->email : '-');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex(++$colIndex) . $rowIndex, $usulan->dokumen ? $usulan->dokumen->count() : 0);

            $sheet->setCellValue(Coordinate::stringFromColumnIndex(++$colIndex) . $rowIndex, $usulan->bisa_edit ? 'Ya' : 'Tidak'); // Gunakan nilai dari $usulan jika logika di getData() sudah diterapkan
            $sheet->setCellValue(Coordinate::stringFromColumnIndex(++$colIndex) . $rowIndex, $usulan->bisa_ajukan ? 'Ya' : 'Tidak');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex(++$colIndex) . $rowIndex, $usulan->bisa_hapus ? 'Ya' : 'Tidak');
            $sheet->setCellValue(Coordinate::stringFromColumnIndex(++$colIndex) . $rowIndex, $usulan->bisa_tindak_lanjut ? 'Ya' : 'Tidak');

            $rowIndex++;
        }

        foreach (range('A', Coordinate::stringFromColumnIndex(count($headers))) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        $fileName = 'daftar_usulan_' . date('Y-m-d_H-i-s') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }
}