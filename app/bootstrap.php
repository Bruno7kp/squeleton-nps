<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use Slim\App;

return static function (App $app): void {
    $projectRoot = dirname(__DIR__);

    if (file_exists($projectRoot . '/.env')) {
        Dotenv::createImmutable($projectRoot)->safeLoad();
    }

    $app->addBodyParsingMiddleware();
    $app->addRoutingMiddleware();

    $displayErrorDetails = filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOL);
    $app->addErrorMiddleware($displayErrorDetails, true, true);

    (require __DIR__ . '/Routes/web.php')($app);
    (require __DIR__ . '/Routes/api.php')($app);
};
