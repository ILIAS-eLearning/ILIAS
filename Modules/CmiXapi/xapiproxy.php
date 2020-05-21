<?php

chdir("../../");

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GuzzleHttp\Client;
use \GuzzleHttp\RequestOptions;
use \GuzzleHttp\Psr7\Uri;
use \Zend\HttpHandlerRunner\Emitter\SapiEmitter;

// check options requests
if (strtoupper($_SERVER["REQUEST_METHOD"]) == "OPTIONS") {
    header('HTTP/1.1 204 No Content');
    header('Access-Control-Allow-Origin: ' . $_SERVER["HTTP_ORIGIN"]);
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: X-Experience-API-Version,Accept,Authorization,Etag,Cache-Control,Content-Type,DNT,If-Modified-Since,Keep-Alive,Origin,User-Agent,X-Mx-ReqToken,X-Requested-With');
    exit;
}

if (!empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW'])) {
    $client = $_SERVER['PHP_AUTH_USER'];
    $token = $_SERVER['PHP_AUTH_PW'];
} elseif (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
    $basicAuth = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
    $client = $basicAuth[0];
    $token = $basicAuth[1];
} else {
    //$log->info("no credentials:\nREQUEST_METHOD=".$_SERVER["REQUEST_METHOD"]."\nPHP_AUTH_USER=".$_SERVER['PHP_AUTH_USER']."\nPHP_AUTH_PW=".$_SERVER['PHP_AUTH_PW']);
    header('HTTP/1.1 401 Authorization Required');
    exit;
}

require_once 'Modules/CmiXapi/classes/XapiProxy/DataService.php';
\XapiProxy\DataService::initIlias($client);

$dic = $GLOBALS['DIC'];

$log = ilLoggerFactory::getLogger('cmix');

try {
    $authToken = ilCmiXapiAuthToken::getInstanceByToken($token);
    $lrsType = new ilCmiXapiLrsType($authToken->getLrsTypeId());
    
    if (!$lrsType->isAvailable()) {
        throw new ilCmiXapiException(
            'lrs endpoint (id=' . $authToken->getLrsTypeId() . ') unavailable (responded 401-unauthorized)'
        );
    }
} catch (ilCmiXapiException $e) {
    $log->error($e->getMessage());
    header('HTTP/1.1 401 Unauthorized');
    exit;
}

ilCmiXapiUser::saveProxySuccess($authToken->getObjId(), $authToken->getUsrId());

$request = $dic->http()->request();

/*
 * async
 */

handleRequest($request);

/**
 * handle main request
 */
function handleRequest(ServerRequestInterface $request)
{
    global $log;
    $method = strtolower($request->getMethod());
    $log->debug("Request Method: " . $method);
    switch ($method) {
        case "post":
        case "put":
            handlePostPutRequest($request);
            break;
        default:
            handleProxy($request);
    }
}

/**
 * handle request for body sniffing, only post put requests
 */
function handlePostPutRequest(ServerRequestInterface $request)
{
    global $log;
    $body = $request->getBody()->getContents();
    if (empty($body)) {
        $log->warning("empty body in handlePostPutRequest");
        handleProxy($request);
    } else {
        try {
            $body = modifyBody($body);
            $log->debug($body);
            $changes = array(
                "body" => $body
            );
            $req = \GuzzleHttp\Psr7\modify_request($request, $changes);
            handleProxy($req);
        } catch (Exception $e) { // ToDo: Errorhandling
            $log->error($e->getMessage());
            handleProxy($request);
        }
    }
}

/**
 * handle proxy request
 */
function handleProxy(ServerRequestInterface $request)
{
    global $log, $authToken, $lrsType;
    
    $endpoint = $lrsType->getLrsEndpoint();
    $log->debug("Endpoint: " . $endpoint);
    //$endpoint = "https://lrs.example.com/lrs.php";
    $auth = 'Basic ' . base64_encode($lrsType->getLrsKey() . ':' . $lrsType->getLrsSecret());
    $req_opts = array(
        RequestOptions::VERIFY => false,
        RequestOptions::CONNECT_TIMEOUT => 5
    );
    $full_uri = $request->getUri();
    $serverParams = $request->getServerParams();
    $queryParams = $request->getQueryParams();
    $parts_reg = '/^(.*?xapiproxy\.php)(.+)/'; // ToDo: replace hard coded regex?
    preg_match($parts_reg, $full_uri, $cmd_parts);
    
    if (count($cmd_parts) === 3) { // should always
        try {
            $cmd = $cmd_parts[2];
            $upstream = $endpoint . $cmd;
            $uri = new Uri($upstream);
            $changes = array(
                'uri' => $uri,
                'set_headers' => array('Cache-Control' => 'no-cache, no-store, must-revalidate', 'Authorization' => $auth)
            );
            $req = \GuzzleHttp\Psr7\modify_request($request, $changes);
            $httpclient = new Client();
            $promise = $httpclient->sendAsync($req, $req_opts);
            $response = $promise->wait();
            handleResponse($request, $response);
        } catch (Exception $e) { // ToDo: Errorhandling
            header("HTTP/1.1 500 XapiProxy Error");
            echo "HTTP/1.1 500 XapiProxy Error";
            exit;
        }
    } else {
        $log->warning("Wrong command parts!");
        header("HTTP/1.1 412 Wrong Request Parameter");
        echo "HTTP/1.1 412 Wrong Request Parameter";
        exit;
    }
}

function handleResponse(ServerRequestInterface $request, ResponseInterface $response)
{
    global $log;
    // check transfer encoding bug
    $headers = $response->getHeaders();
    if (array_key_exists('Transfer-Encoding', $headers) && $headers['Transfer-Encoding'][0] == "chunked") {
        $log->info("sniff response transfer-encoding for unallowed Content-length");
        $body = $response->getBody();
        $status = $response->getStatusCode();
        unset($headers['Transfer-Encoding']);
        $headers['Content-Length'] = array(strlen($body));
        $response2 = new \GuzzleHttp\Psr7\Response($status, $headers, $body);
        (new SapiEmitter())->emit($response2);
    } else {
        (new SapiEmitter())->emit($response);
    }
}

function modifyBody($body)
{
    global $log;

    $obj = json_decode($body, false);
 
    if (json_last_error() != JSON_ERROR_NONE) {
        // JSON is not valid
        $log->error(json_last_error_msg());
        return $body;
    }
    
    $log->debug(json_encode($obj, JSON_PRETTY_PRINT)); // only in DEBUG mode for better performance
    
    if (is_object($obj)) {
        $log->debug("");
        
        handleStatementEvaluation($obj);
    }
    
    if (is_array($obj)) {
        for ($i = 0; $i < count($obj); $i++) {
            handleStatementEvaluation($obj[$i]);
        }
    }
    
    return json_encode($obj);
}

function handleStatementEvaluation($xapiStatement)
{
    global $authToken, $log;
    
    /* @var ilObjCmiXapi $object */
    $object = ilObjectFactory::getInstanceByObjId($authToken->getObjId());

    if( (string)$object->getLaunchMode() === (string)ilObjCmiXapi::LAUNCH_MODE_NORMAL ) {
        $statementEvaluation = new ilXapiStatementEvaluation($log, $object);
        $statementEvaluation->evaluateStatement($xapiStatement, $authToken->getUsrId());

        ilLPStatusWrapper::_updateStatus(
            $authToken->getObjId(),
            $authToken->getUsrId()
        );
    }
}

// use only for debugging states before ILIAS Init
function _log($txt)
{
    if (DEVMODE) {
        file_put_contents("xapilog.txt", $txt . "\n", FILE_APPEND);
    }
}
