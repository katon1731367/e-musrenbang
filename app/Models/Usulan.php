<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Usulan extends Model
{
    use HasFactory;

    protected $table = 'usulan';

    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'id_jenis_usulan',
        'judul',
        'deskripsi',
        'latitude',
        'longitude',
        'id_status_usulan',
        'created_by',
        'id_desa',
        'id_kecamatan',
        'alamat'
    ];

    public const STATUS_DRAFT_RT = 1;
    public const STATUS_DIAJUKAN_RT = 2;
    public const STATUS_REJECT_DESA = 3;
    public const STATUS_GAGAL_REVIEW_DESA = 4;
    public const STATUS_APPROVE_DESA = 5;
    public const STATUS_DRAFT_DESA = 6;
    public const STATUS_DIAJUKAN_DESA = 7;
    public const STATUS_REJECT_KECAMATAN = 8;
    public const STATUS_GAGAL_REVIEW_KECAMATAN = 9;
    public const STATUS_APPROVE_KECAMATAN = 10;
    public const STATUS_DIAJUKAN_KECAMATAN = 11;
    public const STATUS_NOTIF_KE_DESA = 12;
    public const STATUS_NOTIF_KE_RT = 13;

    public function dokumen()
    {
        return $this->hasMany(DokumenUsulan::class, 'id_usulan');
    }

    public function jenisUsulan()
    {
        return $this->belongsTo(JenisUsulan::class, 'id_jenis_usulan');
    }

    public function status()
    {
        return $this->belongsTo(StatusUsulan::class, 'id_status_usulan');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pengusul()
    {
        return $this->belongsTo(Pengusul::class, 'id_pengusul');
    }

    public function desa()
    {
        return $this->belongsTo(Location::class, 'id_desa');
    }

    public function kecamatan()
    {
        return $this->belongsTo(Location::class, 'id_kecamatan');
    }

    public function tindakLanjut()
    {
        return $this->hasOne(TindakLanjutUsulan::class, 'id_usulan');
    }

    public static function getStatusRejected()
    {
        return [
            self::STATUS_REJECT_DESA,
            self::STATUS_GAGAL_REVIEW_DESA,
            self::STATUS_REJECT_KECAMATAN,
            self::STATUS_GAGAL_REVIEW_KECAMATAN,
        ];
    }

    public static function getStatusApproved()
    {
        return [
            self::STATUS_APPROVE_DESA,
            self::STATUS_APPROVE_KECAMATAN,
        ];
    }
}
