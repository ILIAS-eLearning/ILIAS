<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Shibboleth login script for ilias
*
* $Id$
* @author Lukas Haemmerle <haemmerle@switch.ch>
* @package ilias-layout
*/

// Load all the IILIAS stuff
require_once "include/inc.header.php";

if (!$_SERVER['HTTP_SHIB_APPLICATION_ID'] && !$_SERVER['Shib-Application-ID'])
{
	$message = "This file must be protected by Shibboleth, otherwise you cannot use Shibboleth authentication! Consult the <a href=\"Services/AuthShibboleth/README.SHIBBOLETH.txt\">documentation</a> on how to configure Shibboleth authentication properly.";
	$ilias->raiseError($message,$ilias->error_obj->WARNING);
}

// Check if all the essential attributes are available
if (
		!$_SERVER[$ilias->getSetting('shib_login')]
		|| !$_SERVER[$ilias->getSetting('shib_firstname')]
		|| !$_SERVER[$ilias->getSetting('shib_lastname')]
		|| !$_SERVER[$ilias->getSetting('shib_email')]
   )
{
	$message =  "ILIAS needs at least the attributes '".$ilias->getSetting('shib_login')."', '".$ilias->getSetting('shib_firstname')."', '".$ilias->getSetting('shib_lastname')."' and '".$ilias->getSetting('shib_email')."' to work properly !\n<br>Please consult the <a href=\"README.SHIBBOLETH.txt\">documentation</a> on how to configure Shibboleth authentication properly.";
	
	$ilias->raiseError($message,$ilias->error_obj->WARNING);
}

global $ilAuth;

// Shibboleth login
if (!empty($_SERVER[$ilias->getSetting("shib_login")]))
{
	$ilAuth->login();
}

// We only get here if we didn't login successfully
ilUtil::redirect("login.php");
?>