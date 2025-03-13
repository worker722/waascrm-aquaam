<?php

namespace App\Models\Central;

use App\Models\Main\Tenant;
use App\Models\Tenant\TenantUser;
use App\Models\User;
use Hash;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Storage;

class Company extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $timestamps = false;

    const MAX_STATUS = 1;
    
    protected $fillable = [
        'domain',
        'name',
        'business_name',
        'cif',
        'logo',
        'users',
        'email',
        'address',
        'fiscal_address',
        'price',
        'status',
        'products',
        'tenant_id',
        'payment_method',
        'bank_account',
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($company) {
            if (!empty(tenant('id'))) return;
            $tenant = Tenant::find($company->tenant_id);
            if (!$tenant) return;
            Company::where('id', $company->id)->delete();
        });

        static::created(function ($company) {
            if (!empty($company->tenant_id)) return;

            ////Create Tenant
            $tenant = Tenant::create();
            $company->tenant_id = $tenant->id;
            $company->save();

            ///Add Domain
            $tenant->createDomain(['domain' => $company->domain.'.'.env('APP_DOMAIN')]);

            ///Create Company, User and Products
            $products = Product::withTrashed()->orderBy('id')->get();
            $parts = SparePart::withTrashed()->get();
            $catalogs = AdminCatalog::withTrashed()->get();
            $tenant->run(function () use ($company, $products, $parts, $catalogs) {
                Company::create($company->toArray());

                foreach($catalogs as $catalog){
                    $pr = AdminCatalog::create($catalog->toArray());
                }

                foreach($parts as $part){
                    $pr = SparePart::create($part->toArray());
                }

                $first = $products->first();
                if ($first){
                    $id = $first->id;
                    DB::statement("ALTER SEQUENCE products_id_seq RESTART WITH $id");
                }

                foreach($products as $product){
                    $data = $product->toArray();
                    unset($data['deleted_at']);
                    unset($data['created_at']);
                    unset($data['updated_at']);
                    $pr = Product::create($data);

                    $product->attributes()->each(function ($attribute) use ($pr){
                        $pr->attributes()->create($attribute->toArray());
                    });

                    $product->files()->each(function ($image) use ($pr){
                        $pr->images()->create($image->toArray());
                    });

                }
            });
        });

        static::updated(function ($company) {
            if (!empty(tenant('id'))){
                tenancy()->central(function ($tenant) use ($company){
                    $data = $company->toArray();
                    unset($data['id']);
                    Company::where('tenant_id', $company->tenant_id)->update($data);
                });
            }else {
                $tenant = Tenant::find($company->tenant_id);
                if (!$tenant) return;

                $tenant->domains()->each(function ($domain) use ($company){
                    $domain->update(['domain' => $company->domain.'.'.env('APP_DOMAIN')]);
                });

                $tenant->run(function () use ($company) {
                    $cc = Company::first();
                    if ($cc) {
                        $cc->update($company->toArray());
                    }
                });
            }
           
        });

        static::deleted(function ($company) {
            if (!empty(tenant('id'))) return;
            $tenant = Tenant::find($company->tenant_id);
            if (!$tenant) return;
            $tenant->domains()->delete();
            $tenant->delete();
        });
    }

    public function getTenant()
    {
        return Tenant::find($this->tenant_id);
    }

    public function getLogoUrlAttribute()
    {
        return !empty($this->logo) ? Storage::disk('companies')->url($this->logo) : 'https://ui-avatars.com/api/?name='.$this->name.'&color=7F9CF5&background=EBF4FF';
    }

    public function getProducts()
    {
        $data = [];
        foreach(explode(',', $this->products) as $e1){
            if (!empty($e1)){
                $x = Product::find($e1);
                if ($x) $data[] = $x;
            }
        }
        return $data;
    }

    public static function decodeStatus($x)
    {
        switch($x){
            case 0: return 'Inactivo';
            case 1: return 'Activo';
            default: return 'Desconocido';
        }
    }
}
