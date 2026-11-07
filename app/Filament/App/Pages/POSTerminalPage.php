<?php

namespace App\Filament\App\Pages;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Service;
use App\Models\StaffProfile;
use App\Services\Modules\ModuleService;
use App\Services\POS\POSService;
use App\Services\Tenancy\TenantContext;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class POSTerminalPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calculator';

    protected static ?string $navigationLabel = 'Point of Sale (POS)';

    protected static ?string $navigationGroup = 'Point of Sale & Billing';

    protected static string $view = 'filament.app.pages.pos-terminal';

    public ?int $selectedBranchId = null;
    public ?int $selectedCustomerId = null;
    public ?int $selectedStaffId = null;
    public string $searchQuery = '';
    public string $activeCategory = 'all'; // all, services, products

    public array $cart = [];
    public float $discount = 0.0;
    public float $taxRate = 8.5; // percent
    public float $tip = 0.0;
    public string $paymentMethod = 'card';

    public static function canViewAny(): bool
    {
        return ModuleService::isEnabled('pos');
    }

    public function mount(): void
    {
        $tenant = TenantContext::getTenant();
        $firstBranch = Branch::first();
        $this->selectedBranchId = $firstBranch?->id;
    }

    public function addToCart(string $type, int $id, string $name, float $price): void
    {
        $key = "{$type}_{$id}";
        if (isset($this->cart[$key])) {
            $this->cart[$key]['quantity'] += 1;
        } else {
            $this->cart[$key] = [
                'type' => $type,
                'id' => $id,
                'name' => $name,
                'price' => $price,
                'quantity' => 1,
                'staff_id' => $this->selectedStaffId,
            ];
        }

        Notification::make()->title("Added to Cart: {$name}")->success()->send();
    }

    public function updateQuantity(string $key, int $qty): void
    {
        if ($qty <= 0) {
            unset($this->cart[$key]);
        } else {
            $this->cart[$key]['quantity'] = $qty;
        }
    }

    public function removeItem(string $key): void
    {
        unset($this->cart[$key]);
    }

    public function getSubtotalProperty(): float
    {
        $sum = 0.0;
        foreach ($this->cart as $item) {
            $sum += ((float) $item['price']) * ((int) $item['quantity']);
        }
        return $sum;
    }

    public function getTaxAmountProperty(): float
    {
        return round(($this->subtotal - $this->discount) * ($this->taxRate / 100), 2);
    }

    public function getTotalProperty(): float
    {
        return max(0.0, round($this->subtotal - $this->discount + $this->taxAmount + $this->tip, 2));
    }

    public function processCheckout(): void
    {
        if (empty($this->cart)) {
            Notification::make()->title('Cart is empty')->warning()->send();
            return;
        }

        if (!$this->selectedBranchId) {
            Notification::make()->title('Please select a branch')->warning()->send();
            return;
        }

        $items = [];
        foreach ($this->cart as $item) {
            $items[] = [
                'type' => $item['type'],
                'id' => $item['id'],
                'name' => $item['name'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['price'],
                'staff_id' => $item['staff_id'] ?? $this->selectedStaffId,
            ];
        }

        $posService = new POSService();
        $sale = $posService->processSale([
            'branch_id' => $this->selectedBranchId,
            'customer_id' => $this->selectedCustomerId,
            'staff_id' => $this->selectedStaffId,
            'items' => $items,
            'discount_amount' => $this->discount,
            'tax_amount' => $this->taxAmount,
            'tip_amount' => $this->tip,
            'paid_amount' => $this->total,
            'payment_method' => $this->paymentMethod,
            'notes' => 'Processed via Filament POS Terminal',
        ]);

        $this->cart = [];
        $this->discount = 0.0;
        $this->tip = 0.0;

        Notification::make()
            ->title('Checkout Completed Successfully')
            ->body("Sale #{$sale->sale_number} processed for \${$sale->total_amount}. Tax invoice generated.")
            ->success()
            ->send();
    }
}
