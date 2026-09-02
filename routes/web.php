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

    Route::group(['prefix' => 'vehicle', 'as' => 'vehicle.'], function () {
        Route::get('/', 'App\Http\Controllers\VehicleController@index')->name('index');
        Route::get('/{invoice_no}/invoice', 'App\Http\Controllers\VehicleController@invoice')->name('invoice');
        Route::get('/create', 'App\Http\Controllers\VehicleController@create')->name('create');
        Route::post('/store', 'App\Http\Controllers\VehicleController@store')->name('store');
        Route::get('{vehicle}/edit', 'App\Http\Controllers\VehicleController@edit')->name('edit');
        Route::get('/{vehicle}', 'App\Http\Controllers\VehicleController@show')->name('show');
        Route::post('{vehicle}/update', 'App\Http\Controllers\VehicleController@update')->name('update');
        Route::delete('{vehicle}/delete', 'App\Http\Controllers\VehicleController@delete')->name('delete');
        Route::get('/{vehicle}/barcode-print', 'App\Http\Controllers\VehicleController@printBarcode')->name('print_barcode');
        Route::get('/{vehicle}/qrcode-print', 'App\Http\Controllers\VehicleController@printQRcode')->name('print_qrcode');
    });

    Route::group(['prefix' => 'spare-parts', 'as' => 'spare-parts.'], function () {
        Route::get('/', 'App\Http\Controllers\SparePartsController@index')->name('index');
        Route::get('/invoice-list', 'App\Http\Controllers\SparePartsController@invoiceList')->name('invoiceList');
        Route::get('/invoice/{invoice}/view', 'App\Http\Controllers\SparePartsController@invoice')->name('invoice');
        Route::get('/export', 'App\Http\Controllers\SparePartsController@export')->name('export');
        Route::get('/print', 'App\Http\Controllers\SparePartsController@print')->name('print');
        Route::get('/create', 'App\Http\Controllers\SparePartsController@create')->name('create');
        Route::post('/store', 'App\Http\Controllers\SparePartsController@store')->name('store');
        Route::post('/ajax-store', 'App\Http\Controllers\SparePartsController@ajaxStore')->name('ajaxStore');
        Route::get('{sparePart}/edit', 'App\Http\Controllers\SparePartsController@edit')->name('edit');
        Route::get('/{sparePart}', 'App\Http\Controllers\SparePartsController@show')->name('show');
        Route::post('{sparePart}/update', 'App\Http\Controllers\SparePartsController@update')->name('update');
//        Route::get('add-similar/{id}', 'App\Http\Controllers\SparePartsController@addSimilar')->name('add-similar');
//        Route::post('/{id}/add-similar', 'App\Http\Controllers\SparePartsController@storeSimilarPurchase')->name('store-similar');
        Route::delete('{sparePart}/delete', 'App\Http\Controllers\SparePartsController@delete')->name('delete');
        Route::get('/{sparePart}/barcode-print', 'App\Http\Controllers\SparePartsController@printBarcode')->name('print_barcode');
        Route::get('/{sparePart}/qrcode-print', 'App\Http\Controllers\SparePartsController@printQRcode')->name('print_qrcode');
    });

    // all parts search
    Route::group(['prefix' => 'product-search', 'as' => 'productSearch.'], function () {
        Route::get('/', 'App\Http\Controllers\SparePartsController@searchParts')->name('index');
        Route::get('/print', 'App\Http\Controllers\SparePartsController@searchPartsPrint')->name('print');
        Route::get('/export', 'App\Http\Controllers\SparePartsController@searchPartsExport')->name('export');
    });

    //Sales Routes
    Route::group(['prefix' => 'sales', 'as' => 'sales.'], function () {
        Route::get('/parts-return', [PartsReturnListController::class, 'index'])->name('parts-return.index');
        Route::get('/', [App\Http\Controllers\SaleController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\SaleController::class, 'create'])->name('create');
        Route::post('/store', [App\Http\Controllers\SaleController::class, 'store'])->name('store');

        Route::get('/get-spare-parts/{vehicleId}', [App\Http\Controllers\SaleController::class, 'getSpareParts'])->name('getSpareParts');
        Route::get('/get-data/{barcode}', [App\Http\Controllers\SaleController::class, 'getDataByBarcode'])->name('getDataByBarcode');
        Route::post('/custom-store/ajax', [App\Http\Controllers\SaleController::class, 'customeStoreViaAjax'])->name('customeStoreViaAjax');
        Route::post('/bank-store/ajax', [App\Http\Controllers\SaleController::class, 'bankStoreViaAjax'])->name('bankStoreViaAjax');

        Route::get('/{sale}/edit', [App\Http\Controllers\SaleController::class, 'edit'])->name('edit');
        Route::post('/{sale}/update', [App\Http\Controllers\SaleController::class, 'update'])->name('update');

        Route::get('/{sale}', [App\Http\Controllers\SaleController::class, 'show'])->name('show');
        Route::get('/{sale}/return', [App\Http\Controllers\SaleController::class, 'returnForm'])->name('returnForm');
        Route::post('/{sale}/return', [App\Http\Controllers\SaleController::class, 'returnSale'])->name('return');
        Route::get('/{sale}/pay-due', [App\Http\Controllers\SaleController::class, 'showPayDueForm'])->name('pay-due');
        Route::post('/{sale}/pay-due', [App\Http\Controllers\SaleController::class, 'payDue'])->name('pay-due');
        Route::get('/{sale}/download', [App\Http\Controllers\SaleController::class, 'downloadInvoice'])->name('download');
        Route::delete('/{sale}/delete', [App\Http\Controllers\SaleController::class, 'delete'])->name('delete');
    });


   //Quote Routes
    Route::group(['prefix' => 'quotes', 'as' => 'quotes.'], function () {
        Route::get('/', [App\Http\Controllers\QuoteController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\QuoteController::class, 'create'])->name('create');
        Route::post('/store', [App\Http\Controllers\QuoteController::class, 'store'])->name('store');
        Route::post('/store-to-sale', [App\Http\Controllers\QuoteController::class, 'storeToSale'])->name('storeToSale');

        Route::get('/get-vehicle-details/{vehicleId}', [App\Http\Controllers\QuoteController::class, 'getVehicleDetails'])->name('getVehicleDetails');
        Route::get('/get-item/{itemId}', [App\Http\Controllers\QuoteController::class, 'getItem'])->name('getItem');

        Route::get('/{quote}/edit', [App\Http\Controllers\QuoteController::class, 'edit'])->name('edit');
        Route::get('/{quote}/to-sale', [App\Http\Controllers\QuoteController::class, 'convertToSale'])->name('convertToSale');
        Route::post('/{quote}/update', [App\Http\Controllers\QuoteController::class, 'update'])->name('update');

        Route::get('/{quote}', [App\Http\Controllers\QuoteController::class, 'show'])->name('show');
        Route::get('/{quote}/download', [App\Http\Controllers\QuoteController::class, 'downloadQuote'])->name('download');
        Route::delete('/{quote}/delete', [App\Http\Controllers\QuoteController::class, 'delete'])->name('delete');
    });

    //Bank Routes
    Route::group(['prefix' => 'banks', 'as' => 'banks.'], function () {
        Route::get('/', [BankController::class, 'index'])->name('index');
        Route::get('/trash', [BankController::class, 'trash'])->name('trash');
        Route::post('/restore/{id}', [BankController::class, 'restore'])->name('restore');
        Route::get('/create', [BankController::class, 'create'])->name('create');
        Route::post('/store', [BankController::class, 'store'])->name('store');
        Route::get('/{bank}/edit', [BankController::class, 'edit'])->name('edit');
        Route::put('/{bank}/update', [BankController::class, 'update'])->name('update');
        Route::delete('/{bank}/delete', [BankController::class, 'destroy'])->name('delete');
    });

    //Balance Routes
    Route::group(['prefix' => 'balances', 'as' => 'balances.'], function () {
        Route::get('/', [BalanceController::class, 'index'])->name('index');
        Route::get('/create', [BalanceController::class, 'create'])->name('create');
        Route::post('/store', [BalanceController::class, 'store'])->name('store');
        Route::get('/{balance}/edit', [BalanceController::class, 'edit'])->name('edit');
        Route::put('/{balance}/update', [BalanceController::class, 'update'])->name('update');
        Route::delete('/{balance}/delete', [BalanceController::class, 'destroy'])->name('delete');
        Route::get('/get-balance', [BalanceController::class, 'getBalance'])->name('getBalance');
    });

    //Balance Out Routes
    Route::group(['prefix' => 'balances-out', 'as' => 'balances-out.'], function () {
        Route::get('/create', [BalanceOutController::class, 'create'])->name('create');
        Route::post('/store', [BalanceOutController::class, 'store'])->name('store');
        Route::get('/{balance}/edit', [BalanceOutController::class, 'edit'])->name('edit');
        Route::put('/{balance}/update', [BalanceOutController::class, 'update'])->name('update');
        Route::delete('/{balance}/delete', [BalanceOutController::class, 'destroy'])->name('delete');
    });

    Route::group(['prefix' => 'customer', 'as' => 'customer.'], function () {
        Route::get('/', 'App\Http\Controllers\CustomerController@index')->name('index');
        Route::get('/create', 'App\Http\Controllers\CustomerController@create')->name('create');
        Route::post('/store', 'App\Http\Controllers\CustomerController@store')->name('store');
        Route::get('{id}/edit', 'App\Http\Controllers\CustomerController@edit')->name('edit');
        Route::post('/update', 'App\Http\Controllers\CustomerController@update')->name('update');
        Route::get('/pay/{sale}', 'App\Http\Controllers\CustomerController@payForm')->name('pay');
        Route::post('/pay/{sale}', 'App\Http\Controllers\CustomerController@pay')->name('pay');
        // Route::get('{customer}/direct-pay', 'App\Http\Controllers\CustomerController@directPayForm')->name('directPayForm');
        // Route::post('/direct-pay', 'App\Http\Controllers\CustomerController@directPay')->name('directPay');
        Route::delete('{id}/delete', 'App\Http\Controllers\CustomerController@delete')->name('delete');
        Route::get('/{id}/transactions', [CustomerController::class, 'transactions'])->name('transactions');

    });
    //Suppliers
    Route::group(['prefix' => 'supplier', 'as' => 'supplier.'], function () {
        Route::get('/', [SupplierController::class,'index'])->name('index');
        Route::get('/create', [SupplierController::class, 'create'])->name('create');
        Route::post('/store', [SupplierController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [SupplierController::class, 'edit'])->name('edit');
        Route::put('/{id}/update',[SupplierController::class,'update'])->name('update');
        Route::delete('/{id}/delete', [SupplierController::class, 'destroy'])->name('delete');
        Route::get('/{id}/pay', [SupplierController::class, 'payForm'])->name('payForm');
        Route::post('/{id}/pay', [SupplierController::class, 'pay'])->name('pay');
        Route::match(['get', 'post'], '/{id}/transactions', [SupplierController::class, 'transactions'])->name('transactions');
    });

    Route::group(['prefix' => 'expense-type', 'as' => 'expense-type.'], function () {
        Route::get('/', 'App\Http\Controllers\ExpenseTypeController@index')->name('index');
        Route::get('/create', 'App\Http\Controllers\ExpenseTypeController@create')->name('create');
        Route::post('/store', 'App\Http\Controllers\ExpenseTypeController@store')->name('store');
        Route::get('{id}/edit', 'App\Http\Controllers\ExpenseTypeController@edit')->name('edit');
        Route::post('update', 'App\Http\Controllers\ExpenseTypeController@update')->name('update');
        Route::delete('{id}/delete', 'App\Http\Controllers\ExpenseTypeController@delete')->name('delete');
    });

    //Expenses Routes
    Route::group(['prefix' => 'expense', 'as' => 'expense.'], function () {
        Route::get('/', 'App\Http\Controllers\ExpenseController@index')->name('index');
        Route::get('/create', 'App\Http\Controllers\ExpenseController@create')->name('create');
        Route::post('/store', 'App\Http\Controllers\ExpenseController@store')->name('store');
        Route::get('{id}/edit', 'App\Http\Controllers\ExpenseController@edit')->name('edit');
        Route::post('{id}/update', 'App\Http\Controllers\ExpenseController@update')->name('update');
        Route::delete('{id}/delete', 'App\Http\Controllers\ExpenseController@delete')->name('delete');

        Route::get('/print', 'App\Http\Controllers\ExpenseController@expensesPrint')->name('print');
        Route::get('/export', 'App\Http\Controllers\ExpenseController@expensesExport')->name('export');
    });


    Route::group(['prefix' => 'report', 'as' => 'report.'], function () {
        Route::get('/stock', 'App\Http\Controllers\ReportController@stock')->name('stock');
        Route::match(['get', 'post'],'/profit', 'App\Http\Controllers\ReportController@grossProfit')->name('profit');
        Route::match(['get', 'post'],'/vehicle-sale', 'App\Http\Controllers\ReportController@vehicleSale')->name('vehicleSale');
        Route::match(['get', 'post'],'/due', 'App\Http\Controllers\ReportController@dueReport')->name('dueReport');
        Route::get('/sale-details', [App\Http\Controllers\ReportController::class, 'getSaleDetails'])->name('saleDetails');
    });

    Route::prefix('purchase')->name('purchase.')->group(function () {
        Route::get('/', [PurchaseController::class, 'index'])->name('index');
        Route::get('/vehicle', 'App\Http\Controllers\VehicleController@create')->name('purchaseVehicle');
        Route::get('/create', [PurchaseController::class, 'create'])->name('create');
        Route::post('/store', [PurchaseController::class, 'store'])->name('store');
        Route::get('/barcode/{barcode}', [PurchaseController::class, 'getDataByBarcode'])->name('getDataByBarcode');
        // Show invoice by invoice number
        Route::get('/invoice/{invoice_no}', [PurchaseController::class, 'invoice'])->name('invoice');
        Route::get('/{purchase}/pay-due', [PurchaseController::class, 'showPayDueForm'])->name('pay-due');
        Route::post('/{purchase}/pay-due', [PurchaseController::class, 'payDue'])->name('pay-due');
        Route::get('/{purchase}/return', [PurchaseController::class, 'returnPurchase'])->name('returnPurchase');
        Route::post('/{purchase}/return', [PurchaseController::class, 'returnPurchasePost'])->name('returnPurchasePost');
        Route::get('/parts-return', [PurchasePartsReturnListController::class, 'index'])->name('parts-return.index');
    });

    Route::group(['prefix' => 'user', 'as' => 'user.'], function () {
        Route::get('/', 'App\Http\Controllers\UserController@index')->name('index');
        Route::get('/create', 'App\Http\Controllers\UserController@create')->name('create');
        Route::post('/store', 'App\Http\Controllers\UserController@store')->name('store');
        Route::get('/edit/{id}', 'App\Http\Controllers\UserController@edit')->name('edit');
        Route::put('/update/{id}', 'App\Http\Controllers\UserController@update')->name('update');
        Route::delete('{id}/delete', 'App\Http\Controllers\UserController@delete')->name('delete');
    });

    //Balance Statement Routes

    Route::group(['prefix' => 'balance-statement', 'as' => 'balance-statement.'], function () {
        Route::get('/', [BalanceStatementController::class, 'index'])->name('index');
    });
});
