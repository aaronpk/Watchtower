<?php
namespace Controllers;
use ORM;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Stats {

  public function tiers(ServerRequestInterface $request, ResponseInterface $response) {
    $params = $request->getQueryParams();

    $tiers = ORM::for_table('feeds')
      ->raw_query('SELECT tier, COUNT(1) AS num
        FROM feeds
        GROUP BY tier
        ORDER BY tier')
      ->find_many();

    if(isset($params['config'])) {
      $text = 'graph_title Watchtower Polling Tiers
graph_info Number of feeds in each polling tier
graph_vlabel Feeds
graph_category watchtower
graph_args --lower-limit 0
graph_scale yes

';
      foreach($tiers as $tier) {
        $code = 'tier'.$tier->tier;
        $text .= $code.'.label '.self::tier_label($tier->tier).'
'.$code.'.type GAUGE
'.$code.'.min 0
';
      }
    } else {
      $text = '';
      foreach($tiers as $tier) {
        $code = 'tier'.$tier->tier;
        $text .= $code.'.value '.$tier->num."\n";
      }
    }
    return text_response($response, $text."\n");
  }

  private static function tier_label($minutes) {
    if($minutes >= 60) {
      return floor($minutes / 60).' Hour'.(floor($minutes / 60) == 1 ? '' : 's');
    } else {
      return $minutes.' Minute'.($minutes == 1 ? '' : 's');
    }
  }

}

