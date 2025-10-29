<?php

use App\Controllers\ProductCategoriesControl;
use App\Pages\Views\ProductCategories;

$router->mount('/product-categories', function () use ($router) {
    
    $router->get('/', [new ProductCategories(), 'render']);

    $router->get(
        '/q/{paramProductCategoriesID}',
        fn(int $paramProductCategoriesID) => (new ProductCategories($paramProductCategoriesID))->render()
    );

    $router->post('/', [new ProductCategoriesControl(), 'create']);

    $router->put(
        '/q/{paramProductCategoriesID}',
        fn(int $paramProductCategoriesID) => (new ProductCategoriesControl())->update($paramProductCategoriesID)
    );

    $router->delete(
        '/q/{paramProductCategoriesID}',
        fn(int $paramProductCategoriesID) => (new ProductCategoriesControl())->softDelete($paramProductCategoriesID)
    );
});