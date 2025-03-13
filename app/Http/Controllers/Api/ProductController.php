<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Address;
use App\Models\Tenant\Catalog;
use App\Models\Tenant\Client;
use App\Models\Tenant\TenantProduct;
use App\Models\Tenant\TenantUser;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ProductController extends ApiController
{
    /**
    * @OA\Get(
    *      path="/product",
    *      tags={"Productos"},
    *      description="Listado de productos",
    *      @OA\Response(
    *           response=200, 
    *           description="Successful operation",
    *           @OA\JsonContent(
    *               @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/TenantProduct")),
    *               @OA\Property(property="total", type="integer"),
    *               @OA\Property(property="page", type="integer"),
    *               @OA\Property(property="rows", type="integer")
    *           )
    *      ),
    *      @OA\Response(response=400, description="Not found"),
    *      @OA\Response(response=401, description="Unauthorized"),
    * )
    
    */
    
    public function list()
    {
        $page = request()->input('page', 1);
        $limit = request()->input('rows', 10);
        
        $data = TenantProduct::whereIn('id', ALLOWED_PRODUCTS)->where('active', 1)->skip(($page - 1) * $limit)->take($limit)->get()->map(function($pr){
            $pr->main_image = $pr->getMainImage();
            $pr->family_name = $pr->family->name ?? '';
            $pr->name = $pr->final_name;
            $pr->model = $pr->final_model;
            $pr->active = $pr->inner_active;
            $pr->stock = $pr->inner_stock;
            $pr->stock_min = $pr->inner_stock_min;
            $pr->stock_max = $pr->inner_stock_max;

            $pr->images = $pr->images->map(function($img){
                $img->url = $img->getUrlAttribute();
                unset($img->created_at, $img->updated_at, $img->deleted_at, $img->id, $img->image_type, $img->type);
                return $img;
            });

            $pr->attributes = $pr->tenantAttributes->map(function($attr){
                return $attr;
            });

            $home = $business = [];
            if ($pr->prices){
                foreach ($pr->prices as $pd) {
                    $d = str_ireplace(['h-', 'b-'], '', $pd['id']);
                    $pp = ['id' => $pd['id'], 'price' => $pd['price'], 'duties' => $d];
                    if (strpos($pd['id'], 'h-') !== false) $home[] = $pp;
                    else $business[] = $pp;
                }
            }
            

            $pr->home_prices = $home;
            $pr->business_prices = $business;

            unset($pr->inner_name, $pr->inner_prices, $pr->parts, $pr->other_parts, $pr->dismantling, $pr->inner_stock, $pr->inner_stock_min, 
                $pr->inner_stock_max, $pr->inner_active, $pr->inner_model, $pr->final_name, $pr->final_model);
            return $pr;
        });

        return parent::returnList($data, $page, $limit, TenantProduct::whereIn('id', ALLOWED_PRODUCTS)->where('active', 1)->count());
    }
}
