<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'sale_price',
        'duration_minutes',
        'buffer_before',
        'buffer_after',
        'required_room',
        'required_equipment',
        'deposit_required',
        'deposit_amount',
        'commission_rate',
        'is_active',
        'is_featured',
        'image_url',
        'preparation_instructions',
        'aftercare_instructions',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'duration_minutes' => 'integer',
        'buffer_before' => 'integer',
        'buffer_after' => 'integer',
        'required_room' => 'boolean',
        'required_equipment' => 'boolean',
        'deposit_required' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(StaffProfile::class, 'service_staff', 'service_id', 'staff_id')
            ->withPivot('custom_price', 'custom_duration');
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'service_branches', 'service_id', 'branch_id')
            ->withPivot('custom_price');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function getEffectivePriceAttribute(): float
    {
        return (float) ($this->sale_price && $this->sale_price > 0 ? $this->sale_price : $this->price);
    }

    public function getTotalDurationAttribute(): int
    {
        return (int) ($this->duration_minutes + $this->buffer_before + $this->buffer_after);
    }
}
