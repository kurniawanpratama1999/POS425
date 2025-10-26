<?php

use App\Controllers\UsersControl;
use App\Pages\Views\Users;


$router->mount('/users', function () use ($router) {
    $router->get('/', [new Users(), 'render']);

    $router->get(
        '/q/{paramUsersID}',
        fn(int $paramUsersID) => (new Users($paramUsersID))->render()
    );


    $router->post('/', [new UsersControl(), 'create']);

    $router->put(
        '/q/{paramUsersID}',
        fn(int $paramUsersID) => (new UsersControl())->update($paramUsersID)
    );

    $router->delete(
        '/q/{paramUsersID}',
        fn(int $paramUsersID) => (new UsersControl())->softDelete($paramUsersID)
    );
});
