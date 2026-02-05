<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
  {
    if (!Schema::hasTable('clases_extracurriculares')) {
      return;
    }

    if (Schema::hasColumn('clases_extracurriculares', 'profesor_id')) {
      return;
    }

    Schema::table('clases_extracurriculares', function (Blueprint $table) {
      $table->foreignId('profesor_id')
        ->nullable()
        ->after('nombre')
        ->constrained('profesores')
        ->nullOnDelete();
    });
  }

  public function down(): void
  {
    if (!Schema::hasTable('clases_extracurriculares')) {
      return;
    }

    if (!Schema::hasColumn('clases_extracurriculares', 'profesor_id')) {
      return;
    }

    Schema::table('clases_extracurriculares', function (Blueprint $table) {
      $table->dropConstrainedForeignId('profesor_id');
    });
  }
};
