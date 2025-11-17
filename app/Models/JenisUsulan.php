<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisUsulan extends Model
{
    use HasFactory;

    protected $table = 'jenis_usulan';

    // Primary key
    protected $primaryKey = 'id';

    // Kolom yang bisa diisi
    protected $fillable = [
        'nama',
        'deskripsi',
    ];

    /**
     * Relasi ke model Usulan
     * Satu jenis usulan bisa memiliki banyak usulan
     */
    public function usulan()
    {
        return $this->hasMany(Usulan::class, 'id_jenis_usulan');
    }
}
