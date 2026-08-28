<?php

namespace App\Http\Controllers;

use App\Http\Requests\PizzaPresets\CreatePizzaPreset;
use App\Http\Requests\PizzaPresets\StorePizzaPreset;
use App\Http\Requests\PizzaPresets\UpdatePizzaPreset;
use App\Http\Resources\PizzaPresetResource;
use App\Models\PizzaPreset;
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
        $toppingCodes = explode(',', $request->validated('topping_codes'));

        return Inertia::render('pizzas/manage/CreatePizzaPreset', [
            'initialToppingCodes' => $toppingCodes,
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
        $pizzaPreset->update($request->validated());

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
