<?php

use App\Controllers\ProductsControl;
use App\Pages\Views\Products;


$router->mount('/products', function () use ($router) {
    $router->get('/', [new Products(), 'render']);

    $router->get(
        '/q/{paramProductsID}',
        fn(int $paramProductsID) => (new Products($paramProductsID))->render()
    );


    $router->post('/', [new ProductsControl(), 'create']);

    $router->put(
        '/q/{paramProductsID}',
        fn(int $paramProductsID) => (new ProductsControl())->update($paramProductsID)
    );

    $router->delete(
        '/q/{paramProductsID}',
        fn(int $paramProductsID) => (new ProductsControl())->softDelete($paramProductsID)
    );
});
