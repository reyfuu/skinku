<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('role')->index();           // admin | gudang | distributor | reseller
            $table->string('permission_key')->index();  // e.g. create_po, manage_products
            $table->boolean('allowed')->default(false);
            $table->timestamps();

            $table->unique(['role', 'permission_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};
