<?php

// Simple bootloader for phpunit using composer autoloader

$loader = require __DIR__ . "/../vendor/autoload.php";

$loader->addPsr4('Comodojo\\Database\\Tests\\', __DIR__);