<?php

use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProposalController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::name('api.')->prefix('api/v1')->middleware([
    'api',
    'validate-api',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {

    //Products
    Route::get('product', [ProductController::class, 'list']);

    //Clients
    Route::resource('client', ClientController::class)->only(['index', 'show', 'store', 'update']);

    //Contacts
    Route::resource('contact', ClientController::class)->only(['index', 'show', 'store', 'update']);

    //Proposals
    Route::post('proposal/create', [ProposalController::class, 'create']);
    Route::post('proposal/accept/{pid}/{did}', [ProposalController::class, 'accept']);
});
