<?php

echo "<pre>";

ini_set('soap.wsdl_cache', 0);
ini_set('soap.wsdl_cache_enabled', 0);

$testObjId = 287;

$WSDL = 'http://php5/il/51x/Customizing/global/plugins/Services/UIComponent/UserInterfaceHook/TestQuizzAppExtension/api/server.php?sendwsdl=1';

try
{
	$soap = new SoapClient($WSDL, array(
		'exceptions' => true, 'trace' => true
	));
	$soap->__setCookie('XDEBUG_SESSION', 'PHPSTORM');
	
	#$sid = $soap->login('default', 'root', 'homer', '0.5.0');
	$sid = $soap->login('default', 'bheyser', 'bheyser', '0.5.0');
	
	$guido = $soap->startDuelTestPass($sid, $testObjId);
	print_r(json_decode($guido));
	
	#$soap->finishTestPass($sid, $testObjId, json_encode(array(33 => 0)));
	#$soap->finishTestPass($sid, $testObjId, json_encode(array(34 => 0)));
	
	#$solutions = $soap->getSolutions($sid, $testObjId, 0);
	#echo "(".$solutions.")\n";
	
	#$results = $soap->getPassResults($sid, $testObjId);
	#print_r(json_decode($results));
	
}
catch(SoapFault $f)
{
	echo $f;
}
