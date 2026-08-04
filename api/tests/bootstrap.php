<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

// Force l'environnement de test
$_ENV['APP_ENV'] = 'test';
$_SERVER['APP_ENV'] = 'test';

if ($_SERVER['APP_DEBUG'] ?? true) {
    umask(0000);
}