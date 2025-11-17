<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DokumenUsulan extends Model
{
    protected $table = 'dokumen_usulan';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'id_usulan',
        'id_jenis_dokumen',
        'name_file',
        'path_file'
    ];

    public function usulan()
    {
        return $this->belongsTo(Usulan::class, 'id_usulan');
    }

    public function jenisDokumen()
    {
        return $this->belongsTo(JenisDokumenUsulan::class, 'id_jenis_dokumen');
    }
}
