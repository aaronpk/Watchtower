<?php
chdir('..');
require 'vendor/autoload.php';

$app = new \Slim\App;

require 'controllers/controllers.php';
require 'controllers/api.php';

$app->run();
