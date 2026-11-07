<?php

namespace App\Services\POS;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\Finance\CommissionService;
use App\Services\Inventory\InventoryService;
use App\Services\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class POSService
{
    /**
     * Process a checkout sale in the POS terminal.
     */
    public function processSale(array $data, ?int $cashierId = null): Sale
    {
        return DB::transaction(function () use ($data, $cashierId) {
            $tenantId = TenantContext::getTenantId();
            $branchId = $data['branch_id'];
            $customerId = $data['customer_id'] ?? null;
            $items = $data['items']; // array of items: [type, id, name, quantity, unit_price, staff_id]

            $subtotal = 0.0;
            foreach ($items as $item) {
                $subtotal += ((float) $item['unit_price']) * ((float) ($item['quantity'] ?? 1));
            }

            $discountAmount = (float) ($data['discount_amount'] ?? 0.0);
            $taxAmount = (float) ($data['tax_amount'] ?? 0.0);
            $tipAmount = (float) ($data['tip_amount'] ?? 0.0);
            $totalAmount = max(0.0, $subtotal - $discountAmount + $taxAmount + $tipAmount);
            $paidAmount = (float) ($data['paid_amount'] ?? $totalAmount);

            $saleNumber = 'POS-' . date('Ymd') . '-' . strtoupper(Str::random(5));

            $sale = Sale::create([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'customer_id' => $customerId,
                'staff_id' => $data['staff_id'] ?? null,
                'cash_register_id' => $data['cash_register_id'] ?? null,
                'sale_number' => $saleNumber,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'tip_amount' => $tipAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'payment_status' => $paidAmount >= $totalAmount ? 'paid' : ($paidAmount > 0 ? 'partially_paid' : 'unpaid'),
                'status' => 'completed',
                'notes' => $data['notes'] ?? null,
            ]);

            $inventoryService = new InventoryService();
            $commissionService = new CommissionService();

            foreach ($items as $item) {
                $itemType = $item['type'] ?? 'service';
                $itemId = $item['id'] ?? null;
                $qty = (float) ($item['quantity'] ?? 1);
                $unitPrice = (float) $item['unit_price'];
                $itemTotal = $unitPrice * $qty;

                $saleItem = SaleItem::create([
                    'sale_id' => $sale->id,
                    'item_type' => $itemType,
                    'item_id' => $itemId,
                    'item_name' => $item['name'],
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'discount_amount' => (float) ($item['discount_amount'] ?? 0.0),
                    'tax_amount' => (float) ($item['tax_amount'] ?? 0.0),
                    'total_amount' => $itemTotal,
                    'staff_id' => $item['staff_id'] ?? null,
                ]);

                // If product sale, deduct stock automatically
                if ($itemType === 'product' && $itemId) {
                    $inventoryService->recordStockMovement(
                        productId: $itemId,
                        branchId: $branchId,
                        quantity: -(int) $qty,
                        type: 'sale',
                        referenceType: Sale::class,
                        referenceId: $sale->id,
                        notes: "POS Checkout #{$saleNumber}"
                    );
                }

                // If staff commission applies for product
                if ($saleItem->staff_id) {
                    $commissionService->calculateProductCommission($saleItem);
                }
            }

            // Record payment if paid
            if ($paidAmount > 0) {
                Payment::create([
                    'tenant_id' => $tenantId,
                    'branch_id' => $branchId,
                    'payable_type' => Sale::class,
                    'payable_id' => $sale->id,
                    'amount' => $paidAmount,
                    'currency' => $data['currency'] ?? 'USD',
                    'method' => $data['payment_method'] ?? 'cash',
                    'transaction_reference' => $data['transaction_reference'] ?? ('TXN-' . strtoupper(Str::random(8))),
                    'status' => 'completed',
                    'notes' => 'POS payment',
                ]);
            }

            // Generate tax invoice
            $invoiceNumber = 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(5));
            Invoice::create([
                'tenant_id' => $tenantId,
                'branch_id' => $branchId,
                'customer_id' => $customerId,
                'sale_id' => $sale->id,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->toDateString(),
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'status' => $paidAmount >= $totalAmount ? 'paid' : 'partial',
            ]);

            return $sale->load(['items', 'customer', 'branch', 'payments', 'invoices']);
        });
    }

    /**
     * Process refund for a sale.
     */
    public function processRefund(Sale $sale, ?float $amount = null, ?string $reason = null): Sale
    {
        return DB::transaction(function () use ($sale, $amount, $reason) {
            $refundAmount = $amount ?? (float) $sale->paid_amount;

            $sale->update([
                'status' => 'refunded',
                'payment_status' => 'refunded',
                'notes' => trim($sale->notes . " | Refunded: {$refundAmount}. Reason: {$reason}"),
            ]);

            Payment::create([
                'tenant_id' => $sale->tenant_id,
                'branch_id' => $sale->branch_id,
                'payable_type' => Sale::class,
                'payable_id' => $sale->id,
                'amount' => -$refundAmount,
                'currency' => 'USD',
                'method' => 'cash',
                'transaction_reference' => 'REF-' . strtoupper(Str::random(8)),
                'status' => 'completed',
                'notes' => 'Refund processed: ' . $reason,
            ]);

            return $sale;
        });
    }
}
