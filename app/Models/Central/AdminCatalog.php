<?php

namespace App\Models\Central;

use App\Models\Main\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminCatalog extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $timestamps = false;
    
    protected $fillable = [
        'type',
        'name',
        'description',
        'extra_1',
        'name_en',
        'order',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($adminCatalog) {
            $adminCatalog->order = AdminCatalog::where('type', $adminCatalog->type)->max('order') + 1;
        });
        
        static::deleted(function ($adminCatalog) {
            if (!empty(tenant('id'))) return;
            $tenants = Tenant::all();
            foreach ($tenants as $tenant){
                $tenant->run(function () use ($adminCatalog) {
                    AdminCatalog::where('id', $adminCatalog->id)->delete();
                });
            }
        });

        static::created(function ($adminCatalog) {
            if (!empty(tenant('id'))) return;
            $tenants = Tenant::all();
            foreach ($tenants as $tenant){
                $tenant->run(function () use ($adminCatalog) {
                    AdminCatalog::create($adminCatalog->toArray());
                });
            }
        });

        static::updated(function ($adminCatalog) {
            if (!empty(tenant('id'))) return;
            $tenants = Tenant::all();
            foreach ($tenants as $tenant){
                $tenant->run(function () use ($adminCatalog) {
                    AdminCatalog::where('id', $adminCatalog->id)->update($adminCatalog->toArray());
                });
            }
        });
    }

    public function getExtraData()
    {
        $data = [];
        foreach(explode(',', $this->extra_1) as $e1){
            if (!empty($e1)){
                if ($this->type == 2) $x = Product::find($e1);
                else $x = AdminCatalog::find($e1);
                if ($x) $data[] = $x;
            }
        }

        usort($data, function($x1, $x2){
            return $x1->order - $x2->order;
        });

        return $data;
    }
}
