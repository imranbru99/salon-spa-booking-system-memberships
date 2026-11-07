<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Sale;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\SubscriptionPlan;
use App\Models\Tenant;
use App\Models\User;
use App\Services\PDF\PDFService;
use App\Services\Tenancy\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendAndPdfTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Luxe Glow Beauty Spa',
            'slug' => 'luxe-glow',
            'status' => 'active',
            'email' => 'contact@luxeglow.test',
        ]);
        TenantContext::setTenant($this->tenant);

        $this->branch = Branch::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Downtown Luxe',
            'slug' => 'downtown-luxe',
            'is_active' => true,
        ]);
    }

    public function test_saas_public_marketing_pages_load_successfully(): void
    {
        SubscriptionPlan::create([
            'name' => 'Starter Salon',
            'slug' => 'starter',
            'price_monthly' => 49.00,
            'price_yearly' => 490.00,
            'max_branches' => 1,
            'max_staff' => 5,
            'max_bookings_per_month' => 200,
            'is_active' => true,
        ]);

        $responseHome = $this->get('/');
        $responseHome->assertStatus(200);
        $responseHome->assertSee('Complete Business Management Platform');

        $responsePricing = $this->get('/pricing');
        $responsePricing->assertStatus(200);
        $responsePricing->assertSee('Starter Salon');

        $responseFeatures = $this->get('/features');
        $responseFeatures->assertStatus(200);
        $responseFeatures->assertSee('Enterprise Capabilities Directory');
    }

    public function test_tenant_public_salon_pages_load_successfully(): void
    {
        $category = ServiceCategory::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Skin Therapy',
            'slug' => 'skin-therapy',
            'is_active' => true,
        ]);

        Service::create([
            'tenant_id' => $this->tenant->id,
            'category_id' => $category->id,
            'name' => 'Aromatherapy Facial',
            'slug' => 'aromatherapy-facial',
            'price' => 95.00,
            'duration_minutes' => 60,
            'is_active' => true,
        ]);

        $responseSalon = $this->get('/salon');
        $responseSalon->assertStatus(200);
        $responseSalon->assertSee('Reserve Your Appointment');

        $responseServices = $this->get('/salon/services');
        $responseServices->assertStatus(200);
        $responseServices->assertSee('Aromatherapy Facial');

        $responseBooking = $this->get('/salon/booking');
        $responseBooking->assertStatus(200);
        $responseBooking->assertSee('Direct Reservation');
    }

    public function test_customer_portal_displays_customer_data(): void
    {
        $customerUser = User::create([
            'name' => 'Sarah Connor',
            'email' => 'customer@example.test',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'tenant_id' => $this->tenant->id,
        ]);

        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'user_id' => $customerUser->id,
            'first_name' => 'Sarah',
            'last_name' => 'Connor',
            'email' => 'customer@example.test',
        ]);

        $response = $this->actingAs($customerUser)->get('/portal');
        $response->assertStatus(200);
        $response->assertSee('Welcome Back');
        $response->assertSee('Sarah Connor');
    }

    public function test_unicode_pdf_service_generates_invoice_output(): void
    {
        $customer = Customer::create([
            'tenant_id' => $this->tenant->id,
            'first_name' => 'Sophia',
            'last_name' => 'Loren',
            'email' => 'sophia@example.test',
        ]);

        $sale = Sale::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'customer_id' => $customer->id,
            'sale_number' => 'POS-2026-TEST',
            'subtotal' => 150.00,
            'total_amount' => 150.00,
            'paid_amount' => 150.00,
            'status' => 'completed',
            'payment_status' => 'paid',
        ]);

        $invoice = Invoice::create([
            'tenant_id' => $this->tenant->id,
            'sale_id' => $sale->id,
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-TEST-001',
            'invoice_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'subtotal' => 150.00,
            'total_amount' => 150.00,
            'status' => 'issued',
        ]);

        $pdfService = new PDFService();
        $outputHtml = $pdfService->renderInvoiceHtml($invoice);

        $this->assertNotEmpty($outputHtml);
        $this->assertStringContainsString('INV-TEST-001', $outputHtml);
        $this->assertStringContainsString('Sophia Loren', $outputHtml);
    }
}
