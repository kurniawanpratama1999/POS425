<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Pages\Views\NotFound404;
use App\Pages\Views\PrintTransaction;
use Bramus\Router\Router;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_METHOD_'])) {
    $_SERVER['REQUEST_METHOD'] = strtoupper($_POST['_METHOD_']);
}

$router = new Router();

$router->mount('/dashboard', function () use ($router) {
    $routesPath = __DIR__ . '/Routes';
    foreach (glob(pattern: (string) $routesPath . '/*.php') as $file) {
        require_once $file;
    }
});

$router->get('/print/{order_id}', function ($order_id) {
    return (new PrintTransaction())->render($order_id);
});
$router->set404([new NotFound404(), "render"]);

$router->run();
