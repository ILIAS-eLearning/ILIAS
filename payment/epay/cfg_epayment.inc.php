<?php

/******************************************************************************

This file contains all general variables for the ePay payment

*******************************************************************************/

// ID of PayPal vendor (e-mail address)
$merchant_number = "";

// PayPal server
$server_path = "foo";
$server_host = "bar";
$server = "https://".$server_host.$server_path;
$auth_token = "";

$epayConfig = array(
	"merchant_number" => $merchant_number,
	"server_path" => $server_path,
	"server_host" => $server_host,
	"server" => $server,
	"auth_token" => $auth_token
);

$GLOBALS["epayConfig"] = $epayConfig;
?>
