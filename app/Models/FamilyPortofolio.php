<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilyPortofolio extends Model
{
    use HasFactory;

    protected $fillable = ['family_id','portofolio_id', 'target' , 'is_active', 'is_achievement'];

    public function portofolio()
    {
        return $this->hasOne(Portofolio::class, 'id', 'portofolio_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'portofolio_id','portofolio_id')->orderBy('tanggal','ASC');
    }
}
