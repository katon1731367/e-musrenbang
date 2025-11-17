<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusUsulan extends Model
{
    use HasFactory;
    
    protected $table = 'status_usulan';

    protected $fillable = [
        'nama_status',
        'keterangan',
    ];

    public function usulan()
    {
        return $this->hasMany(Usulan::class, 'id_status_usulan');
    }
}
