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

if (!$_SERVER['HTTP_SHIB_APPLICATION_ID'])
{
	echo "This file must be protected by Shibboleth, otherwise you cannot use Shibboleth authentication! Consult the <a href=\"README.SHIBBOLETH.txt\">documentation</a> on how to configure Shibboleth authentication properly.";
	exit;
}

// Load all the IILIAS stuff
require_once "include/inc.header.php";

// Shibboleth login
if (!empty($_SERVER[$ilias->getSetting("shib_login")]))
{
	$ilias->auth->login();
}

// We only get here if we didn't login successfully
header("Location: login.php");
?>