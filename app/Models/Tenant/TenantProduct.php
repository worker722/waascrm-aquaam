<?php

namespace App\Models\Tenant;

use App\Helpers\Lerph;
use App\Models\Central\Product;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Storage;

class TenantProduct extends Product
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'products';
    
    protected $fillable = [
        'inner_name',
        'inner_prices',
        'inner_stock',
        'inner_stock_min',
        'inner_stock_max',
        'inner_active',
        'inner_model',
    ];

    protected $appends = [
        'final_name',
        'final_model',
        'prices'
    ];

    public function getFinalNameAttribute(){
        return !empty($this->inner_name) ? $this->inner_name : $this->name;
    }

    public function getFinalModelAttribute(){
        return !empty($this->inner_model) ? $this->inner_model : $this->model;
    }

    public function getPricesAttribute()
    {
        return json_decode($this->inner_prices, true);
    }

    public function tenantAttributes()
    {
        return $this->hasMany(TenantProductAttribute::class, 'product_id')->where('inner_active', 1);
    }
}
