<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;

Route::prefix('v1')->group(function () {
    //  Authentication Routes
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    //  Protected Routes (require login)
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('user', [AuthController::class, 'user']);
        // Route::get('users', [UserController::class, 'index']);
        Route::post('logout', [AuthController::class, 'logout']);

        //  User Management Routes (CRUD API)
        /*  | HTTP Method | Endpoint             | Action  | Controller Method |
            | ----------- | -------------------- | ------- | ----------------- |
            | GET         | `/api/v1/users`      | Index   | `index()`         |
            | GET         | `/api/v1/users/{id}` | Show    | `show()`          |
            | POST        | `/api/v1/users`      | Store   | `store()`         |
            | PUT/PATCH   | `/api/v1/users/{id}` | Update  | `update()`        |
            | DELETE      | `/api/v1/users/{id}` | Destroy | `destroy()`       | */
        Route::apiResource('users', UserController::class);
        //  Category Management Routes (CRUD API)
        /*  | HTTP Method | Endpoint                 | Action  | Controller Method |
            | ----------- | ------------------------| ------- | ----------------- |
            | GET         | `/api/v1/categories`    | Index   | `index()`         |
            | GET         | `/api/v1/categories/{id}`| Show   | `show()`          |
            | POST        | `/api/v1/categories`    | Store   | `store()`         |
            | PUT/PATCH   | `/api/v1/categories/{id}`| Update | `update()`        |
            | DELETE      | `/api/v1/categories/{id}`| Destroy| `destroy()`       | */
        Route::apiResource('categories', CategoryController::class);
        //  Product Management Routes (CRUD API)
        /*  | HTTP Method | Endpoint                   | Action  | Controller Method |
            | ----------- | -------------------------- | ------- | ----------------- |
            | GET         | `/api/v1/products`         | Index   | `index()`         |
            | GET         | `/api/v1/products/{id}`    | Show    | `show()`          |
            | POST        | `/api/v1/products`         | Store   | `store()`         |
            | PUT/PATCH   | `/api/v1/products/{id}`    | Update  | `update()`        |
            | DELETE      | `/api/v1/products/{id}`    | Destroy | `destroy()`       | */
        Route::apiResource('products', ProductController::class);
        //  Get products by category
        //  | HTTP Method | Endpoint                                 | Action                | Controller Method         |
        //  | ----------- | ---------------------------------------- | --------------------- | -------------------------|
        //  | GET         | `/api/v1/products-by-category/{categoryId}` | Products by Category | `productsByCategory()`   |
        Route::get('products-by-category/{categoryId}', [ProductController::class, 'productsByCategory']);
        //  Favorite Management Routes (CRUD API)
        /*  | HTTP Method | Endpoint                   | Action  | Controller Method |
            | ----------- | -------------------------- | ------- | ----------------- |
            | GET         | `/api/v1/favorites`        | Index   | `index()`         |
            | GET         | `/api/v1/favorites/{id}`   | Show    | `show()`          |
            | POST        | `/api/v1/favorites`        | Store   | `store()`         |
            | DELETE      | `/api/v1/favorites/{id}`   | Destroy | `destroy()`       | */
        Route::apiResource('favorites', FavoriteController::class)->only(['index', 'show', 'store', 'destroy', 'update']);
        // =============================
        // Order Management Routes
        // =============================
        // GET    /api/v1/orders           - List all/api/v1 orders (OrderController@index)
        // POST   /api/v1/orders           - Create a new order (OrderController@store)
        // GET    /api/v1/orders/{id}      - Show a specific order (OrderController@show)
        // PUT    /api/v1/orders/{id}      - Update a specific order (OrderController@update)
        // PATCH  /api/v1/orders/{id}      - Partially update a specific order (OrderController@update)
        // DELETE /api/v1/orders/{id}      - Delete a specific order (OrderController@destroy)
        Route::apiResource('orders', OrderController::class);
    });
});
