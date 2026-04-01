<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\EngineerMappingController;

Route::get('/', [LoginController::class, 'showLoginForm'])->name('login')->middleware('guest');
Route::get('/login', [LoginController::class, 'showLoginForm'])->middleware('guest');
Route::post('/login', [LoginController::class, 'login'])->middleware('guest');

Route::middleware('auth')->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'adminDashboard'])
        ->name('admin.dashboard'); //admin Dashboard
    Route::get('/admin/dashboard/data', [DashboardController::class, 'adminDashboardData'])
        ->name('admin.dashboard.data');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/engineer-stats', [DashboardController::class, 'engineerStats'])
        ->name('dashboard.engineer_stats');
    Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
    Route::get('/tickets/open', [TicketController::class, 'openTickets'])->name('tickets.open');
    Route::get('/tickets/engineer-open', [TicketController::class, 'engineerOpenTickets'])
        ->name('tickets.engineer_open');
    Route::get('/tickets/by-category', [TicketController::class, 'byCategory'])
        ->name('tickets.by_category');
    // CRUD ticket
    Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{id}', [TicketController::class, 'show'])->name('tickets.show');
    Route::put('/tickets/{id}', [TicketController::class, 'update'])->name('tickets.update');
    Route::post('/tickets/{id}/priority', [TicketController::class, 'updatePriority'])
        ->name('tickets.update_priority');

    // CRUD Categories
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/edit/{id}', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::post('/categories/update/{id}', [CategoryController::class, 'update'])->name('categories.update');
    Route::get('/categories/delete/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Manage Sub Categories (config)
    Route::get('/config/sub-categories', [CategoryController::class, 'subCategoriesIndex'])
        ->name('config.sub_categories');
    Route::get('/config/sub-categories/edit/{id}', [CategoryController::class, 'editSubCategory'])
        ->name('config.sub_categories.edit');
    Route::post('/config/sub-categories', [CategoryController::class, 'storeSubCategory'])
        ->name('config.sub_categories.store');
    Route::post('/config/sub-categories/update/{id}', [CategoryController::class, 'updateSubCategory'])
        ->name('config.sub_categories.update');
    Route::get('/config/sub-categories/delete/{id}', [CategoryController::class, 'destroySubCategory'])
        ->name('config.sub_categories.destroy');

    // Engineer Mapping (classic checkbox-based)
    Route::get('/config/engineer-mapping', [EngineerMappingController::class, 'index'])
        ->name('config.engineer_mapping');
    Route::get('/config/engineer-mapping/category/{id}', [EngineerMappingController::class, 'showCategory'])
        ->name('config.engineer_mapping.category');
    Route::post('/config/engineer-mapping', [EngineerMappingController::class, 'store'])
        ->name('config.engineer_mapping.store');
    Route::post('/config/engineer-mapping/category/{id}/add', [EngineerMappingController::class, 'addEngineer'])
        ->name('config.engineer_mapping.add_engineer');
    Route::post('/config/engineer-mapping/category/{id}/remove', [EngineerMappingController::class, 'removeEngineer'])
        ->name('config.engineer_mapping.remove_engineer');

    //sub-categories
    Route::get('/sub-categories/{categoryId}', [TicketController::class, 'getSubCategories'])
        ->name('sub_categories.by_category');

    Route::get('/dashboard/category/{id}', [DashboardController::class, 'subCategories'])
        ->name('dashboard.subcategories'); //for dashbaord sub-cat selecting

    // replies
    Route::post('/tickets/{id}/replies', [TicketController::class, 'storeReply'])
        ->name('tickets.replies.store');

    //notes
    // route::post('/tickets/{ticket}/notes', [TicketController::class, 'storeNote'])
    // ->name('tickets.notes.store');

    // assign engineers
    Route::post('/tickets/{id}/assign', [TicketController::class, 'assignEngineer'])
        ->name('tickets.assign');

    // Engineer take action (attend / forward)
    Route::post('/tickets/{id}/take-action', [TicketController::class, 'takeAction'])
        ->name('tickets.take_action');

    // reports
    Route::get('/reports/branch', [ReportsController::class, 'branchReport']);
    Route::get('/reports/problem', [ReportsController::class, 'problemReport'])->name('reports.problem');


    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    //redirect to login
    //  Route::get('/dashboard', function () {
    //     return redirect('/tickets');
    // })->name('dashboard');
});
