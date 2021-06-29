<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Portofolio;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\FamilyPortofolio;
use App\Models\Transaction;
use App\Models\UserFamily;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PortofolioController extends Controller
{
    public function addPortofolio(Request $request)
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'target' => ['required', 'integer']
            ]);

            // Get Family id
            $user = Auth::user();
            $family_id = UserFamily::where('user_id', $user->id)->first()->family_id;

            /**
             * Insert to table portofolios
             * @required name, target
             */
            $portofolio = Portofolio::create([
                'name' => $request->name,

            ]);

            /**
             * Insert again to table family_portofolios
             * @required family_id, portofolio_id, target, 
             * @automatic insert is_active, is_achievement
             */

            FamilyPortofolio::create([
                'family_id' => $family_id,
                'portofolio_id' => $portofolio->id,
                'target' => $request->target,
                'is_active' => 1,
                'is_achievement' => 0
            ]);

            $result = Portofolio::with('familyPortofolio')->find($portofolio->id);

            return ResponseFormatter::success([
                'portofolio' => $result
            ]);
        } catch (Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error
            ], 'Authentication Failed', 500);
        }
    }

    public function editPortofolio(Request $request, $id){
        $portofolio = Portofolio::with('familyPortofolio')->find($id);

        return ResponseFormatter::success($portofolio, 'Data berhasil di ambil');
    }

    public function updatePortofolio(Request $request, $id)
    {
        $portofolio = Portofolio::with('familyPortofolio')->find($id);

        if($portofolio){

            $request->validate([
                'name' => 'required|string|max:255',
                'target' => 'required|integer',
                'is_active' => 'required|integer'
            ]);

            $data = Portofolio::find($id);
            $data['name'] = $request->name;
            $data->save();

            $next = FamilyPortofolio::where('portofolio_id', $id)->first();
            $next['target'] = $request->target;
            $next['is_active'] = $request->is_active;
            $next->save();

            $respon = Portofolio::with('familyPortofolio')->find($id);
            return ResponseFormatter::success($respon, 'Data berhasil di ubah');

        } else {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => 'Update Failed'
            ], 'Authentication Failed', 500);
        }
    }

    public function getData(Request $request, $id)
    {
        $portofolio = FamilyPortofolio::with(['portofolio','transactions'])->where('family_id',$id)->where('portofolio_id','!=','1')->get();

        foreach($portofolio as $key => $val){
            $kredit = 0;
            $debit = 0;
            $last_saving = "0000-00-00 00:00:00";
            foreach($val['transactions'] as $key1 => $val1){
                if($val1['dk'] == 1) { // Debit
                    $debit += $val1['nominal'];
                } else { // kredit
                    $kredit += $val1['nominal'];
                }
                $last_saving = $val1['tanggal'];
            }
            $portofolio[$key]['my_saving'] = $debit - $kredit;
            $portofolio[$key]['last_saving'] = $last_saving;
        }
        return ResponseFormatter::success($portofolio, 'Data berhasil di ambil');
    }

    public function getDropdown(Request $request, $id)
    {
        $portofolio = FamilyPortofolio::with('portofolio')->where('family_id',$id)->get();
        return ResponseFormatter::success($portofolio, 'Data berhasil di ambil');
    }

    public function addTarget(Request $request, $id)
    {
        $request->validate([
            'target' => 'required|integer'
        ]);

        $user = Auth::user()->id;
        
        $data = FamilyPortofolio::where('portofolio_id',1)->where('family_id',$id)->first();
        $data['target'] = $request->target;
        $data->save();

        return ResponseFormatter::success($data, 'Data berhasil di ambil');

    }

    public function savingTarget(Request $request, $id)
    {
        $target = FamilyPortofolio::where('family_id',$id)->where('portofolio_id','!=', '1')->sum('target');
        $transaction_debit = DB::table('transactions')
            ->leftJoin('user_families','transactions.user_id', '=','user_families.user_id')
            ->select('nominal')
            ->where('dk',1)
            ->where('user_families.family_id',$id)->sum('nominal');
        $transaction_kredit = DB::table('transactions')
            ->leftJoin('user_families','transactions.user_id', '=','user_families.user_id')
            ->select('nominal')
            ->where('dk',2)
            ->where('user_families.family_id',$id)->sum('nominal');
        $data = array(
            'my_savings' => (int)$transaction_debit,
            'target'    => (int)$target
        );

        return ResponseFormatter::success($data, 'Data berhasil di ambil');

    }
}
