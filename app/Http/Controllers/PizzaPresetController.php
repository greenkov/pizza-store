<?php

namespace App\Http\Controllers;

use App\Http\Requests\PizzaPresets\CreatePizzaPreset;
use App\Http\Requests\PizzaPresets\UpdatePizzaPreset;
use App\Http\Resources\PizzaPresetResource;
use App\Models\PizzaPreset;
use App\Models\Topping;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PizzaPresetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $items = PizzaPreset::all();

        return inertia('pizzas/Index', ['presets' => $items->toResourceCollection(PizzaPresetResource::class)]);
    }

    public function adminIndex(Request $request)
    {
        $items = PizzaPreset::query()->orderBy('created_at', 'desc')->get();

        return inertia('pizzas/manage/ListPizzaPreset', ['presets' => $items->toResourceCollection(PizzaPresetResource::class)]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('pizzas/manage/CreatePizzaPreset', [
            'toppings' => Topping::orderBy('name')->get(['code', 'name', 'md_cal']),
            'calories' => [
                'md_base_cal' => config('calories.md_base_cal'),
                'sm_coefficient' => config('calories.sm_coefficient'),
                'lg_coefficient' => config('calories.lg_coefficient'),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreatePizzaPreset $request)
    {
        PizzaPreset::create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Pizza preset
  created.')]);

        return to_route('pizza-presets.index-admin');
    }

    /**
     * Display the specified resource.
     */
    public function show(PizzaPreset $pizzaPreset)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PizzaPreset $pizzaPreset)
    {
        return Inertia::render('pizzas/manage/UpdatePizzaPreset', [
            'pizzaPreset' => $pizzaPreset->toArray(),
            'toppings' => Topping::orderBy('name')->get(['code', 'name', 'md_cal']),
            'calories' => [
                'md_base_cal' => config('calories.md_base_cal'),
                'sm_coefficient' => config('calories.sm_coefficient'),
                'lg_coefficient' => config('calories.lg_coefficient'),
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePizzaPreset $request, PizzaPreset $pizzaPreset)
    {
        $pizzaPreset->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Pizza preset
  updated.')]);

        return to_route('pizza-presets.index-admin');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PizzaPreset $pizzaPreset)
    {
        $pizzaPreset->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Pizza preset deleted.')]);

        return inertia()->back();
    }
}
