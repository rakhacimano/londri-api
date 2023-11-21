<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetilTransaksi extends Model
{
    use SoftDeletes;

    protected $table = 'detail_transaksi';
    protected $fillable = ['id_transaksi', 'id_paket', 'quantity', 'subtotal'];
}
