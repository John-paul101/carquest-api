<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BuyerController;
use App\Http\Controllers\ShippingLocationController;

Route::group([
    'middleware' => 'api'
], function () {
    // Admin Routes
    Route::post('/admin-login', [AuthController::class, 'adminLogin']);

    // Buyer Routes
    Route::post('/buyer-login', [AuthController::class, 'buyerLogin']);
    Route::post('/buyer-register', [AuthController::class, 'buyerRegister']);
});

Route::group([
    'middleware' => ['api', 'auth:admin']
], function () {
    // Admin Routes
    Route::post('/admin-logout', [AuthController::class, 'adminLogout']);
    Route::get('/admin-dashboard', [AdminController::class, 'adminDashboard']);
    Route::patch('/admin-update/{id}', [AdminController::class, 'adminUpdate']);
    Route::post('/admin-add-cars', [AdminController::class, 'addCar']);
    Route::get('/admin-view-cars', [AdminController::class, 'viewAllCars']);
    Route::patch('/admin-update-car/{id}', [AdminController::class, 'updateCar']);
    Route::delete('/admin-delete-car/{id}', [AdminController::class, 'deleteCar']);
    Route::get('/admin-view-buyers', [AdminController::class, 'viewAllBuyers']);
    Route::delete('/admin-delete-buyer/{id}', [AdminController::class, 'deleteBuyer']);
    Route::patch('/admin-update-buyer/{id}', [AdminController::class, 'updateBuyer']);
    Route::get('/admin-view-orders', [AdminController::class, 'viewAllOrders']);
    Route::delete('/admin-delete-order/{id}', [AdminController::class, 'deleteOrder']);
    Route::patch('/admin-update-order/{id}', [AdminController::class, 'updateOrder']);
    Route::get('/admin-view-payments', [AdminController::class, 'viewAllPayments']);

    // Shipping Location Routes
    Route::post('/admin-add-shipping-location', [AdminController::class, 'addShippingLocation']);
    Route::get('/admin-view-shipping-locations', [AdminController::class, 'viewAllShippingLocations']);
    Route::delete('/admin-delete-shipping-location/{id}', [AdminController::class, 'deleteShippingLocation']);
    Route::patch('/admin-update-shipping-location/{id}', [AdminController::class, 'updateShippingLocation']);

    // Shipping Method Routes
    Route::post('/admin-add-shipping-method', [AdminController::class, 'addShippingMethod']);
    Route::get('/admin-view-shipping-methods', [AdminController::class, 'viewAllShippingMethods']);
    Route::get('/admin-view-shipping-methods/{locationId}', [AdminController::class, 'viewAllShippingMethods']);
    Route::delete('/admin-delete-shipping-method/{id}', [AdminController::class, 'deleteShippingMethod']);
    Route::patch('/admin-update-shipping-method/{id}', [AdminController::class, 'updateShippingMethod']);

    Route::patch('/orders/{id}/status', [AdminController::class, 'updateOrderStatus']);
});

Route::group([
    'middleware' => ['api', 'auth:buyer']
], function () {
    // Buyer Routes
    Route::post('/buyer-logout', [AuthController::class, 'buyerLogout']);
    Route::get('/buyer-dashboard', [BuyerController::class, 'buyerDashboard']);
    Route::patch('/buyer-update/{id}', [BuyerController::class, 'buyerUpdate']);
    Route::get('/cars/{id}', [BuyerController::class, 'getCarDetails']);
    Route::post('/orders', [BuyerController::class, 'createOrder']);
    Route::get('/orders', [BuyerController::class, 'getAllOrders']);
    Route::get('/orders/{id}', [BuyerController::class, 'getOrderDetails']);
});
Route::group([
    'middleware' => ['api']
], function () {
    // Shipping Location Routes
    Route::get('/cars', [BuyerController::class, 'getAllCars']);
    Route::get('/featured-cars', [BuyerController::class, 'getFeaturedCars']);
    Route::get('/shipping-locations', [ShippingLocationController::class, 'index']);

    // Shipping Method Routes
    Route::get('/shipping-methods', [ShippingLocationController::class, 'getAllShippingMethods']);
    Route::get('/shipping-methods/{locationId}', [ShippingLocationController::class, 'getShippingMethodsByLocation']);
    Route::post('/shipping-price', [ShippingLocationController::class, 'getShippingPrice']);
});
