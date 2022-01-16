<?php
    // hardcoded namespace
    // attention: maybe a problem with composer v2 / psr4 autoload  requires exact matching of namespace and parent folder name?
    namespace XapiProxy;

    // hardcoded context for better performance 
    // $plugin = file_exists(__DIR__."/plugin.php"); // for testing
    $plugin = false;

    /**
     * handle preflight
     */
    if (strtoupper($_SERVER["REQUEST_METHOD"]) == "OPTIONS") {
        header('HTTP/1.1 204 No Content');
        header('Access-Control-Allow-Origin: '.$_SERVER["HTTP_ORIGIN"]);
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: X-Experience-API-Version,Accept,Authorization,Etag,Cache-Control,Content-Type,DNT,If-Modified-Since,Keep-Alive,Origin,User-Agent,X-Mx-ReqToken,X-Requested-With');
        exit;
    }

    /**
     * handle basic auth
     */
    if( !empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW']) )
    {
        $client = $_SERVER['PHP_AUTH_USER'];
        $token = $_SERVER['PHP_AUTH_PW'];
    }
    elseif( !empty($_SERVER['HTTP_AUTHORIZATION']) )
    {
        $basicAuth = explode(':' , base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
        $client = $basicAuth[0];
        $token = $basicAuth[1];
    }
    else
    {
        header('HTTP/1.1 401 Authorization Required');
        exit;
    }

    /**
     * handle path context
     */
    
    if ($plugin) {
        /**
         *  
         * required for Plugin in ILIAS 5.x
        */
        //require_once __DIR__.'/classes/XapiProxy/vendor/autoload.php';

        chdir("../../../../../../../");
    }
    else {
        chdir("../../");
    }
    
    /**
     * handle ILIAS Init
     */
    require_once __DIR__.'/classes/XapiProxy/DataService.php';
    DataService::initIlias($client);
    
    /**
     * handle XapiProxy Init
     */
    require_once __DIR__.'/classes/XapiProxy/XapiProxy.php';
    $dic = $GLOBALS['DIC'];
    
    $dic['xapiproxy'] = function ($c) use ($client, $token, $plugin) {
        return new XapiProxy($client, $token ,$plugin);
    };

    /**
     * handle Lrs Init
     */
    try {
        $dic['xapiproxy']->initLrs();
    }
    catch(\Exception $e) { // ?
        $dic['xapiproxy']->log()->error($dic['xapiproxy']->getLogMessage($e->getMessage()));
    }

    require_once __DIR__.'/classes/XapiProxy/XapiProxyRequest.php';
    require_once __DIR__.'/classes/XapiProxy/XapiProxyResponse.php';
    $req = new XapiProxyRequest();
    $resp = new XapiProxyResponse();
    
    $dic['xapiproxy']->setXapiProxyRequest($req);
    $dic['xapiproxy']->setXapiProxyResponse($resp);

    $req->handle();
?>
