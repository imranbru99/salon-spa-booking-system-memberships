<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PackageRedemption extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_package_id',
        'appointment_id',
        'service_id',
        'credits_used',
        'redeemed_at',
        'notes',
    ];

    protected $casts = [
        'credits_used' => 'integer',
        'redeemed_at' => 'datetime',
    ];

    public function customerPackage(): BelongsTo
    {
        return $this->belongsTo(CustomerPackage::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
