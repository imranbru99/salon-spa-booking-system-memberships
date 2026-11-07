<?php

namespace App\Services\Inventory;

use App\Models\Product;
use App\Models\StockMovement;
use App\Services\Tenancy\TenantContext;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InventoryService
{
    /**
     * Record an auditable stock movement and update the product stock quantity.
     */
    public function recordStockMovement(
        int $productId,
        int $branchId,
        int $quantity,
        string $type,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?string $notes = null
    ): StockMovement {
        return DB::transaction(function () use ($productId, $branchId, $quantity, $type, $referenceType, $referenceId, $notes) {
            $product = Product::lockForUpdate()->findOrFail($productId);
            $stockBefore = $product->stock_quantity;
            $stockAfter = max(0, $stockBefore + $quantity);

            $product->update([
                'stock_quantity' => $stockAfter,
            ]);

            return StockMovement::create([
                'tenant_id' => TenantContext::getTenantId(),
                'branch_id' => $branchId,
                'product_id' => $productId,
                'user_id' => auth()->id(),
                'type' => $type,
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
            ]);
        });
    }

    /**
     * Adjust stock level directly with audit log.
     */
    public function adjustStock(int $productId, int $branchId, int $newQuantity, ?string $notes = null): StockMovement
    {
        $product = Product::findOrFail($productId);
        $diff = $newQuantity - $product->stock_quantity;

        return $this->recordStockMovement(
            productId: $productId,
            branchId: $branchId,
            quantity: $diff,
            type: 'adjustment',
            notes: $notes ?? 'Manual inventory count adjustment'
        );
    }

    /**
     * Get products running below minimum stock alert threshold.
     */
    public function getLowStockProducts(?int $branchId = null): Collection
    {
        return Product::where('is_active', true)
            ->whereColumn('stock_quantity', '<=', 'min_stock_alert')
            ->get();
    }
}
