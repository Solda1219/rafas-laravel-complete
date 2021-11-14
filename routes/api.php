<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
use App\Http\Controllers\Controller;

Route::post('getAllProducts', [Controller::class, 'getAllProducts'])->name('products.getAll');
Route::post('getProductsByCategoryId', [Controller::class, 'getProductsByCategoryId'])->name('products.getProductsByCategoryId');
Route::post('productsByCategoryIdFilter', [Controller::class, 'productsByCategoryIdFilter'])->name('products.productsByCategoryIdFilter');//this is in categorys stack
Route::post('productsByName', [Controller::class, 'productsByName'])->name('products.productsByName');

Route::post('getBannerHighProduct', [Controller::class, 'getBannerHighProduct'])->name('products.bannerProduct');
Route::post('getFeaturedProducts', [Controller::class, 'getFeaturedProducts'])->name('products.featuredProducts');

Route::get('getAllCategories', [Controller::class, 'getAllCategories'])->name('categories.getAll');

Route::post('reviewsByProductId', [Controller::class, 'reviewsByProductId'])->name('product.reviews');

Route::post('createComment', [Controller::class, 'createComment'])->name('product.review.create');

//payment
Route::get('getAllCurrencies', [Controller::class, 'getAllCurrencies'])->name('currency.getAll');
Route::get('getAllCouponCode', [Controller::class, 'getAllCouponCode'])->name('product.couponCode');

Route::post('getAddressDefault', [Controller::class, 'getAddressDefault'])->name('customer.address');

Route::post('createNewOrder', [Controller::class, 'createNewOrder'])->name('order.create');
Route::post('ordersByCustomerId', [Controller::class, 'ordersByCustomerId'])->name('order.create');
Route::post('updateOrder', [Controller::class, 'updateOrder'])->name('order.update');

//login
Route::get('emailUnique', [Controller::class, 'emailUnique'])->name('auth.emailUnique');
Route::get('phoneUnique', [Controller::class, 'phoneUnique'])->name('auth.phoneUnique');

Route::post('register', [Controller::class, 'register'])->name('auth.register');
Route::get('getUserById', [Controller::class, 'getUserById'])->name('auth.getUserById');
Route::post('login',[Controller::class, 'login'])->name('auth.login');
Route::post('resetPass',[Controller::class, 'resetPass'])->name('auth.resetPass');

//profile
Route::post('getCustomerAddressInfo', [Controller::class, 'getCustomerAddressInfo'])->name('user.getCustomerAddressInfo');
Route::post('removeAddress', [Controller::class, 'removeAddress'])->name('user.removeAddress');
Route::post('addAddress', [Controller::class, 'addAddress'])->name('user.addAddress');

Route::post('getCustompages', [Controller::class, 'getCustompages'])->name('page.getCustompages');

//notification
Route::post('sendPushNotiToAdmin', [Controller::class, 'sendPushNotiToAdmin'])->name('push.fcmToAdmin');