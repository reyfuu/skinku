<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property string $action
 * @property string|null $target_type
 * @property int|null $target_id
 * @property int|null $target_user_id
 * @property string|null $target_email
 * @property int|null $performed_by
 * @property string|null $performed_by_email
 * @property array<array-key, mixed>|null $before_data
 * @property array<array-key, mixed>|null $after_data
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property-read \App\Models\User|null $performer
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereAfterData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereBeforeData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog wherePerformedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog wherePerformedByEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereTargetEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereTargetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereTargetType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereTargetUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditLog whereUserAgent($value)
 */
	class AuditLog extends \Eloquent {}
}

namespace App\Models{
/**
 * Central store for every uploaded file/image. Linked polymorphically to the
 * owning record (product, purchase order, …) via fileable_type + fileable_id.
 *
 * @property int $id
 * @property string $fileable_type
 * @property int $fileable_id
 * @property string $collection
 * @property string $disk
 * @property string $path
 * @property string|null $original_name
 * @property string|null $mime_type
 * @property int|null $size
 * @property int $sort_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $fileable
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereCollection($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereDisk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereFileableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereFileableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereMimeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereOriginalName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereSortOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|File whereUpdatedAt($value)
 */
	class File extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int $product_id
 * @property int $quantity
 * @property int $minimum_stock
 * @property \Illuminate\Support\Carbon|null $last_updated
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory whereLastUpdated($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory whereMinimumStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Inventory whereUserId($value)
 */
	class Inventory extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string|null $contact_person
 * @property string|null $phone
 * @property string $email
 * @property string|null $address
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\File> $files
 * @property-read int|null $files_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterDistributor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterDistributor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterDistributor onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterDistributor query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterDistributor whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterDistributor whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterDistributor whereContactPerson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterDistributor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterDistributor whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterDistributor whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterDistributor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterDistributor whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterDistributor wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterDistributor whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterDistributor whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterDistributor withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MasterDistributor withoutTrashed()
 */
	class MasterDistributor extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $sku
 * @property string|null $category
 * @property string|null $description
 * @property string|null $images
 * @property numeric $price_distributor
 * @property numeric $price_reseller
 * @property numeric $price_retail
 * @property numeric $cogs
 * @property int $hq_stock
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\File> $files
 * @property-read int|null $files_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCogs($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereHqStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereImages($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePriceDistributor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePriceReseller($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePriceRetail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product withoutTrashed()
 */
	class Product extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $po_number
 * @property int $created_by
 * @property int $user_id
 * @property string|null $company_name
 * @property string|null $user_role
 * @property string $status
 * @property numeric $subtotal
 * @property numeric $discount
 * @property numeric $shipping_cost
 * @property numeric $total_amount
 * @property string $payment_status
 * @property string|null $payment_note
 * @property \Illuminate\Support\Carbon|null $paid_at
 * @property int|null $payment_verified_by
 * @property string|null $shipping_address
 * @property string|null $notes
 * @property string|null $revision_notes
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $deleted_by
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\File> $files
 * @property-read int|null $files_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PurchaseOrderItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder wherePaidAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder wherePaymentNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder wherePaymentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder wherePaymentVerifiedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder wherePoNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereRevisionNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereShippingAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereShippingCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereUserRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder withoutTrashed()
 */
	class PurchaseOrder extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $purchase_order_id
 * @property int $product_id
 * @property string $product_name
 * @property string|null $sku
 * @property int $qty
 * @property numeric $unit_price
 * @property numeric $total_price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\PurchaseOrder|null $purchaseOrder
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereProductName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem wherePurchaseOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereSku($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereTotalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereUpdatedAt($value)
 */
	class PurchaseOrderItem extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $role
 * @property string $permission_key
 * @property bool $allowed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RolePermission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RolePermission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RolePermission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RolePermission whereAllowed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RolePermission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RolePermission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RolePermission wherePermissionKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RolePermission whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RolePermission whereUpdatedAt($value)
 */
	class RolePermission extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $product_id
 * @property int|null $user_id
 * @property string $movement_type
 * @property int $quantity
 * @property int $before_qty
 * @property int $after_qty
 * @property string|null $reference_type
 * @property int|null $reference_id
 * @property string|null $notes
 * @property int|null $created_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property-read \App\Models\Product|null $product
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereAfterQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereBeforeQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereMovementType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereReferenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereReferenceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockMovement whereUserId($value)
 */
	class StockMovement extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string|null $uid
 * @property string|null $name
 * @property string|null $fullname
 * @property string $email
 * @property string $username
 * @property string $password
 * @property string $role
 * @property string|null $company_name
 * @property string|null $phone
 * @property string|null $address
 * @property string $status
 * @property string|null $region
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $disabled_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Inventory> $inventory
 * @property-read int|null $inventory_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PurchaseOrder> $purchaseOrders
 * @property-read int|null $purchase_orders_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereDisabledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFullname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutTrashed()
 */
	class User extends \Eloquent {}
}

