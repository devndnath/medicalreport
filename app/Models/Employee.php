<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_code', 'name', 'department_id', 'designation',
        'employment_status', 'phone', 'joining_date', 'remarks',
        'needs_id_confirmation',
    ];

    protected $casts = [
        'joining_date' => 'date',
        'needs_id_confirmation' => 'boolean',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function issueVouchers(): HasMany
    {
        return $this->hasMany(MedicineIssueVoucher::class);
    }
}
