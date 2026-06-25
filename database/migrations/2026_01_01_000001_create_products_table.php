<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique();
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();        // emoji / short label / legacy
            $table->string('image_path')->nullable();    // stored relative path on the public disk
            $table->decimal('price_distributor', 15, 2)->default(0);
            $table->decimal('price_reseller', 15, 2)->default(0);
            $table->decimal('price_retail', 15, 2)->default(0);
            $table->decimal('cogs', 15, 2)->default(0);  // harga pokok / hpp
            $table->integer('hq_stock')->default(0);
            $table->string('status')->default('active')->index(); // active | inactive | deleted
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
