<?php

use App\Controllers\UserRolesControl;
use App\Pages\Views\UserRoles;

$router->mount('/user-roles', function () use ($router) {
    $router->get('/', [new UserRoles(), 'render']);

    $router->get(
        '/q/{paramUserRolesID}',
        fn(int $paramUserRolesID) => (new UserRoles($paramUserRolesID))->render()
    );

    $router->post('/', [new UserRolesControl(), 'create']);

    $router->put(
        '/q/{paramUserRolesID}',
        fn(int $paramUserRolesID) => (new UserRolesControl())->update($paramUserRolesID)
    );

    $router->delete(
        '/q/{paramUserRolesID}',
        fn(int $paramUserRolesID) => (new UserRolesControl())->softDelete($paramUserRolesID)
    );
});