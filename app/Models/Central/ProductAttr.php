<?php

namespace App\Models\Central;

use App\Models\Main\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductAttr extends Model
{
    use HasFactory;

    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = null;
    
    protected $fillable = [
        'product_id',
        'attribute_id',
        'text',
        'text_en',
    ];

    protected $appends = [
        'attr_name'
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($productAttr) {
            if (!empty(tenant('id'))) return;
            $tenants = Tenant::all();
            foreach ($tenants as $tenant){
                $tenant->run(function () use ($productAttr) {
                    ProductAttr::where('product_id', $productAttr->product_id)->where('attribute_id', $productAttr->attribute_id)->delete();
                });
            }
        });

        static::created(function ($productAttr) {
            if (!empty(tenant('id'))) return;
            $tenants = Tenant::all();
            foreach ($tenants as $tenant){
                $tenant->run(function () use ($productAttr) {
                    ProductAttr::create($productAttr->toArray());
                });
                
            }
        });

        static::updated(function ($productAttr) {
            if (!empty(tenant('id'))) return;
            $tenants = Tenant::all();
            foreach ($tenants as $tenant){
                $tenant->run(function () use ($productAttr) {
                    ProductAttr::where('id', $productAttr->id)->update($productAttr->toArray());
                });
            }
        });
    }

    public function attribute()
    {
        return $this->belongsTo(AdminCatalog::class, 'attribute_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getAttrNameAttribute()
    {
        return $this->attribute->name ?? '';
    }
}
