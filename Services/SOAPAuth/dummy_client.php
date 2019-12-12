<?php

exit;

//
// PART 3: Test it the normal way
//

include_once("./include/nusoap.php");
//include_once ("./include/class.ilXmlWriter.php");

$server = $_GET["server"]
    ? $_GET["server"]
    : "http://www.ilias.de/lt4el/Services/SOAPAuth/dummy_server.php";
    
$ext_uid = $_GET["ext_uid"]
    ? $_GET["ext_uid"]
    : "testuser";

$soap_pw = $_GET["soap_pw"]
    ? $_GET["soap_pw"]
    : "testpw";

$new_user = $_GET["new_user"];

echo '<form>' .
    'server <input size="80" type="text" name="server" "value="' . $server . '"/>' .
    '<br />ext_uid <input size="50" type="text" name="ext_uid" "value="' . $ext_uid . '"/>' .
    '<br />soap_pw <input size="50" type="text" name="soap_pw" "value="' . $soap_pw . '"/>' .
    '<br />new_user <input size="50" type="text" name="new_user" "value="' . $new_user . '"/> (1 for true, 0 for false)' .
    '<br /><input type="submit" /><br />' .
    '<b>The test server will return true/valid, if ext_uid == soap_pw.</b>' .
    '</form>';

echo "<br /><br />----------------------------------------------<br /><br /> Calling Server...";

// initialize soap client
$client = new soap_client($server);
if ($err = $client->getError()) {
    echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
}


// isValidSession call
//$valid = $client->call('isValidSession',
//		array('ext_uid' => $ext_uid,
//			'soap_pw' => $soap_pw,
//			'new_user' => $new_user));

$namespace = "http://testuri.org";

$valid = $client->call(
    'isValidSession',
    array('ns1:ext_uid' => $ext_uid,
            'ns1:soap_pw' => $soap_pw,
            'ns1:new_user' => $new_user),
    $namespace,
    $namespace . "/isValidSession"
);

showResult($client, $valid, 'isValidSession');

echo "<br />End Test";

function showResult(&$client, $data, $message)
{
    if ($client->fault) {
        echo '<h2>Fault</h2><pre>';
        print_r($data);
        echo '</pre>';
    } else {
        // Check for errors
        $err = $client->getError();
        if ($err) {
            // Display the error
            echo '<h2>Error</h2><pre>' . $err . '</pre>';
            exit;
        } else {
            // Display the result
            echo '<h2>Result ' . $message . '</h2><pre>';
            print_r($data ? $data : 'FAILED');
            echo '</pre>';
        }
    }
}
