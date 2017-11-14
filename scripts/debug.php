<?php
chdir(dirname(__FILE__).'/..');
include('vendor/autoload.php');

q()->run_foreground();
