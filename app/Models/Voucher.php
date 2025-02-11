<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Voucher extends Model
{

    use HasFactory, SoftDeletes;
    protected $table = 'vouchers';

    protected $primaryKey = 'id';
    protected $fillable = ['code'];
    public $timestamps = true;
}
