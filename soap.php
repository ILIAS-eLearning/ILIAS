<?php

$wsdl = 'http://php7/il/53x/webservice/soap/server.php?wsdl=1';
$user = 'root';
$pass = 'homerr';
$client = 'default';


try
{
	$soap = new SoapClient($wsdl);
	$refId = 80;
	
	$sid = $soap->login($client, $user, $pass);
	$results = $soap->getTestResults($sid, $refId, false);
	
	var_dump($results);
	
	$dom = new DOMDocument();
	$dom->formatOutput = true;
	$dom->loadXML($results);
	
	echo '<pre>'.htmlentities($dom->saveXML()).'</pre>';
}
catch(SoapFault $f)
{
	echo '<pre>'.htmlentities($soap->__getLastResponse()).'</pre>';
	echo '<pre>'.htmlentities($f).'</pre>';
	exit;
}

