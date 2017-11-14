<?php
namespace Jobs;

class CheckFeed {

  public static function poll($feed_id, $subscription_id=false) {
    echo "Checking feed $feed_id\n";
  }

}