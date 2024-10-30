<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuditTrailTable extends Migration
{
    public function up()
    {
        Schema::create('audit_trail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('email');
            $table->unsignedBigInteger('role_id');
            $table->string('action');
            $table->timestamps();


        });
    }

    public function down()
    {
        Schema::dropIfExists('audit_trail');
    }
}
