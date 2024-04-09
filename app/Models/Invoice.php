<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'invoices';

    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'invoice_no',
        'consignee_id',
        'transporter_id',
        'sub_total',
        'sgst_amount',
        'cgst_amount',
        'igst_amount',
        'transport_mode',
        'gst_percentage',
        'invoice_date',
        'final_amount',
        'estatus',
        'place_of_supply'
    ];

    public function invoice_item(){
        return $this->hasMany(InvoiceItem::class,'invoice_id','id')->orderBy('id', 'asc');
    }

    public function consignee(){
        return $this->hasOne(Consignee::class,'id','consignee_id');
    }

    public function transporter(){
        return $this->hasOne(Transporter::class,'id','transporter_id');
    }
}
