<?php

namespace App\Models\Central;

use App\Models\Main\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SparePartOther extends Model
{
    use HasFactory;
    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = null;
    
    protected $fillable = [
        'spare_part_id',
        'product_id'
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($sparePartOther) {
            if (!empty(tenant('id'))) return;
            $tenants = Tenant::all();
            foreach ($tenants as $tenant){
                $tenant->run(function () use ($sparePartOther) {
                    SparePartOther::where('id', $sparePartOther->id)->delete();
                });
            }
        });

        static::created(function ($sparePartOther) {
            if (!empty(tenant('id'))) return;
            $tenants = Tenant::all();
            foreach ($tenants as $tenant){
                $tenant->run(function () use ($sparePartOther) {
                    SparePartOther::create($sparePartOther->toArray());
                });
            }
        });

        static::updated(function ($sparePartOther) {
            if (!empty(tenant('id'))) return;
            $tenants = Tenant::all();
            foreach ($tenants as $tenant){
                $tenant->run(function () use ($sparePartOther) {
                    SparePartOther::where('id', $sparePartOther->id)->update($sparePartOther->toArray());
                });
            }
        });
    }

    public function sparePart()
    {
        return $this->belongsTo(SparePart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
