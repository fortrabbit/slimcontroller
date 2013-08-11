<?php

$loader = require __DIR__. '/../vendor/autoload.php';

$loader->add('SlimController\\Tests\\', __DIR__);
$loader->register();

/*include 'SlimControllerUnitTestCase.php';
include 'Controller/Test.php';*/