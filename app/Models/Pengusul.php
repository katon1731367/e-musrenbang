<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pengusul extends Model
{
    use HasFactory;

    protected $table = 'pengusul';
    protected $primaryKey = 'id';
    public $timestamps = false; 

    protected $fillable = [
        'nama_pengusul',
        'alamat_pengusul',
        'no_telp_pengusul',
    ];

    public function usulan()
    {
        return $this->hasMany(Usulan::class, 'id_pengusul');
    }
}
