<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id(); $table->string('code', 25)->unique(); $table->string('name', 150);
            $table->string('phone', 30)->nullable(); $table->string('skill', 120)->nullable();
            $table->boolean('is_active')->default(true); $table->timestamps();
        });
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 20)->default('staff');
            $table->foreignId('staff_id')->nullable()->unique()->constrained('staff')->nullOnDelete();
        });
        Schema::create('work_locations', function (Blueprint $table) {
            $table->id(); $table->string('name', 150)->unique(); $table->text('address')->nullable();
            $table->boolean('is_active')->default(true); $table->timestamps();
        });
        Schema::create('shifts', function (Blueprint $table) {
            $table->id(); $table->string('name', 60)->unique(); $table->time('start_time');
            $table->time('end_time'); $table->boolean('is_active')->default(true); $table->timestamps();
        });
        Schema::create('availabilities', function (Blueprint $table) {
            $table->id(); $table->foreignId('staff_id')->constrained('staff');
            $table->date('date'); $table->string('status', 20); $table->string('notes', 255)->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps(); $table->unique(['staff_id', 'date']); $table->index(['date', 'status']);
        });
        Schema::create('shift_requirements', function (Blueprint $table) {
            $table->id(); $table->foreignId('location_id')->constrained('work_locations');
            $table->foreignId('shift_id')->constrained('shifts'); $table->unsignedInteger('required_count')->default(0);
            $table->timestamps(); $table->unique(['location_id', 'shift_id']);
        });
        Schema::create('schedule_batches', function (Blueprint $table) {
            $table->id(); $table->date('schedule_date');
            $table->foreignId('location_id')->nullable()->constrained('work_locations')->nullOnDelete();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('required_count')->default(0);
            $table->unsignedInteger('created_count')->default(0);
            $table->unsignedInteger('shortage_count')->default(0);
            $table->text('notes')->nullable(); $table->timestamps();
        });
        Schema::create('schedule_assignments', function (Blueprint $table) {
            $table->id(); $table->foreignId('batch_id')->constrained('schedule_batches');
            $table->date('schedule_date'); $table->foreignId('location_id')->constrained('work_locations');
            $table->foreignId('shift_id')->constrained('shifts');
            $table->foreignId('staff_id')->constrained('staff');
            $table->string('status', 20)->default('draft'); $table->timestamp('published_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps(); $table->index(['staff_id','schedule_date','status'], 'assignment_staff_date_status_idx');
            $table->index(['schedule_date', 'location_id','status'], 'assignment_date_location_status_idx');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('schedule_assignments'); Schema::dropIfExists('schedule_batches');
        Schema::dropIfExists('shift_requirements'); Schema::dropIfExists('availabilities');
        Schema::dropIfExists('shifts'); Schema::dropIfExists('work_locations');
        Schema::table('users', fn (Blueprint $table) => $table->dropConstrainedForeignId('staff_id'));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('role'));
        Schema::dropIfExists('staff');
    }
};
