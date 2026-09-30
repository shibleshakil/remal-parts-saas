<?php

use App\Http\Controllers\ArtisanCommandController;
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\BalanceOutController;
use App\Http\Controllers\BalanceStatementController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PartsReturnListController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchasePartsReturnListController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


Auth::routes();

Route::group(['middleware' => ['auth']], function () {

    // artisan command (to run from browser, url: /run/{command})

    Route::get('/run/{command}', [ArtisanCommandController::class, 'run'])
        ->whereIn('command', ['optimize', 'route-clear', 'migrate','migrate-status', 'migrate-rollback'])
        ->name('run');

    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/stock-summary', [HomeController::class, 'stockSummary'])->name('stockSummary');
    Route::match(['get', 'post'],'/profit-summary', [HomeController::class, 'profitSummary'])->name('profitSummary');

    Route::get('/profile/{name}', 'App\Http\Controllers\AccountController@viewProfile')->name('profile');
    Route::post('/profile/{name}', 'App\Http\Controllers\AccountController@updateProfile');

    Route::group(['prefix' => 'password', 'as' => 'password.'], function () {
        Route::get('update', 'App\Http\Controllers\AccountController@showPasswordForm')->name('update');
        Route::post('update', 'App\Http\Controllers\AccountController@updatePassword');
    });


    //Vehicles Routes
    Route::group(['prefix' => 'vehicle', 'as' => 'vehicle.'], function () {
        Route::get('/', 'App\Http\Controllers\VehicleController@index')->name('index')->middleware('permission:vehicle.index');
        Route::get('/{invoice_no}/invoice', 'App\Http\Controllers\VehicleController@invoice')->name('invoice')->middleware('permission:vehicle.invoice');
        Route::get('/create', 'App\Http\Controllers\VehicleController@create')->name('create')->middleware('permission:vehicle.create');
        Route::post('/store', 'App\Http\Controllers\VehicleController@store')->name('store')->middleware('permission:vehicle.store');
        Route::get('{vehicle}/edit', 'App\Http\Controllers\VehicleController@edit')->name('edit')->middleware('permission:vehicle.edit');
        Route::get('/{vehicle}', 'App\Http\Controllers\VehicleController@show')->name('show')->middleware('permission:vehicle.show');
        Route::post('{vehicle}/update', 'App\Http\Controllers\VehicleController@update')->name('update')->middleware('permission:vehicle.update');
        Route::delete('{vehicle}/delete', 'App\Http\Controllers\VehicleController@delete')->name('delete')->middleware('permission:vehicle.delete');
        Route::get('/{vehicle}/barcode-print', 'App\Http\Controllers\VehicleController@printBarcode')->name('print_barcode')->middleware('permission:vehicle.print_barcode');
        Route::get('/{vehicle}/qrcode-print', 'App\Http\Controllers\VehicleController@printQRcode')->name('print_qrcode')->middleware('permission:vehicle.print_qrcode');
    });


    //Spare Parts Routes
    Route::group(['prefix' => 'spare-parts', 'as' => 'spare-parts.'], function () {
        Route::get('/', 'App\Http\Controllers\SparePartsController@index')->name('index')->middleware('permission:spare-parts.index');
        Route::get('/invoice-list', 'App\Http\Controllers\SparePartsController@invoiceList')->name('invoiceList')->middleware('permission:spare-parts.invoiceList');
        Route::get('/invoice/{invoice}/view', 'App\Http\Controllers\SparePartsController@invoice')->name('invoice')->middleware('permission:spare-parts.invoice');
        Route::get('/export', 'App\Http\Controllers\SparePartsController@export')->name('export')->middleware('permission:spare-parts.export');
        Route::get('/print', 'App\Http\Controllers\SparePartsController@print')->name('print')->middleware('permission:spare-parts.print');
        Route::get('/create', 'App\Http\Controllers\SparePartsController@create')->name('create')->middleware('permission:spare-parts.create');
        Route::post('/store', 'App\Http\Controllers\SparePartsController@store')->name('store')->middleware('permission:spare-parts.store');
        Route::post('/ajax-store', 'App\Http\Controllers\SparePartsController@ajaxStore')->name('ajaxStore')->middleware('permission:spare-parts.ajaxStore');
        Route::get('{sparePart}/edit', 'App\Http\Controllers\SparePartsController@edit')->name('edit')->middleware('permission:spare-parts.edit');
        Route::get('/{sparePart}', 'App\Http\Controllers\SparePartsController@show')->name('show')->middleware('permission:spare-parts.show');
        Route::post('{sparePart}/update', 'App\Http\Controllers\SparePartsController@update')->name('update')->middleware('permission:spare-parts.update');
        // Route::get('add-similar/{id}', 'App\Http\Controllers\SparePartsController@addSimilar')->name('add-similar')->middleware('permission:spare-parts.add-similar');
        // Route::post('/{id}/add-similar', 'App\Http\Controllers\SparePartsController@storeSimilarPurchase')->name('store-similar')->middleware('permission:spare-parts.store-similar');
        Route::delete('{sparePart}/delete', 'App\Http\Controllers\SparePartsController@delete')->name('delete')->middleware('permission:spare-parts.delete');
        Route::get('/{sparePart}/barcode-print', 'App\Http\Controllers\SparePartsController@printBarcode')->name('print_barcode')->middleware('permission:spare-parts.print_barcode');
        Route::get('/{sparePart}/qrcode-print', 'App\Http\Controllers\SparePartsController@printQRcode')->name('print_qrcode')->middleware('permission:spare-parts.print_qrcode');
    });


    // all parts search
    Route::group(['prefix' => 'product-search', 'as' => 'productSearch.'], function () {
        Route::get('/', 'App\Http\Controllers\SparePartsController@searchParts')->name('index')->middleware('permission:productSearch.index');
        Route::get('/print', 'App\Http\Controllers\SparePartsController@searchPartsPrint')->name('print')->middleware('permission:productSearch.print');
        Route::get('/export', 'App\Http\Controllers\SparePartsController@searchPartsExport')->name('export')->middleware('permission:productSearch.export');
    });


    //Sales Routes
    Route::group(['prefix' => 'sales', 'as' => 'sales.'], function () {
        Route::get('/parts-return', [PartsReturnListController::class, 'index'])->name('parts-return.index')->middleware('permission:sales.parts-return.index');
        Route::get('/', [App\Http\Controllers\SaleController::class, 'index'])->name('index')->middleware('permission:sales.index');
        Route::get('/create', [App\Http\Controllers\SaleController::class, 'create'])->name('create')->middleware('permission:sales.create');
        Route::post('/store', [App\Http\Controllers\SaleController::class, 'store'])->name('store')->middleware('permission:sales.store');

        Route::get('/get-spare-parts/{vehicleId}', [App\Http\Controllers\SaleController::class, 'getSpareParts'])->name('getSpareParts')->middleware('permission:sales.getSpareParts');
        Route::get('/get-data/{barcode}', [App\Http\Controllers\SaleController::class, 'getDataByBarcode'])->name('getDataByBarcode')->middleware('permission:sales.getDataByBarcode');
        Route::post('/custom-store/ajax', [App\Http\Controllers\SaleController::class, 'customeStoreViaAjax'])->name('customeStoreViaAjax')->middleware('permission:sales.customeStoreViaAjax');
        Route::post('/bank-store/ajax', [App\Http\Controllers\SaleController::class, 'bankStoreViaAjax'])->name('bankStoreViaAjax')->middleware('permission:sales.bankStoreViaAjax');

        Route::get('/{sale}/edit', [App\Http\Controllers\SaleController::class, 'edit'])->name('edit')->middleware('permission:sales.edit');
        Route::post('/{sale}/update', [App\Http\Controllers\SaleController::class, 'update'])->name('update')->middleware('permission:sales.update');

        Route::get('/{sale}', [App\Http\Controllers\SaleController::class, 'show'])->name('show')->middleware('permission:sales.show');
        Route::get('/{sale}/return', [App\Http\Controllers\SaleController::class, 'returnForm'])->name('returnForm')->middleware('permission:sales.returnForm');
        Route::post('/{sale}/return', [App\Http\Controllers\SaleController::class, 'returnSale'])->name('return')->middleware('permission:sales.return');
        Route::get('/{sale}/pay-due', [App\Http\Controllers\SaleController::class, 'showPayDueForm'])->name('pay-due')->middleware('permission:sales.pay-due');
        Route::post('/{sale}/pay-due', [App\Http\Controllers\SaleController::class, 'payDue'])->name('pay-due')->middleware('permission:sales.pay-due');
        Route::get('/{sale}/download', [App\Http\Controllers\SaleController::class, 'downloadInvoice'])->name('download')->middleware('permission:sales.download');
        Route::delete('/{sale}/delete', [App\Http\Controllers\SaleController::class, 'delete'])->name('delete')->middleware('permission:sales.delete');
    });


   //Quote Routes
    Route::group(['prefix' => 'quotes', 'as' => 'quotes.'], function () {
        Route::get('/', [App\Http\Controllers\QuoteController::class, 'index'])->name('index')->middleware('permission:quotes.index');
        Route::get('/create', [App\Http\Controllers\QuoteController::class, 'create'])->name('create')->middleware('permission:quotes.create');
        Route::post('/store', [App\Http\Controllers\QuoteController::class, 'store'])->name('store')->middleware('permission:quotes.store');
        Route::post('/store-to-sale', [App\Http\Controllers\QuoteController::class, 'storeToSale'])->name('storeToSale')->middleware('permission:quotes.storeToSale');

        Route::get('/get-vehicle-details/{vehicleId}', [App\Http\Controllers\QuoteController::class, 'getVehicleDetails'])->name('getVehicleDetails')->middleware('permission:quotes.getVehicleDetails');
        Route::get('/get-item/{itemId}', [App\Http\Controllers\QuoteController::class, 'getItem'])->name('getItem')->middleware('permission:quotes.getItem');

        Route::get('/{quote}/edit', [App\Http\Controllers\QuoteController::class, 'edit'])->name('edit')->middleware('permission:quotes.edit');
        Route::get('/{quote}/to-sale', [App\Http\Controllers\QuoteController::class, 'convertToSale'])->name('convertToSale')->middleware('permission:quotes.convertToSale');
        Route::post('/{quote}/update', [App\Http\Controllers\QuoteController::class, 'update'])->name('update')->middleware('permission:quotes.update');

        Route::get('/{quote}', [App\Http\Controllers\QuoteController::class, 'show'])->name('show')->middleware('permission:quotes.show');
        Route::get('/{quote}/download', [App\Http\Controllers\QuoteController::class, 'downloadQuote'])->name('download')->middleware('permission:quotes.download');
        Route::delete('/{quote}/delete', [App\Http\Controllers\QuoteController::class, 'delete'])->name('delete')->middleware('permission:quotes.delete');
    });


    //Bank Routes
    Route::group(['prefix' => 'banks', 'as' => 'banks.'], function () {
        Route::get('/', [BankController::class, 'index'])->name('index')->middleware('permission:banks.index');
        Route::get('/trash', [BankController::class, 'trash'])->name('trash')->middleware('permission:banks.trash');
        Route::post('/restore/{id}', [BankController::class, 'restore'])->name('restore')->middleware('permission:banks.restore');
        Route::get('/create', [BankController::class, 'create'])->name('create')->middleware('permission:banks.create');
        Route::post('/store', [BankController::class, 'store'])->name('store')->middleware('permission:banks.store');
        Route::get('/{bank}/edit', [BankController::class, 'edit'])->name('edit')->middleware('permission:banks.edit');
        Route::put('/{bank}/update', [BankController::class, 'update'])->name('update')->middleware('permission:banks.update');
        Route::delete('/{bank}/delete', [BankController::class, 'destroy'])->name('delete')->middleware('permission:banks.delete');
    });


    //Balance Routes
    Route::group(['prefix' => 'balances', 'as' => 'balances.'], function () {
        Route::get('/', [BalanceController::class, 'index'])->name('index')->middleware('permission:balances.index');
        Route::get('/create', [BalanceController::class, 'create'])->name('create')->middleware('permission:balances.create');
        Route::post('/store', [BalanceController::class, 'store'])->name('store')->middleware('permission:balances.store');
        Route::get('/{balance}/edit', [BalanceController::class, 'edit'])->name('edit')->middleware('permission:balances.edit');
        Route::put('/{balance}/update', [BalanceController::class, 'update'])->name('update')->middleware('permission:balances.update');
        Route::delete('/{balance}/delete', [BalanceController::class, 'destroy'])->name('delete')->middleware('permission:balances.delete');
        Route::get('/get-balance', [BalanceController::class, 'getBalance'])->name('getBalance')->middleware('permission:balances.getBalance');
    });


    //Balance Out Routes
    Route::group(['prefix' => 'balances-out', 'as' => 'balances-out.'], function () {
        Route::get('/create', [BalanceOutController::class, 'create'])->name('create')->middleware('permission:balances-out.create');
        Route::post('/store', [BalanceOutController::class, 'store'])->name('store')->middleware('permission:balances-out.store');
        Route::get('/{balance}/edit', [BalanceOutController::class, 'edit'])->name('edit')->middleware('permission:balances-out.edit');
        Route::put('/{balance}/update', [BalanceOutController::class, 'update'])->name('update')->middleware('permission:balances-out.update');
        Route::delete('/{balance}/delete', [BalanceOutController::class, 'destroy'])->name('delete')->middleware('permission:balances-out.delete');
    });


    // Customer Routes
    Route::group(['prefix' => 'customer', 'as' => 'customer.'], function () {
        Route::get('/', 'App\Http\Controllers\CustomerController@index')->name('index')->middleware('permission:customer.index');
        Route::get('/create', 'App\Http\Controllers\CustomerController@create')->name('create')->middleware('permission:customer.create');
        Route::post('/store', 'App\Http\Controllers\CustomerController@store')->name('store')->middleware('permission:customer.store');
        Route::get('{id}/edit', 'App\Http\Controllers\CustomerController@edit')->name('edit')->middleware('permission:customer.edit');
        Route::post('/update', 'App\Http\Controllers\CustomerController@update')->name('update')->middleware('permission:customer.update');
        Route::get('/pay/{sale}', 'App\Http\Controllers\CustomerController@payForm')->name('pay')->middleware('permission:customer.pay');
        Route::post('/pay/{sale}', 'App\Http\Controllers\CustomerController@pay')->name('pay')->middleware('permission:customer.pay');
        // Route::get('{customer}/direct-pay', 'App\Http\Controllers\CustomerController@directPayForm')->name('directPayForm');
        // Route::post('/direct-pay', 'App\Http\Controllers\CustomerController@directPay')->name('directPay');
        Route::delete('{id}/delete', 'App\Http\Controllers\CustomerController@delete')->name('delete')->middleware('permission:customer.delete');
        Route::get('/{id}/transactions', [CustomerController::class, 'transactions'])->name('transactions')->middleware('permission:customer.transactions');
    });


    //Suppliers
    Route::group(['prefix' => 'supplier', 'as' => 'supplier.'], function () {
        Route::get('/', [SupplierController::class,'index'])->name('index')->middleware('permission:supplier.index');
        Route::get('/create', [SupplierController::class, 'create'])->name('create')->middleware('permission:supplier.create');
        Route::post('/store', [SupplierController::class, 'store'])->name('store')->middleware('permission:supplier.store');
        Route::get('/{id}/edit', [SupplierController::class, 'edit'])->name('edit')->middleware('permission:supplier.edit');
        Route::put('/{id}/update',[SupplierController::class,'update'])->name('update')->middleware('permission:supplier.update');
        Route::delete('/{id}/delete', [SupplierController::class, 'destroy'])->name('delete')->middleware('permission:supplier.delete');
        Route::get('/{id}/pay', [SupplierController::class, 'payForm'])->name('payForm')->middleware('permission:supplier.payForm');
        Route::post('/{id}/pay', [SupplierController::class, 'pay'])->name('pay')->middleware('permission:supplier.pay');
        Route::match(['get', 'post'], '/{id}/transactions', [SupplierController::class, 'transactions'])->name('transactions')->middleware('permission:supplier.transactions');
    });


    //Expenses Type Routes
    Route::group(['prefix' => 'expense-type', 'as' => 'expense-type.'], function () {
        Route::get('/', 'App\Http\Controllers\ExpenseTypeController@index')->name('index')->middleware('permission:expense-type.index');
        Route::get('/create', 'App\Http\Controllers\ExpenseTypeController@create')->name('create')->middleware('permission:expense-type.create');
        Route::post('/store', 'App\Http\Controllers\ExpenseTypeController@store')->name('store')->middleware('permission:expense-type.store');
        Route::get('{id}/edit', 'App\Http\Controllers\ExpenseTypeController@edit')->name('edit')->middleware('permission:expense-type.edit');
        Route::post('update', 'App\Http\Controllers\ExpenseTypeController@update')->name('update')->middleware('permission:expense-type.update');
        Route::delete('{id}/delete', 'App\Http\Controllers\ExpenseTypeController@delete')->name('delete')->middleware('permission:expense-type.delete');
    });


    //Expenses Routes
    Route::group(['prefix' => 'expense', 'as' => 'expense.'], function () {
        Route::get('/', 'App\Http\Controllers\ExpenseController@index')->name('index')->middleware('permission:expense.index');
        Route::get('/create', 'App\Http\Controllers\ExpenseController@create')->name('create')->middleware('permission:expense.create');
        Route::post('/store', 'App\Http\Controllers\ExpenseController@store')->name('store')->middleware('permission:expense.store');
        Route::get('{id}/edit', 'App\Http\Controllers\ExpenseController@edit')->name('edit')->middleware('permission:expense.edit');
        Route::post('{id}/update', 'App\Http\Controllers\ExpenseController@update')->name('update')->middleware('permission:expense.update');
        Route::delete('{id}/delete', 'App\Http\Controllers\ExpenseController@delete')->name('delete')->middleware('permission:expense.delete');
        Route::get('/print', 'App\Http\Controllers\ExpenseController@expensesPrint')->name('print')->middleware('permission:expense.print');
        Route::get('/export', 'App\Http\Controllers\ExpenseController@expensesExport')->name('export')->middleware('permission:expense.export');
    });


    //Report Routes
    Route::group(['prefix' => 'report', 'as' => 'report.'], function () {
        Route::get('/stock', 'App\Http\Controllers\ReportController@stock')->name('stock')->middleware('permission:report.stock');
        Route::match(['get', 'post'],'/profit', 'App\Http\Controllers\ReportController@grossProfit')->name('profit')->middleware('permission:report.profit');
        Route::match(['get', 'post'],'/vehicle-sale', 'App\Http\Controllers\ReportController@vehicleSale')->name('vehicleSale')->middleware('permission:report.vehicleSale');
        Route::match(['get', 'post'],'/due', 'App\Http\Controllers\ReportController@dueReport')->name('dueReport')->middleware('permission:report.dueReport');
        Route::get('/sale-details', [App\Http\Controllers\ReportController::class, 'getSaleDetails'])->name('saleDetails')->middleware('permission:report.saleDetails');
    });


    //Purchase Routes
    Route::prefix('purchase')->name('purchase.')->group(function () {
        Route::get('/', [PurchaseController::class, 'index'])->name('index')->middleware('permission:purchase.index');
        Route::get('/vehicle', 'App\Http\Controllers\VehicleController@create')->name('purchaseVehicle')->middleware('permission:purchase.purchaseVehicle');
        Route::get('/create', [PurchaseController::class, 'create'])->name('create')->middleware('permission:purchase.create');
        Route::post('/store', [PurchaseController::class, 'store'])->name('store')->middleware('permission:purchase.store');
        Route::get('/barcode/{barcode}', [PurchaseController::class, 'getDataByBarcode'])->name('getDataByBarcode')->middleware('permission:purchase.getDataByBarcode');
        // Show invoice by invoice number
        Route::get('/invoice/{invoice_no}', [PurchaseController::class, 'invoice'])->name('invoice')->middleware('permission:purchase.invoice');
        Route::get('/{purchase}/pay-due', [PurchaseController::class, 'showPayDueForm'])->name('pay-due')->middleware('permission:purchase.pay-due');
        Route::post('/{purchase}/pay-due', [PurchaseController::class, 'payDue'])->name('pay-due')->middleware('permission:purchase.pay-due');
        Route::get('/{purchase}/return', [PurchaseController::class, 'returnPurchase'])->name('returnPurchase')->middleware('permission:purchase.returnPurchase');
        Route::post('/{purchase}/return', [PurchaseController::class, 'returnPurchasePost'])->name('returnPurchasePost')->middleware('permission:purchase.returnPurchasePost');
        Route::get('/parts-return', [PurchasePartsReturnListController::class, 'index'])->name('parts-return.index')->middleware('permission:purchase.parts-return.index');
    });


    //User Management Routes
    Route::group(['prefix' => 'user', 'as' => 'user.'], function () {
        Route::get('/', 'App\Http\Controllers\UserController@index')->name('index')->middleware('permission:user.index');
        Route::get('/create', 'App\Http\Controllers\UserController@create')->name('create')->middleware('permission:user.create');
        Route::post('/store', 'App\Http\Controllers\UserController@store')->name('store')->middleware('permission:user.store');
        Route::get('/edit/{id}', 'App\Http\Controllers\UserController@edit')->name('edit')->middleware('permission:user.edit');
        Route::put('/update/{id}', 'App\Http\Controllers\UserController@update')->name('update')->middleware('permission:user.update');
        Route::delete('{id}/delete', 'App\Http\Controllers\UserController@delete')->name('delete')->middleware('permission:user.delete');
    });


    //Balance Statement Routes
    Route::group(['prefix' => 'balance-statement', 'as' => 'balance-statement.'], function () {
        Route::get('/', [BalanceStatementController::class, 'index'])->name('index')->middleware('permission:balance-statement.index');
    });


    // branch routes
    Route::group(['prefix' => 'branch', 'as' => 'branch.'], function () {
        Route::get('/', 'App\Http\Controllers\BranchController@index')->name('index')->middleware('permission:branch.index');
        Route::get('/create', 'App\Http\Controllers\BranchController@create')->name('create')->middleware('permission:branch.create');
        Route::post('/store', 'App\Http\Controllers\BranchController@store')->name('store')->middleware('permission:branch.store');
        Route::get('/{branch}/edit', 'App\Http\Controllers\BranchController@edit')->name('edit')->middleware('permission:branch.edit');
        Route::put('/{branch}/update', 'App\Http\Controllers\BranchController@update')->name('update')->middleware('permission:branch.update');
        Route::delete('/{branch}/delete', 'App\Http\Controllers\BranchController@destroy')->name('delete')->middleware('permission:branch.delete');
        Route::post('/switch-branch', 'App\Http\Controllers\BranchController@switch')->name('switch');
    });


    //Roles Routes
    Route::group(['prefix' => 'roles', 'as' => 'roles.'], function () {
        Route::get('/', 'App\Http\Controllers\RoleController@index')->name('index')->middleware('permission:roles.index');
        Route::post('/store', 'App\Http\Controllers\RoleController@store')->name('store')->middleware('permission:roles.store');
        Route::get('/{id}/edit', 'App\Http\Controllers\RoleController@edit')->name('edit')->middleware('permission:roles.edit');
        Route::post('/{id}/update', 'App\Http\Controllers\RoleController@update')->name('update')->middleware('permission:roles.update');
        Route::delete('{id}/delete', 'App\Http\Controllers\RoleController@destroy')->name('delete')->middleware('permission:roles.delete');
        Route::get('/set-permissions/{id}', 'App\Http\Controllers\RoleController@setPermissionsForm')->name('setPermissionsForm')->middleware('permission:roles.setPermissionsForm');
        Route::post('/set-permissions/{id}', 'App\Http\Controllers\RoleController@storePermissions')->name('storePermissions')->middleware('permission:roles.storePermissions');
    });


    //Stock Movment
    Route::group(['prefix' => 'stock-movment', 'as' => 'stock-movment.'], function () {
        Route::get('/', 'App\Http\Controllers\StockMovementController@index')->name('index')->middleware('permission:stock-movment.index');
        Route::get('/create', 'App\Http\Controllers\StockMovementController@create')->name('create')->middleware('permission:stock-movment.create');
        Route::post('/store', 'App\Http\Controllers\StockMovementController@store')->name('store')->middleware('permission:stock-movment.store');
        Route::get('/{id}/edit', 'App\Http\Controllers\StockMovementController@edit')->name('edit')->middleware('permission:stock-movment.edit');
        Route::post('/{id}/update', 'App\Http\Controllers\StockMovementController@update')->name('update')->middleware('permission:stock-movment.update');
        Route::delete('{id}/delete', 'App\Http\Controllers\StockMovementController@destroy')->name('delete')->middleware('permission:stock-movment.delete');
        Route::get('/get-parts-by-branch/{branch_id}', 'App\Http\Controllers\StockMovementController@getPartsByBranch')->name('getPartsByBranch')->middleware('permission:stock-movment.getPartsByBranch');
    });
});
