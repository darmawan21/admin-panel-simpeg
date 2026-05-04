<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sync_errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('attendance_log_id')->nullable()->constrained('attendance_logs')->nullOnDelete();
            $table->string('error_type'); // e.g. 'validation', 'face_mismatch', 'geofence_violation'
            $table->text('error_message');
            $table->json('payload')->nullable(); // original request payload for debugging
            $table->boolean('resolved')->default(false);
            $table->timestamps();

            $table->index(['employee_id', 'resolved']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_errors');
    }
};
