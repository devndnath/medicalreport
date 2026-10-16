<?php

namespace App\Models\Scopes;

use App\Support\CurrentFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class FactoryScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if ($factoryId = CurrentFactory::id()) {
            $builder->where($model->getTable().'.factory_id', $factoryId);
        }
    }
}
