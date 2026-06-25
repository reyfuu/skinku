<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('user_id')->nullable();  // null = HQ / pusat
            // IN | OUT | ADJUSTMENT | TRANSFER | PO_FULFILLMENT
            $table->string('movement_type')->index();
            $table->integer('quantity');
            $table->integer('before_qty')->default(0);
            $table->integer('after_qty')->default(0);
            $table->string('reference_type')->nullable();       // e.g. purchase_order
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['product_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
