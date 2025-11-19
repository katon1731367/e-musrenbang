<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DokumenTindakLanjutUsulan extends Model
{
    use HasFactory;

    protected $table = 'dokumen_tindak_lanjut_usulan';

    public $timestamps = false;

    protected $fillable = [
        'id_tindak_lanjut_usulan',
        'path_file',
        'name_file',
        'created_at',
    ];

    public function tindakLanjut()
    {
        return $this->belongsTo(TindakLanjutUsulan::class, 'id_tindak_lanjut_usulan');
    }
}