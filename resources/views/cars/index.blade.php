@extends('layouts.app')

@section('title', 'Автомобілі')

@section('content')
    @php
        // Посилання для сортування: повторний клік міняє напрямок, page скидається
        $currentSort = request('sort');
        $sortLink = function (string $field, string $label) use ($currentSort) {
            $next  = $currentSort === $field ? '-' . $field : $field;
            $arrow = $currentSort === $field ? ' ↑' : ($currentSort === '-' . $field ? ' ↓' : '');
            $url   = route('cars.index', array_merge(request()->except('sort', 'page'), ['sort' => $next]));

            return '<a href="' . e($url) . '" class="text-white text-decoration-none">' . e($label . $arrow) . '</a>';
        };
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3">Автомобілі в наявності</h1>
        <a href="{{ route('cars.create') }}" class="btn btn-primary">+ Додати авто</a>
    </div>

    <form method="GET" action="{{ route('cars.index') }}" class="row g-2 mb-3">
        <div class="col-md-3">
            <label for="q" class="form-label small mb-0">Марка / модель</label>
            <input type="text" id="q" name="q" class="form-control"
                   value="{{ request('q') }}" placeholder="Пошук за маркою чи моделлю">
        </div>
        <div class="col-md-3">
            <label for="featureName" class="form-label small mb-0">Назва опції</label>
            <input type="text" id="featureName" name="featureName" class="form-control"
                   value="{{ request('featureName') }}" placeholder="Наприклад, шкіряний">
        </div>
        <div class="col-md-2">
            <label for="brand" class="form-label small mb-0">Марка</label>
            <select id="brand" name="brand" class="form-select" onchange="this.form.submit()">
                <option value="">Усі марки</option>
                @foreach ($brands as $brand)
                    <option value="{{ $brand }}" @selected(request('brand') === $brand)>{{ $brand }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label for="feature_id" class="form-label small mb-0">Опція</label>
            <select id="feature_id" name="feature_id" class="form-select" onchange="this.form.submit()">
                <option value="">Усі опції</option>
                @foreach ($features as $id => $name)
                    <option value="{{ $id }}" @selected(request('feature_id') == $id)>{{ $name }}</option>
                @endforeach
            </select>
        </div>
        <input type="hidden" name="sort" value="{{ request('sort') }}">
        <div class="col-md-2 d-flex align-items-end gap-2">
            <button type="submit" class="btn btn-secondary">Застосувати</button>
            <a href="{{ route('cars.index') }}" class="btn btn-outline-secondary">Скинути</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle border">
            <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>{!! $sortLink('brand', 'Марка') !!}</th>
                <th>Модель</th>
                <th>Назва ({{ strtoupper(app()->getLocale()) }})</th>
                <th>{!! $sortLink('year', 'Рік') !!}</th>
                <th>{!! $sortLink('price', 'Ціна, $') !!}</th>
                <th>Опції</th>
                <th>{!! $sortLink('featuresCount', 'К-сть опцій') !!}</th>
                <th>Дії</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($cars as $car)
                <tr>
                    <td>{{ $cars->firstItem() + $loop->index }}</td>
                    <td>{{ $car->brand }}</td>
                    <td>
                        <a href="{{ route('cars.show', $car) }}" class="fw-bold text-decoration-none">
                            {{ $car->model }}
                        </a>
                    </td>
                    <td>
                        {{ $car->title }}
                        @if ($car->isFallback())
                            <span class="badge bg-warning text-dark">{{ strtoupper(config('app.fallback_locale')) }}</span>
                        @endif
                    </td>
                    <td>{{ $car->year }}</td>
                    <td>{{ number_format($car->price, 0, '.', ' ') }}</td>
                    <td>
                        @forelse ($car->features as $feature)
                            <span class="badge bg-secondary">{{ $feature->name }}</span>
                        @empty
                            —
                        @endforelse
                    </td>
                    <td>{{ $car->features_count }}</td>
                    <td>
                        <a href="{{ route('cars.edit', $car) }}" class="btn btn-sm btn-outline-primary">Редагувати</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center text-muted">Автомобілі не знайдено.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-center mt-3">
        {{ $cars->links() }}
    </div>
@endsection
