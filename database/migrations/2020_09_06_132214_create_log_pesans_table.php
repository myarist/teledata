<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogPesansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_pesan', function (Blueprint $table) {
            $table->id();
            $table->string('username')->nullable();
            $table->string('chatid');
            $table->text('isi_pesan');
            $table->string('chatid_penerima')->nullable();
            $table->boolean('chat_admin')->default(0);
            $table->string('msg_id',20)->nullable();
            $table->string('waktu_kirim',12)->nullable();
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
        Schema::dropIfExists('log_pesans');
    }
}
