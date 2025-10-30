<?php
require __DIR__ . '/../vendor/autoload.php';

use App\Pages\Views\Login;
use App\Pages\Views\Documentation;
use App\Controllers\LoginControl;
use App\Pages\Views\NotFound404;
use Bramus\Router\Router;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_METHOD_'])) {
    $_SERVER['REQUEST_METHOD'] = strtoupper($_POST['_METHOD_']);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$router = new Router();

$router->get("/", [new Login(), 'render']);
$router->post("/login", fn() => (new LoginControl())->login());
$router->delete("/logout", fn() => (new LoginControl())->delete());

$router->get('/dokumentasi', [new Documentation(), 'render']);

$router->mount('/dashboard', function () use ($router) {
    $routesPath = __DIR__ . '/Routes';
    foreach (glob(pattern: (string) $routesPath . '/*.php') as $file) {
        require_once $file;
    }
});

$router->before('GET|POST|PUT|DELETE', '/dashboard/.*', function () {
    if (empty($_SESSION['_USER_'])) {
        header('Location: /');
        exit;
    }
});

$router->set404([new NotFound404(), "render"]);

$router->run();
