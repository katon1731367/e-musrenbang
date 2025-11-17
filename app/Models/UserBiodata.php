<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBiodata extends Model
{
    use HasFactory;

    protected $table = 'user_biodata';
    protected $primaryKey = 'id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'nik',
        'jabatan',
        'alamat',
        'no_hp'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
