<?php
namespace Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use db;

class API {

  public function index(ServerRequestInterface $request, ResponseInterface $response) {

    // First check for an API key
    if(!$request->hasHeader('Authorization')) {
      return json_response($response, ['error'=>'forbidden'], 403);
    }

    if(!preg_match('/Bearer (.+)/', $request->getHeaderLine('Authorization'), $match)) {
      return json_response($response, ['error'=>'forbidden'], 403);
    }

    $token = $match[1];

    $user = db\find('users', ['token'=>$token]);
    if(!$user) {
      return json_response($response, ['error'=>'unauthorized'], 401);
    }

    $body = $request->getParsedBody();

    // Check for required parameters
    $params = ['hub_mode', 'hub_topic', 'hub_callback'];
    foreach($params as $p) {
      if(!isset($body[$p]) || trim($body[$p]) == '') {
        return json_response($response, ['error'=>'invalid '.$p], 400);
      }
    }

    switch($body['hub_mode']) {
      case 'subscribe':

        $feed = db\find_or_create('feeds', [
          'url'=>$body['hub_topic']
        ], [
          'tier'=>30,
          'domain'=>parse_url($body['hub_topic'], PHP_URL_HOST)
        ], true);
        $subscriber = db\find_or_create('subscribers', [
          'user_id' => $user->id,
          'feed_id' => $feed->id,
          'callback_url' => $body['hub_callback']
        ], [], true);
        $response_data = ['result'=>'subscribed'];

        // Queue a poll of this feed now, and force delivery to this subscriber
        q()->queue('\\Jobs\\CheckFeed', 'poll', [$feed->id, $subscriber->id]);

        break;
      case 'unsubscribe':

        $feed = db\find('feeds', ['url'=>$body['hub_topic']]);
        $response_data = ['result'=>'subscription_not_found'];
        if($feed) {
          $subscriber = db\find('subscribers', [
            'user_id' => $user->id,
            'feed_id' => $feed->id,
            'callback_url' => $body['hub_callback']
          ]);
          if($subscriber) {
            $subscriber->delete();
            $response_data = ['result'=>'unsubscribed'];
          }
        }

        break;
      default:
        return json_response($response, ['error'=>'invalid mode'], 400);
    }

    return json_response($response, $response_data);
  }
}
