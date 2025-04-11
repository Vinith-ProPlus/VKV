<?php

use App\Http\Controllers\Admin\CRM\LeadController;
use App\Http\Controllers\Admin\CRM\LeadSourceController;
use App\Http\Controllers\Admin\CRM\VisitorController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\Settings\ContentController;
use App\Http\Controllers\Admin\Users\BlogController;
use App\Http\Controllers\Admin\Users\SupportTicketController;
use App\Http\Controllers\Admin\Users\SupportTicketMessageController;
use App\Http\Controllers\admin\Users\UserController;
use App\Http\Controllers\Auth\SocialLoginController;
use App\Http\Controllers\GeneralController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectStockController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\PurchaseRequestController;
use App\Http\Controllers\SqlImportController;
use App\Http\Controllers\StockUsageLogController;
use App\Http\Controllers\Admin\ProjectReports\ProjectReportsController;
use App\Models\Admin\Master\City;
use App\Models\Admin\Master\District;
use App\Models\Admin\Master\Pincode;
use App\Models\Admin\Master\State;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Tax;
use App\Models\UnitOfMeasurement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;


Route::get('/clear', static function() {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');
    return "Cleared!";
});

Route::get('cliff-chat-notification-token', static function() {
  return generateFirebaseAccessToken(storage_path('app/firebase/rdf_firebase_credentials.json'));
});

Route::get('/', static function () {
    if(auth()->user()){
        return redirect()->route('dashboard');
    }
    return view('auth.login');
});

Route::get('/import-default-rows', [SqlImportController::class, 'importSqlFiles']);
Route::get('/import-table-rows/{TableName}', [SqlImportController::class, 'importTableRows']);
Route::get('/export-menus', [SqlImportController::class, 'exportMenus']);


Route::get('/dashboard', static function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::group(['prefix'=>'admin'], static function (){
    Route::middleware('auth')->group(function () {

        Route::group(['prefix'=>'manage-projects'], static function () {
            require __DIR__.'/admin/manage_projects.php';
        });

        Route::group(['prefix'=>'master'], static function (){
            require __DIR__.'/admin/master.php';
            Route::get('categories/list', static function () {
                return response()->json(ProductCategory::select('id', 'name')->where('is_active', 1)->get());
            })->name('categories.list');

            Route::get('taxes/list', static function () {
                return response()->json(Tax::select('id', 'name')->where('is_active', 1)->get());
            })->name('taxes.list');

            Route::get('uoms/list', static function () {
                return response()->json(UnitOfMeasurement::select('id', 'name')->where('is_active', 1)->get());
            })->name('uoms.list');
            Route::get('states/list', static function () {
                return response()->json(State::select('id', 'name')->where('is_active', 1)->get());
            })->name('states.list');
            Route::get('districts/list', static function (Request $request) {
                logger("districts: ".$request);
                return response()->json(District::select('id', 'name')->where('is_active', 1)->get());
            })->name('districts.list');
            Route::get('cities/list', static function () {
                return response()->json(City::select('id', 'name')->where('is_active', 1)->get());
            })->name('cities.list');
            Route::get('pincodes/list', static function () {
                return response()->json(Pincode::select('id', 'name')->where('is_active', 1)->get());
            })->name('pincodes.list');
        });


        //role crud
            Route::get('roles', [RoleController::class, 'index'])->name('role.index');
            Route::get('role/create', [RoleController::class, 'create'])->name('role.create')->middleware('can:Create Roles and Permissions');
            Route::post('role/store', [RoleController::class, 'store'])->name('role.store');
            Route::get('role/{id}/edit', [RoleController::class, 'edit'])->name('role.edit');
            Route::get('role/{id}', [RoleController::class, 'show'])->name('role.show');
            Route::put('role/update/{id}', [RoleController::class, 'update'])->name('role.update');
            Route::delete('role/delete/{id}', [RoleController::class, 'destroy'])->name('role.destroy');

        Route::resource('users', UserController::class);
        Route::put('users/restore/{id}', [UserController::class, 'restore'])->name('users.restore')->middleware('can:Restore Users');

        Route::resource('lead_sources', LeadSourceController::class);
        Route::put('lead_sources/restore/{id}', [LeadSourceController::class, 'restore'])->name('lead_sources.restore')->middleware('can:Restore Lead Source');

        Route::resource('leads', LeadController::class);
        Route::put('leads/restore/{id}', [LeadController::class, 'restore'])->name('leads.restore')->middleware('can:Restore Lead');

        Route::resource('visitors', VisitorController::class);
        Route::put('visitors/restore/{id}', [VisitorController::class, 'restore'])->name('visitors.restore')->middleware('can:Restore Visitors');

        Route::resource('contents', ContentController::class);
        Route::put('contents/restore/{id}', [ContentController::class, 'restore'])->name('contents.restore')->middleware('can:Restore Contents');

        Route::resource('blogs', BlogController::class);
        Route::put('blogs/restore/{id}', [BlogController::class, 'restore'])->name('blogs.restore')->middleware('can:Restore Blogs');

        Route::resource('support_tickets', SupportTicketController::class);
        Route::put('support_tickets/restore/{id}', [SupportTicketController::class, 'restore'])->name('support_tickets.restore')->middleware('can:Restore Support Tickets');
        Route::get('support_tickets/{supportTicket}/messages', [SupportTicketMessageController::class, 'loadMessages'])->name('support_tickets.loadMessages');
        Route::post('support_tickets/{supportTicket}/messages', [SupportTicketMessageController::class, 'storeMessage'])->name('support_tickets.storeMessage');
        Route::post('support_tickets/{supportTicket}/close', [SupportTicketController::class, 'close'])->name('support_tickets.close');

        Route::resource('purchase-requests', PurchaseRequestController::class);
        Route::post('purchase-requests/restore/{id}', [PurchaseRequestController::class, 'restore'])->name('purchase-requests.restore');

        Route::resource('purchase-orders', PurchaseOrderController::class);
        Route::post('purchase-orders/mark-delivered', [PurchaseOrderController::class, 'markAsDelivered'])->name('purchase-orders.mark-delivered');

        Route::resource('project-stocks', ProjectStockController::class)->except('show');
        Route::get('project-stocks/get-categories', [ProjectStockController::class, 'getCategories'])->name('project-stocks.get-categories');
        Route::get('project-stocks/get-products', [ProjectStockController::class, 'getProducts'])->name('project-stocks.get-products');
        Route::get('project-stocks/get-stock', [ProjectStockController::class, 'getStock'])->name('project-stocks.get-stock');
        Route::post('project-stocks/adjust', [ProjectStockController::class, 'adjust'])->name('project-stocks.adjust');

        Route::resource('stock-usages', StockUsageLogController::class)->except(['show']);
        Route::get('stock-usages/get-products-by-category', [StockUsageLogController::class, 'getProductsByCategory'])->name('stock-usages.get-products-by-category');
        Route::get('stock-usages/get-product-stock', [StockUsageLogController::class, 'getProductStock'])->name('stock-usages.get-product-stock');

        Route::prefix('payroll')->group(function () {
            Route::view('/', 'payroll.index')->name('payroll.index');
            Route::post('/unpaid-labor', [PayrollController::class, 'getUnpaidLabor'])->name('payroll.getUnpaidLabor');
            Route::post('/process-payment', [PayrollController::class, 'processPayment'])->name('payroll.processPayment');
            Route::get('/payroll/history', [PayrollController::class, 'payrollHistory'])->name('payroll.history');

        });
    });

});

Route::get('/getDistricts', [GeneralController::class, 'getDistricts'])->name('getDistricts');
Route::get('/getCities', [GeneralController::class, 'getCities'])->name('getCities');
Route::get('/getStates', [GeneralController::class, 'getStates'])->name('getStates');
Route::get('/getPinCodes', [GeneralController::class, 'getPinCodes'])->name('getPinCodes');
Route::get('/getLeadSource', [GeneralController::class, 'getLeadSource'])->name('getLeadSource');
Route::get('/getLeadStatus', [GeneralController::class, 'getLeadStatus'])->name('getLeadStatus');
Route::get('/getUsers', [GeneralController::class, 'getUsers'])->name('getUsers');
Route::get('/getSiteSupervisors', [GeneralController::class, 'getSiteSupervisors'])->name('getSiteSupervisors');
Route::get('/getEngineers', [GeneralController::class, 'getEngineers'])->name('getEngineers');
Route::get('/getRoles', [GeneralController::class, 'getRoles'])->name('getRoles');
Route::get('/getProjects', [GeneralController::class, 'getProjects'])->name('getProjects');
Route::get('/getStages', [GeneralController::class, 'getStages'])->name('getStages');
Route::get('/getSites', [GeneralController::class, 'getSites'])->name('getSites');
Route::get('/getSupportTypes', [GeneralController::class, 'getSupportTypes'])->name('getSupportTypes');
Route::post('/getDocuments', [GeneralController::class, 'getDocuments'])->name('getDocuments');
Route::post('/documentHandler', [GeneralController::class, 'documentHandler'])->name('documentHandler');
Route::post('/updateDocuments', [GeneralController::class, 'updateDocuments'])->name('updateDocuments');
Route::delete('deleteDocuments', [GeneralController::class,'deleteDocuments'])->name('deleteDocuments');
Route::get('/getContractTypes', [GeneralController::class,'getContractTypes'])->name('getContractTypes');
Route::get('/getVendors', [GeneralController::class, 'getVendors'])->name('getVendors');
Route::get('/getContractors', [GeneralController::class, 'getContractors'])->name('getContractors');
Route::get('/getAmenities', [GeneralController::class, 'getAmenities'])->name('getAmenities');
Route::get('/getProjectContractors', [GeneralController::class, 'getProjectContractors'])->name('getProjectContractors');
Route::get('/getLaborDesignations', [GeneralController::class, 'getLaborDesignations'])->name('getLaborDesignations');
Route::get('/getCategories', [GeneralController::class, 'getCategories'])->name('getCategories');
Route::get('/getProductsByCategory', [GeneralController::class, 'getProductsByCategory'])->name('getProductsByCategory');

Route::get('/getAllProjects', [GeneralController::class,'getAllProjects'])->name('projects.all');
Route::get('/getProjectTasks', [GeneralController::class,'getProjectTasks'])->name('project.tasks');
Route::get('/getSupervisors', [GeneralController::class,'getSupervisors'])->name('getSupervisors');
Route::get('/getCheckedInSupervisors', [GeneralController::class,'getCheckedInSupervisors'])->name('getCheckedInSupervisors');
Route::get('/getLaborStatus', [GeneralController::class,'getLaborStatus'])->name('getLaborStatus');
Route::get('/project-products', static function () {
    return Product::with('category')->get();
});

Route::controller(SocialLoginController::class)->group(function () {
    Route::get('/apple-login', 'loginWithApple')->name('login-with-apple');
    Route::post('/apple-login-callback', 'loginWithAppleCallback')->name('apple-login-callback');
});

Route::group(['prefix' => 'project_reports'], static function () {
    Route::get('/', [ProjectReportsController::class, 'index'])->name('project_reports.index');
    Route::get('/create', [ProjectReportsController::class, 'create'])->name('project_reports.create');
    Route::get('/getProjectTasks', [ProjectReportsController::class, 'getProjectTasks'])->name('getProjectTasks');
    Route::get('/tasksTableLists', [ProjectReportsController::class, 'tasksTableLists'])->name('tasksTableLists');
    Route::get('/contractsTableLists', [ProjectReportsController::class, 'contractsTableLists'])->name('contractsTableLists');
});

require __DIR__.'/auth.php';

Route::get('/csrf-token', static function (Request $request) {
    return response()->json(['csrf_token' => csrf_token()]);
});
