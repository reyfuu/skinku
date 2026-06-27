<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('fileable_type');                 // owning model class, e.g. App\Models\Product
            $table->unsignedBigInteger('fileable_id');        // owning record id
            $table->string('collection')->default('default'); // purpose: product_gallery | payment_proof | ...
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['fileable_type', 'fileable_id', 'collection']);
        });

        $this->backfill();
    }

    /** Move existing product photos & payment proofs into the central files table. */
    private function backfill(): void
    {
        $now = now();

        if (Schema::hasColumn('products', 'images') || Schema::hasColumn('products', 'image_path')) {
            foreach (DB::table('products')->get() as $p) {
                $images = [];
                if (isset($p->images) && $p->images) {
                    $decoded = json_decode($p->images, true);
                    if (is_array($decoded)) {
                        $images = $decoded;
                    }
                }
                if (empty($images) && ! empty($p->image_path)) {
                    $images = [$p->image_path];
                }

                $order = 0;
                foreach ($images as $path) {
                    if (! $path) {
                        continue;
                    }
                    DB::table('files')->insert([
                        'fileable_type' => 'App\\Models\\Product',
                        'fileable_id' => $p->id,
                        'collection' => 'product_gallery',
                        'disk' => 'public',
                        'path' => $path,
                        'sort_order' => $order++,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }

        if (Schema::hasColumn('purchase_orders', 'payment_proof_path')) {
            foreach (DB::table('purchase_orders')->whereNotNull('payment_proof_path')->get() as $po) {
                if (! $po->payment_proof_path) {
                    continue;
                }
                DB::table('files')->insert([
                    'fileable_type' => 'App\\Models\\PurchaseOrder',
                    'fileable_id' => $po->id,
                    'collection' => 'payment_proof',
                    'disk' => 'public',
                    'path' => $po->payment_proof_path,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
