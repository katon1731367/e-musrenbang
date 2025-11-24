<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Storage;

class UserBiodata extends Model
{
    use HasFactory;

    protected $table = 'user_biodata';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'nik',
        'jabatan',
        'alamat',
        'no_hp',
        'foto'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getFotoUrlAttribute()
    {
        if ($this->foto) {
            return Storage::url('profil/' . $this->foto);
            
        }

        return asset('assets/compiled/jpg/1.jpg');
    }
}