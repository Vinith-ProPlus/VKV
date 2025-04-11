<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BlogController;
use App\Http\Controllers\API\GeneralController;
use App\Http\Controllers\API\LaborController;
use App\Http\Controllers\API\MobileUserAttendanceController;
use App\Http\Controllers\API\SupportTicketController;
use App\Models\Admin\Labor\LaborDesignation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/sendForgotPasswordOTP', [AuthController::class, 'sendForgotPasswordOTP']);
Route::post('/verifyForgotPasswordOTP', [AuthController::class, 'verifyForgotPasswordOTP']);
Route::post('/changePassword', [AuthController::class, 'changePassword']);
Route::get('/user', [AuthController::class, 'profile'])->middleware('auth:sanctum');
Route::post('/updateProfile', [AuthController::class, 'updateProfile'])->middleware('auth:sanctum');
Route::post('/delete-account', [AuthController::class, 'deleteAccount'])->middleware('auth:sanctum');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/logout_all_devices', [AuthController::class, 'logout_all_devices'])->middleware('auth:sanctum');

Route::group(['prefix' => 'master', 'middleware' => 'auth:sanctum'], static function () {
    Route::post('/getCategories', [GeneralController::class, 'getCategories'])->name('getCategories');
    Route::post('/getProducts', [GeneralController::class, 'getProducts'])->name('getProducts');
    Route::post('/getStates', [GeneralController::class, 'getStates'])->name('getStates');
    Route::post('/getDistricts', [GeneralController::class, 'getDistricts'])->name('getDistricts');
    Route::post('/getCities', [GeneralController::class, 'getCities'])->name('getCities');
    Route::post('/getPinCodes', [GeneralController::class, 'getPinCodes'])->name('getPinCodes');
    Route::post('/getLeadSource', [GeneralController::class, 'getLeadSource'])->name('getLeadSource');
    Route::post('/getLeadStatus', [GeneralController::class, 'getLeadStatus'])->name('getLeadStatus');
    Route::post('/getUsers', [GeneralController::class, 'getUsers'])->name('getUsers');
    Route::post('/getRoles', [GeneralController::class, 'getRoles'])->name('getRoles');
    Route::post('/getProjects', [GeneralController::class, 'getProjects'])->name('getProjects');
    Route::post('/getSupervisorProjects', [GeneralController::class, 'getSupervisorProjects'])->name('getSupervisorProjects');
    Route::post('/getStages', [GeneralController::class, 'getStages'])->name('getStages');
    Route::post('/getContents', [GeneralController::class, 'getContent'])->name('getContent');
    Route::post('/getDocuments', [GeneralController::class, 'getDocuments'])->name('getDocuments');
    Route::post('/getLaborDesignations', [GeneralController::class, 'getLaborDesignations'])->name('getLaborDesignations');
    Route::post('/getProjectContractors', [GeneralController::class, 'getProjectContractors'])->name('getProjectContractors');
});

Route::middleware('auth:sanctum')->group(static function () {
    // Attendance
    Route::post('attendance/record-attendance', [MobileUserAttendanceController::class, 'recordAttendance']);
    Route::post('attendance/history', [MobileUserAttendanceController::class, 'getAttendanceHistory']);

    // Manage Task
    Route::post('manage-task/get-projects', [GeneralController::class, 'getTaskProjects']);
    Route::post('manage-task/get-task', [GeneralController::class, 'getTask']);
    Route::post('manage-task/list', [GeneralController::class, 'getTasks']);
    Route::post('manage-task/create-task', [GeneralController::class, 'createProjectTask']);
    Route::post('manage-task/update-task-status/{task}', [GeneralController::class, 'updateTaskStatus']);

    // Visitors
    Route::post('visitors/get-visitor', [GeneralController::class, 'getVisitor']);
    Route::post('visitors/get-visitors', [GeneralController::class, 'getVisitors']);
    Route::post('visitors/create-visitor', [GeneralController::class, 'createVisitor']);

    // Support Ticket
    Route::post('support/getSupportTypes', [SupportTicketController::class, 'getSupportTypes'])->name('getSupportTypes');
    Route::post('support/getSupportTicketsHistory', [SupportTicketController::class, 'getSupportTicketsHistory'])->name('getSupportTicketsHistory');
    Route::post('support/getSupportTicketMessages', [SupportTicketController::class, 'getSupportTicketMessages'])->name('getSupportTicketMessages');
    Route::post('support/createTicket', [SupportTicketController::class, 'createTicket'])->name('createTicket');
    Route::post('support/storeMessage/{supportTicket}', [SupportTicketController::class, 'storeMessage'])->name('storeMessage');

    // Blog
    Route::post('blog/getBlogDateMonth', [BlogController::class, 'getBlogDateMonth'])->name('getBlogDateMonth');
    Route::post('blog/getBlogData', [BlogController::class, 'getBlogData'])->name('getBlogData');
    Route::post('blog/getDamagedData', [BlogController::class, 'getDamagedData'])->name('getDamagedData');
    Route::post('blog/getCompletedTaskData', [BlogController::class, 'getCompletedTaskData'])->name('getCompletedTaskData');
    Route::post('blog/createBlog', [BlogController::class, 'createBlog'])->name('createBlog');


    // Blog
    Route::post('labors/getLaborDates', [LaborController::class, 'getLaborDates'])->name('getLaborDates');
    Route::post('labors/getLaborData', [LaborController::class, 'getLaborData'])->name('getLaborData');
    Route::post('labors/create', [LaborController::class, 'storeMultipleLabors'])->name('storeMultipleLabors');
    Route::post('labors/getLaborsByProject', [LaborController::class, 'getLaborsByProject'])->name('getLaborsByProject');
    Route::post('labors/reallocateLabors', [LaborController::class, 'reallocateLabors'])->name('reallocateLabors');



































    Route::post('HomeScreen', [GeneralController::class, 'HomeScreen']);
    Route::post('update-fcm-token', [GeneralController::class, 'updateFcmToken']);
    Route::post('record-location-history', [GeneralController::class, 'recordLocationHistory']);

});
