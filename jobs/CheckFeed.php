<?php
namespace Jobs;
use db, ORM;

class CheckFeed {

  public static function poll($feed_id, $subscriber_id=false) {
    echo "Checking feed $feed_id\n";

    $feed = db\get_by_id('feeds', $feed_id);
    if(!$feed) {
      echo "Feed not found\n";
      return;
    }

    // Download the contents of the feed
    $http = new \p3k\http('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36 p3k-http/0.1.5 p3k-watchtower/0.1');
    $data = $http->get($feed->url);

    // Check if the new hash is different from the old hash
    $content_hash = md5($data['body']);

    if($content_hash != $feed->content_hash) {
      // Store the new hash
      // Store the new content type
      $feed->content_hash = $content_hash;
      $content_type = 'unknown';
      if(isset($data['headers']['Content-Type'])) {
        if(is_string($data['headers']['Content-Type']))
          $content_type = $data['headers']['Content-Type'];
        elseif(is_array($data['headers']['Content-Type']))
          $content_type = $data['headers']['Content-Type'][0];
      }
      $feed->content_type = $content_type;
      $feed->checks_since_last_change = 0;
      $feed->updated_at = date('Y-m-d H:i:s');

      // Deliver the content to each subscriber
      $subscribers = ORM::for_table('subscribers')->where('feed_id', $feed->id)->find_many();
      foreach($subscribers as $subscriber) {
        echo "Delivering to $subscriber->callback_url\n";
        $response = $http->post($subscriber->callback_url, $data['body'], [
          'Content-Type: ' . $content_type
        ]);
        $subscriber->last_http_status = $response['code'];
        if(floor($response['code'] / 200) != 2) {
          $subscriber->error_count++;
        }
        $subscriber->last_notified_at = date('Y-m-d H:i:s');
        $subscriber->save();

      }
    } else {
      echo "No change\n";
      $feed->checks_since_last_change++;
    }

    // TODO: increase or decrease the tier

    $feed->last_checked_at = date('Y-m-d H:i:s');

    // Schedule the next check of this feed
    $feed->next_check_at = date('Y-m-d H:i:s', time()+($feed->tier*60));
    $feed->save();

  }

}
