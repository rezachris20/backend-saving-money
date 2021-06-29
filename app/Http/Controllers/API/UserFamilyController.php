<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Models\UserFamily;

class UserFamilyController extends Controller
{
    public function fetch(Request $request)
    {
        return ResponseFormatter::success($request->user(),'Data Profile Berhasil di ambil');
    }

    public function getData(Request $request, $familyId)
    {
        $data = UserFamily::with(['user'])->where('family_id',$familyId)->get();

        return ResponseFormatter::success($data,'Data berhasil di ambil');
    }
}
