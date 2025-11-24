<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Usulan;
use App\Models\HistoryUsulan;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        return view('page.dashboard');
    }

    public function getDashboardData()
    {
        $user = Auth::user();
        $roleNames = $user->getRoleNames();
        if ($roleNames->isEmpty()) {
            return response()->json(['error' => 'User tidak memiliki role.'], 403);
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

        $queryTotal = Usulan::query();
        $queryDraft = Usulan::query();
        $queryDiajukan = Usulan::query();
        $queryApprove = Usulan::query();
        $queryReject = Usulan::query();

        if ($isAdminOrBappeda) {
        } elseif ($role === 'USER – RT/RW') {
            $queryTotal->where('created_by', $user->id);
            $queryDraft->where('created_by', $user->id);
            $queryDiajukan->where('created_by', $user->id);
            $queryApprove->where('created_by', $user->id);
            $queryReject->where('created_by', $user->id);
        } elseif ($role === 'ADMIN – DESA') {
            $queryTotal->whereIn('id_desa', $userDesaIds);
            $queryDraft->whereIn('id_desa', $userDesaIds);
            $queryDiajukan->whereIn('id_desa', $userDesaIds);
            $queryApprove->whereIn('id_desa', $userDesaIds);
            $queryReject->whereIn('id_desa', $userDesaIds);
        } elseif ($role === 'SUPER ADMIN – KECAMATAN') {
            $queryTotal->whereIn('id_desa', $userDesaIds);
            $queryDraft->whereIn('id_desa', $userDesaIds);
            $queryDiajukan->whereIn('id_desa', $userDesaIds);
            $queryApprove->whereIn('id_desa', $userDesaIds);
            $queryReject->whereIn('id_desa', $userDesaIds);
        }

        $totalUsulan = $queryTotal->count();
        $draftUsulan = $queryDraft->whereIn('id_status_usulan', [Usulan::STATUS_DRAFT_RT, Usulan::STATUS_DRAFT_DESA])->count();
        $diajukanUsulan = $queryDiajukan->whereIn('id_status_usulan', [Usulan::STATUS_DIAJUKAN_RT, Usulan::STATUS_DIAJUKAN_DESA])->count();
        $approveUsulan = $queryApprove->whereIn('id_status_usulan', [Usulan::STATUS_APPROVE_DESA, Usulan::STATUS_APPROVE_KECAMATAN])->count();
        $rejectUsulan = $queryReject->whereIn('id_status_usulan', [Usulan::STATUS_REJECT_DESA, Usulan::STATUS_REJECT_KECAMATAN])->count();

        $chartData = [
            'labels' => ['Draft', 'Diajukan', 'Disetujui', 'Ditolak'],
            'data' => [
                $draftUsulan,
                $diajukanUsulan,
                $approveUsulan,
                $rejectUsulan
            ]
        ];

        $aktivitasQuery = HistoryUsulan::with(['usulan', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->limit(5);

        if ($isAdminOrBappeda) {
            // Tidak ada filter untuk admin/bappeda
        } elseif ($role === 'USER – RT/RW') {
            $aktivitasQuery->whereHas('usulan', function ($q) use ($user) {
                $q->where('created_by', $user->id);
            });
        } elseif ($role === 'ADMIN – DESA' || $role === 'SUPER ADMIN – KECAMATAN') {
            $aktivitasQuery->whereHas('usulan', function ($q) use ($userDesaIds) {
                $q->whereIn('id_desa', $userDesaIds);
            });
        }

        $aktivitas = $aktivitasQuery->get()->map(function ($item) {
            return [
                'action' => $item->keterangan,
                'usulan_judul' => $item->usulan->judul,
                'user_name' => $item->createdBy->name,
                'created_at' => $item->created_at,
            ];
        });

        return response()->json([
            'total_usulan' => $totalUsulan,
            'draft_usulan' => $draftUsulan,
            'diajukan_usulan' => $diajukanUsulan,
            'approve_usulan' => $approveUsulan,
            'chart_data' => $chartData,
            'aktivitas_terbaru' => $aktivitas,
        ]);
    }
}