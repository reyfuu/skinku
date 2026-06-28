<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Services\PurchaseOrderService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ChartDataSeeder extends Seeder
{
    public function run(): void
    {
        // Pastikan DevDataSeeder sudah jalan (agar ada produk & user)
        $this->call(DevDataSeeder::class);

        $svc = app(PurchaseOrderService::class);
        
        $partners = User::whereIn('role', [User::ROLE_DISTRIBUTOR, User::ROLE_RESELLER], 'and', false)->get();
        $products = Product::all();

        if ($partners->isEmpty() || $products->isEmpty()) {
            $this->command->error('Tidak ada partner atau produk. Jalankan DevDataSeeder dulu.');
            return;
        }

        // FIX: Injeksi stok pusat agar transaksi PO (yang butuh banyak stok) tidak gagal
        foreach ($products as $prod) {
            $prod->increment('hq_stock', 10000);
            $before = $prod->hq_stock - 10000;
            DB::table('stock_movements')->insert([
                'product_id' => $prod->id,
                'movement_type' => 'ADJUSTMENT',
                'quantity' => 10000,
                'before_qty' => $before,
                'after_qty' => $prod->hq_stock,
                'notes' => 'Injeksi stok untuk testing grafik',
                'created_at' => Carbon::now()->subDays(15),
            ]);
        }

        $this->command->info('Membuat 50 PO acak dalam 14 hari terakhir untuk data grafik...');

        DB::beginTransaction();
        try {
            for ($i = 0; $i < 50; $i++) {
                $partner = $partners->random();
                
                // Pilih 1 - 3 produk acak
                $items = [];
                foreach ($products->random(rand(1, 3)) as $prod) {
                    $items[] = ['product_id' => $prod->id, 'qty' => rand(5, 50)];
                }

                // Tanggal acak dalam 14 hari terakhir
                $daysAgo = rand(0, 14);
                $createdAt = Carbon::now()->subDays($daysAgo)->subHours(rand(1, 12));

                $po = $svc->createForPartner($partner, $items, $partner->address ?? 'Alamat Dummy', "Pesanan Chart $i");
                
                // Set tanggal mundur
                $po->update(['created_at' => $createdAt, 'updated_at' => $createdAt]);
                
                // Acak status
                $statusRand = rand(1, 10);
                if ($statusRand > 2) { // 80% bayar
                    $svc->setShipping($po, rand(15, 50) * 1000);
                    $po->update(['payment_status' => PurchaseOrder::PAYMENT_PAID, 'paid_at' => $createdAt->copy()->addHours(1)]);
                    
                    if ($statusRand > 4) { // 60% diproses
                        $svc->updateStatus($po->fresh(), PurchaseOrder::STATUS_APPROVED);
                        $svc->updateStatus($po->fresh(), PurchaseOrder::STATUS_PROCESSING);
                        $po->update(['updated_at' => $createdAt->copy()->addHours(2)]);
                    }
                    if ($statusRand > 6) { // 40% dikirim/selesai
                        $svc->updateStatus($po->fresh(), PurchaseOrder::STATUS_SHIPPED);
                        $po->update(['updated_at' => $createdAt->copy()->addDays(1)]);
                    }
                    if ($statusRand > 8) { // 20% selesai
                        $svc->updateStatus($po->fresh(), PurchaseOrder::STATUS_COMPLETED);
                        // Update created_at untuk history stock movement
                        DB::table('stock_movements')->where('reference_type', PurchaseOrder::class)->where('reference_id', $po->id)
                            ->update(['created_at' => $createdAt->copy()->addDays(2)]);
                    }
                } elseif ($statusRand == 1) { // 10% batal
                    $svc->updateStatus($po->fresh(), PurchaseOrder::STATUS_CANCELLED);
                }
            }
            DB::commit();
            $this->command->info('Data grafik berhasil di-generate!');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error: ' . $e->getMessage());
        }
    }
}
