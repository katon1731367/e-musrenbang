<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusTindakLanjut extends Model
{
    use HasFactory;

    protected $table = 'status_tindak_lanjut';
    public $timestamps = false;

    public const DITOLAK = 1;
    public const DISETUJUI = 2;
    public const PROSES = 3;

    protected $fillable = ['nama', 'status_usulan_id'];

    public function tindakLanjutUsulan()
    {
        return $this->hasMany(TindakLanjutUsulan::class, 'id_status_tindak_lanjut');
    }
}