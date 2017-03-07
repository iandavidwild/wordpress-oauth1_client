<?php

require_once './Oauth.php';
require_once './BasicOauth.php';

use \OAuth1\BasicOauth;

define(CLIENT_KEY, 'xxxxxxxxx');
define(CLIENT_SECRET, 'xxxxxxxxx');

if(!isset($_REQUEST['oauth_token'])) {
    $connection = new BasicOAuth(CLIENT_KEY, CLIENT_SECRET);
    
    $connection->host = "http://wordpress.localhost/wp-json";
    $connection->requestTokenURL = "http://wordpress.localhost/oauth1/request";
    
    $tempCredentials = $connection->getRequestToken('http://testing.localhost/oauth/');
    
    session_start();
    $_SESSION['oauth_token'] = $tempCredentials['oauth_token'];
    $_SESSION['oauth_token_secret'] = $tempCredentials['oauth_token_secret'];
    
    $connection->authorizeURL = "http://wordpress.localhost/oauth1/authorize";
    
    $redirect_url = $connection->getAuthorizeURL($tempCredentials);
    
    header('Location: ' . $redirect_url);
    die;
} else {
    session_start();
    // we have been provided with new permanent token
    $connection = new BasicOAuth(CLIENT_KEY, CLIENT_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
    
    $connection->host = "http://wordpress.localhost/wp-json";
    
    $connection->accessTokenURL = "http://wordpress.localhost/oauth1/access";
    
    $tokenCredentials = $connection->getAccessToken($_REQUEST['oauth_verifier']);

    // $tokenCredentials are permanent and need to be stored in the DB
    
    $perm_connection = new BasicOAuth(CLIENT_KEY, CLIENT_SECRET, $tokenCredentials['oauth_token'],
            $tokenCredentials['oauth_token_secret']);
    
    $account = $perm_connection->get('http://wordpress.localhost/wp-json/wp/v2/users/me');
    
    print_r($account);
    
}
/*
 
// Once we have a permanent token and key we don't have to keep authorizing... 
 
define('OAUTH_TOKEN', 'permanent token from WP');
define('OAUTH_TOKEN_SECRET', 'permanent token secret from WP');

$connection = new BasicOAuth(CLIENT_KEY, CLIENT_SECRET, OAUTH_TOKEN, OAUTH_TOKEN_SECRET);

$connection->host = "http://wordpress.localhost/wp-json";

$account = $connection->get('http://wordpress.localhost/wp-json/wp/v2/users/me');

print_r($account);
*/

