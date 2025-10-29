<?php

use App\Pages\Views\Transactions;

$router->mount('/transactions', function () use ($router) {
    $router->get('/', [new Transactions(), 'render']);
    $router->get('/print/{code}', function ($code) {
        (new Transactions())->print($code);
    });
    $router->post('/store', [new Transactions(), 'store']);
});
