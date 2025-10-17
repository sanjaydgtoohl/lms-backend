<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('login_logs', function (Blueprint $table) {
            $table->id();
            
            $table->string('email_attempted'); 
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); 
            $table->ipAddress('ip_address');
            $table->text('user_agent')->nullable();
            $table->enum('status', ['success', 'failure']);
            $table->string('failure_reason')->nullable(); 
            
            // --- LMS Specific Columns ---
            $table->string('auth_method')->default('password'); 
            $table->foreignId('role_id')->nullable()->constrained()->onDelete('set null'); 
            $table->foreignId('impersonator_user_id')->nullable()->constrained('users')->onDelete('cascade'); 
            
            // --- Timestamps ---
            $table->timestamp('login_time');
            $table->timestamp('logout_time')->nullable();
            $table->softDeletes(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_logs');
    }
};
