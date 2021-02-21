<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class OnlineOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('online_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('order_id');
            $table->string('receiver_name');
            $table->bigInteger('contact_number');
            $table->string('email');
            $table->string('building_or_street');
            $table->string('barangay');
            $table->string('city_or_municipality');
            $table->string('province');
            $table->double('total_payment');
            $table->string('preferred_delivery_date');
            // $table->double('distance');
            $table->string('order_status');
            $table->string('landmark');
            $table->string('payment_method');
            $table->string('payment_status');
            // $table->double('latitude');
            // $table->double('longitude');
            $table->timestamps();
        }); 
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
