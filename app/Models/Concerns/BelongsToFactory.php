<?php

namespace App\Models\Concerns;

use App\Models\Factory;
use App\Models\Scopes\FactoryScope;
use App\Support\CurrentFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Applied to every model that belongs to one factory (medicines,
 * employees, departments, purchases, issue vouchers, stock
 * transactions, etc.). Two things happen automatically:
 *
 * 1. Every query against the model is filtered to the current factory
 *    (App\Support\CurrentFactory), so a store officer at Factory A can
 *    never see or guess their way into Factory B's data, and a Super
 *    Admin only ever sees the one factory they are currently viewing.
 * 2. factory_id is stamped onto new records automatically if the caller
 *    didn't set it, from the current factory context.
 *
 * NOT applied to App\Models\User — see that model for why.
 */
trait BelongsToFactory
{
    public static function bootBelongsToFactory(): void
    {
        static::addGlobalScope(new FactoryScope);

        static::creating(function ($model) {
            if (! $model->factory_id && $factoryId = CurrentFactory::id()) {
                $model->factory_id = $factoryId;
            }
        });
    }

    /**
     * Named operatingFactory() rather than factory() because every model
     * using this trait also uses Eloquent's own HasFactory trait (for
     * test/database factories), whose static factory() method would
     * otherwise collide with this relation and cause a fatal error.
     */
    public function operatingFactory(): BelongsTo
    {
        return $this->belongsTo(Factory::class, 'factory_id');
    }
}
