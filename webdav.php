<?php
// BEGIN WebDAV
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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
* This script provides a WebDAV interface for the ILIAS repository.
*
* @author Werner Randelshofer, Hochschule Luzern, werner.randelshofer@hslu.ch
* @version $Id: class.ilDAVServer.php,v 1.0 2005/07/08 12:00:00 wrandelshofer Exp $
*
* @package webdav
*/

// Initialize
// -----------------------------------------------------

// Retrieve the client id from PATH_INFO
// Component 1 contains the ILIAS client_id.
$path_info_components = explode('/',$_SERVER['PATH_INFO']);
$client_id = $path_info_components[1];

// For all requests, except for GET-Requests for files, we enforce HTTP 
// authentication for the WebDAV protocol.
if ($_SERVER['REQUEST_METHOD'] != 'GET' || 
	count($path_info_components) < 3 ||
	substr($path_info_components[2],0,5) != 'file_') {
	define ('WebDAV_Authentication', 'HTTP');
}
define ('WebDAV_Authentication', 'HTTP');

// Launch ILIAS using the client id we have determined
// -----------------------------------------------------
$_COOKIE["ilClientId"] = $client_id;

// we can't include inc.header.php here, because we need to pass the
// context 'webdav' to initILIAS.
//require_once "include/inc.header.php";
require_once("Services/Init/classes/class.ilInitialisation.php");
$ilInit = new ilInitialisation();
$GLOBALS['ilInit'] =& $ilInit;
$ilInit->initILIAS('webdav');

// Launch the WebDAV Server
// -----------------------------------------------------
include_once "Services/WebDAV/classes/class.ilDAVServer.php";
$server =  new ilDAVServer();
$server->ServeRequest();
// END WebDAV
?>
