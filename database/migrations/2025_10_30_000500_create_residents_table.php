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
        Schema::create('residents', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 16)->unique(); // Changed length to 16 to match SQL
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict'); // Added user_id
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete(); // Added created_by_user_id
            $table->foreignId('banjar_id')->nullable()->constrained('banjars'); // Made nullable as per SQL
            $table->string('family_card_number', 16)->index(); // Added index
            $table->string('name', 80); // Added length constraint
            $table->enum('gender', ['L', 'P']); // Kept as L/P
            $table->string('place_of_birth', 50);
            $table->date('date_of_birth');
            $table->enum('family_status', ['HEAD_OF_FAMILY', 'PARENT', 'HUSBAND', 'WIFE', 'CHILD']);
            $table->string('religion', 50)->nullable();
            $table->string('education', 50)->nullable();
            $table->string('work_type', 50)->nullable();
            $table->enum('marital_status', ['MARRIED', 'SINGLE', 'DEAD_DIVORCE', 'LIVING_DIVORCE'])->nullable();
            $table->text('origin_address')->nullable();
            $table->text('residential_address')->nullable();
            $table->string('house_number', 20)->nullable();
            $table->longText('location')->nullable(); // JSON check is DB specific, skipping for generic migration or using json() type
            $table->date('arrival_date')->nullable();
            $table->string('phone', 12)->nullable(); // Kept as phone, length 12
            $table->string('email', 50)->nullable();
            $table->enum('validation_status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            // $table->enum('krama_status', ['KRAMA_DESA_PEMIRAK', 'KRAMA_DESA_NEGAK', 'KRAMA_DESA_PENGAMPEL', 'KRAMA_TAMIU', 'TAMIU'])->nullable();
            $table->enum('village_status', ['NEGAK', 'PEMIRAK', 'PENGAMPEL'])->nullable();
            $table->text('photo_house')->nullable();
            $table->text('resident_photo')->nullable();
            $table->text('photo_ktp')->nullable();

            $table->foreignId('resident_status_id')->nullable()->constrained('resident_statuses'); // Made nullable just in case
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('residents');
    }
};
