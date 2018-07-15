<?php
chdir(dirname(__FILE__).'/..');
include('vendor/autoload.php');

$feeds = ORM::for_table('feeds')->find_many();
foreach($feeds as $feed) {
  $feed->domain = parse_url($feed->url, PHP_URL_HOST);
  $feed->save();
}
