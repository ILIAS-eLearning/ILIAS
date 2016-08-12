<?php

chdir ('..');


$cookie_path = dirname(dirname($_SERVER['PHP_SELF']));

/* if ilias is called directly within the docroot $cookie_path
is set to '/' expecting on servers running under windows..
here it is set to '\'.
in both cases a further '/' won't be appended due to the following regex
*/
$cookie_path .= (!preg_match("/[\/|\\\\]$/", $cookie_path)) ? "/" : "";

if(isset($_GET["client_id"]))
{
	if($cookie_path == "\\")
	{
		$cookie_path = '/';
	}
	
	setcookie("ilClientId", $_GET["client_id"], 0, $cookie_path, '');
	$_COOKIE["ilClientId"] = $_GET["client_id"];
}

define('IL_COOKIE_PATH', $cookie_path);

include_once './Services/Context/classes/class.ilContext.php';
ilContext::init(ilContext::CONTEXT_APACHE_SSO);

require_once("Services/Init/classes/class.ilInitialisation.php");
ilInitialisation::initILIAS();

$ilCtrl->initBaseClass("ilStartUpGUI");
$ilCtrl->setCmd('doApacheAuthentication');
$ilCtrl->setTargetScript("ilias.php");
$ilCtrl->callBaseClass();



exit;



include_once './Services/Authentication/classes/class.ilAuthUtils.php';

$_POST['auth_mode'] = AUTH_APACHE;

ilAuthFactory::setContext(ilAuthFactory::CONTEXT_APACHE);

require_once "include/inc.header.php";

$redirect = $_GET['r'];

$validDomains = array();

$path = ILIAS_DATA_DIR . '/' . CLIENT_ID . '/apache_auth_allowed_domains.txt';
if(file_exists($path) && is_readable($path))
{
	foreach(file($path) as $line)
	{
		if(trim($line))
		{
			$validDomains[] = trim($line);
		}
	}
}

$P = parse_url($redirect);
$redirectDomain = $P["host"];

$validRedirect = false;

foreach($validDomains as $validDomain)
{
	if( $redirectDomain === $validDomain )
	{
		$validRedirect = true;
		break;
	}
	
	if( strlen($redirectDomain) > (strlen($validDomain) + 1) )
	{
		if( substr($redirectDomain, (0 - strlen($validDomain) - 1)) === '.'. $validDomain)
		{
			$validRedirect = true;
			break;
		}	
	}
}

if( !$validRedirect )
{
	die('The redirect target "'.$redirect.'" is not in the list of allowed domains.');
}

if (strpos($redirect, '?') === false)
	$redirect .= '?passed_sso=1';
else
	$redirect .= '&passed_sso=1';

if ((defined('APACHE_ERRORCODE') && APACHE_ERRORCODE) || (!$ilUser || $ilUser->getId() == ANONYMOUS_USER_ID || !$ilUser->getId()))
	$redirect .= '&auth_stat='. AUTH_APACHE_FAILED;



header('Location: ' . $redirect);