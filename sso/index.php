<?php

chdir ('..');

define('IL_CERT_SSO', true);
define('IL_COOKIE_PATH', $_REQUEST['cookie_path']);
if ($_REQUEST['ilias_path'])
    define('ILIAS_HTTP_PATH', $_REQUEST['ilias_path']);

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