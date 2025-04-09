<?php

namespace App\Http\Controllers\Tenant;

use App\Models\Central\Brands;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Inertia\Inertia;

class BrandsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //

        return Inertia::render('Tenant/Brands/BrandsForm', [
            'title' => 'Marcas',
            'brands' => Brands::orderBy('created_at')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = array_merge($request->watermarks ?? [], $request->aquaservice ?? []);

        $brandsInRequest = [];

        foreach ($data as $item) {
            if (!empty($item['id'])) $brandsInRequest[] = $item['id'];
            Brands::updateOrCreate(
                ['id' => $item['id']],
                [
                    'brand' => $item['brand'],
                    'type' => $item['type'],
                    'prices' => $item['prices']
                ]
            );
        }
        Brands::whereNotIn('id', $brandsInRequest)->delete();
        return redirect()->route('brand')->with('message', 'Datos guardados correctamente.');
    }
    /**
     * Display the specified resource.
     */
    public function show(Brands $brands)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Brands $brands)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Brands $brands)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brands $brands)
    {
        //
    }
}
