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

    if (Schema::hasColumn('clases_extracurriculares', 'aula')) {
      return;
    }

    Schema::table('clases_extracurriculares', function (Blueprint $table) {
      $table->string('aula')->nullable()->after('profesor_id');
    });
  }

  public function down(): void
  {
    if (!Schema::hasTable('clases_extracurriculares')) {
      return;
    }

    if (!Schema::hasColumn('clases_extracurriculares', 'aula')) {
      return;
    }

    Schema::table('clases_extracurriculares', function (Blueprint $table) {
      $table->dropColumn('aula');
    });
  }
};
