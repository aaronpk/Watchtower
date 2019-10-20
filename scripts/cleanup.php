<?php
chdir(dirname(__FILE__).'/..');
include('vendor/autoload.php');

// For some reason some feeds get "stuck" marked as pending, even though there is no
// task queued for them.
// If a feed has been "pending" for more than 24 hours, reset it so that it will be
// queued again later.

$feeds = ORM::for_table('feeds')
  ->select_many_expr('feeds.*')
  ->join('subscribers', ['feeds.id','=','subscribers.feed_id'])
  ->where_lt('next_check_at', date('Y-m-d H:i:s', time()-86400))
  ->where('pending', 1)
  ->find_many();
foreach($feeds as $feed) {
  echo "Resetting $feed->id $feed->url\n";
  $feed->pending = 0;
  $feed->save();
}
