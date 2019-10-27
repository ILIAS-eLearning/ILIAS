<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

chdir("../../");
require_once 'libs/composer/vendor/autoload.php';

if( !isset($_GET['param']) || !strlen($_GET['param']) )
{
	header('HTTP/1.1 401 Authorization Required');
	exit;
}

try
{
	$param = base64_decode(rawurldecode($_GET['param']));
	
	$param = json_decode(openssl_decrypt(
		$param, ilCmiXapiAuthToken::OPENSSL_ENCRYPTION_METHOD, ilCmiXapiAuthToken::getWacSalt(), 0,
		ilCmiXapiAuthToken::OPENSSL_IV
	), true);
}
catch(ilCmiXapiException $e)
{
	header('HTTP/1.1 500 Internal Server Error');
	exit;
}

$_COOKIE[session_name()] = $param[session_name()];
$_COOKIE['ilClientId'] = $param['ilClientId'];
$objId = $param['obj_id'];

#\XapiProxy\DataService::initIlias($_COOKIE['ilClientId']);
ilInitialisation::initILIAS();

$DIC = $GLOBALS['DIC']; /* @var \ILIAS\DI\Container $DIC */

try
{
	$token = ilCmiXapiAuthToken::getInstanceByObjIdAndUsrId($objId, $DIC->user()->getId());
}
catch(ilCmiXapiException $e)
{
	header('HTTP/1.1 401 Authorization Failed');
	exit;
}

/* @var ilObjCmiXapi $object */
$object = ilObjectFactory::getInstanceByObjId($objId, false);

if( !$object )
{
	header('HTTP/1.1 401 Authorization Failed');
	exit;
}

if( $object->isBypassProxyEnabled() )
{
	$authToken = $object->getLrsType()->getBasicAuth();
}
else
{
	$authToken = base64_encode(CLIENT_ID . ':' . $token->getToken());
}

$response = array("auth-token" => $authToken);

header('Content-type:application/json;charset=utf-8');
echo json_encode($response);
