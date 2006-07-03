<?php

/******************************************************************************

This file contains all general variables for the PayPal payment

*******************************************************************************/

// ID of PayPal vendor (e-mail address)
$vendor = "";

// PayPal server
$server_path = "/cgi-bin/webscr";
$server_host = "www.paypal.com";
$server = "https://".$server_host.$server_path;

// For securely posting back to paypal using HTTPS your PHP server will need to be SSL enabled
$ssl = true;

// PayPal auth token
$auth_token = "";

$paypalConfig = array(
	"vendor" => $vendor,
	"server_path" => $server_path,
	"server_host" => $server_host,
	"server" => $server,
	"auth_token" => $auth_token
);

$GLOBALS["paypalConfig"] = $paypalConfig;
?>
