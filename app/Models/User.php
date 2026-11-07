<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'role',
        'phone',
        'avatar_url',
        'branch_id',
        'is_active',
        'preferred_locale',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function staffProfile(): HasOne
    {
        return $this->hasOne(StaffProfile::class);
    }

    public function customerProfile(): HasOne
    {
        return $this->hasOne(Customer::class);
    }

    // Role verification helpers
    public function isSuperAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'platform_admin']);
    }

    public function isBusinessOwner(): bool
    {
        return in_array($this->role, ['business_owner', 'business_admin']);
    }

    public function isBranchManager(): bool
    {
        return in_array($this->role, ['branch_manager', 'salon_manager', 'spa_manager']);
    }

    public function isReceptionist(): bool
    {
        return in_array($this->role, ['receptionist', 'front_desk']);
    }

    public function isStaff(): bool
    {
        return in_array($this->role, [
            'stylist', 'hairdresser', 'beautician', 'makeup_artist',
            'nail_technician', 'massage_therapist', 'spa_therapist', 'therapist'
        ]);
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    /**
     * Determine if user can access the given Filament panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $panelId = $panel->getId();

        return match ($panelId) {
            'admin' => $this->isSuperAdmin(),
            'app' => $this->isSuperAdmin() || $this->isBusinessOwner() || $this->isBranchManager() || in_array($this->role, ['cashier', 'inventory_manager', 'accountant', 'hr_manager']),
            'staff' => $this->isSuperAdmin() || $this->isStaff() || $this->isBusinessOwner() || $this->isBranchManager(),
            'reception' => $this->isSuperAdmin() || $this->isReceptionist() || $this->isBusinessOwner() || $this->isBranchManager(),
            default => false,
        };
    }
}
