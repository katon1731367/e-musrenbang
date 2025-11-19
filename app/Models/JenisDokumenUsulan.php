<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisDokumenUsulan extends Model
{
    use HasFactory;

    protected $table = 'jenis_dokumen_usulan';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nama',
        'keterangan'
    ];

    public function dokumen()
    {
        return $this->hasMany(DokumenUsulan::class, 'id_jenis_dokumen');
    }
}
