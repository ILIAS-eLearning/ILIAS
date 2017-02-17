<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* SOAP server
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @package ilias
*/

chdir("../..");
define ("ILIAS_MODULE", "webservice/soap");
define ("IL_SOAPMODE_NUSOAP", 0);
define ("IL_SOAPMODE_INTERNAL", 1);

// php7 only SOAPMODE_INTERNAL
define('IL_SOAPMODE', IL_SOAPMODE_INTERNAL);

include_once "Services/Context/classes/class.ilContext.php";
ilContext::init(ilContext::CONTEXT_SOAP);

require_once("./Services/Init/classes/class.ilIniFile.php");
$ilIliasIniFile = new ilIniFile("./ilias.ini.php");
$ilIliasIniFile->read();

if ((bool)$ilIliasIniFile->readVariable('https', 'auto_https_detect_enabled')) 
{
	$headerName = $ilIliasIniFile->readVariable('https', 'auto_https_detect_header_name');
	$headerValue = $ilIliasIniFile->readVariable('https', 'auto_https_detect_header_value');

	$headerName = "HTTP_".str_replace("-","_", strtoupper($headerName));
	if (strcasecmp($_SERVER[$headerName], $headerValue) == 0) 
	{
		$_SERVER['HTTPS'] = 'on';
	}
}

if(IL_SOAPMODE == IL_SOAPMODE_INTERNAL && strcasecmp($_SERVER["REQUEST_METHOD"], "post") == 0 )
{
	// called by webservice
	//ini_set("soap.wsdl_cache_enabled", "1"); 
	include_once('webservice/soap/include/inc.soap_functions.php');
	$soapServer = new SoapServer(ilSoapFunctions::buildHTTPPath()."/webservice/soap/nusoapserver.php?wsdl");
	$soapServer->setClass("ilSoapFunctions");
	$soapServer->handle();				
} 
else 
{
	include ('webservice/soap/nusoapserver.php');	
}
?>