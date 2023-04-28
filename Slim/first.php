<?php
require 'vendor/autoload.php';
/*
echo '<a href="https://connect.stripe.com/express/oauth/authorize?redirect_uri=https://stripe.com/connect/default/oauth/test&client_id=ca_AyoRf9JVYxIE2t14yDXCjTFYv5hHL5Ad&state={STATE_VALUE}">ssd</a>';
try {
  \Stripe\Stripe::setApiKey('sk_test_KCw3VI3lIRVwfirqG4KlyYjB');
  
  
 $token =  \Stripe\Token::create(array(
  "card" => array(
    "number" => "4242424242424242",
    "exp_month" => 10,
    "exp_year" => 2018,
    "cvc" => "314"
  )
));
  
 
  
 $customer =  \Stripe\Customer::create(array(
  "description" => "Customer for joshua.thompson@example.com",
  "source" => "$token->id" // obtained with Stripe.js
));
//$p = \Stripe\Token::retrieve("$token->id");

 $c = \Stripe\Charge::create(array(
  "amount" => 2000,
  "currency" => "usd",
  "customer" => "$customer->id",
 // "source" => "$t->id", // obtained with Stripe.js
 "metadata" => array("order_id" => "6735")
));
var_dump($c);
 //die;


} catch(\Stripe\Error\Card $e) {
  // Since it's a decline, \Stripe\Error\Card will be caught
  $body = $e->getJsonBody();
  $err  = $body['error'];

  print('Status is:' . $e->getHttpStatus() . "\n");
  print('Type is:' . $err['type'] . "\n");
  print('Code is:' . $err['code'] . "\n");
  // param is '' in this case
  print('Param is:' . $err['param'] . "\n");
  print('Message is:' . $err['message'] . "\n");
} catch (\Stripe\Error\RateLimit $e) {
  // Too many requests made to the API too quickly
   echo 'r';
 
} catch (\Stripe\Error\InvalidRequest $e) {
  // Invalid parameters were supplied to Stripe's API
   echo $e->getMessage();
} catch (\Stripe\Error\Authentication $e) {
  // Authentication with Stripe's API failed
  // (maybe you changed API keys recently)
   echo 'A';
} catch (\Stripe\Error\ApiConnection $e) {
  // Network communication with Stripe failed
   echo 'AI';
} catch (\Stripe\Error\Base $e) {
  // Display a very generic error to the user, and maybe send
   echo 'B';
  // yourself an email
} catch (Exception $e) {
   echo 'E';
  // Something else happened, completely unrelated to Stripe
}

*/
 define('CLIENT_ID', 'ca_AyoRf9JVYxIE2t14yDXCjTFYv5hHL5Ad');
  define('API_KEY', 'sk_test_KCw3VI3lIRVwfirqG4KlyYjB');
  define('TOKEN_URI', 'https://connect.stripe.com/oauth/token');
  define('AUTHORIZE_URI', 'https://connect.stripe.com/oauth/authorize');
  if (isset($_GET['code'])) { // Redirect w/ code
    $code = $_GET['code'];
    $token_request_body = array(
      'client_secret' => API_KEY,
      'grant_type' => 'authorization_code',
      'client_id' => CLIENT_ID,
      'code' => $code,
    );
    $req = curl_init(TOKEN_URI);
    curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($req, CURLOPT_POST, true );
    curl_setopt($req, CURLOPT_POSTFIELDS, http_build_query($token_request_body));
    // TODO: Additional error handling
    $respCode = curl_getinfo($req, CURLINFO_HTTP_CODE);
    $resp = json_decode(curl_exec($req), true);
    curl_close($req);
    echo $resp['access_token'];
  } else if (isset($_GET['error'])) { // Error
    echo $_GET['error_description'];
  } else { // Show OAuth link
    $authorize_request_body = array(
      'response_type' => 'code',
      'scope' => 'read_write',
      'client_id' => CLIENT_ID
    );
    $url = AUTHORIZE_URI . '?' . http_build_query($authorize_request_body);
    echo "<a href='$url'>Connect with Stripe</a>";
	}