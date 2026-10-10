<?php

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ArchiveController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GroomingController;
use App\Http\Controllers\GroomingPriceController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\PetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| PUBLIC
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route(auth()->user()->landingRoute())
        : redirect()->route('login');
});


/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::middleware('role:admin,manager,owner')->group(function () {
        Route::get('/archive', [ArchiveController::class, 'index'])->name('archive.index');
        Route::post('/archive/{type}/{id}/restore', [ArchiveController::class, 'restore'])->name('archive.restore');
        Route::delete('/archive/{type}/{id}', [ArchiveController::class, 'destroy'])->name('archive.destroy');
        Route::delete('/archive/{type}', [ArchiveController::class, 'destroyAll'])->name('archive.destroy-all');
    });

    Route::middleware('role:admin,manager,owner')->get('/activity-logs', [AuditLogController::class, 'index'])
        ->name('activity-logs.index');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    /*
    |--------------------------------------------------------------------------
    | DASHBOARD
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,manager,groomer,owner,frontdesk')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');
    });

    Route::middleware('role:admin,manager')->group(function () {
        Route::resource('users', UserController::class)->except(['show']);
    });


    /*
    |--------------------------------------------------------------------------
    | POS
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,manager,frontdesk,customer')->group(function () {

        Route::get('/pos', [PosController::class, 'index'])
            ->name('pos.index');

        Route::post('/pos/cart/add', [PosController::class, 'addToCart'])
            ->name('pos.cart.add');

        Route::post('/pos/cart/update', [PosController::class, 'updateCart'])
            ->name('pos.cart.update');

        Route::post('/pos/cart/remove', [PosController::class, 'removeFromCart'])
            ->name('pos.cart.remove');

        Route::post('/pos/clear', [PosController::class, 'clearCart'])
            ->name('pos.clear');

        Route::post('/checkout', [CheckoutController::class, 'store'])
            ->name('pos.checkout');

        Route::get('/pos/receipt/{sale}', [ReceiptController::class, 'show'])
        ->name('pos.receipt');

    });


    /*
    |--------------------------------------------------------------------------
    | RECEIPTS
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,manager,frontdesk,customer')->group(function () {
        Route::get('/receipts/{sale}/done', [ReceiptController::class, 'done'])
            ->name('receipts.done');

        Route::get('/receipts/{sale}', [ReceiptController::class, 'show'])
            ->name('receipts.show');

        Route::get('/receipts/{sale}/print', [ReceiptController::class, 'print'])
            ->name('receipts.print');
    });


    /*
    |--------------------------------------------------------------------------
    | PRODUCTS
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,manager')->get('/categories', [CategoryController::class, 'index'])
        ->name('categories.index');

    Route::middleware('role:admin,manager,frontdesk')->group(function () {

        Route::get('/products', [ProductController::class, 'index'])
            ->name('products.index');

        Route::middleware('role:admin,manager')->group(function () {
            Route::post('/categories', [CategoryController::class, 'store'])
                ->name('categories.store');
            Route::put('/categories/{category}', [CategoryController::class, 'update'])
                ->name('categories.update');
            Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])
                ->name('categories.destroy');
        });

        Route::get('/products/expired', [ProductController::class, 'expired'])
            ->name('products.expired');

        Route::post('/products/expired/write-off', [ProductController::class, 'writeOffExpired'])
            ->name('products.expired.write-off');

        Route::delete('/products/expired/{batch}', [ProductController::class, 'removeExpiredBatch'])
            ->name('products.expired.remove');

        Route::get('/inventory/products/create', [ProductController::class, 'create'])
            ->name('products.create');

        Route::post('/inventory/products', [ProductController::class, 'store'])
            ->name('products.store');

        Route::post('/inventory/products/{product}/allocate', [InventoryController::class, 'allocateToProducts'])
            ->name('inventory.allocate');

        Route::get('/inventory/products/{product}/edit', [ProductController::class, 'edit'])
            ->name('products.edit');

        Route::put('/inventory/products/{product}', [ProductController::class, 'update'])
            ->name('products.update');

        Route::delete('/inventory/products/{product}', [ProductController::class, 'destroy'])
            ->name('products.destroy');

    });


    /*
    |--------------------------------------------------------------------------
    | INVENTORY
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,manager')->group(function () {

        Route::get('/inventory', [InventoryController::class, 'index'])
            ->name('inventory.index');

        Route::post('/inventory/{product}/stock-in', [InventoryController::class, 'stockIn'])
            ->name('inventory.stock-in');

        Route::post('/inventory/import', [InventoryController::class, 'import'])
            ->name('inventory.import');

        Route::get('/inventory/{product}/history', [InventoryController::class, 'history'])
            ->name('inventory.history');

        Route::post('/inventory/movements/{movement}/archive', [InventoryController::class, 'archiveMovement'])
            ->name('inventory.movements.archive');

});


    /*
    |--------------------------------------------------------------------------
    | CUSTOMERS
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,manager,frontdesk')->group(function () {

        Route::get('/customers', [CustomerController::class, 'index'])
            ->name('customers.index');

        Route::get('/customers/create', [CustomerController::class, 'create'])
            ->name('customers.create');

        Route::post('/customers', [CustomerController::class, 'store'])
            ->name('customers.store');

        Route::get('/customers/{customer}', [CustomerController::class, 'show'])
            ->name('customers.show');

        Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])
            ->name('customers.edit');

        Route::put('/customers/{customer}', [CustomerController::class, 'update'])
            ->name('customers.update');

    });

    Route::middleware('role:admin,manager')->group(function () {

        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])
            ->name('customers.destroy');

    });


    /*
    |--------------------------------------------------------------------------
    | PETS
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,manager')->group(function () {

        Route::get(
            '/customers/{customer}/pets/create',
            [PetController::class, 'create']
        )->name('pets.create');

        Route::post(
            '/customers/{customer}/pets',
            [PetController::class, 'store']
        )->name('pets.store');

        Route::get(
            '/pets/{pet}/edit',
            [PetController::class, 'edit']
        )->name('pets.edit');

        Route::put(
            '/pets/{pet}',
            [PetController::class, 'update']
        )->name('pets.update');

        Route::delete(
            '/pets/{pet}',
            [PetController::class, 'destroy']
        )->name('pets.destroy');

    });


    /*
    |--------------------------------------------------------------------------
    | SERVICES
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,manager')->group(function () {

        Route::post('/grooming-addons', [App\Http\Controllers\GroomingAddonController::class, 'store'])
            ->name('grooming-addons.store');

        Route::get('/grooming-addons/{groomingAddon}/edit', [App\Http\Controllers\GroomingAddonController::class, 'edit'])
            ->name('grooming-addons.edit');

        Route::put('/grooming-addons/{groomingAddon}', [App\Http\Controllers\GroomingAddonController::class, 'update'])
            ->name('grooming-addons.update');

        Route::delete('/grooming-addons/{groomingAddon}', [App\Http\Controllers\GroomingAddonController::class, 'destroy'])
            ->name('grooming-addons.destroy');

        Route::get('/services', [ServiceController::class, 'index'])
            ->name('services.index');

        Route::get('/services/create', [ServiceController::class, 'create'])
            ->name('services.create');

        Route::post('/services', [ServiceController::class, 'store'])
            ->name('services.store');

        Route::get('/services/{service}/edit', [ServiceController::class, 'edit'])
            ->name('services.edit');

        Route::put('/services/{service}', [ServiceController::class, 'update'])
            ->name('services.update');

        Route::delete('/services/{service}', [ServiceController::class, 'destroy'])
            ->name('services.destroy');

    });


    /*
    |--------------------------------------------------------------------------
    | SETTINGS
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,manager,owner')->group(function () {

        Route::get('/settings', [SettingController::class, 'index'])
            ->name('settings.index');

        Route::get('/settings/edit', [SettingController::class, 'edit'])
            ->name('settings.edit');

        Route::put('/settings', [SettingController::class, 'update'])
            ->name('settings.update');

    });


    /*
    |--------------------------------------------------------------------------
    | GROOMING
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,manager,frontdesk,groomer,customer')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | GROOMING REQUEST
        |--------------------------------------------------------------------------
        */

        Route::get('/grooming', [GroomingController::class, 'index'])
            ->name('grooming.index');

        Route::get('/grooming/create', [GroomingController::class, 'create'])
            ->name('grooming.create');

        Route::post('/grooming', [GroomingController::class, 'store'])
            ->name('grooming.store');

        Route::put('/grooming/{appointment}/addons', [GroomingController::class, 'updateAddons'])
            ->middleware('role:admin,manager,frontdesk')
            ->name('grooming.addons.update');

        Route::get('/grooming/{appointment}/payment', [GroomingController::class, 'payment'])
            ->middleware('role:admin,manager,frontdesk,customer')
            ->name('grooming.payment');

        Route::middleware('role:admin,manager,frontdesk,groomer')->group(function () {
            Route::get('/grooming/{appointment}/waiver-photo', [GroomingController::class, 'waiverPhoto'])
                ->name('grooming.waiver-photo');
        });


        /*
        |--------------------------------------------------------------------------
        | APPROVE
        |--------------------------------------------------------------------------
        */

        Route::middleware('role:admin,manager,frontdesk')->group(function () {

            Route::post(
                '/grooming/{appointment}/approve',
                [GroomingController::class, 'approve']
            )->name('grooming.approve');

        });


        /*
        |--------------------------------------------------------------------------
        | RECEIVE PET
        |--------------------------------------------------------------------------
        */

        Route::middleware('role:admin,manager,frontdesk')->group(function () {

            Route::get(
                '/grooming/{appointment}/receive',
                [GroomingController::class, 'receiveForm']
            )->name('grooming.receive');

            Route::post(
                '/grooming/{appointment}/receive',
                [GroomingController::class, 'receive']
            )->name('grooming.receive.store');

        });


        /*
        |--------------------------------------------------------------------------
        | START GROOMING
        |--------------------------------------------------------------------------
        */

        Route::middleware('role:admin,manager,groomer')->group(function () {

            Route::post(
                '/grooming/{appointment}/start',
                [GroomingController::class, 'start']
            )->name('grooming.start');

        });


        /*
        |--------------------------------------------------------------------------
        | MARK READY
        |--------------------------------------------------------------------------
        */

        Route::middleware('role:admin,manager,frontdesk,groomer')->group(function () {

            Route::post(
                '/grooming/{appointment}/ready',
                [GroomingController::class, 'ready']
            )->name('grooming.ready');

        });


        /*
        |--------------------------------------------------------------------------
        | CANCEL
        |--------------------------------------------------------------------------
        */

        Route::middleware('role:admin,manager,frontdesk')->group(function () {

            Route::post(
                '/grooming/{appointment}/cancel',
                [GroomingController::class, 'cancel']
            )->name('grooming.cancel');

        });

    });


    /*
    |--------------------------------------------------------------------------
    | GROOMING PRICES
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,manager')->group(function () {

        Route::get(
            '/grooming-prices',
            [GroomingPriceController::class, 'index']
        )->name('grooming-prices.index');

        Route::post(
            '/grooming-prices',
            [GroomingPriceController::class, 'store']
        )->name('grooming-prices.store');

        Route::put(
            '/grooming-prices/{groomingPrice}',
            [GroomingPriceController::class, 'update']
        )->name('grooming-prices.update');

        Route::delete(
            '/grooming-prices/{groomingPrice}',
            [GroomingPriceController::class, 'destroy']
        )->name('grooming-prices.destroy');

    });


    /*
    |--------------------------------------------------------------------------
    | REPORTS
    |--------------------------------------------------------------------------
    */

    Route::middleware('role:admin,manager,owner')->group(function () {

    Route::get('/reports', [ReportController::class, 'index'])
        ->name('reports.index');

    Route::get('/reports/products', [ReportController::class, 'products'])
        ->name('reports.products');

    Route::get('/reports/inventory', [ReportController::class, 'inventory'])
        ->name('reports.inventory');

    Route::post('/reports/{type}/generate', [ReportController::class, 'generate'])
        ->whereIn('type', ['sales', 'products', 'grooming', 'inventory'])
        ->name('reports.generate');

    Route::post('/reports/complete/generate', [ReportController::class, 'generateComplete'])
        ->name('reports.generate-complete');

    Route::get('/reports/generated/{generatedReport}/download', [ReportController::class, 'download'])
        ->name('reports.download');

    Route::delete('/reports/generated/{generatedReport}', [ReportController::class, 'destroy'])
        ->name('reports.destroy');

    Route::delete('/reports/generated', [ReportController::class, 'destroyAll'])
        ->name('reports.destroy-all');

});

    Route::middleware('role:admin,manager,frontdesk,owner')->group(function () {
        Route::get('/reports/sales', [ReportController::class, 'sales'])
            ->name('reports.sales');

        Route::get('/reports/grooming', [ReportController::class, 'grooming'])
            ->name('reports.grooming');
    });

});


/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/

require __DIR__.'/auth.php';