<?php
declare(strict_types=1);

use App\Helpers\Lerph;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;


Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {

    require 'auth.php';
    
    Route::middleware('auth')->group(function () {
        Lerph::requireFolder(__DIR__.'/tenant');
    });
});

require 'tenant-api.php';