<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Setting extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'settings';

    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'prefix_invoice_no',
        'company_name',
        'company_logo',
        'company_address',
        'company_mobile_no',
        'company_gstno',
        'company_panno',
        'place_of_supply',
        'company_statecode',
        'gst_percentage',
        'msme_no',
        'estatus',
    ];
}
