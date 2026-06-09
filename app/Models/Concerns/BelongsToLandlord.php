<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Scopes queries to the authenticated landlord and sets user_id on create.
 * For seeders or cross-tenant admin: Property::withoutLandlordScope()->get()
 * or $query->withoutGlobalScope('landlord').
 */
trait BelongsToLandlord
{
    public static function bootBelongsToLandlord(): void
    {
        static::addGlobalScope('landlord', function (Builder $builder): void {
            if (auth()->check()) {
                $builder->where($builder->getModel()->getTable().'.user_id', auth()->id());
            }
        });

        static::creating(function (Model $model): void {
            if (auth()->check() && empty($model->getAttribute('user_id'))) {
                $model->setAttribute('user_id', auth()->id());
            }
        });
    }

    public function scopeWithoutLandlordScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('landlord');
    }
}
