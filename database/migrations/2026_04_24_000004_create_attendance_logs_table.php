<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->enum('type', ['clock_in', 'clock_out']);
            $table->json('embedding')->nullable(); // 128-dim vector sent from device
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('accuracy', 8, 2)->nullable();
            $table->timestamp('device_timestamp'); // timestamp from the mobile device
            $table->boolean('verified')->default(false);
            $table->decimal('verification_score', 5, 4)->nullable(); // 0.0000 to 1.0000
            $table->boolean('synced_from_offline')->default(false);
            $table->foreignId('site_id')->nullable()->constrained('work_sites')->nullOnDelete();
            $table->timestamps();

            $table->index(['employee_id', 'type', 'created_at']);
            $table->index('device_timestamp');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};
