<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Transaksi;
use App\Models\DetilTransaksi;
use App\Models\User;
use Carbon\Carbon;
use Tymon\JWTAuth\Facades\JWTAuth;

class TransaksiController extends Controller
{
    public $user;

    public function __construct()
    {
        $this->user = JWTAuth::parseToken()->authenticate();
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_member' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $transaksi = new Transaksi();
        $transaksi->id_member = $request->id_member;
        $transaksi->tgl_order = Carbon::now();
        $transaksi->batas_waktu = Carbon::now()->addDays(3);
        $transaksi->status = 'baru';
        $transaksi->dibayar = 'belum dibayar';
        $transaksi->id_user = $this->user->id;

        $transaksi->save();

        $data = Transaksi::where('id', '=', $transaksi->id)->first();

        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => 'Data transaksi berhasil ditambahkan',
        ]);
    }

    public function getAll()
    {
        $id_user = $this->user->id;
        $data_user = User::where('id', '=', $id_user)->first();

        $data = DB::table('transaksi')->join('member', 'transaksi.id_member', '=', 'member.id')
            ->join('users', 'transaksi.id_user', 'users.id')
            ->select('transaksi.id', 'member.nama', 'transaksi.tgl_order', 'transaksi.status', 'users.name')
            ->where('users.id_outlet', $data_user->id_outlet)
            ->get();

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function getById($id)
    {
        $data = Transaksi::where('id', '=', $id)->first();
        $data = DB::table('transaksi')->join('member', 'transaksi.id_member', '=', 'member.id')
            ->select('transaksi.*', 'member.nama')
            ->where('transaksi.id', '=', $id)
            ->first();
        return response()->json($data);
    }

    public function changeStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $transaksi = Transaksi::where('id', '=', $id)->first();
        $transaksi->status = $request->status;

        $transaksi->save();

        return response()->json(['message' => 'Status berhasil diubah']);
    }

    public function bayar($id)
    {
        $transaksi = Transaksi::where('id', '=', $id)->first();
        $total = DetilTransaksi::where('id_transaksi', $id)->sum('subtotal');

        $transaksi->tgl_bayar = Carbon::now();
        $transaksi->status = "diambil";
        $transaksi->dibayar = "dibayar";
        $transaksi->total_bayar = $total;

        $transaksi->save();

        return response()->json(['message' => 'Pembayaran berhasil']);
    }

    public function report(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tahun' => 'required',
            'bulan' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $tahun = $request->tahun;
        $bulan = $request->bulan;

        $id_user = $this->user->id;
        $data_user = User::where('id', '=', $id_user)->first();

        $data = DB::table('transaksi')->join('member', 'transaksi.id_member', '=', 'member.id')
            ->join('users', 'transaksi.id_user', '=', 'users.id')
            ->select('transaksi.id', 'member.nama', 'transaksi.tgl_order', 'transaksi.tgl_bayar', 'transaksi.total_bayar', 'users.name')
            ->where('users.id_outlet', $data_user->id_outlet)
            ->whereYear('transaksi.tgl_order', '=', $tahun)
            ->whereMonth('transaksi.tgl_order', '=', $bulan)
            ->get();

        return response()->json($data);
    }
}
