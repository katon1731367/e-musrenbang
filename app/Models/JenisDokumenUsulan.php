<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisDokumenUsulan extends Model
{
    use HasFactory;

    protected $table = 'jenis_dokumen_usulan';

    // Jika primary key bukan 'id', ubah di sini:
    protected $primaryKey = 'id';

    // Kolom yang bisa diisi (fillable)
    protected $fillable = [
        'nama',
        'keterangan'
    ];

    /**
     * Relasi ke DokumenUsulan (satu jenis dokumen bisa punya banyak file dokumen)
     */
    public function dokumen()
    {
        return $this->hasMany(DokumenUsulan::class, 'id_jenis_dokumen');
    }
}
