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

// start correct client
// if no client_id is given, default client is loaded (in class.ilias.php)
if (isset($_GET["client_id"]))
{
	setcookie("ilClientId",$_GET["client_id"]);
	$_COOKIE["ilClientId"] = $_GET["client_id"];
}

require_once "include/inc.get_pear.php";
require_once "include/inc.check_pear.php";
require_once "include/inc.header.php";

// display client selection list if enabled
if (!isset($_COOKIE["ilClientId"]) and !isset($_GET["cmd"]) and $ilias->ini_ilias->readVariable("clients","list"))
{
	// catch reload
	if ($_GET["reload"])
	{
        if ($_GET["inactive"])
        {
            echo "<script language=\"Javascript\">\ntop.location.href = \"./login.php?inactive=true\";\n</script>\n";
        }
        else
        {
            echo "<script language=\"Javascript\">\ntop.location.href = \"./login.php?expired=true\";\n</script>\n";
        }
	}

	include_once "./include/inc.client_list.php";
	exit();
}

if ($_GET["cmd"] == "login")
{
	$ilias->auth->logout();
	session_destroy();

	// reset cookie
	$client_id = $_COOKIE["ilClientId"];
	setcookie("ilClientId","");
	$_COOKIE["ilClientId"] = "";

	ilUtil::redirect("login.php?client_id=".$client_id);
}

// check correct setup
if (!$ilias->getSetting("setup_ok"))
{
	echo "setup is not completed. Please run setup routine again.";
	exit();
}

// Specify your start page in ilias.ini.php
$start = $ilias->ini->readVariable("server", "start");

// if no start page was given, ILIAS defaults to the standard login page
if ($start == "")
{
	$start = "login.php";
}

if ($ilias->getSetting("pub_section"))
{
	$start = "nologin.php";
}

$connector = "?";

// catch reload
if ($_GET["reload"])
{
    if ($_GET["inactive"])
    {
        $start .= "?reload=true&inactive=true";
    }
    else
    {
        $start .= "?reload=true";
    }
    $connector = "&";
}

ilUtil::redirect($start.$connector."return_to=".$_GET["return_to"]);
?>
