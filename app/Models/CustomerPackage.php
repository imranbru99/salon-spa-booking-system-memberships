<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerPackage extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'package_id',
        'purchase_date',
        'expires_at',
        'total_credits',
        'remaining_credits',
        'status',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'expires_at' => 'date',
        'total_credits' => 'integer',
        'remaining_credits' => 'integer',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(ServicePackage::class, 'package_id');
    }

    public function redemptions(): HasMany
    {
        return $this->hasMany(PackageRedemption::class);
    }

    public function isUsable(): bool
    {
        return $this->status === 'active' && $this->remaining_credits > 0 && $this->expires_at->isFuture();
    }
}
