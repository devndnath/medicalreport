<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicineIssueItemBatch extends Model
{
    protected $fillable = ['medicine_issue_item_id', 'medicine_batch_id', 'quantity', 'unit_cost'];

    public function issueItem(): BelongsTo
    {
        return $this->belongsTo(MedicineIssueItem::class, 'medicine_issue_item_id');
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(MedicineBatch::class, 'medicine_batch_id');
    }
}
