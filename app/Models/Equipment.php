<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Equipment extends Model
{
    use HasFactory, BelongsToTenant;

    protected $table = 'equipment';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'name',
        'code',
        'serial_number',
        'status',
        'maintenance_notes',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
