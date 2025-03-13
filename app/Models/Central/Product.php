<?php

namespace App\Models\Central;

use App\Models\Main\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory;
    use SoftDeletes;
    
    protected $fillable = [
        'model',
        'name',
        'description',
        'active',
        'code',
        'family_id',
        'category_id',
        'parts',
        'other_parts',
        'dismantling',
        'model_en',
        'name_en',
        'description_en',
        'lts',
        'gas',
        'worktop',
        'predosing',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($product) {
            if (!empty(tenant('id'))) return;
            $tenants = Tenant::all();
            foreach ($tenants as $tenant){
                $tenant->run(function () use ($product) {
                    Product::where('id', $product->id)->delete();
                });
            }
        });

        static::created(function ($product) {
            if (!empty(tenant('id'))) return;
            $tenants = Tenant::all();
            foreach ($tenants as $tenant){
                $tenant->run(function () use ($product) {
                    $data = $product->toArray();
                    unset($data['deleted_at']);
                    unset($data['created_at']);
                    unset($data['updated_at']);
                    Product::create($data);
                });
                
            }
        });

        static::updated(function ($product) {
            if (!empty(tenant('id'))) return;
            $tenants = Tenant::all();
            foreach ($tenants as $tenant){
                $tenant->run(function () use ($product) {
                    $data = $product->toArray();
                    unset($data['deleted_at']);
                    unset($data['created_at']);
                    unset($data['updated_at']);
                    Product::where('id', $product->id)->update($data);
                });
                
            }
        });
    }
    
    public function family()
    {
        return $this->belongsTo(AdminCatalog::class, 'family_id');
    }

    public function category()
    {
        return $this->belongsTo(AdminCatalog::class, 'category_id');
    }

    public function files()
    {
        return $this->hasMany(ProductFile::class);
    }

    public function attributes()
    {
        return $this->hasMany(ProductAttr::class, 'product_id')->join('admin_catalogs', 'product_attrs.attribute_id', '=', 'admin_catalogs.id')->select('product_attrs.*', 'admin_catalogs.name as attribute_name', 'admin_catalogs.type as attribute_type')->orderBy('admin_catalogs.order');
    }

    public function images()
    {
        return $this->hasMany(ProductFile::class, 'product_id')->where('type', 1)->orderBy('order');
    }

    public function videos()
    {
        return $this->hasMany(ProductFile::class, 'product_id')->where('type', 2)->orderBy('order');
    }

    public function documents()
    {
        return $this->hasMany(ProductFile::class, 'product_id')->where('type', 3)->orderBy('order');
    }

    public function getMainImage()
    {
        $img = ProductFile::where('product_id', $this->id)->where('type', 1)->where('image_type', 1)->orderBy('order')->first();
        return $img ? $img->getUrlAttribute() : 'https://ui-avatars.com/api/?name=BB&color=7F9CF5&background=EBF4FF';
    }

    public function getFilesData($t)
    {
        $files = $t == 1 ? $this->images : ($t == 2 ? $this->videos : $this->documents);
        $data = [];
        foreach ($files as $file) {
            $data[] = [
                'id' => $file->id,
                'title' => $file->title,
                'file' => $file->file,
                'size' => $file->size,
                'order' => $file->order,
                'img' => $file->url,
                'type' => $t == 1 ? 'image' : ($t == 2 ? 'video' : 'file'),
                'image_type' => $file->image_type ?? '0'
            ];
        }
        return $data;
    }
}
