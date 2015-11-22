<?php
use Minim\Application;

require '../vendor/autoload.php';

$app = Application::getInstance(include '../app/config.php');
$app->run();
