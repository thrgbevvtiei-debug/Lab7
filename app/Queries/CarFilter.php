<?php

namespace App\Queries;

use App\Models\Car;
use Illuminate\Database\Eloquent\Builder;

class CarFilter
{
    // Білий список дозволених колонок для сортування
    private const SORTABLE = [
        'brand'         => 'cars.brand',
        'price'         => 'cars.price',
        'year'          => 'cars.year',
        'featuresCount' => 'features_count', // агрегат withCount()
    ];

    public function apply(array $input): Builder
    {
        $query = Car::query()
            ->withTranslation()
            ->with('features')          // попереднє завантаження опцій (без N+1)
            ->withCount('features');    // кількість опцій одним підзапитом

        // Пошук за основними полями: марка або модель
        $query->when(
            $input['q'] ?? null,
            fn (Builder $q, $value) => $q->where(function (Builder $w) use ($value) {
                $w->where('cars.brand', 'like', "%{$value}%")
                    ->orWhere('cars.model', 'like', "%{$value}%");
            })
        );

        // Пошук за полем пов'язаної таблиці: назва опції
        $query->when(
            $input['featureName'] ?? null,
            fn (Builder $q, $value) => $q->whereHas(
                'features',
                fn (Builder $related) => $related->where('name', 'like', "%{$value}%")
            )
        );

        // Випадаючий список: марка
        $query->when(
            $input['brand'] ?? null,
            fn (Builder $q, $value) => $q->where('cars.brand', $value)
        );

        // Випадаючий список: опція (фільтр за пов'язаною таблицею)
        $query->when(
            $input['feature_id'] ?? null,
            fn (Builder $q, $value) => $q->whereHas(
                'features',
                fn (Builder $related) => $related->whereKey($value)
            )
        );

        return $this->sort($query, $input['sort'] ?? null);
    }

    private function sort(Builder $query, ?string $sort): Builder
    {
        $sort  = $sort ?? '';
        $desc  = str_starts_with($sort, '-');
        $field = $desc ? substr($sort, 1) : $sort;

        if (! isset(self::SORTABLE[$field])) {
            return $query->orderBy('cars.brand')->orderBy('cars.model');
        }

        return $query
            ->orderBy(self::SORTABLE[$field], $desc ? 'desc' : 'asc')
            ->orderBy('cars.id');
    }
}
