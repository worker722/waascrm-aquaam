<?php

namespace App\Models\Central;

use App\Models\Main\Tenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Storage;

class ProductFile extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    protected $fillable = [
        'product_id',
        'type',
        'file',
        'title',
        'order',
        'size',
        'image_type'
    ];

    protected static function boot()
    {
        parent::boot();

        static::deleted(function ($file) {
            if (!empty(tenant('id'))) return;
            $tenants = Tenant::all();
            foreach ($tenants as $tenant){
                $tenant->run(function () use ($file) {
                    ProductFile::where('id', $file->id)->delete();
                });
            }
        });

        static::created(function ($file) {
            if (!empty(tenant('id'))) return;
            $tenants = Tenant::all();
            foreach ($tenants as $tenant){
                $tenant->run(function () use ($file) {
                    ProductFile::create($file->toArray());
                });
            }
        });

        static::updated(function ($file) {
            if (!empty(tenant('id'))) return;
            $tenants = Tenant::all();
            foreach ($tenants as $tenant){
                $tenant->run(function () use ($file) {
                    ProductFile::where('id', $file->id)->update($file->toArray());
                });
            }
        });
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getUrlAttribute()
    {
        return !empty($this->file) ? Storage::disk('products')->url($this->product_id.'/'.$this->file) : 'https://ui-avatars.com/api/?name=Aqua&color=7F9CF5&background=EBF4FF';
    }
}
