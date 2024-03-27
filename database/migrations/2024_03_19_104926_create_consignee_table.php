<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConsigneeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('consignee', function (Blueprint $table) {
            $table->id();
            $table->text('partyname', 200);
            $table->text('mobile_no', 10)->nullable();
            $table->text('address', 500);
            $table->text('state');
            $table->text('state_code');
            $table->text('gst_no');
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
        Schema::dropIfExists('consignee');
    }
}
