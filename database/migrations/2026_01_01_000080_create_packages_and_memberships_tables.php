<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Service Packages
        Schema::create('service_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('validity_days')->default(90);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->string('image_url')->nullable();
            $table->text('terms')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'slug']);
        });

        Schema::create('package_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('service_packages')->cascadeOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();

            $table->unique(['package_id', 'service_id']);
        });

        Schema::create('customer_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('package_id')->constrained('service_packages')->cascadeOnDelete();
            $table->date('purchase_date');
            $table->date('expires_at');
            $table->unsignedInteger('total_credits');
            $table->unsignedInteger('remaining_credits');
            $table->string('status', 30)->default('active'); // active, exhausted, expired, cancelled
            $table->timestamps();

            $table->index(['tenant_id', 'customer_id', 'status']);
        });

        Schema::create('package_redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_package_id')->constrained('customer_packages')->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->unsignedInteger('credits_used')->default(1);
            $table->timestamp('redeemed_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Memberships
        Schema::create('membership_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('billing_frequency', 30)->default('monthly'); // monthly, quarterly, annual, lifetime
            $table->decimal('discount_percentage', 5, 2)->default(0); // discount on all services
            $table->unsignedInteger('free_service_credits')->default(0); // monthly complimentary services
            $table->boolean('priority_booking')->default(false);
            $table->boolean('is_active')->default(true);
            $table->json('features')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'slug']);
        });

        Schema::create('customer_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('membership_plan_id')->constrained('membership_plans')->cascadeOnDelete();
            $table->string('status', 30)->default('active'); // active, paused, frozen, cancelled, expired
            $table->date('starts_at');
            $table->date('ends_at');
            $table->boolean('auto_renew')->default(true);
            $table->unsignedInteger('remaining_free_credits')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'customer_id', 'status']);
        });

        Schema::create('membership_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_membership_id')->constrained('customer_memberships')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('transaction_type', 30)->default('subscription'); // subscription, renewal, cancellation_fee
            $table->string('status', 30)->default('successful'); // successful, failed, pending
            $table->string('payment_method')->nullable();
            $table->date('date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_transactions');
        Schema::dropIfExists('customer_memberships');
        Schema::dropIfExists('membership_plans');
        Schema::dropIfExists('package_redemptions');
        Schema::dropIfExists('customer_packages');
        Schema::dropIfExists('package_items');
        Schema::dropIfExists('service_packages');
    }
};
