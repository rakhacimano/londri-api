<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaksi extends Model
{
    use SoftDeletes;
    protected $table = 'transaksi';
    protected $fillable = ['id_member', 'tgl_order', 'batas_waktu', 'tgl_bayar', 'status', 'dibayar', 'id_user'];

    // public function detail()
    // {
    //     return $this->hasMany(DetilTransaksi::class,'id_transaksi', 'id');
    // }
}
