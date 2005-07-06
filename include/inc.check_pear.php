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
* checks if PEAR is installed and includes the auth module 
*
* @author Sascha Hofmann <shofmann@databay.de>
* @version $Id$
*
* @package ilias-core
*/
include_once("PEAR.php");
include_once("Auth/Auth.php");

// wrapper for php 4.3.2 & higher
@include_once "HTML/ITX.php";
$tpl_class_name = "IntegratedTemplate";

if (!class_exists("IntegratedTemplateExtension"))
{
	include_once "HTML/Template/ITX.php";
	$tpl_class_name = "HTML_Template_ITX";
}

$include_paths = ini_get("include_path");

// unix & windows use different characters to separate paths
$separator = ";";

if (!strstr(php_uname(), "Windows"))
{
	$separator = ":";
}

$include_paths = explode($separator,$include_paths);

$pear = class_exists("PEAR");
$auth = class_exists("Auth");

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