<?php
    require 'vendor/autoload.php';
    require_once __DIR__ . '/Autoloader.php';

    $autoloader = new Autoloader();
    $autoloader->addNamespace('Controllers', __DIR__ . '/src/Controllers');
    $autoloader->addNamespace('src', __DIR__ . '/src');
    $autoloader->register();

    use src\ApiStartPoint;
    (new ApiStartPoint())->run();
?>