<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicineIssueItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'medicine_issue_voucher_id', 'medicine_id', 'quantity_issued', 'issue_unit',
        'base_unit_quantity', 'unit_cost', 'total_cost', 'cost_unavailable',
    ];

    protected $casts = [
        'cost_unavailable' => 'boolean',
    ];

    public function voucher(): BelongsTo
    {
        return $this->belongsTo(MedicineIssueVoucher::class, 'medicine_issue_voucher_id');
    }

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function batchConsumptions(): HasMany
    {
        return $this->hasMany(MedicineIssueItemBatch::class);
    }
}
