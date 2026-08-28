<?php

namespace App\Http\Controllers;

use App\Http\Requests\PizzaPresets\CreatePizzaPreset;
use App\Http\Requests\PizzaPresets\StorePizzaPreset;
use App\Http\Requests\PizzaPresets\UpdatePizzaPreset;
use App\Http\Resources\PizzaPresetResource;
use App\Models\PizzaPreset;
use Arr;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Inertia\ResponseFactory;

class PizzaPresetController extends Controller
{
    /**
     * @param Request $request
     *
     * @return Response|ResponseFactory
     *
     * @throws \Throwable
     */
    public function index(Request $request)
    {
        $items = PizzaPreset::query()
            ->orderByDesc('hot')
            ->latest()
            ->get();

        return inertia('pizzas/Index', ['presets' => $items->toResourceCollection(PizzaPresetResource::class)]);
    }

    /**
     * @param Request $request
     *
     * @return Response|ResponseFactory
     *
     * @throws \Throwable
     */
    public function adminIndex(Request $request)
    {
        $items = PizzaPreset::query()->latest()->get();

        return inertia('pizzas/manage/ListPizzaPreset', ['presets' => $items->toResourceCollection(PizzaPresetResource::class)]);
    }

    /**
     * @param CreatePizzaPreset $request
     *
     * @return Response
     */
    public function create(CreatePizzaPreset $request)
    {
        $presetId = $request->validated('preset_id') ?? null;
        if ($presetId !== null) {
            $pizzaPreset = PizzaPreset::findOrFail($presetId);
            $name = trim($pizzaPreset->name . ' Duplicate');
            $toppingCodes = $pizzaPreset->topping_codes;
            $isHot = (bool)$pizzaPreset->hot;
        } else {
            $name = null;
            $toppingCodes = [];
            $isHot = false;
        }

        return Inertia::render('pizzas/manage/CreatePizzaPreset', [
            'initialName' => $name,
            'initialToppingCodes' => $toppingCodes,
            'initialIsHot' => $isHot,
        ]);
    }

    /**
     * @param StorePizzaPreset $request
     *
     * @return RedirectResponse
     */
    public function store(StorePizzaPreset $request)
    {
        $dataForCreation = $request->validated();
        $dataForCreation['image_path'] = PizzaPreset::getRandomImagePath();
        $dataForCreation['hot'] = (bool)Arr::get($dataForCreation, 'is_hot', false) ?? false;
        unset($dataForCreation['is_hot']);
        PizzaPreset::create($dataForCreation);

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
     * @param PizzaPreset $pizzaPreset
     *
     * @return Response
     */
    public function edit(PizzaPreset $pizzaPreset)
    {
        return Inertia::render('pizzas/manage/UpdatePizzaPreset', [
            'pizzaPreset' => $pizzaPreset->toArray(),
        ]);
    }

    /**
     * @param UpdatePizzaPreset $request
     * @param PizzaPreset $pizzaPreset
     *
     * @return RedirectResponse
     */
    public function update(UpdatePizzaPreset $request, PizzaPreset $pizzaPreset)
    {
        $dataForUpdate = $request->validated();
        $dataForUpdate['hot'] = (bool)Arr::get($dataForUpdate, 'is_hot', false) ?? false;
        unset($dataForUpdate['is_hot']);
        $pizzaPreset->update($dataForUpdate);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Pizza preset
  updated.')]);

        return to_route('pizza-presets.index-admin');
    }

    /**
     * @param PizzaPreset $pizzaPreset
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function destroy(PizzaPreset $pizzaPreset)
    {
        $pizzaPreset->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Pizza preset deleted.')]);

        return inertia()->back();
    }
}
