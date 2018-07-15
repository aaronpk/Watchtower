<?php

ORM::configure('mysql:host=' . Config::$dbHost . ';dbname=' . Config::$dbName);
ORM::configure('username', Config::$dbUsername);
ORM::configure('password', Config::$dbPassword);
ORM::configure('driver_options', [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4']);

function q() {
  static $caterpillar;
  if(!isset($caterpillar)) {
    $logdir = __DIR__.'/../scripts/logs/';
    $caterpillar = new Caterpillar('watchtower', Config::$beanstalkServer, Config::$beanstalkPort, $logdir);
  }
  return $caterpillar;
}

function view($template, $data=[]) {
  global $templates;
  return $templates->render($template, $data);
}

function json_response($response, $data, $code=200) {
  $response->getBody()->write(json_encode($data));
  return $response->withHeader('Content-Type', 'application/json')->withStatus($code);
}

function text_response($response, $data, $code=200) {
  $response->getBody()->write($data);
  return $response->withHeader('Content-Type', 'text/plain')->withStatus($code);
}
