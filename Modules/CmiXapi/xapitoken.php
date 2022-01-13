<?php
/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

chdir("../../");
require_once 'libs/composer/vendor/autoload.php';

/**
 * see: https://github.com/AICC/CMI-5_Spec_Current/blob/quartz/cmi5_spec.md#fetch_url
 * response should always be a valid json object
 * check oneway fetching is optional:
 * 
 * The AU SHOULD NOT attempt to retrieve the authorization token more than once. The fetch URL is a "one-time use" URL and subsequent uses SHOULD generate an error (see Section 8.2.3).
 * 
 * On reloading the initial content page it will send the exact url twice, should we really restrict this behavior?
 * If there are issues on page reload it might be useful to set $tokenRestriction = false .
 */
$tokenRestriction = true;

$origParam = $_GET['param'];

if (!isset($origParam) || !strlen($origParam))
{
    $error = array('error-code' => 3,'error-text'=> 'invalid request: missing or empty param request parameter');
    send($error);
}

try
{
    $param = base64_decode(rawurldecode($origParam));
    
    $param = json_decode(openssl_decrypt(
        $param,
        ilCmiXapiAuthToken::OPENSSL_ENCRYPTION_METHOD,
        ilCmiXapiAuthToken::getWacSalt(),
        0,
        ilCmiXapiAuthToken::OPENSSL_IV
    ), true);

    $_COOKIE[session_name()] = $param[session_name()];
    $_COOKIE['ilClientId'] = $param['ilClientId'];
    $objId = $param['obj_id'];
    $refId = $param['ref_id'];

    #\XapiProxy\DataService::initIlias($_COOKIE['ilClientId']);
    ilInitialisation::initILIAS();
    $DIC = $GLOBALS['DIC'];
}
catch (ilCmiXapiException $e)
{
    $error = array('error-code' => '3','error-text'=> 'internal server error');
    send($error);
}

try
{
    $object = ilObjectFactory::getInstanceByObjId($objId, false);
    $token = ilCmiXapiAuthToken::getInstanceByObjIdAndRefIdAndUsrId($objId, $refId, $DIC->user()->getId());
    if ($object->getContentType() == ilObjCmiXapi::CONT_TYPE_CMI5)
    {
        $tokenCmi5Session = $token->getCmi5Session();
        $alreadyReturnedCmi5Session = $token->getReturnedForCmi5Session();
        if ($tokenCmi5Session == $alreadyReturnedCmi5Session)
        {
            // what about reloaded or refreshed pages?
            // see: https://stackoverflow.com/questions/456841/detect-whether-the-browser-is-refreshed-or-not-using-php/456915
            // Beware that the xapitoken request is an ajax request and not all clients send HTTP_REFERRER Header
            if ($tokenRestriction == true)
            {
                $error = array('error-code' => '1','error-text'=> 'The authorization token has already been returned.');
                send($error);
            }
        }
        $token->setReturnedForCmi5Session($tokenCmi5Session);
        $token->update();
    }
    if ($object->isBypassProxyEnabled()) {
        $authToken = $object->getLrsType()->getBasicAuthWithoutBasic();
    } else {
        $authToken = base64_encode(CLIENT_ID . ':' . $token->getToken());
    }
    
    
    $response = array("auth-token" => $authToken);
    send($response);
}
catch (ilCmiXapiException $e)
{
    $error = array('error-code' => '2','error-text'=> 'could not create valid session from token.');
    send($error);
}

function send($response)
{
    header('Access-Control-Allow-Origin: '.$_SERVER["HTTP_ORIGIN"]);
    header('Access-Control-Allow-Credentials: true');
    header('Content-type:application/json;charset=utf-8');
    echo json_encode($response);
    exit;
}