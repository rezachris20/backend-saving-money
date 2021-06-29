<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = ['portofolio_id','user_id','nominal','dk','tanggal','description'];

    public static function portofolio($id)
    {
        $portofolio = Portofolio::find($id);

        return $portofolio;
    }

    // public function getTanggalAttribute($value)
    // {
    //     return Carbon::parse($value)->timestamp;
    // }

}
