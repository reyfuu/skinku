<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->unsignedBigInteger('created_by');           // user id who created
            $table->unsignedBigInteger('user_id');              // owner mitra (distributor/reseller)
            $table->string('company_name')->nullable();
            $table->string('user_role')->nullable();            // distributor | reseller
            // draft | pending | approved | processing | shipped | completed | cancelled | deleted
            $table->string('status')->default('pending')->index();
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('shipping_address')->nullable();
            $table->text('notes')->nullable();
            $table->text('revision_notes')->nullable();
            $table->timestamp('completed_at')->nullable();      // guards double-fulfilment
            $table->timestamps();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();

            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
