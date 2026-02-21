<?php

declare(strict_types=1);

namespace Crumbls\Subscriptions\Models;

use Carbon\Carbon;
use Crumbls\Subscriptions\Enums\Interval;
use Crumbls\Subscriptions\Services\Period;
use Crumbls\Subscriptions\Traits\BelongsToPlan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property int $plan_id
 * @property string $slug
 * @property array $name
 * @property array|null $description
 * @property string $value
 * @property int $resettable_period
 * @property Interval|null $resettable_interval
 * @property int $sort_order
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class PlanFeature extends Model implements Sortable
{
    use BelongsToPlan, HasFactory, HasSlug, HasTranslations, SoftDeletes, SortableTrait;

    protected $fillable = [
        'plan_id',
        'slug',
        'name',
        'description',
        'value',
        'resettable_period',
        'resettable_interval',
        'sort_order',
    ];

    public array $translatable = ['name', 'description'];

    public array $sortable = ['order_column_name' => 'sort_order'];

    public function __construct(array $attributes = [])
    {
        $this->setTable(config('subscriptions.tables.plan_features'));
        parent::__construct($attributes);
    }

    protected function casts(): array
    {
        return [
            'plan_id' => 'integer',
            'resettable_period' => 'integer',
            'resettable_interval' => Interval::class,
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleted(fn (PlanFeature $feature) => $feature->usage()->delete());
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->doNotGenerateSlugsOnUpdate()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function usage(): HasMany
    {
        return $this->hasMany(config('subscriptions.models.plan_subscription_usage'), 'feature_id');
    }

    public function getResetDate(Carbon $dateFrom): Carbon
    {
        return (new Period($this->resettable_interval, $this->resettable_period, $dateFrom))
            ->getEndDate();
    }
}
