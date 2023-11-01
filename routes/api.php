<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CardCodeController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\AccessTokenController;
use App\Http\Controllers\BannerController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\ContentItemController;
use App\Http\Controllers\ItsnologyInfoController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Models\CardCode;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\EmailVerificationController;
use App\Http\Controllers\PrivateInformationController;
use App\Http\Controllers\ResetPasswordController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/



Route::post('/register', [AuthController::class, 'register']); //->name('verification.verify');
Route::post('/signIn', [AuthController::class, 'signIn']);

Route::group(['prefix' => 'v1'], function () {
    Route::get('email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])->name('verification.verify');
    Route::post('email/resend', [EmailVerificationController::class, 'resend'])->name('verification.resend');
});

//reset email routes
Route::post("/sendResetPasswordEmail", [ResetPasswordController::class, "resetPassword"]);
Route::get('/reset-password/{token}', [ResetPasswordController::class, 'resetPasswordAfterAuthentication'])->name('reset-link');

// Auth::routes([
//     "verify" => true
// ]);
// Route::get('/getAccessToken', [AccessTokenController::class, 'getAccessToken']);
// Route::post('/logInCheck', [User::class, 'logInCheck'])->name('logInCheck');


Route::group([
    'middleware' => ['auth:sanctum', 'verified']
], function () {

    //Routes Allow to admin only
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::resource('notifications', NotificationController::class);
        Route::get("/statics", [UserController::class, "getStatics"]);


        Route::resource('users', UserController::class)->only(['store', 'index', 'show']);
        Route::post('/delete_users', [UserController::class, 'destroyUsers']);
        Route::post('/update_users_to_admin', [UserController::class, 'updateToAdmin']);
        Route::post('/update_users_to_customers', [UserController::class, 'updateToCustomer']);

        //index users
        Route::get('/indexWithOutTrashed', [UserController::class, 'indexWithOutTrashed']);
        Route::get('/indexTrashed', [UserController::class, 'indexTrashed']);
        Route::get('/indexAdmins', [UserController::class, 'indexAdmins']);
        //Search Users
        Route::post('/searchUsers', [UserController::class, 'searchUsers']);


        Route::resource('categories', CategoryController::class)->except("index", "show");

        Route::resource('card_codes', CardCodeController::class);
        Route::get('/card_codes/{cardCode}/product', [CardCodeController::class, 'showProductOfCard'])->name('card_codes.product');


        Route::resource('products', ProductController::class)->only(['store', 'update', 'destroy']);

        Route::post('/delete_products', [ProductController::class, 'destroyProducts']);
        Route::post('/get_by_category_name', [ProductController::class, 'get_by_category_name']);

        Route::get('/get_only_trashed', [ProductController::class, 'indexTrashedOnly']);
        Route::get('/get_without_trashed', [ProductController::class, 'indexWithOutTrashed']);
        Route::get('/get_services', [ProductController::class, 'indexServices']);
        Route::get('/get_digital_cards', [ProductController::class, 'indexDigitalCards']);

        // Route::get('/products/by-category/{category}', [ProductController::class, 'showAllByCategoryName'])->name('category.products');
        Route::get('/products/{product}/card-codes', [ProductController::class, 'showCardCodes'])->name('products.card-codes');

        Route::resource('orders', OrderController::class);
        Route::post('delete_orders', [OrderController::class, "destroyOrders"]);

        Route::resource('order-items', OrderItemController::class);

        Route::resource('reviews', ReviewController::class);

        //public info
        Route::post("/update_values", [ItsnologyInfoController::class, "update"]);
        Route::get("/get_value_by_key/{key}", [ItsnologyInfoController::class, "get_value_by_key"]);

        // private info
        Route::post("/update_private_values", [PrivateInformationController::class, "update"]);
        Route::get("/get_private_values", [PrivateInformationController::class, "getValues"]);


        //banner controller
        Route::resource('banners', BannerController::class)->only(['store', 'update', 'destroy']);
        Route::resource('contents', ContentController::class)->only(['store', 'update', 'destroy']);
        Route::resource('contentItems', ContentItemController::class)->only(['store', 'update', 'destroy']);
    });

    //get user info
    Route::get('/user-profile', [UserController::class, 'getUserProfile']);
    Route::resource('users', UserController::class)->only(['update', 'destroy']);
    Route::post('/users_update_password/{user}', [UserController::class, 'updatePassword']);

    //check if token is valid
    Route::post('/check-token', [AuthController::class, 'checkToken']);


    Route::post('/logOut', [AuthController::class, "SignOut"]);


    Route::resource('users', UserController::class)->except(['store', 'index']);
    Route::get('users/{user}/reviews', [UserController::class, 'showReviews']);
    Route::get('users/{user}/payments', [UserController::class, 'showPayments']);
    Route::get('users/{user}/orders', [UserController::class, 'showOrders']);

    Route::resource('products', ProductController::class)->only(['index', 'show']);


    Route::post("/store_order", [PaymentController::class, "store_order"])->name("store_order");

    Route::post("/check_out/{user}", [PaymentController::class, "check_out"])->name("check_out");

    Route::get("/callback", [PaymentController::class, "callback"])->name("callback_route");
});

//banner controller exept store, update, delete
Route::resource('banners', BannerController::class)->only(['index']);
Route::resource('contents', ContentController::class)->only(['index']);

Route::get("/get_values", [ItsnologyInfoController::class, "getValues"]);

//get services from categories
Route::resource('categories', CategoryController::class)->only("index", "show");
Route::get("/get_services", [CategoryController::class, "indexServices"]);
Route::get("/get_cards", [CategoryController::class, "indexCards"]);





//get products image
Route::get('products_images/{image}', function ($image) {
    $path = public_path('storage/images/products/' . $image);

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path);
})->where('image', '.*');


//get category banner image
Route::get('category_banners_images/{image}', function ($image) {
    $path = public_path('storage/images/categories/banners/' . $image);

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path);
})->where('image', '.*');


//get category icon image
Route::get('category_icons_images/{image}', function ($image) {
    $path = public_path('storage/images/categories/icons/' . $image);

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path);
})->where('image', '.*');


//get itsnology icon image
Route::get('itsnology_icon/{image}', function ($image) {
    $path = public_path('storage/images/itsnology/' . $image);

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path);
})->where('image', '.*');

//get banners image
Route::get('get_banner/{image}', function ($image) {
    $path = public_path('storage/images/banners/' . $image);

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path);
})->where('image', '.*');

//get banners image
Route::get('get_content/{image}', function ($image) {
    $path = public_path('storage/images/contents/' . $image);

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path);
})->where('image', '.*');

Route::get('get_content_item_icon/{image}', function ($image) {
    $path = public_path('storage/images/contents/items_icons/' . $image);

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path);
})->where('image', '.*');
