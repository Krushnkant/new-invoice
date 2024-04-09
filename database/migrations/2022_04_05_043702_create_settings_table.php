<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            // $table->text('prefix_invoice_no')->nullable();
            // $table->integer('invoice_no')->default(1);
            $table->text('company_name')->nullable();
            $table->text('company_logo')->nullable();
            $table->text('company_address')->nullable();
            $table->text('company_mobile_no')->nullable();
            $table->text('company_gstno')->nullable();
            $table->text('company_panno')->nullable();
            $table->text('msme_no')->nullable();
            // $table->text('place_of_supply')->nullable();
            $table->integer('company_statecode')->default(0);
            $table->float('gst_percentage')->default(0);
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
        Schema::dropIfExists('settings');
    }
}
