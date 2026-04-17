<?php

declare(strict_types=1);

use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE) {
	session_set_cookie_params([
		'httponly' => true,
		'samesite' => 'Lax',
	]);
	session_start();
}

$app = AppFactory::create();

$bootstrap = require __DIR__ . '/../app/bootstrap.php';
$bootstrap($app);

$app->run();
