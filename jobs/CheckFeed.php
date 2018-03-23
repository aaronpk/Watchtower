<?php
namespace Jobs;
use db, ORM;

class CheckFeed {

  private static $http;

  private static $tiers = [
    1,5,15,30,60,120,240,360,480,720,960,1440,2880
  ];

  private static function nextTier($tier) {
    $index = array_search((int)$tier, self::$tiers);
    if(array_key_exists($index+1, self::$tiers))
      return self::$tiers[$index+1];
    else
      return false;
  }

  private static function previousTier($tier) {
    $index = array_search((int)$tier, self::$tiers);
    if(array_key_exists($index-1, self::$tiers))
      return self::$tiers[$index-1];
    else
      return false;
  }

  private static function parseHttpHeader($headers, $key) {
    if(isset($headers[$key])) {
      if(is_string($headers[$key]))
        return $headers[$key];
      elseif(is_array($headers[$key]) && isset($headers[$key][0]))
        return $headers[$key][0];
    }
    return null;
  }

  public static function poll($feed_id, $subscriber_id=false) {
    $feed = db\get_by_id('feeds', $feed_id);
    if(!$feed) {
      echo "Feed not found\n";
      return;
    }

    echo "Checking feed $feed_id $feed->url\n";

    // Download the contents of the feed
    self::$http = new \p3k\HTTP('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/57.0.2987.133 Safari/537.36 p3k-http/0.1.5 p3k-watchtower/0.1');
    self::$http->set_timeout(30);
    $headers = [];
    if($feed->http_etag) {
      $headers['If-None-Match'] = $feed->http_etag;
    }
    $data = self::$http->get($feed->url, $headers);

    $last_checks_since_last_change = $feed->checks_since_last_change;

    if($data['body'] == '' || $data['error']) {
      echo "Error fetching $feed->url\n";
      $feed->checks_since_last_change++;
    } else {

      $content_type = self::parseHttpHeader($data['headers'], 'Content-Type') ?: 'unknown';

      $feed->http_last_modified = self::parseHttpHeader($data['headers'], 'Last-Modified') ?: '';
      $feed->http_etag = self::parseHttpHeader($data['headers'], 'Etag') ?: '';
      $feed->content_length = self::parseHttpHeader($data['headers'], 'Content-Length');
      $feed->content_type = $content_type;

      // Check if the new content is different from the old content
      $previous_content_file = 'data/'.$feed_id.'.txt';
      if(!file_exists($previous_content_file))
        $previous_content = '';
      else
        $previous_content = file_get_contents($previous_content_file);

      if(stripos($content_type, 'html')) {
        $previous_content = strip_tags($previous_content);
        $current_content = strip_tags($data['body']);
      } else {
        $current_content = $data['body'];
      }

      // Calculate the similarity of the previous and current content
      $p = false;
      similar_text($previous_content, $current_content, $p);

      // If the new content different enough, deliver to the subscribers
      if($p < 98) {
        // Store the new content type
        #$feed->content_hash = $content_hash;
        $feed->checks_since_last_change = 0;
        $feed->updated_at = date('Y-m-d H:i:s');
        $feed->save();

        // Deliver the content to each subscriber
        $subscribers = ORM::for_table('subscribers')->where('feed_id', $feed->id)->find_many();
        foreach($subscribers as $subscriber) {
          self::deliver_to_subscriber($data['body'], $content_type, $subscriber);
        }
      } else {
        echo "No change\n";
        $feed->checks_since_last_change++;
        $feed->save();

        // Even if there was no change, deliver to the new subscriber right away
        if($subscriber_id) {
          $subscriber = db\get_by_id('subscribers', $subscriber_id);
          self::deliver_to_subscriber($data['body'], $content_type, $subscriber);
        }
      }

      file_put_contents($previous_content_file, $data['body']);
    }

    // If a feed changed after only 1 check, bump up a tier
    if($last_checks_since_last_change == 0 && $feed->checks_since_last_change == 0) {
      $feed->tier = self::previousTier($feed->tier) ?: $feed->tier;
      echo "Changed immediately, bumping up to to $feed->tier\n";
    }
    // If 4 checks happened with no changes, drop down one tier
    if($feed->checks_since_last_change >= 4) {
      $feed->tier = self::nextTier($feed->tier) ?: $feed->tier;
      $feed->checks_since_last_change = 0;
      echo "No changes in 4 intervals, dropping down to $feed->tier\n";
    }

    $feed->last_checked_at = date('Y-m-d H:i:s');

    // Schedule the next check of this feed
    $feed->next_check_at = date('Y-m-d H:i:s', time()+($feed->tier*60));
    $feed->save();

  }

  private static function deliver_to_subscriber($body, $content_type, $subscriber) {
    if($subscriber && $subscriber->callback_url) {
      echo "Delivering to $subscriber->callback_url\n";
      $user = db\get_by_id('users', $subscriber->user_id);
      $response = self::$http->post($subscriber->callback_url, $body, [
        'Content-Type: ' . $content_type,
        'Authorization: Bearer ' . $user->token
      ]);
      $subscriber->last_http_status = $response['code'];
      if(floor($response['code'] / 200) != 2) {
        $subscriber->error_count++;
      }
      $subscriber->last_notified_at = date('Y-m-d H:i:s');
      $subscriber->save();
    }
  }

}
