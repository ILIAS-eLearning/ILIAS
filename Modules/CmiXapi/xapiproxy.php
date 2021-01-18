<?php

chdir("../../");

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \GuzzleHttp\Client;
use \GuzzleHttp\RequestOptions;
use \GuzzleHttp\Psr7\Uri;
use \Zend\HttpHandlerRunner\Emitter\SapiEmitter;
use \GuzzleHttp\Exception\GuzzleException;

// ToDo: json_decode(obj,true) as assoc array might be faster?
$specificAllowedStatements = NULL;
/*
$specificAllowedStatements = array(
    "http://adlnet.gov/expapi/verbs/completed",
    "http://adlnet.gov/expapi/verbs/passed",
    "http://adlnet.gov/expapi/verbs/initialized",
    "http://adlnet.gov/expapi/verbs/terminated",
    "http://adlnet.gov/expapi/verbs/launched"
);
*/
$replacedValues = NULL;

/*
$replacedValues = array(
  'timestamp' => '1970-01-01T00:00:00.000Z',
  'result.duration' => 'PT00.000S'
);
*/
$blockSubStatements = false;

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
    //$log->debug("no credentials:\nREQUEST_METHOD=".$_SERVER["REQUEST_METHOD"]."\nPHP_AUTH_USER=".$_SERVER['PHP_AUTH_USER']."\nPHP_AUTH_PW=".$_SERVER['PHP_AUTH_PW']);
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
    $objId = $authToken->getObjId();

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

/**
 * set all important params globally at once for multiple usage
 */
$method = strtolower($request->getMethod());
$log->debug("Request-Method: " . $method);
$partsReg = '/^(.*?xapiproxy\.php)(\/([^\?]+)?\??.*)/';
preg_match($partsReg, $request->getUri(), $cmdParts);
$queryParams = $request->getQueryParams();

try {
    handleRequest($request);
}
catch(GuzzleException $e) { // ToDo: clean exception handling
    $log->error($e->getMessage());
}
/**
 * handle main request
 * @param ServerRequestInterface $request
 * @throws GuzzleException
 */
function handleRequest(ServerRequestInterface $request)
{
    global $log, $cmdParts;

    if (count($cmdParts) === 4) {
        if ($cmdParts[3] === "statements") {
            $log->debug("handleStatementsRequest");
            handleStatementsRequest($request);
        } elseif ($cmdParts[3] === "activities/state") {
            $log->debug("handleStateRequest");
            handleStateRequest($request);
        } elseif ($cmdParts[3] === "agents/profile") {
            $log->debug("handleProfileRequest");
            handleProfileRequest($request);
        } else {
            $log->info("Not handled xApi Query: " . $cmdParts[3]);
            handleProxy($request);
        }
    } else {
        $log->error("Wrong xApi Query: " . $request->getUri());
        handleProxy($request);
    }
}

/**
 * handle statements request
 * @param ServerRequestInterface $request
 * @throws GuzzleException
 */
function handleStatementsRequest(ServerRequestInterface $request) {
    global $method;
    if ($method === "post" || $method === "put") {
        handlePostPutStatementsRequest($request);
    }
    else {
        // get Method is not handled yet
        handleProxy($request);
    }
}

/**
 * handle request for body sniffing, only post put requests
 * @param ServerRequestInterface $request
 * @throws GuzzleException
 */
function handlePostPutStatementsRequest(ServerRequestInterface $request)
{
    global $log;
    $body = $request->getBody()->getContents();
    if (empty($body)) {
        $log->warning("empty body in handlePostPutRequest");
        handleProxy($request);
    }
    else {
        try {
            $log->debug("process statements");
            $retArr = processStatements($request, $body);
            if (is_array($retArr)) {
                $body = json_encode($retArr[0]); // new body with allowed statements
                $fakePostBody = $retArr[1]; // fake post php array of ALL statments as if all statements were processed
            }
        }
        catch(Exception $e) {
            $log->error($e->getMessage());
            exitProxyError();
        }
        try {
            $body = modifyBody($body);
            // $log->debug($body);
            $changes = array (
                "body" => $body
            );
            $req = \GuzzleHttp\Psr7\modify_request($request, $changes);
            handleProxy($req, $fakePostBody);
        }
        catch(Exception $e) {
            $log->error($e->getMessage());
            handleProxy($request, $fakePostBody);
        }
    }
}

/**
 * process statements
 * @param ServerRequestInterface $request
 * @return array[]|null
 */
function processStatements(ServerRequestInterface $request, $body) {
    global $log, $specificAllowedStatements, $blockSubStatements;
    // everything is allowed
    if (!is_array($specificAllowedStatements) && !$blockSubStatements) {
        $log->debug("all statement are allowed");
        return NULL;
    }
    $obj = json_decode($body, false);
    // single statement object
    if (is_object($obj) && isset($obj->verb)) {
        $log->debug("json is object and statement");
        $isSubStatement = isSubStatementCheck($obj);
        $verb = $obj->verb->id;
        if ($blockSubStatements && $isSubStatement) {
            $log->debug("sub-statement is NOT allowed, fake response - " . $verb);
            fakeResponseBlocked(NULL);
        }
        // $specificAllowedStatements
        if (!is_array($specificAllowedStatements)) {
            return NULL;
        }
        if (in_array($verb,$specificAllowedStatements)) {
            $log->debug("statement is allowed, do nothing - " . $verb);
            return NULL;
        }
        else {
            $log->debug("statement is NOT allowed, fake response - " . $verb);
            fakeResponseBlocked(NULL);
        }
    }
    // array of statement objects
    if (is_array($obj) && count($obj) > 0 && isset($obj[0]->verb)) {
        $log->debug("json is array of statements");
        $ret = array();
        $up = array();
        for ($i=0; $i<count($obj); $i++) {
            array_push($ret,$obj[$i]->id); // push every statementid for fakePostResponse
            $isSubStatement = isSubStatementCheck($obj[$i]);
            $verb = $obj[$i]->verb->id;
            if ($blockSubStatements && $isSubStatement) {
                $log->debug("sub-statement is NOT allowed - " .$verb);
            }
            else {
                if (!is_array($specificAllowedStatements) || (is_array($specificAllowedStatements) && in_array($verb,$specificAllowedStatements))) {
                    $log->debug("statement is allowed - " . $verb);
                    array_push($up,$obj[$i]);
                }
            }
        }
        if (count($up) === 0) { // nothing allowed
            $log->debug("no allowed statements in array - fake response...");
            fakeResponseBlocked($ret);
        }
        elseif (count($up) !== count($ret)) { // mixed request with allowed and not allowed statements
            $log->debug("mixed with allowed and unallowed statements");
            return array($up,$ret);
        }
        else {
            // just return nothing
            return NULL;
        }
    }
}

/**
 * @param $xapiStatement
 * @throws ilDatabaseException
 * @throws ilObjectNotFoundException
 */
function handleStatementEvaluation($xapiStatement)
{
    global $authToken, $log;

    /* @var ilObjCmiXapi $object */
    $object = ilObjectFactory::getInstanceByObjId($authToken->getObjId());

    if( (string)$object->getLaunchMode() === (string)ilObjCmiXapi::LAUNCH_MODE_NORMAL ) {
        // ToDo: check function hasContextActivitiesParentNotEqualToObject!
        $statementEvaluation = new ilXapiStatementEvaluation($log, $object);
        $statementEvaluation->evaluateStatement($xapiStatement, $authToken->getUsrId());

        ilLPStatusWrapper::_updateStatus(
            $authToken->getObjId(),
            $authToken->getUsrId()
        );
    }
}

/**
 * @param $obj
 * @param $path
 * @param $value
 */
function setValue(&$obj, $path, $value) {
    global $log;
    $path_components = explode('.', $path);
    if (count($path_components) == 1) {
        if (property_exists($obj,$path_components[0])) {
            $obj->{$path_components[0]} = $value;
        }
    }
    else {
        if (property_exists($obj, $path_components[0])) {
            setValue($obj->{array_shift($path_components)}, implode('.', $path_components), $value);
        }
    }
}

/**
 * @param $obj
 * @return bool
 */
function isSubStatementCheck($obj) {
    global $log;
    if (
        isset($obj->context) &&
        isset($obj->context->contextActivities) &&
        is_array($obj->context->contextActivities->parent)
    ) {
        $log->debug("is Substatement");
        return true;
    }
    else {
        $log->debug("is not Substatement");
        return false;
    }
}

/**
 * handle state request
 * @param ServerRequestInterface $request
 * @throws GuzzleException
 */
function handleStateRequest(ServerRequestInterface $request) {
    global $method;
    if ($method === "get") {
        handleStateGetRequest($request);
    }
    else {
        // post | put Methods are not handled yet
        handleProxy($request);
    }
}

/**
 * handle state get request
 * @param ServerRequestInterface $request
 * @throws GuzzleException
 */
function handleStateGetRequest(ServerRequestInterface $request) {
    global $log, $objId, $queryParams, $lrsType;
    $stateId = strtolower($queryParams["stateId"]);
    if ($stateId === "lms.launchdata") {
        sendData($lrsType::getLaunchData($objId)); // ToDo: get real LaunchData
    }
    elseif ($stateId === "status") {
        sendData("{\"completion\":null,\"success\":null,\"score\":null,\"launchModes\":[]}"); // ToDo: get real status
    }
    else {
        $log->debug("not handled stateId: " . $stateId);
        handleProxy($request);
    }
}

/**
 * @param ServerRequestInterface $request
 * @throws GuzzleException
 */
function handleProfileRequest(ServerRequestInterface $request) {
    global $method;
    if ($method === "get") {
        handleProfileGetRequest($request);
    }
    else {
        // post | put Methods are not handled yet
        handleProxy($request);
    }
}

/**
 * @param ServerRequestInterface $request
 * @throws GuzzleException
 */
function handleProfileGetRequest(ServerRequestInterface $request) {
    global $queryParams;
    $profileId = strtolower($queryParams["profileId"]);
    if ($profileId === "cmi5learnerpreferences") {
        sendData("{\"languagePreference\":\"de-DE\",\"audioPreference\":\"on\"}"); // ToDo: get real preferences
    }
    else {
        // not handled
        $log->debug("not handled profileId: " . $profileId);
        handleProxy($request);
    }
}

/**
 * handle proxy request
 * @param ServerRequestInterface $request
 * @param null                   $fakePostBody
 */
function handleProxy(ServerRequestInterface $request, $fakePostBody = NULL)
{
    global $log, $lrsType, $cmdParts;

    $endpoint = $lrsType->getLrsEndpoint();
    $log->debug("Endpoint: " . $endpoint);
    $auth = 'Basic ' . base64_encode($lrsType->getLrsKey() . ':' . $lrsType->getLrsSecret());
    $req_opts = array(
        RequestOptions::VERIFY => false,
        RequestOptions::CONNECT_TIMEOUT => 7
    );

    try {
        $cmd = $cmdParts[2];
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
        handleResponse($request, $response, $fakePostBody);
    } catch (Exception $e) { // ToDo: Errorhandling!
        $log->error($e->getMessage());
        header("HTTP/1.1 500 XapiProxy Error");
        echo "HTTP/1.1 500 XapiProxy Error";
        exit;
    }
}

/**
 * @param ServerRequestInterface $request
 * @param ResponseInterface      $response
 * @param null                   $fakePostBody
 */
function handleResponse(ServerRequestInterface $request, ResponseInterface $response, $fakePostBody = NULL)
{
    global $log;
    if ($fakePostBody !== NULL) {
        $origBody = $response->getBody();
        $log->debug("orig body: " . $origBody);
        $log->debug("fake body: " . json_encode($fakePostBody));
        // because there is a real response object, it should also be possible to override the response stream...
        // but this does the job as well:
        fakeResponseBlocked($fakePostBody);
    }
    // check transfer encoding bug
    $headers = $response->getHeaders();
    if (array_key_exists('Transfer-Encoding', $headers) && $headers['Transfer-Encoding'][0] == "chunked") {
        $log->debug("sniff response transfer-encoding for unallowed Content-length");
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

/**
 * @param null $post
 */
function fakeResponseBlocked($post=NULL) {
    global $log;
    $log->debug("fakeResponseFromBlockedRequest");
    if ($post===NULL) {
        $log->debug("post === NULL");
        header('HTTP/1.1 204 No Content');
        header('Access-Control-Allow-Origin: '.$_SERVER["HTTP_ORIGIN"]);
        header('Access-Control-Allow-Credentials: true');
        header('X-Experience-API-Version: 1.0.3');
        exit;
    }
    else {
        $ids = json_encode($post);
        $log->debug("post: " . $ids);
        header('HTTP/1.1 200 Ok');
        header('Access-Control-Allow-Origin: '.$_SERVER["HTTP_ORIGIN"]);
        header('Access-Control-Allow-Credentials: true');
        header('X-Experience-API-Version: 1.0.3');
        header('Content-Length: ' . strlen($ids));
        header('Content-Type: application/json; charset=utf-8');
        echo $ids;
        exit;
    }
}

/**
 * @param $body
 * @return false|string
 * @throws ilDatabaseException
 * @throws ilObjectNotFoundException
 */
function modifyBody($body)
{
    global $log, $replacedValues;

    $obj = json_decode($body, false);

    if (json_last_error() != JSON_ERROR_NONE) {
        // JSON is not valid
        $log->error(json_last_error_msg());
        return $body;
    }

    // $log->debug(json_encode($obj, JSON_PRETTY_PRINT)); // only in DEBUG mode for better performance

    if (is_object($obj)) {
        if (is_array($replacedValues)) {
            foreach ($replacedValues as $key => $value) {
                setValue($obj,$key,$value);
            }
        }
        handleStatementEvaluation($obj);
    }

    if (is_array($obj)) {
        for ($i = 0; $i < count($obj); $i++) {
            if (is_array($replacedValues)) {
                foreach ($replacedValues as $key => $value) {
                    setValue($obj[$i],$key,$value);
                }
            }
            handleStatementEvaluation($obj[$i]);
        }
    }

    return json_encode($obj);
}

/**
 * @param $obj
 */
function sendData($obj) {
    global $log;
    $log->debug("senData: " . $obj);
    header('HTTP/1.1 200 Ok');
    header('Access-Control-Allow-Origin: '.$_SERVER["HTTP_ORIGIN"]);
    header('Access-Control-Allow-Credentials: true');
    header('X-Experience-API-Version: 1.0.3');
    header('Content-Length: ' . strlen($obj));
    header('Content-Type: application/json; charset=utf-8');
    echo $obj;
    exit;
}

/**
 *
 */
function exitResponseError() {
    header("HTTP/1.1 412 Wrong Response");
    echo "HTTP/1.1 412 Wrong Response";
    exit;
}

/**
 *
 */
function exitProxyError() {
    header("HTTP/1.1 500 XapiProxy Error (Ask For Logs)");
    echo "HTTP/1.1 500 XapiProxy Error (Ask For Logs)";
    exit;
}

// use only for debugging states before ILIAS Init
/**
 * @param $txt
 */
function _log($txt)
{
    if (DEVMODE) {
        file_put_contents("xapilog.txt", $txt . "\n", FILE_APPEND);
    }
}
