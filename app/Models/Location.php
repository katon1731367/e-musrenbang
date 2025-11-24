<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'parent_id'];

    // Relasi: kecamatan punya banyak desa
    public function children()
    {
        return $this->hasMany(Location::class, 'parent_id');
    }

    // Relasi: desa punya kecamatan (parent)
    public function parent()
    {
        return $this->belongsTo(Location::class, 'parent_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_locations', 'location_id', 'user_id');
    }

    public function userLocations()
    {
        return $this->hasMany(UserLocation::class, 'user_id');
    }
}