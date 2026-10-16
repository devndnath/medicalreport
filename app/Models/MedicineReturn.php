<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicineReturn extends Model
{
    use BelongsToFactory, HasFactory;

    protected $table = 'medicine_returns';

    protected $fillable = [
        'factory_id', 'return_number', 'return_date', 'medicine_issue_item_id', 'medicine_id',
        'medicine_batch_id', 'quantity', 'condition', 'received_by', 'reason',
    ];

    protected $casts = ['return_date' => 'date'];

    public function medicine(): BelongsTo
    {
        return $this->belongsTo(Medicine::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(MedicineBatch::class, 'medicine_batch_id');
    }

    public function issueItem(): BelongsTo
    {
        return $this->belongsTo(MedicineIssueItem::class, 'medicine_issue_item_id');
    }
}
