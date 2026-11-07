<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaffProfile extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'branch_id',
        'employee_code',
        'job_title',
        'bio',
        'commission_rate',
        'employment_type',
        'base_salary',
        'joining_date',
        'status',
        'emergency_contact',
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'base_salary' => 'decimal:2',
        'joining_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_staff', 'staff_id', 'service_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(StaffSchedule::class, 'staff_id');
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(StaffLeave::class, 'staff_id');
    }

    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(StaffAttendanceRecord::class, 'staff_id');
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class, 'staff_id');
    }

    public function payrollRecords(): HasMany
    {
        return $this->hasMany(PayrollRecord::class, 'staff_id');
    }

    public function appointmentServices(): HasMany
    {
        return $this->hasMany(AppointmentService::class, 'staff_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'staff_id');
    }
}
