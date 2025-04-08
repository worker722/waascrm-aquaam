<?php
use App\Helpers\Lerph;
use App\Http\Controllers\Tenant\AddressController;
use App\Http\Controllers\Tenant\BrandsController;
use App\Http\Controllers\Tenant\BudgetController;
use App\Http\Controllers\Tenant\CalendarController;
use App\Http\Controllers\Tenant\CatalogController;
use App\Http\Controllers\Tenant\ClientController;
use App\Http\Controllers\Tenant\CommonNoteController;
use App\Http\Controllers\Tenant\CompanyController;
use App\Http\Controllers\Tenant\DashboardController;
use App\Http\Controllers\Tenant\FileController;
use App\Http\Controllers\Tenant\InstallationController;
use App\Http\Controllers\Tenant\InstallationNoteController;
use App\Http\Controllers\Tenant\MaterialController;
use App\Http\Controllers\Tenant\HorecaController;
use App\Http\Controllers\Tenant\ProductController;
use App\Http\Controllers\Tenant\TaskController;
use App\Http\Controllers\Tenant\UserController;
use App\Http\Controllers\Tenant\VariableController;
use Illuminate\Support\Facades\Route;

///Public Access
Route::middleware('check-permission:0,1,2,3,4,5,6')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::post('/dashboard/stats', [DashboardController::class, 'stats'])->name('dashboard.stats');

    ///Oreca
    Route::post('/horeca/calculate', [HorecaController::class, 'calculate'])->name('horeca.calculate');
    Route::get('/horeca/variables', [HorecaController::class, 'getData'])->name('horeca.variables');
    Route::post('/budgets/store/horeca', [BudgetController::class, 'horecaStore'])->name('budgets.store.horeca');

    ///TMP files
    Route::post('/tenant/uploads/tmp/{type}', [FileController::class, 'uploadFile'])->name('tenant.upload.tmp');
    
    ///Tasks
    Route::resource('/tasks', TaskController::class, ['names' => ['index' => 'tasks']]);
    Route::post('/tasks/list', [TaskController::class, 'list'])->name('tasks.list');
    Route::post('/tasks/status/{id}', [TaskController::class, 'changeStatus'])->name('tasks.status');

    ///Calendar
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');
    Route::post('/calendar/list', [CalendarController::class, 'list'])->name('calendar.list');

    ///Common Notes
    Route::get('/notes/list/{type}/{tid}', [CommonNoteController::class, 'list'])->name('notes.list');
    Route::post('/notes/store', [CommonNoteController::class, 'store'])->name('notes.store');
    Route::delete('/notes/{notes}', [CommonNoteController::class, 'destroy'])->name('notes.destroy');
});

///Admin Access
Route::middleware('check-permission:0,1')->group(function () {
    ///Companies
    Route::resource('/company', CompanyController::class, ['names' => ['index' => 'company']]);

    ///Catalogs
    Route::get('/catalogs/{type}', [CatalogController::class, 'index'])->name('catalogs.index');
    Route::post('/catalogs/{type}/list', [CatalogController::class, 'list'])->name('catalogs.list');
    Route::post('/catalogs/{type}/store', [CatalogController::class, 'store'])->name('catalogs.store');
    Route::delete('/catalogs/{adminCatalog}', [CatalogController::class, 'destroy'])->name('catalogs.destroy');
    Route::get('/catalogs/{type}/{id}', [CatalogController::class, 'get'])->name('catalogs.get');

    ///Users
    Route::resource('/users', UserController::class, ['names' => ['index' => 'users']]);
    Route::post('/users/list', [UserController::class, 'list'])->name('users.list');
    Route::get('/profile', [UserController::class, 'profile'])->name('users.profile');
    Route::post('/profile', [UserController::class, 'profileStore'])->name('users.profile.store');

    ///Materials
    Route::resource('/materials', MaterialController::class, ['names' => ['index' => 'materials']]);
    Route::post('/materials/list', [MaterialController::class, 'list'])->name('materials.list');
    Route::post('/materials/changeStatus/{cid}', [MaterialController::class, 'changeStatus'])->name('materials.change.status');

    ///Brands
    Route::resource('/brands', BrandsController::class, ['names' => ['index' => 'brand']]);

    ///Products
    Route::resource('/prs', ProductController::class, ['names' => ['index' => 'prs']]);
    Route::post('/prs/list', [ProductController::class, 'list'])->name('prs.list');
    Route::post('/prs/changeStatus', [ProductController::class, 'changeStatus'])->name('prs.change.status');
    Route::get('/prs/pdf/{id}', [ProductController::class, 'pdf'])->name('prs.pdf');

    ///Oreca
    Route::get('/variables', [VariableController::class, 'index'])->name('variables');
    Route::post('/variables', [VariableController::class, 'save'])->name('variables.store');
});

///Comercial Access
Route::middleware('check-permission:0,1,2,4,6')->group(function () {
    ///Clients
    Route::get('/clients/opportunities', [ClientController::class, 'opportunities'])->name('clients.opportunities');
    Route::resource('/clients', ClientController::class, ['names' => ['index' => 'clients']]);
    Route::post('/clients/list', [ClientController::class, 'list'])->name('clients.list');
    Route::get('/clients/addresses/{cid}', [ClientController::class, 'getAddresses'])->name('clients.addresses');
    Route::post('/clients/board/updateStatus/{cid}', [ClientController::class, 'updateStatus'])->name('clients.board.updateStatus');

    ///Contactos
    Route::get('/contacts/opportunities', [ClientController::class, 'opportunities'])->name('contacts.opportunities');
    Route::resource('/contacts', ClientController::class, ['names' => ['index' => 'contacts']]);
    Route::post('/contacts/list', [ClientController::class, 'list'])->name('contacts.list');
    Route::post('/contacts/client/{cid}', [ClientController::class, 'convertClient'])->name('contacts.convert');

    ///Addresses
    Route::post('/address/validate', [AddressController::class, 'validateForm'])->name('address.validate');
    Route::post('/address/store', [AddressController::class, 'store'])->name('address.store');

    ///Budgets
    Route::get('/budgets/{cid}', [BudgetController::class, 'index'])->name('budgets.index');
    Route::get('/budgets/{cid}/get/{id}', [BudgetController::class, 'get'])->name('budgets.get');
    Route::post('/budgets/{cid}/list', [BudgetController::class, 'list'])->name('budgets.list');
    Route::post('/budgets/{cid}/store', [BudgetController::class, 'store'])->name('budgets.store');
    Route::delete('/budgets/{bid}', [BudgetController::class, 'destroy'])->name('budgets.destroy');
    Route::get('/budgets/{cid}/create', [BudgetController::class, 'create'])->name('budgets.create');
    Route::get('/budgets/{cid}/{id}', [BudgetController::class, 'edit'])->name('budgets.edit');
    Route::post('/budgets/details/validate', [BudgetController::class, 'validateDetailsForm'])->name('budgets.details.validate');
    Route::get('/budgets/pdf/download/{id}', [BudgetController::class, 'downloadBudget'])->name('budgets.pdf');
    
    Route::post('/budgets/reject/{id}', [BudgetController::class, 'reject'])->name('budgets.reject');
    Route::post('/budgets/accept/{id}', [BudgetController::class, 'accept'])->name('budgets.accept');
});

///Tecnic Access
Route::middleware('check-permission:0,1,3,5')->group(function () {
    ///Instalations
    Route::get('/installations/pending', [InstallationController::class, 'pending'])->name('installations.pendings');
    Route::get('/installations/all', [InstallationController::class, 'allData'])->name('installations.all');
    Route::resource('/installations', InstallationController::class, ['names' => ['index' => 'installations']]);
    Route::post('/installations/list', [InstallationController::class, 'list'])->name('installations.list');
    Route::post('/installations/create', [InstallationController::class, 'create'])->name('installations.create');
    Route::post('/installations/assign', [InstallationController::class, 'assign'])->name('installations.assign');

    ///Installation Notes
    Route::get('/installations/{iid}/notes', [InstallationNoteController::class, 'list'])->name('installations.notes');
    Route::post('/installations/{iid}/notes/store', [InstallationNoteController::class, 'store'])->name('installations.notes.store');
    Route::delete('/installations/notes/{notes}', [InstallationNoteController::class, 'destroy'])->name('installations.notes.destroy');

    ///Maintences
    Route::get('/maintenances/pending', [InstallationController::class, 'pending'])->name('maintenances.pendings');
    Route::get('/maintenances/all', [InstallationController::class, 'allData'])->name('maintenances.all');
    Route::resource('/maintenances', InstallationController::class, ['names' => ['index' => 'maintenances']]);
    Route::post('/maintenances/list', [InstallationController::class, 'list'])->name('maintenances.list');
    Route::post('/maintenances/create', [InstallationController::class, 'create'])->name('maintenances.create');
    Route::post('/maintenances/assign', [InstallationController::class, 'assign'])->name('maintenances.assign');
});