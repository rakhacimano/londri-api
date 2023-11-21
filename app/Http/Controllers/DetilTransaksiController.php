<?php

namespace App\Http\Controllers;

use App\Models\DetilTransaksi;
use App\Models\Paket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class DetilTransaksiController extends Controller
{
    public $user;
    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_transaksi' => 'required',
            'id_paket' => 'required',
            'quantity' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $detil = new DetilTransaksi();
        $detil->id_transaksi = $request->id_transaksi;
        $detil->id_paket = $request->id_paket;

        //GET HARGA PAKET
        $paket = Paket::where('id', '=', $detil->id_paket)->first();
        $harga = $paket->harga;

        $detil->quantity = $request->quantity;
        $detil->subtotal = $detil->quantity * $harga;

        $detil->save();

        $data = DetilTransaksi::where('id', '=', $detil->id)->first();

        return response()->json(['message' => 'Berhasil tambah detil transaksi', 'data' => $data]);
    }

    public function getById($id)
    {
        //untuk ambil detil dari transaksi tertentu

        $data = DB::table('detail_transaksi')->join('paket', 'detail_transaksi.id_paket', 'paket.id')
            ->select('detail_transaksi.*', 'paket.jenis')
            ->where('detail_transaksi.id_transaksi', '=', $id)
            ->get();
        return response()->json($data);
    }

    public function getTotal($id)
    {
        $total = DetilTransaksi::where('id_transaksi', $id)->sum('subtotal');

        return response()->json([
            'total' => $total
        ]);
    }
}
