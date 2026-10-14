<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportBatchRow extends Model
{
    protected $fillable = [
        'import_batch_id', 'source_sheet', 'source_row', 'raw_data', 'row_status', 'errors',
    ];

    protected $casts = [
        'raw_data' => 'array',
        'errors' => 'array',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(ImportBatch::class, 'import_batch_id');
    }
}
