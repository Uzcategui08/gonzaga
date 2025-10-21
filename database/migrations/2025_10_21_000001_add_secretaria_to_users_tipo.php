<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
  public function up(): void
  {
    DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_tipo_check;");
    DB::statement("ALTER TABLE users ADD CONSTRAINT users_tipo_check CHECK (tipo IN ('admin', 'profesor', 'coordinador', 'secretaria'));");
  }

  public function down(): void
  {
    DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_tipo_check;");
    DB::statement("ALTER TABLE users ADD CONSTRAINT users_tipo_check CHECK (tipo IN ('admin', 'profesor', 'coordinador'));");
  }
};
