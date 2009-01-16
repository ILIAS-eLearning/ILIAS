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
// if no client_id is given, default client is loaded (in class.ilias.php)
if (isset($_GET["client_id"]))
{
	$cookie_domain = $_SERVER['SERVER_NAME'];
	$cookie_path = dirname( $_SERVER['PHP_SELF'] ).'/';
	
	$cookie_domain = ''; // Temporary Fix
	
	setcookie("ilClientId", $_GET["client_id"], 0, $cookie_path, $cookie_domain);
	
	$_COOKIE["ilClientId"] = $_GET["client_id"];
}

require_once "include/inc.header.php";

$ilCtrl->initBaseClass("ilStartUpGUI");
$ilCtrl->setTargetScript("ilias.php");
$ilCtrl->callBaseClass();
$ilBench->save();

?>
