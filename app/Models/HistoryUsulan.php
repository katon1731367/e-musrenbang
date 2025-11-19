<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoryUsulan extends Model
{
    use HasFactory;

    protected $table = 'history_usulan';
    public $timestamps = true;

    protected $fillable = [
        'id_usulan',
        'id_status_usulan_lama',
        'id_status_usulan_baru',
        'created_by',
        'keterangan',
    ];

    public function usulan()
    {
        return $this->belongsTo(Usulan::class, 'id_usulan');
    }

    public function statusLama()
    {
        return $this->belongsTo(StatusUsulan::class, 'id_status_usulan_lama');
    }

    public function statusBaru()
    {
        return $this->belongsTo(StatusUsulan::class, 'id_status_usulan_baru');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}