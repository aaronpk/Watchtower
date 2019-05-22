<?php
chdir(dirname(__FILE__).'/..');
require 'vendor/autoload.php';

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

$templates = new League\Plates\Engine(dirname(__FILE__).'/../views');

$container = new League\Container\Container;

$container->share('response', Zend\Diactoros\Response::class);
$container->share('request', function () {
    return Zend\Diactoros\ServerRequestFactory::fromGlobals(
        $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
    );
});
$container->share('emitter', Zend\Diactoros\Response\SapiEmitter::class);

$route = new League\Route\RouteCollection();

$route->map('GET', '/', 'Controllers\\Main::index');
$route->map('POST', '/', 'Controllers\\API::index');

$route->map('GET', '/stats/tiers', 'Controllers\\Stats::tiers');
$route->map('GET', '/stats/feeds', 'Controllers\\Stats::feeds');
$route->map('GET', '/stats/polls', 'Controllers\\Stats::polls');

$route->map('GET', '/websub/{hash:[a-z]+}', 'Controllers\\WebSub::get');
$route->map('POST', '/websub/{hash:[a-z]+}', 'Controllers\\WebSub::post');

$response = $route->dispatch($container->get('request'), $container->get('response'));
$container->get('emitter')->emit($response);
