<?php

namespace App\Http\Controllers\API;

use Carbon\Carbon;
use App\Models\Portofolio;
use App\Models\UserFamily;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\FamilyPortofolio;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function getData(Request $request)
    {
        $user = Auth::user();
        $familyId = UserFamily::where('user_id', $user->id)->first()->family_id;
        $transactions = DB::table('transactions')
            ->leftJoin('user_families', 'user_families.user_id', '=', 'transactions.user_id')
            ->leftJoin('families', 'families.id', '=', 'user_families.family_id')
            ->leftJoin('users', 'users.id', '=', 'transactions.user_id')
            ->leftJoin('portofolios', 'portofolios.id', '=', 'transactions.portofolio_id')
            ->leftJoin('family_portofolios', 'portofolios.id', '=', 'family_portofolios.portofolio_id')
            ->select('transactions.*', 'users.name')
            ->where('families.id', $familyId)
            ->where('family_portofolios.is_active', 1)
            ->where('family_portofolios.is_achievement', 0)
            ->where('portofolios.id', 1)
            ->groupBy('transactions.id')
            ->get();

        $arr_transactions = array();
        $total_transaksi = 0;
        $target = 0;

        if (count($transactions) != 0) {

            foreach ($transactions as $key => $val) {

                if ($val->dk == 1) {
                    $total_transaksi += $val->nominal;
                } else {
                    $total_transaksi -= $val->nominal;
                }

                $arr_transactions[$key]['id'] = $val->id;
                $arr_transactions[$key]['portofolio'] = Transaction::portofolio($val->portofolio_id);
                $arr_transactions[$key]['user_id'] = $val->user_id;
                $arr_transactions[$key]['user_name'] = $val->name;
                $arr_transactions[$key]['nominal'] = $val->nominal;
                $arr_transactions[$key]['tanggal'] = $val->tanggal;
                $arr_transactions[$key]['dk'] = $val->dk;
                $arr_transactions[$key]['description'] = $val->description;
                $arr_transactions[$key]['keterangan_dk'] = $val->dk == 1 ? "Debit" : "Kredit";
            }
        }
        $target = FamilyPortofolio::where('portofolio_id', 1)->where('family_id', $familyId)->first()->target;

        $arr_final = array(
            "list_transaksi" => $arr_transactions,
            "my_saving"    => array(
                "transaksi" => $total_transaksi,
                "target" => $target
            )
        );

        return ResponseFormatter::success($arr_final, 'Data berhasil di ambil');
    }

    public function insertData(Request $request)
    {
        if ($request->dk == 1) {
            $request->validate([
                'portofolio_id' => 'required|integer',
                'nominal' => 'required|integer',
                'dk' => 'required|integer',
                'tanggal' => 'required|date'
            ]);
        } else {
            $request->validate([
                'portofolio_id' => 'required|integer',
                'nominal' => 'required|integer',
                'dk' => 'required|integer',
                'tanggal' => 'required|date',
                'description' => 'required|string'
            ]);
        }

        $cekPortofolio = Portofolio::find($request->portofolio_id);

        if (!$cekPortofolio) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => 'Portofolio tidak ditemukan'
            ], 'Authentication Failed', 500);
        }

        if ($request->dk == 1) {
            $result = Transaction::create([
                'portofolio_id' => $request->portofolio_id,
                'user_id' => Auth::user()->id,
                'nominal' => $request->nominal,
                'dk' => $request->dk,
                'tanggal' => $request->tanggal,
            ]);
        } else {
            $result = Transaction::create([
                'portofolio_id' => $request->portofolio_id,
                'user_id' => Auth::user()->id,
                'nominal' => $request->nominal,
                'dk' => $request->dk,
                'tanggal' => $request->tanggal,
                'description' => $request->description,
            ]);
        }


        return ResponseFormatter::success($result, 'Transaksi Berhasil');
    }

    public function detailTransactions(Request $request, $transactionsId, $familyId)
    {
        $data = DB::table('transactions')
            ->select(
                DB::raw('
                    transactions.id,
                    (SELECT name FROM portofolios WHERE id = transactions.portofolio_id) AS name_portofolio,
                    ( SELECT name FROM users WHERE id = transactions.user_id) name_user,
                    transactions.nominal,
                    IF(transactions.dk = 1,"Debit","Kredit") as dk,
                    IFNULL(transactions.description,"-") AS description,
                    transactions.tanggal,
                    (SELECT fp.target FROM family_portofolios fp WHERE fp.portofolio_id = transactions.portofolio_id AND family_id = ' . $familyId . ') AS target,
                    (SELECT sum(t.nominal) FROM transactions t JOIN user_families ON t.user_id = user_families.user_id WHERE user_families.family_id = ' . $familyId . ' AND t.dk = 1 AND portofolio_id = transactions.portofolio_id) AS debit,
                    (SELECT sum(t.nominal) FROM transactions t JOIN user_families ON t.user_id = user_families.user_id WHERE user_families.family_id = ' . $familyId . ' AND t.dk = 2 AND portofolio_id = transactions.portofolio_id) AS kredit'
                )
            )
            ->join('user_families','user_families.user_id','=','transactions.user_id')
            ->where('transactions.id', $transactionsId)
            ->where('user_families.family_id', $familyId)
            ->get();
            
        
        return ResponseFormatter::success($data, (count($data) == 1) ? 'Data berhasil di ambil' : 'Data kosong');
    }
}
