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
* logout script for ilias
*
* @author Sascha Hofmann <shofmann@databay.de>
* @version $Id$
*
* @package ilias-core
*/

require_once "include/inc.header.php";

// LOGOUT CHAT USER
if($ilias->getSetting("chat_active"))
{
	include_once "./chat/classes/class.ilChatServerCommunicator.php";
	ilChatServerCommunicator::_logout();
}
$ilias->auth->logout();
session_destroy();

// reset cookie
$client_id = $_COOKIE["ilClientId"];
setcookie("ilClientId","");
$_COOKIE["ilClientId"] = "";

//instantiate logout template
$tpl->addBlockFile("CONTENT", "content", "tpl.logout.html");

if ($ilias->getSetting("pub_section"))
{
	$tpl->setCurrentBlock("homelink");
	$tpl->setVariable("CLIENT_ID","?client_id=".$client_id."&lang=".$_GET['lang']);
	$tpl->setVariable("TXT_HOME",$lng->txt("home"));
	$tpl->parseCurrentBlock();
}

if ($ilias->ini_ilias->readVariable("clients","list"))
{
	$tpl->setCurrentBlock("client_list");
	$tpl->setVariable("TXT_CLIENT_LIST",$lng->txt("to_client_list"));
	$tpl->parseCurrentBlock();	
}

$tpl->setVariable("TXT_PAGEHEADLINE",$lng->txt("logout"));
$tpl->setVariable("TXT_LOGOUT_TEXT",$lng->txt("logout_text"));
$tpl->setVariable("TXT_LOGIN",$lng->txt("login_to_ilias"));
$tpl->setVariable("CLIENT_ID","?client_id=".$client_id."&lang=".$_GET['lang']);
	
$tpl->show();
?>