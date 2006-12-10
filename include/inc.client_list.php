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

die ("inc.client_list.php is deprecated.");

/**
* display list of available clients
* 
* @author	Sascha Hofmann <shofmann@databay.de> 
* @version $Id$
*
* @package ilias
*/

//include files from PEAR
require_once "PEAR.php";
require_once "DB.php";

// wrapper for php 4.3.2 & higher
@include_once "HTML/ITX.php";

if (!class_exists(IntegratedTemplateExtension))
{
	include_once "HTML/Template/ITX.php";
	//include_once "classes/class.ilTemplate2.php";
	include_once "classes/class.ilTemplateHTMLITX.php";
}
else
{
	//include_once "classes/class.ilTemplate.php";
	include_once "classes/class.ilTemplateITX.php";
}

require_once "classes/class.ilTemplate.php";

require_once "./classes/class.ilIniFile.php";
require_once "./Services/Table/classes/class.ilTableGUI.php";
require_once "./Services/Utilities/classes/class.ilUtil.php";
require_once "./setup/classes/class.ilClient.php";
require_once "./setup/classes/class.ilClientList.php";

// load setup.ini
$ini_ilias = new ilIniFile("./ilias.ini.php");
$ini_ilias->read();

define("ILIAS_DATA_DIR",$ini_ilias->readVariable("clients","datadir"));
define("ILIAS_WEB_DIR",$ini_ilias->readVariable("clients","path"));

define ("ILIAS_HTTP_PATH",$ini_ilias->readVariable('server','http_path'));
define ("ILIAS_ABSOLUTE_PATH",$ini_ilias->readVariable('server','absolute_path'));

define ("DEBUG",false);
	
// read path + command for third party tools from ilias.ini
define ("PATH_TO_CONVERT",$ini_ilias->readVariable("tools","convert"));	
define ("PATH_TO_ZIP",$ini_ilias->readVariable("tools","zip"));
define ("PATH_TO_UNZIP",$ini_ilias->readVariable("tools","unzip"));
define ("PATH_TO_JAVA",$ini_ilias->readVariable("tools","java"));
define ("PATH_TO_HTMLDOC",$ini_ilias->readVariable("tools","htmldoc"));
		
//instantiate template - in the main program please use ILIAS Template class
//$tpl = new $tpl_class_name ("./templates/default");
//$tpl->loadTemplatefile("tpl.main.html", true, true);
// instantiate main template
$tpl = new ilTemplate("tpl.main.html", true, true);

$tpl->setVariable("PAGETITLE","Client List");
$tpl->setVariable("LOCATION_STYLESHEET","./templates/default/delos.css");
$tpl->setVariable("LOCATION_JAVASCRIPT","./templates/default");

// load client list template
$tpl->addBlockfile("CONTENT", "content", "tpl.client_list.html");

// load template for table
$tpl->addBlockfile("CLIENT_LIST", "client_list", "tpl.table.html");
// load template for table content data
$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.obj_tbl_rows.html");

// load table content data
$clientlist = new ilClientList();
$list = $clientlist->getClients();

if (count($list) == 0)
{
    header("Location: ./setup/setup.php");
	exit();
}

foreach ($list as $key => $client)
{
	if ($client->checkDatabaseExists() and $client->ini->readVariable("client","access") and $client->getSetting("setup_ok"))
	{
		$data[] = array(
						$client->getName(),
						"<a href=\"index.php?client_id=".$key."\">Start page</a>",
						"<a href=\"login.php?client_id=".$key."\">Login page</a>"
						);
	}
}

// create table
$tbl = new ilTableGUI();

// title & header columns
$tbl->setTitle("Available Clients");
$tbl->setHeaderNames(array("Installation Name","Public Access","Login"));
$tbl->setHeaderVars(array("name","index","login"));
$tbl->setColumnWidth(array("50%","25%","25%"));

// control
$tbl->setOrderColumn($_GET["sort_by"],"name");
$tbl->setOrderDirection($_GET["sort_order"]);
$tbl->setLimit($_GET["limit"]);
$tbl->setOffset($_GET["offset"]);

// content
$tbl->setData($data);

// footer
$tbl->setFooter("tblfooter");

// styles
$tbl->setStyle("table","std");

$tbl->disable("icon");
$tbl->disable("numinfo");

// render table
$tbl->render();

$tpl->show();
?>