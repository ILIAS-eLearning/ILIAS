<?php
/**
* checks if PEAR is installed and includes the auth module 
*
* @author Sascha Hofmann <shofmann@databay.de>
* @version $Id$
*
* @package ilias-core
*/
$include_paths = ini_get("include_path");

// unix & windows use different characters to separate paths
$separator = ";";

if (!strstr(php_uname(), "Windows"))
{
	$separator = ":";
}

$include_paths = explode($separator,$include_paths);

// WE HAVE TO MAKE ANOTHER CHECK SINCE file_exists IS NOT ALLOWED IN SAFE_MODE
/*
$pear = false
$auth = false;

foreach ($include_paths as $path)
{
	if (file_exists(realpath($path)."/PEAR.php"))
	{
		$pear = true;
	}
	
	if (file_exists(realpath($path)."/Auth/Auth.php"))
	{
		$auth = true;
	}
}
*/
$pear = true;
$auth = true;

if (!$pear)
{
	$msg = "<p><b>Error: Couldn't find PEAR API in your include path or in the current directory!</b><br/>".
		   "ILIAS 3 requires several modules from PEAR to run. ".
		   "Please read the manual how to install PEAR first before using ILIAS 3.</p>".
		   "<p>More information and a documetation about the PEAR API can be found at ".
		   "<a href=\"http://pear.php.net\" target=\"_blank\">http://pear.php.net</a></p>";	
	echo $msg;
	exit();
}

if (!$auth)
{
	$msg = "<p><b>Error: Couldn't find module Auth in your PEAR API!</b><br/>".
		   "ILIAS 3 requires this module for authentification. ".
		   "Please read the manual how to install the auth module before using ILIAS 3.</p>".
		   "<p>More information and a documetation about the PEAR API can be found at ".
		   "<a href=\"http://pear.php.net\" target=\"_blank\">http://pear.php.net</a></p>";	
	echo $msg;
	exit();
}
?>