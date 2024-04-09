<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            // $table->text('language');
            $table->text('invoice_no');
            $table->integer('consignee_id');
            $table->integer('transporter_id');
            $table->decimal('sub_total', 10, 2)->default(0);
            $table->decimal('sgst_amount', 10, 2)->default(0);
            $table->decimal('cgst_amount', 10, 2)->default(0);
            $table->decimal('igst_amount', 10, 2)->default(0);
            $table->decimal('final_amount', 10, 2)->default(0);
            $table->float('gst_percentage')->default(0);
            $table->text('transport_mode');
            $table->text('place_of_supply')->nullable();
            $table->date('invoice_date');
            // $table->float('total_qty')->default(0);
            // $table->float('total_price')->default(0);
            // $table->float('total_discount')->nullable()->default(0);
            // $table->float('outstanding_amount')->default(0);
            // $table->float('total_payable_amount')->default(0);
            $table->integer('estatus')->default(1)->comment('1->Active,2->Deactive,3->Deleted,4->Pending');
            $table->dateTime('created_at')->default(\Carbon\Carbon::now());
            $table->dateTime('updated_at')->default(null)->onUpdate(\Carbon\Carbon::now());
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoices');
    }
}
