<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vote_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id'); // Can't constrained cascade if we want history after poll delete? Maybe. Just keep it weak or simple FK.
            $table->foreignId('poll_option_id');
            $table->string('user_ip', 45);
            $table->timestamp('voted_at');
            $table->timestamp('released_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vote_histories');
    }
};
