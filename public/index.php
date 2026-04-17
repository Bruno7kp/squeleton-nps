<?php

declare(strict_types=1);

use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

if (session_status() === PHP_SESSION_NONE) {
	$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
		|| ((int) ($_SERVER['SERVER_PORT'] ?? 0) === 443);

	ini_set('session.use_strict_mode', '1');
	ini_set('session.cookie_httponly', '1');
	ini_set('session.cookie_samesite', 'Lax');
	ini_set('session.cookie_secure', $isHttps ? '1' : '0');

	session_set_cookie_params([
		'lifetime' => 0,
		'path' => '/',
		'domain' => '',
		'secure' => $isHttps,
		'httponly' => true,
		'samesite' => 'Lax',
	]);
	session_start();
}

$app = AppFactory::create();

$bootstrap = require __DIR__ . '/../app/bootstrap.php';
$bootstrap($app);

$app->run();
