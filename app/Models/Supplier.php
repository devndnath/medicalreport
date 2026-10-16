<?php

namespace App\Models;

use App\Models\Concerns\BelongsToFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use BelongsToFactory, HasFactory, SoftDeletes;

    protected $fillable = [
        'factory_id', 'name', 'contact_person', 'phone', 'email', 'address',
        'business_details', 'status', 'remarks',
    ];

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function totalPurchaseValue(): float
    {
        return (float) $this->purchases()->where('status', 'confirmed')->sum('grand_total');
    }

    public function outstandingBalance(): float
    {
        return (float) $this->purchases()->where('status', 'confirmed')->sum('outstanding_amount');
    }
}
