<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\RolePermission;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // The override cache is per-request in production; reset it between tests
        // so saved rows from one test don't leak into the next (single PHPUnit process).
        Permissions::flushCache();
    }

    private function user(string $role): User
    {
        static $n = 0;
        $n++;

        return User::create([
            'name' => "U{$n}", 'fullname' => "U{$n}", 'username' => "{$role}{$n}",
            'email' => "{$role}{$n}@skinku.test", 'password' => Hash::make('secret123'),
            'role' => $role, 'status' => User::STATUS_ACTIVE,
        ]);
    }

    public function test_super_admin_always_has_every_permission(): void
    {
        $su = $this->user(User::ROLE_SUPER_ADMIN);
        foreach (array_keys(Permissions::DEFINITIONS) as $key) {
            $this->assertTrue($su->canDo($key), "super_admin should hold {$key}");
        }
    }

    public function test_defaults_match_business_rules(): void
    {
        $admin = $this->user(User::ROLE_ADMIN);
        $gudang = $this->user(User::ROLE_GUDANG);
        $dist = $this->user(User::ROLE_DISTRIBUTOR);
        $reseller = $this->user(User::ROLE_RESELLER);

        $this->assertFalse($admin->canDo('create_po'));      // admin cannot create PO by default
        $this->assertTrue($dist->canDo('create_po'));        // distributor can
        $this->assertTrue($reseller->canDo('create_po'));    // reseller can
        $this->assertTrue($admin->canDo('manage_products'));
        $this->assertFalse($gudang->canDo('manage_products'));
        $this->assertFalse($admin->canDo('manage_permissions')); // only super_admin
        $this->assertFalse($reseller->canDo('view_reports'));
    }

    public function test_admin_blocked_from_create_po_route_by_default(): void
    {
        $admin = $this->user(User::ROLE_ADMIN);
        $this->actingAs($admin)->get('/purchase-orders/create')->assertForbidden();
    }

    public function test_granting_create_po_to_admin_opens_the_route(): void
    {
        $admin = $this->user(User::ROLE_ADMIN);
        Product::create([
            'name' => 'P', 'sku' => 'SKU-P', 'price_distributor' => 1, 'price_reseller' => 1,
            'price_retail' => 1, 'cogs' => 1, 'hq_stock' => 10, 'status' => Product::STATUS_ACTIVE,
        ]);

        Permissions::save(['admin' => ['create_po' => 'on']]);

        $this->actingAs($admin->fresh())->get('/purchase-orders/create')->assertOk();
    }

    public function test_revoking_manage_products_blocks_admin(): void
    {
        $admin = $this->user(User::ROLE_ADMIN);
        $this->actingAs($admin)->get('/products')->assertOk(); // default allowed

        Permissions::save(['admin' => []]); // nothing checked => manage_products revoked
        Permissions::flushCache();

        $this->actingAs($admin->fresh())->get('/products')->assertForbidden();
    }

    public function test_only_super_admin_reaches_permission_manager(): void
    {
        $this->actingAs($this->user(User::ROLE_SUPER_ADMIN))->get('/permissions')->assertOk();
        $this->actingAs($this->user(User::ROLE_ADMIN))->get('/permissions')->assertForbidden();
    }

    public function test_super_admin_cannot_be_revoked(): void
    {
        // even if a stray row exists, super_admin stays fully privileged
        RolePermission::create(['role' => User::ROLE_SUPER_ADMIN, 'permission_key' => 'manage_users', 'allowed' => false]);
        Permissions::flushCache();

        $su = $this->user(User::ROLE_SUPER_ADMIN);
        $this->assertTrue($su->canDo('manage_users'));
    }
}
