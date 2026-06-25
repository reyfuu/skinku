<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action')->index();
            $table->string('target_type')->nullable();   // user | product | purchase_order | inventory | ...
            $table->unsignedBigInteger('target_id')->nullable();
            $table->unsignedBigInteger('target_user_id')->nullable();
            $table->string('target_email')->nullable();
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->string('performed_by_email')->nullable();
            $table->json('before_data')->nullable();
            $table->json('after_data')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
