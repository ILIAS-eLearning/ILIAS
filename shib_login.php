<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/


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