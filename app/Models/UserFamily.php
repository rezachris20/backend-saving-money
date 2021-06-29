<?php

namespace App\Models;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserFamily extends Model
{
    use HasFactory;

    protected $fillable = ['user_id','family_id','core_status_id'];
    
    public function user()
    {
        return $this->hasMany(User::class,'id','user_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class,'user_id','user_id');
    }

    public function getTanggalAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }
}
