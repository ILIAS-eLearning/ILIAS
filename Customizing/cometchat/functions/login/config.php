<?php
include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'config.php');

if(CROSS_DOMAIN != '1'){
  $http = 'http://';
  if(!empty($_SERVER['HTTPS'])){
    $http = 'https://';
  }
  $baseUrl = $http.$_SERVER['SERVER_NAME'].BASE_URL;
}else{
  $baseUrl = BASE_URL;
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* SETTINGS START */

$facebookKey = '';
$facebookSecret = '';
$twitterKey = '';
$twitterSecret = '';
$googleKey = '';
$googleSecret = '';


/* SETTINGS END */

return array (
  'base_url' => $baseUrl.'functions/login/',
  'networks' =>
  array (
    'facebook' =>
    array (
      'name' => 'Facebook',
      'enabled' => true,
      'keys' =>
      array (
        'key' => $facebookKey,
        'secret' => $facebookSecret,
      ),
    ),
    'twitter' =>
    array (
      'name' => 'Twitter',
      'enabled' => true,
      'keys' =>
      array (
        'key' => $twitterKey,
        'secret' => $twitterSecret,
      ),
    ),
    'google' =>
    array (
      'name' => 'Google',
      'enabled' => true,
      'keys' =>
      array (
        'key' => $googleKey,
        'secret' => $googleSecret,
      ),
    ),
  ),
  'debug_enabled' => false,
  'log_file' => '',
);
