<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('Europe/Moscow');

require __DIR__ . '/../vendor/autoload.php';

use App\CronHandler;

$env = Dotenv\Dotenv::create(__DIR__ . '/../');
$env->load();


$CronHandler = new CronHandler($_ENV['TOKEN'], $_ENV['HOST']);
$CronHandler->start();



