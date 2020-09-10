<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('Europe/Moscow');

require __DIR__ . '/../vendor/autoload.php';

use App\ServerHandler;

$env = Dotenv\Dotenv::create(__DIR__ . '/../');
$env->load();


$handler = new ServerHandler($_ENV['SECRET'], $_ENV['GROUP_ID'], $_ENV['CONFIRMATION_TOKEN'], $_ENV['TOKEN']);
$data = json_decode(file_get_contents('php://input'), true);
//$data = json_decode(file_get_contents(__DIR__ . '/../lib/request.json'), true);
$handler->parse($data);
if($data['type'] != "confirmation"){
    echo "ok";
}