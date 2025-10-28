<?php

use App\Pages\Views\Transactions;

$router->mount('/transactions', function () use ($router) {
    $router->get('/', [new Transactions(), 'render']);
    $router->get('/print', [new Transactions(), 'print']);
    $router->post('/store', [new Transactions(), 'store']);
});
