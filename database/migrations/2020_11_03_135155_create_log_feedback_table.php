<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogFeedbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_feedback', function (Blueprint $table) {
            $table->id();
            $table->string('username')->nullable();
            $table->string('chatid');
            $table->boolean('nilai_feedback')->default(5);
            $table->text('isi_feedback');
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
        Schema::dropIfExists('log_feedback');
    }
}
