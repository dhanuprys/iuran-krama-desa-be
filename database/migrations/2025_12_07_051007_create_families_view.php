<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite doesn't support CREATE OR REPLACE VIEW
        DB::statement("DROP VIEW IF EXISTS families");
        DB::statement("CREATE VIEW families AS SELECT DISTINCT family_card_number FROM residents");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Replaced Schema::dropIfExists with DB::statement for view dropping
        DB::statement("DROP VIEW IF EXISTS families");
    }
};
