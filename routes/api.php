<?php

use App\Http\Controllers\AboutLibrariesController;
use App\Http\Controllers\AddressController;
use App\Http\Controllers\AdminInformationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CustomerInformationController;
use App\Http\Controllers\EarningController;
use App\Http\Controllers\FavoriteCategoryController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\LoadImageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OfferController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\RateController;
use App\Http\Controllers\SaveBookController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\StatisticsController;
use App\Models\CustomerInformation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::get('/test-online', function () {
    dd('online(:');
});

Route::controller(AuthController::class)
    ->group(function () {
        Route::post('/register', 'register');
        Route::post('/login', 'login');
        Route::post('/provider/loginOrRegister', 'loginOrRegister');
        Route::get('/verify/{verification_code}', 'verify');
        Route::get('/profile', 'profile');
        Route::post('/logout', 'logout');
        Route::get('/is_verified', 'is_verified');
    });

Route::post('/change_fcm_token', [NotificationController::class, 'change_fcm_token']);
Route::prefix('/password')->controller(ForgotPasswordController::class)
    ->group(function () {
        Route::post('/email', 'forgot')->name('forgotPassword');
        Route::post('/reset', 'reset')->name('resetPassword');
    });

Route::post('/password/change', ChangePasswordController::class);
Route::prefix('/information/admin')->controller(AdminInformationController::class)
    ->group(function () {
        Route::post('/', 'set');
        Route::put('/{admin}', 'update');
    });

Route::prefix('/information/customer')->controller(CustomerInformationController::class)
    ->group(function () {
        Route::post('/', 'set');
        Route::put('/{customer}', 'update');
    });

Route::prefix('/address')->controller(AddressController::class)
    ->group(function () {
        Route::post('/', 'set');
        Route::put('/{address}', 'update');
        Route::get('/{address}', 'show');
    });

Route::prefix('/image')->controller(LoadImageController::class)
    ->group(function () {
        Route::post('/library', 'libraryImage');
        Route::post('/book/{book}', 'bookImage');
    });

Route::prefix('/book')->controller(BookController::class)
    ->group(function () {
        Route::post('/store', 'store');
        Route::put('/{book}',  'update');
        Route::delete('/{book}', 'destroy');
        Route::get('/', 'index');
        Route::get('/{book}', 'show');
    });

Route::prefix('/category/favorite')->controller(FavoriteCategoryController::class)
    ->group(function () {
        Route::post('/', 'store');
        Route::get('/showCategories', 'showFavoriteCategories');
        Route::get('/showBooks', 'showBooksInFavoriteCategories');
    });

Route::prefix('/comment')->controller(CommentController::class)
    ->group(function () {
        Route::post('/{book}', 'store');
        Route::put('/{comment}', 'update');
        Route::delete('/{comment}', 'destroy');
        Route::get('/{book}',  'index');
    });

Route::prefix('/quote')->controller(QuoteController::class)
    ->group(function () {
        Route::post('/{book}', 'store');
        Route::put('/{quote}', 'update');
        Route::delete('/{quote}', 'destroy');
        Route::get('/', 'index');
        Route::get('/{book}', 'index_on_book');
        Route::get('/{quote}/show', 'show');
    });

Route::prefix('/book/rate')->controller(RateController::class)
    ->group(function () {
        Route::post('/{book}', 'storeOrUpdate');
        Route::get('/{book}', 'show');
        Route::get('/top/top_rate', 'top_rated');
    });

Route::prefix('/save')->controller(SaveBookController::class)
    ->group(function () {
        Route::post('/{book}', 'storeOrDestroy');
        Route::get('/', 'index');
    });

Route::prefix('/search')->controller(SearchController::class)
    ->group(function () {
        Route::get('/book/{book}', 'bookSearch');
        Route::get('/books/recentSearches', 'recentSearches');
        Route::delete('/books/clearRecentSearches', 'clearRecentSearches');
        Route::get('/library/{library}', 'librarySearch');
        Route::get('/books/mostSearched', 'mostSearchedBooks');
    });

Route::prefix('/category')->controller(CategoryController::class)
    ->group(function () {
        Route::get('/', 'index');
        Route::get('/{category}', 'show');
    });


Route::prefix('/about')->controller(AboutLibrariesController::class)
    ->group(function () {
        Route::get('/library/books/{library}', 'booksInLib');
        Route::get('/library/all', 'allLib');
    });

Route::prefix('/offer')->controller(OfferController::class)
    ->group(function () {
        Route::post('/', 'store');
        Route::put('/{offer}', 'update');
        Route::delete('/{offer}', 'destroy');
        Route::get('/{offer}', 'show');
        Route::get('/library/{library}', 'indexOneLib');
        Route::get('/', 'indexAllLib');
    });

Route::prefix('/order')->controller(OrderController::class)
    ->group(function () {
        Route::post('/', 'store');
        Route::get('/{order}/show_an_order', 'show_an_order');
        Route::post('/{book}/restored', 'restored');
        Route::post('/{order}/confirm_order', 'confirm_order');
        Route::post('/{order}/order_delivered', 'order_delivered');
        Route::get('/borrow_books_in_library', 'borrow_books_in_library');
        Route::get('/orderes_in_library', 'orderes_in_library');
        Route::get('/user_orderes', 'user_orderes');
        Route::get('/user_borrow', 'user_borrow');
        Route::post('/{order}/cancel_order', 'cancel_order');
    });
Route::prefix('/statistics')->controller(StatisticsController::class)
    ->group(function () {
        Route::get('/years', 'years');
        Route::get('/years_sales/{year}', 'years_sales');
        Route::get('/category_sales_borrows/{year}', 'category_sales_borrows');
    });

Route::prefix('/earning')->controller(EarningController::class)
    ->group(function () {
        Route::get('/earning_sales', 'earning_sales');
        Route::get('/earning_borrowing', 'earning_borrowing');
    });
