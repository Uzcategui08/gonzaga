<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::table('asignaciones', function (Blueprint $table) {
      $table->json('estudiantes_id')->nullable()->after('seccion_id');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('asignaciones', function (Blueprint $table) {
      $table->dropColumn('estudiantes_id');
    });
  }
};
