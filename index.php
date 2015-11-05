<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* start page of ilias 
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
*
* @package ilias
*/

// jump to setup if ILIAS3 is not installed
if (!file_exists(getcwd()."/ilias.ini.php"))
{
    header("Location: ./setup/setup.php");
	exit();
}

// BEGIN WebDAV: Block WebDAV Requests from Microsoft WebDAV MiniRedir client.
// We MUST block WebDAV requests on the root page of the Web-Server
// in order to make the "Microsoft WebDAV MiniRedir" client work with ILIAS
// WebDAV.
// Important: If this index.php page is NOT at the root of your Web-Server, you 
// MUST create an index page at the root of your Web-Server with the same
// blocking behaviour. If you don't implement this, the "Microsoft WebDAV 
// MiniRedir" client will not work with ILIAS. 
// You can copy the file rootindex.php for this.

// Block WebDAV Requests from Microsoft WebDAV MiniRedir client.
if ($_SERVER['REQUEST_METHOD'] == 'PROPFIND'
|| $_SERVER['REQUEST_METHOD'] == 'OPTIONS')
{
	$status = '404 Not Found';
	header("HTTP/1.1 $status");
	header("X-WebDAV-Status: $status", true);
	exit;
}
// END WebDAV: Block WebDAV Requests from Microsoft WebDAV MiniRedir client.


// start correct client
#added by AJM on 2015-Sep-03 - if domain name is revia, client_id is revia
if(in_array($_SERVER['SERVER_NAME'],array('stage-lms.revia.ca','lms.revia.ca'))){
        $_GET['client_id']='REVIA';
}
#end add by AJM

// if no client_id is given, default client is loaded (in class.ilias.php)
if (isset($_GET["client_id"]))
{
	$cookie_domain = $_SERVER['SERVER_NAME'];
	$cookie_path = dirname( $_SERVER['PHP_SELF'] );

	/* if ilias is called directly within the docroot $cookie_path
	is set to '/' expecting on servers running under windows..
	here it is set to '\'.
	in both cases a further '/' won't be appended due to the following regex
	*/
	$cookie_path .= (!preg_match("/[\/|\\\\]$/", $cookie_path)) ? "/" : "";

	if($cookie_path == "\\") $cookie_path = '/';
	
	$cookie_domain = ''; // Temporary Fix
	
	setcookie("ilClientId", $_GET["client_id"], 0, $cookie_path, $cookie_domain);
	
	$_COOKIE["ilClientId"] = $_GET["client_id"];
}

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

$ilCtrl->initBaseClass("ilStartUpGUI");
$ilCtrl->setTargetScript("ilias.php");
$ilCtrl->callBaseClass();
$ilBench->save();

?>
