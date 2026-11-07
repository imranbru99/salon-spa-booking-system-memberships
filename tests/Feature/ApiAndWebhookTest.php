<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Payment;
use App\Models\Sale;
use App\Models\Service;
use App\Models\Tenant;
use App\Services\Tenancy\TenantContext;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiAndWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Branch $branch;
    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create(['name' => 'API Test Salon', 'slug' => 'api-salon', 'status' => 'active']);
        TenantContext::setTenant($this->tenant);

        $this->branch = Branch::create(['name' => 'API Branch', 'slug' => 'api-branch', 'is_active' => true]);
        $this->service = Service::create([
            'name' => 'Express Blowout',
            'slug' => 'express-blowout',
            'price' => 45.00,
            'duration_minutes' => 30,
            'is_active' => true,
        ]);
    }

    public function test_api_health_endpoint(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200)
            ->assertJson(['status' => 'healthy']);
    }

    public function test_api_services_endpoint_returns_data(): void
    {
        $response = $this->getJson('/api/v1/services');

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_api_availability_endpoint_computes_slots(): void
    {
        $date = Carbon::tomorrow()->toDateString();
        $response = $this->getJson("/api/v1/availability?branch_id={$this->branch->id}&service_id={$this->service->id}&date={$date}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true);
    }

    public function test_payment_webhook_idempotency(): void
    {
        $sale = Sale::create([
            'branch_id' => $this->branch->id,
            'sale_number' => 'POS-WEBHOOK-TEST',
            'subtotal' => 50.00,
            'total_amount' => 50.00,
            'paid_amount' => 0.00,
        ]);

        $payment = Payment::create([
            'payable_type' => Sale::class,
            'payable_id' => $sale->id,
            'amount' => 50.00,
            'method' => 'stripe',
            'transaction_reference' => 'TXN-WEBHOOK-12345',
            'status' => 'pending',
        ]);

        // First webhook call: marks payment completed
        $response1 = $this->postJson('/api/v1/webhooks/payment', [
            'provider' => 'stripe',
            'transaction_reference' => 'TXN-WEBHOOK-12345',
        ]);

        $response1->assertStatus(200)
            ->assertJsonPath('result.status', 'updated');

        $payment->refresh();
        $this->assertEquals('completed', $payment->status);

        // Duplicate webhook call: returns already_processed
        $response2 = $this->postJson('/api/v1/webhooks/payment', [
            'provider' => 'stripe',
            'transaction_reference' => 'TXN-WEBHOOK-12345',
        ]);

        $response2->assertStatus(200)
            ->assertJsonPath('result.status', 'already_processed');
    }

    public function test_ai_assistant_endpoint(): void
    {
        $response = $this->postJson('/api/v1/ai/assistant', [
            'prompt' => 'How much revenue did my salon generate this month?',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['query', 'response']]);
    }
}
