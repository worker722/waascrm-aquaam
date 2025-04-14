<?php

namespace App\Http\Controllers\Central;

use App\Helpers\Lerph;
use App\Http\Controllers\Controller;
use App\Models\Central\AdminCatalog;
use App\Models\Central\Product;
use App\Models\Central\ProductAttr;
use App\Models\Central\SparePart;
use App\Models\Main\Tenant;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class ProductsController extends Controller
{
    public function index()
    {
        $filters = [];
        $filters[] = ['label' => 'Familia', 'options' => AdminCatalog::select('name as label', 'id as value')->where('type', 5)->get(), 'type' => 'select', 'name' => 'family'];

        return Inertia::render('Central/Products/ProductList', [
            'title' => 'Productos',
            'filters' => $filters
        ]);
    }

    public function list(Request $request)
    {
        $query = Product::select('*');
        if ($request->has('family') && $request->input('family') != null) $query->where('family_id', $request->input('family'));
        $data = $query->get()->map(function ($pr) {
            $pr->family_name = $pr->family ? $pr->family->name : '';
            return $pr;
        });

        return $data;
    }

    public function create()
    {
        $parts = SparePart::select('name as label', 'id as value', 'type_id', 'reference')->orderBy('type_id')->get()->map(function ($part) {
            $t = $part->type->name ?? '';
            if (!empty($t)) $part->label = $part->reference . ' - ' . $t . ' > ' . $part->label;
            return $part;
        });

        return Inertia::render('Central/Products/ProductForm', [
            'title' => 'Agregar Producto',
            'product' => new Product(),
            'families' => AdminCatalog::select('name as label', 'id as value')->where('type', 5)->get(),
            'categories' => AdminCatalog::select('name as label', 'id as value')->where('type', 4)->get(),
            'allParts' => $parts,
            'attributes' => [],
            'images' => [],
            'videos' => [],
            'documents' => [],
            'attrs' => [],
            'parts' => [],
            'otherParts' => [],
            'dismantling' => []
        ]);
    }

    public function edit(Product $product)
    {
        $catalog = AdminCatalog::find($product->category_id);
        $parts = SparePart::select('name as label', 'id as value', 'type_id', 'reference')->orderBy('type_id')->get()->map(function ($part) {
            $t = $part->type->name ?? '';
            if (!empty($t)) $part->label = $part->reference . ' - ' . $t . ' > ' . $part->label;
            return $part;
        });

        return Inertia::render('Central/Products/ProductForm', [
            'title' => 'Editar Producto',
            'product' => $product,
            'families' => AdminCatalog::select('name as label', 'id as value')->where('type', 5)->get(),
            'categories' => AdminCatalog::select('name as label', 'id as value')->where('type', 4)->get(),
            'allParts' => $parts,
            'attributes' => $product->attributes,
            'images' => $product->getFilesData(1),
            'videos' => $product->getFilesData(2),
            'documents' => $product->getFilesData(3),
            'attrs' => $catalog ? $catalog->getExtraData() : [],
            'parts' => explode(',', $product->parts),
            'otherParts' => explode(',', $product->other_parts),
            'dismantling' => json_decode($product->dismantling, true)
        ]);
    }

    public function store(Request $request)
    {
        $id = $request->id;
        if (empty($id)) $id = 0;
        return $this->upsertData($request, $id);
    }

    public function upsertData($request, $id)
    {
        $this->validateForm($request, $id);

        if (empty($request->id)) $product = new Product($request->except(['id']));
        else {
            $product = Product::find($request->id);
            if ($product) $product->fill($request->all());
            else $product = new Product($request->except(['id']));
        }
        $product->parts = implode(',', $request->input('parts', []));
        $product->other_parts = implode(',', $request->input('others', []));
        $product->active = $request->active ? 1 : 0;
        $product->dismantling = json_encode($request->input('dismantling', []));
        $product->save();

        $this->upsertAttributes($request, $product);

        $this->upsertFiles($request, $product, $product->images, 'images', 1);
        $this->upsertFiles($request, $product, $product->videos, 'videos', 2);
        $this->upsertFiles($request, $product, $product->documents, 'documents', 3);

        return redirect()->route('products')->with('message', 'Datos guardados correctamente.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->back()->with('message', 'Producto borrado correctamente.');
    }

    private function validateForm(Request $request, $id)
    {
        //if (empty($id)) $id = 'NULL';
        return $request->validate(
            [
                'model' => 'required|max:100|unique:products,model,' . $id . ',id,deleted_at,NULL',
                'name' => 'required|max:200',
                'description' => 'max:500',
                //'code' => 'required|max:100|unique:products,code,'.$id
            ],
            [],
            [
                'model' => 'Modelo',
                'name' => 'Nombre',
                'description' => 'Descripción',
                'code' => 'Código'
            ]
        );
    }

    private function upsertAttributes($request, $product)
    {
        $product->attributes()->delete();

        $tenants = Tenant::all();

        foreach ($tenants as $tenant) {
            $tenant->run(function ($tenant) use ($product, $request) {
                ProductAttr::where('product_id', $product->id)->delete();
            });
        }

        if ($request->input('attributes')) {
            foreach ($request->input('attributes') as $attr) {
                $id = $text = $textEn = null;
                if (isset($attr['id'])) $id = $attr['id'];
                if (isset($attr['text'])) $text = $attr['text'];
                if (isset($attr['text_en'])) $textEn = $attr['text_en'];
                if (!empty($id)) {
                    $product->attributes()->create([
                        'attribute_id' => $id,
                        'text' => $text,
                        'text_en' => $textEn
                    ]);
                }
            }
        }
    }

    private function upsertFiles($request, $product, $savedFiles, $key, $type)
    {
        $uploaded = $request->input($key, []);
        foreach ($uploaded as $n => $file) {
            $saved = false;
            foreach ($savedFiles as $sf) {
                if ($sf->id == $file['id']) {
                    $saved = true;
                    $sf->order = $n;
                    $sf->title = $file['title'];
                    $sf->image_type = $file['image_type'] ?? 0;
                    $sf->save();
                }
            }
            if (!$saved) {
                $product->files()->create([
                    'type' => $type,
                    'file' => $file['file'],
                    'title' => $file['title'],
                    'size' => $file['size'],
                    'order' => $n,
                    'image_type' => $file['image_type'] ?? 0
                ]);

                Storage::disk('products')->put($product->id . '/' . $file['file'], Storage::disk('tmp')->get($file['file']));
                Storage::disk('tmp')->delete($file['file']);
            }
        }
        foreach ($savedFiles as $sf) {
            $deleted = true;
            foreach ($uploaded as $file) {
                if ($file['id'] == $sf->id) $deleted = false;
            }
            if ($deleted) {
                $sf->delete();

                Storage::disk('products')->delete($product->id . '/' . $sf->file);
            }
        }
    }

    public function generatePDF($id, $type) //$type => 0: by product id, 1: by family id 
    {
        set_time_limit(300);
        $data = [];
        if($type == 0) {
            $product[] = Product::find($id);
            if ($product) $products[] = $product;
            else $products = Product::all();
        } else {
            $products = Product::where(['family_id' => $id])->get();
        }

        foreach ($products as $product) $data[] = Lerph::getTechPdf($product);

        $pdf = Pdf::loadView('pdfs.pdf1', ['data' => $data]);

        return $pdf->stream('pdf1.pdf');
    }
    public function pdf($id)
    {
        return $this->generatePDF($id, 0);
    }

    public function pdfs($id)
    {
        return $this->generatePDF($id, 1);
    }
}
