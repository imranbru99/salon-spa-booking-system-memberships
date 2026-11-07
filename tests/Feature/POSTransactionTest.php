<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Service;
use App\Models\Tenant;
use App\Services\POS\POSService;
use App\Services\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class POSTransactionTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Branch $branch;
    protected Customer $customer;
    protected Product $product;
    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create(['name' => 'Elite Salon', 'slug' => 'elite', 'status' => 'active']);
        TenantContext::setTenant($this->tenant);

        $this->branch = Branch::create(['name' => 'Main Branch', 'slug' => 'main', 'is_active' => true]);

        $this->customer = Customer::create([
            'first_name' => 'Sophie',
            'last_name' => 'Turner',
            'email' => 'sophie@test.test',
        ]);

        $this->product = Product::create([
            'name' => 'Botanical Shampoo',
            'slug' => 'botanical-shampoo',
            'sku' => 'BOT-SHP-01',
            'purchase_price' => 15.00,
            'selling_price' => 35.00,
            'stock_quantity' => 20,
            'min_stock_alert' => 5,
            'is_active' => true,
        ]);

        $this->service = Service::create([
            'name' => 'Classic Haircut',
            'slug' => 'classic-cut',
            'price' => 65.00,
            'duration_minutes' => 45,
            'is_active' => true,
        ]);
    }

    public function test_pos_checkout_creates_sale_deducts_inventory_and_generates_invoice(): void
    {
        $posService = new POSService();

        $sale = $posService->processSale([
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'items' => [
                [
                    'type' => 'service',
                    'id' => $this->service->id,
                    'name' => $this->service->name,
                    'quantity' => 1,
                    'unit_price' => 65.00,
                ],
                [
                    'type' => 'product',
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'quantity' => 2,
                    'unit_price' => 35.00,
                ],
            ],
            'discount_amount' => 10.00,
            'tax_amount' => 10.63,
            'tip_amount' => 15.00,
            'paid_amount' => 150.63,
            'payment_method' => 'card',
        ]);

        $this->assertNotNull($sale->id);
        $this->assertEquals(135.00, $sale->subtotal); // 65 + (35 * 2)
        $this->assertEquals(150.63, $sale->total_amount); // 135 - 10 + 10.63 + 15
        $this->assertEquals('paid', $sale->payment_status);

        // Verify stock deducted by 2
        $this->product->refresh();
        $this->assertEquals(18, $this->product->stock_quantity);

        // Verify invoice was created
        $invoice = Invoice::where('sale_id', $sale->id)->first();
        $this->assertNotNull($invoice);
        $this->assertEquals(150.63, $invoice->total_amount);
        $this->assertEquals('paid', $invoice->status);
    }

    public function test_pos_refund_processes_negative_payment_and_updates_status(): void
    {
        $posService = new POSService();

        $sale = $posService->processSale([
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'items' => [
                ['type' => 'service', 'id' => $this->service->id, 'name' => $this->service->name, 'quantity' => 1, 'unit_price' => 65.00],
            ],
            'paid_amount' => 65.00,
            'payment_method' => 'cash',
        ]);

        $refundedSale = $posService->processRefund($sale, 65.00, 'Customer dissatisfied');

        $this->assertEquals('refunded', $refundedSale->status);
        $this->assertEquals('refunded', $refundedSale->payment_status);

        // Verify negative payment record
        $negPayment = Payment::where('payable_id', $sale->id)->where('amount', '<', 0)->first();
        $this->assertNotNull($negPayment);
        $this->assertEquals(-65.00, $negPayment->amount);
    }
}
