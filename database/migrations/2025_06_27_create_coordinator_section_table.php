<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('coordinator_section', function (Blueprint $table) {
            $table->id();
            $table->foreignId('coordinator_id')->constrained('users');
            $table->foreignId('section_id')->constrained('secciones');
            $table->timestamps();
            
            // Índice compuesto para evitar duplicados
            $table->unique(['coordinator_id', 'section_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('coordinator_section');
    }
};
