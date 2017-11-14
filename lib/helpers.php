<?php

ORM::configure('mysql:host=' . Config::$dbHost . ';dbname=' . Config::$dbName);
ORM::configure('username', Config::$dbUsername);
ORM::configure('password', Config::$dbPassword);

function bs()
{
  static $pheanstalk;
  if(!isset($pheanstalk)) {
    $pheanstalk = new Pheanstalk\Pheanstalk(Config::$beanstalkServer, Config::$beanstalkPort);
  }
  return $pheanstalk;
}

function json_response($response, $data, $code=200) {
  $response = $response->withStatus($code)->withJson($data);
  return $response;
}
