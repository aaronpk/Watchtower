<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->post('/', function(Request $request, Response $response) {
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

      $feed = db\find_or_create('feeds', ['url'=>$body['hub_topic']], [], true);
      $subscription = db\find_or_create('subscribers', [
        'user_id' => $user->id, 
        'feed_id' => $feed->id,
        'callback_url' => $body['hub_callback']
      ], [], true);
      $response_data = ['result'=>'ok'];

      // Queue a poll of this feed now, and force delivery to this subscriber


      break;
    case 'unsubscribe':

      $feed = db\find('feeds', ['url'=>$body['hub_topic']]);
      $response_data = ['result'=>'not_found'];
      if($feed) {
        $subscription = db\find('subscribers', [
          'user_id' => $user->id, 
          'feed_id' => $feed->id,
          'callback_url' => $body['hub_callback']
        ]);
        if($subscription) {
          $subscription->delete();
          $response_data = ['result'=>'unsubscribed'];
        }
      }

      break;
    default:
      return json_response($response, ['error'=>'invalid mode'], 400);
  }

  return json_response($response, $response_data);
});
