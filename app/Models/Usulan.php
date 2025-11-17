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
    ];

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
}
