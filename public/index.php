<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Pages\Layouts\Dashboard;
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

$router->set404(function () {
    echo Dashboard::get("<p>NOTFOUND</p>", 'Not Found');
});


$router->run();


