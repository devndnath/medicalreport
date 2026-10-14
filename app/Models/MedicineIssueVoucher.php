<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicineIssueVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_number', 'issued_at', 'employee_id', 'department_id', 'symptoms',
        'issued_by', 'receiver_acknowledged', 'total_cost', 'has_cost_unavailable_lines',
        'status', 'reversed_by_voucher_id', 'remarks',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'receiver_acknowledged' => 'boolean',
        'has_cost_unavailable_lines' => 'boolean',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(MedicineIssueItem::class);
    }
}
