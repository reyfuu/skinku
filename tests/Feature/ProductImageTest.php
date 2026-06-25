<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'name' => 'Super', 'fullname' => 'Super', 'username' => 'super1',
            'email' => 'super1@skinku.test', 'password' => Hash::make('secret123'),
            'role' => User::ROLE_SUPER_ADMIN, 'status' => User::STATUS_ACTIVE,
        ]);
    }

    private function baseFields(array $override = []): array
    {
        return array_merge([
            'name' => 'X', 'sku' => 'SKU-X', 'price_distributor' => 1, 'price_reseller' => 1,
            'price_retail' => 1, 'cogs' => 1, 'hq_stock' => 1, 'status' => 'active',
        ], $override);
    }

    public function test_create_resizes_and_stores_multiple_images(): void
    {
        Storage::fake('public');

        $this->actingAs($this->admin())->post('/products', $this->baseFields([
            'images' => [
                UploadedFile::fake()->image('a.jpg', 2400, 2400),
                UploadedFile::fake()->image('b.png', 1500, 900),
            ],
        ]))->assertSessionHasNoErrors();

        $p = Product::first();
        $this->assertCount(2, $p->images);

        foreach ($p->images as $path) {
            Storage::disk('public')->assertExists($path);
            $this->assertStringEndsWith('.jpg', $path); // re-encoded
            [$w, $h] = getimagesize(Storage::disk('public')->path($path));
            $this->assertLessThanOrEqual(1280, max($w, $h)); // resized down
        }

        $this->assertEquals($p->images[0], $p->image_path); // primary mirrors first
    }

    public function test_max_eight_images_enforced(): void
    {
        Storage::fake('public');

        $images = [];
        for ($i = 0; $i < 9; $i++) {
            $images[] = UploadedFile::fake()->image("p{$i}.jpg", 400, 400);
        }

        $this->actingAs($this->admin())->post('/products', $this->baseFields(['images' => $images]))
            ->assertSessionHasErrors('images'); // 9 > max:8
    }

    public function test_update_removes_selected_and_appends_new(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $this->actingAs($admin)->post('/products', $this->baseFields([
            'images' => [UploadedFile::fake()->image('a.jpg'), UploadedFile::fake()->image('b.jpg')],
        ]));
        $p = Product::first();
        $old = $p->images;
        $this->assertCount(2, $old);

        $this->actingAs($admin)->put('/products/'.$p->id, $this->baseFields([
            'remove_images' => [$old[0]],
            'images' => [UploadedFile::fake()->image('c.jpg')],
        ]))->assertSessionHasNoErrors();

        $p->refresh();
        $this->assertCount(2, $p->images);       // 2 - 1 removed + 1 added
        $this->assertNotContains($old[0], $p->images);
        Storage::disk('public')->assertMissing($old[0]);
    }
}
