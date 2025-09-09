<?php

declare(strict_types=1);

use Ounzy\FrogFramework\Core\App;
use Ounzy\FrogFramework\Http\Middleware\AccessLogMiddleware;
use Ounzy\FrogFramework\Http\Request;

require __DIR__ . '/../vendor/autoload.php';

// Static file passthrough for PHP built-in server when using this router script.
// If the requested URI maps to an actual file under public/, return false so the
// built-in server serves it directly (images, css, js, etc.).
if (PHP_SAPI === 'cli-server') {
    $uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
    $file = __DIR__ . $uri;
    if ($uri !== '/' && is_file($file)) {
        return false; // let built-in server handle the static asset
    }
}

// Ensure favicon.ico available in public/ if placed at project root
($ensureFavicon = function () {
    $pub = __DIR__ . DIRECTORY_SEPARATOR . 'favicon.ico';
    $root = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'favicon.ico';
    if (!is_file($pub) && is_file($root)) {
        @copy($root, $pub);
    }
})();

// Bootstrap application & routes
$app = new App();
require __DIR__ . '/../bootstrap/app.php';
require __DIR__ . '/../bootstrap/routes.php';

// Router auto-registered inside App; use facade or app()->router()
$router = $app->router();

$router->middleware([
    AccessLogMiddleware::class,
]);

// Dispatch
try {
    $response = $router->dispatch(Request::capture());
} catch (Throwable $e) {
    if (config('app.debug', false)) {
        $response = response()->status(500)->html(frog_debug_render($e));
    } else {
        $response = frog_error_response(500, ['description' => 'Unexpected exception.']);
    }
}

$response->send();
