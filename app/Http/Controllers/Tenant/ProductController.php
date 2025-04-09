<?php

namespace App\Http\Controllers\Tenant;

use App\Helpers\Lerph;
use App\Http\Controllers\Controller;
use App\Models\Central\AdminCatalog;
use App\Models\Central\ProductAttr;
use App\Models\Central\SparePart;
use App\Models\Tenant\TenantProduct;
use App\Models\Tenant\TenantProductAttribute;
use Attribute;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index()
    {
        $filters = [];
        $filters[] = ['label' => 'Buscar', 'type' => 'text', 'name' => 'q'];
        $filters[] = ['label' => 'Familia', 'options' => AdminCatalog::select('name as label', 'id as value')->where('type', 5)->get(), 'type' => 'select', 'name' => 'family'];
        
        return Inertia::render('Tenant/Products/ProductList', [
            'title' => 'Productos', 
            'allowed' => ALLOWED_PRODUCTS,
            'filters' => $filters
        ]);
    }

    public function list(Request $request)
    {
        $query = TenantProduct::whereIn('id', ALLOWED_PRODUCTS)->where('active', 1);
        if ($request->has('q')) $query->where('name', 'like', '%'.$request->input('q').'%');
        if ($request->has('family')) $query->where('family_id', $request->input('family'));

        $data = $query->get()->map(function($pr){
            $pr->main_image = $pr->getMainImage();
            $pr->family_name = $pr->family->name ?? '';
            return $pr;
        });
        
        return $data;
    }

    public function edit($pid)
    {
        $prod = TenantProduct::find($pid);
        return Inertia::render('Tenant/Products/ProductForm', [
            'title' => 'Editar Producto',
            'product' => $prod,
            'familyName' => $prod->family->name ?? '',
            'dues' => Lerph::getDues(),
            'attributes' => $prod->attributes,
        ]);
    }

    public function store(Request $request)
    {
        $id = $request->id;
        if (empty($id)) $id = 0;
        return $this->upsertData($request, $id);
    }

    public function upsertData($request, $id){
        $this->validateForm($request, $id);

        $product = TenantProduct::find($request->id);
        $is_extras = $request->is_extras;
        $product->fill($request->except(['id', 'is_extras']));
        $product->inner_prices = json_encode($request->input('inner_prices'));
        $product->inner_active = $request->input('inner_active') ? 1 : 0;
        if($is_extras) {
            $product->inner_extras = json_encode($request->input('inner_extras'));
        } else {
            $product->inner_extras = null;
        }
        $product->save();

        TenantProductAttribute::where('product_id', $product->id)->update(['inner_active' => 0]);
        $attributes = $request->input('attributes');
        foreach ($attributes as $attr){
            TenantProductAttribute::where('product_id', $product->id)->where('attribute_id', $attr)->update(['inner_active' => 1]);
        }

        return redirect()->route('prs')->with('message', 'Datos guardados correctamente.');
    }

    public function changeStatus(Request $request){
        $ids = $request->input('ids');
        $checkedValue = $request->input('checked');
        TenantProduct::whereIn('id', $ids)->get()->each(function ($product) use ($checkedValue) {
            if ($checkedValue !== null) {
                $product->inner_active = (int) $checkedValue;
            } else {
                $product->inner_active = $product->inner_active ? 0 : 1;
            }
            $product->save();
        });
        return redirect()->back()->with('message', 'Estado cambiado correctamente.');
    }

    private function validateForm(Request $request, $id){
        return $request->validate([
        ]);
    }

    public function pdf($id){
        set_time_limit(300);
        $data = [];
        $product = TenantProduct::find($id);
        if ($product) $products[] = $product;
        else {
            $query = TenantProduct::whereIn('id', ALLOWED_PRODUCTS)->where('active', 1);
            if (request()->has('q')) $query->where('name', 'like', '%'.request()->input('q').'%');
            if (request()->has('family')) $query->where('family_id', request()->input('family'));
            $products = $query->get();
        }
        
        foreach ($products as $product) $data[] = Lerph::getTechPdf($product);

        $pdf = Pdf::loadView('pdfs.pdf1', [
            'data' => $data
        ]);

        return $pdf->stream('pdf1.pdf');
    }
}
