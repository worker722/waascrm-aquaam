<?php

namespace App\Models\Tenant;

use App\Models\Central\ProductAttr;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TenantProductAttribute extends ProductAttr
{
    use HasFactory;

    protected $table = 'product_attrs';
    
    protected $fillable = [
        'inner_active',
    ];


}
