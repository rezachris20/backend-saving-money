<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Portofolio extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function familyPortofolio()
    {
        return $this->hasMany(FamilyPortofolio::class, 'portofolio_id', 'id');
    }

    // public function getTanggalAttribute($value)
    // {
    //     return Carbon::parse($value)->timestamp;
    // }
}
