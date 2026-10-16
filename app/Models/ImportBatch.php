<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ImportBatch extends Model
{
    use BelongsToFactory;

    protected $fillable = [
        'factory_id', 'original_filename', 'stored_path', 'uploaded_by', 'status',
        'total_rows', 'valid_rows', 'error_rows', 'duplicate_rows', 'confirmed_at',
    ];

    protected $casts = ['confirmed_at' => 'datetime'];

    public function rows(): HasMany
    {
        return $this->hasMany(ImportBatchRow::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
