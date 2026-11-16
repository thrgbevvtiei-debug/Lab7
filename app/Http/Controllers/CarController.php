<?php

namespace App\Http\Controllers;

use App\Http\Requests\CarRequest;
use App\Models\Car;
use App\Models\Feature;
use App\Queries\CarFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class CarController extends Controller
{
    public function index(Request $request, CarFilter $filter)
    {
        $input = $request->validate([
            'q'           => ['nullable', 'string', 'max:100'],
            'featureName' => ['nullable', 'string', 'max:100'],
            'brand'       => ['nullable', 'string', 'max:50'],
            'feature_id'  => ['nullable', 'integer', 'exists:features,id'],
            'sort'        => ['nullable', 'string', 'max:30'],
        ]);

        // withQueryString() зберігає всі параметри при переході між сторінками
        $cars = $filter->apply($input)
            ->paginate(5)
            ->withQueryString();

        return view('cars.index', [
            'cars'     => $cars,
            'brands'   => Car::query()->distinct()->orderBy('brand')->pluck('brand'),
            'features' => Feature::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function create()
    {
        return view('cars.create', [
            'features' => Feature::orderBy('name')->pluck('name', 'id'),
            'selected' => [],
        ]);
    }

    public function store(CarRequest $request)
    {
        $data = $request->validated();

        $car = Car::create(Arr::except($data, ['features']));
        $car->features()->sync($data['features'] ?? []);

        return redirect()->route('cars.index')->with('success', 'Авто успішно додано.');
    }

    public function show(Car $car)
    {
        $car->load([
            'features',
            'translations' => fn ($q) => $q->whereIn(
                'locale',
                array_unique([app()->getLocale(), config('app.fallback_locale')])
            ),
        ]);

        return view('cars.show', compact('car'));
    }

    public function edit(Car $car)
    {
        $car->load('features');

        return view('cars.edit', [
            'car'      => $car,
            'features' => Feature::orderBy('name')->pluck('name', 'id'),
            'selected' => $car->features->pluck('id')->all(),
        ]);
    }

    public function update(CarRequest $request, Car $car)
    {
        $data = $request->validated();

        $car->update(Arr::except($data, ['features']));
        $car->features()->sync($data['features'] ?? []);

        return redirect()->route('cars.index')->with('success', 'Дані авто оновлено.');
    }

    public function destroy(Car $car)
    {
        $car->delete();

        return redirect()->route('cars.index')->with('success', 'Авто видалено.');
    }
}
