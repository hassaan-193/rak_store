<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ToolController;
use App\Http\Controllers\ToolAssignmentController;
use App\Http\Controllers\ToolReturnController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\IncomingMaterialController;
use App\Http\Controllers\OutgoingMaterialController;
use App\Http\Controllers\ReturnMaterialController;
use App\Http\Controllers\MaterialTransferController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\UserRoleController;
use App\Http\Controllers\Web\PermissionViewController;
use App\Http\Controllers\Web\RoleViewController;
use App\Http\Controllers\Web\UserRoleViewController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PortalOrderController;
use App\Http\Controllers\RecycleBinController;



require __DIR__.'/auth.php';

Route::middleware(['auth'])->group(function () {
    Route::resource('user-management', UserController::class)->parameters([
        'user-management' => 'user'
    ]);
});
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [UserController::class, 'profile'])->name('profile');
    Route::post('/profile/update', [UserController::class, 'updateProfile'])->name('profile.update');

});

// Roles and permissions start here 
// ==============================================
Route::middleware(['auth'])->group(function () {

    Route::resource('/admin/users', UserRoleViewController::class)->only(['index', 'update'])->names('users');
    Route::resource('/admin/roles', RoleViewController::class)->names('roles');
    Route::resource('/admin/permissions', PermissionViewController::class)->names('permissions');


    Route::get('/users', [UserRoleController::class, 'index']);
    Route::get('/users/{id}/roles', [UserRoleController::class, 'show']);
    Route::post('/users/{id}/roles', [UserRoleController::class, 'assignRoles']);


    Route::get('/roles', [RoleController::class, 'index']);
    Route::post('/roles', [RoleController::class, 'store']);
    Route::get('/roles/{id}', [RoleController::class, 'show']);
    Route::put('/roles/{id}', [RoleController::class, 'update']);
    Route::put('/admin/roles/{role}/rename', [RoleViewController::class, 'rename'])->name('roles.rename');

    Route::get('/permissions', [PermissionController::class, 'index']);
    Route::post('/permissions', [PermissionController::class, 'store']);

});

// ==============================================
// Roles and permissions end here 






// outgoin materials approval and rejection starts here 
// ==============================================
Route::middleware(['auth'])->group(function () {
    Route::get('/outgoing-materials/{id}/approve', [OutgoingMaterialController::class, 'showApprovalForm'])->name('outgoing-materials.approve-form');
    Route::post('/outgoing-materials/{id}/approve', [OutgoingMaterialController::class, 'approve'])->name('outgoing-materials.approve');
    Route::post('/outgoing-materials/{id}/reject', [OutgoingMaterialController::class, 'reject'])->name('outgoing-materials.reject');
});
// outgoin materials approval and rejection ends here 
// ==============================================




// outgoin materials transfer to general starts here 
// ==============================================

Route::prefix('materials/transfer')->name('materials.transfer.')->group(function () {
    Route::get('/', [MaterialTransferController::class, 'index'])->name('index'); // display all
    Route::post('store/{item}', [MaterialTransferController::class, 'store'])->name('store'); // transfer
});

// outgoin materials transfer to general ends here 
// ==============================================



// tools availability seperate view starts here 
// ==============================================
Route::middleware(['auth'])->group(function () {
    Route::get('/tools/availability', [ToolController::class, 'availability'])->name('tools.availability');
});
// ==============================================
// tools availability seperate view ends here 



Route::middleware(['auth'])->group(function () {
    Route::get('/incoming-materials/filter-by-project', [IncomingMaterialController::class, 'filterByProject'])
    ->name('incoming-materials.filter-by-project');
});


Route::middleware(['auth'])->group(function () {
    Route::get('return-materials/load-materials', [ReturnMaterialController::class, 'loadMaterials'])->name('return-materials.load-materials');
    Route::resource('return-materials', ReturnMaterialController::class);
});

Route::middleware(['auth'])->group(function () {
    Route::get('/outgoing-materials/get-materials', [OutgoingMaterialController::class, 'getMaterials'])->name('outgoing-materials.get-materials');
    Route::get('/outgoing-materials-pending', [OutgoingMaterialController::class, 'pending'])->name('outgoing-materials.pending');
    Route::get('/outgoing-materials-processed', [OutgoingMaterialController::class, 'processed'])->name('outgoing-materials.processed');
    Route::put('/outgoing-materials/{id}', [OutgoingMaterialController::class, 'update'])->name('outgoing-materials.update');
    Route::resource('outgoing-materials', OutgoingMaterialController::class);
});

Route::middleware(['auth'])->group(function () {
    Route::resource('incoming-materials', IncomingMaterialController::class);
});

Route::middleware(['auth'])->group(function () {
    Route::resource('suppliers', SupplierController::class);
    Route::resource('drivers', DriverController::class);
});

Route::middleware(['auth'])->group(function () {

Route::resource('tools', ToolController::class);
Route::get('/tool-assignments/export', [ToolAssignmentController::class, 'export'])->name('tool-assignments.export');
Route::resource('tool-assignments', ToolAssignmentController::class);
});

Route::middleware(['auth'])->group(function () {

Route::get('/tool-returns/export', [ToolReturnController::class, 'export'])->name('tool-returns.export');
Route::get('/tool-returns/log', [ToolReturnController::class, 'logIndex'])->name('tool-returns.log');
Route::resource('tool-returns', ToolReturnController::class);
});

Route::middleware(['auth'])->group(function () {

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');


});



Route::post('/logout', function () {
    Auth::logout();
    return redirect('/login');
})->name('logout');


Route::resource('projects', ProjectController::class);

// FTS portal purchase orders: delivery log (store receipt / direct to site / from stock).
// Viewing: super-admin, approver or anyone with po-receive. Recording / voiding: po-receive only.
// There is intentionally no delete route for entries.
Route::middleware(['auth', 'role_or_permission:super-admin|approver|po-receive'])->group(function () {
    Route::get('portal-orders', [PortalOrderController::class, 'index'])->name('portal-orders.index');
    Route::get('portal-orders/{portalOrder}', [PortalOrderController::class, 'show'])->name('portal-orders.show');
    Route::get('portal-orders/{portalOrder}/entries/{entry}/dn', [PortalOrderController::class, 'downloadDn'])->name('portal-orders.entries.dn');
});
Route::middleware(['auth', 'permission:po-receive'])->group(function () {
    Route::post('portal-orders/{portalOrder}/entries', [PortalOrderController::class, 'storeEntry'])->name('portal-orders.entries.store');
    Route::post('portal-orders/{portalOrder}/entries/{entry}/void', [PortalOrderController::class, 'voidEntry'])->name('portal-orders.entries.void');
    Route::post('portal-orders/{portalOrder}/resync', [PortalOrderController::class, 'resync'])->name('portal-orders.resync');
});

// Recycle Bin: view for super-admin|approver, restore for super-admin only.
// There is intentionally no delete / force-delete / empty-bin route.
Route::middleware(['auth', 'role:super-admin|approver'])->group(function () {
    Route::get('recycle-bin', [RecycleBinController::class, 'index'])->name('recycle-bin.index');
});
Route::middleware(['auth', 'role:super-admin'])->group(function () {
    Route::post('recycle-bin/{type}/{id}/restore', [RecycleBinController::class, 'restore'])->name('recycle-bin.restore');
});
