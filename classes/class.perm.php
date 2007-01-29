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


// TODO: this function collection must cleaned up!!! Many functions belong to other classes
/**
* perm class (actually a function library)
* general object handling functions
*
* @author	Sascha Hofmann <shofmann@databay.de>
* @author	Stefan Meyer <smeyer@databay.de>
* @version	$Id$
*/




/**
* sends a message to the recent page
* if you call sendInfo without any parameter, function will display a stored message
* in session and delete it afterwards
* @access	public
* @param	string	message
* @param	boolean	if true message is kept in session
*/
function sendInfo($a_info = "",$a_keep = false)
{
	global $tpl;

	if (!empty($a_info))
	{
		$_SESSION["info"] = $a_info;
	}
	if (!empty($_SESSION["info"]))
	{
		$tpl->addBlockFile("MESSAGE", "message", "tpl.message.html");
#		$tpl->setCurrentBlock("message");
		$tpl->setVariable("INFO",$_SESSION["info"]);
#		$tpl->parseCurrentBlock();
	}

	if (!$a_keep)
	{
		session_unregister("info");
	}
}

function infoPanel($a_keep = true)
{
	global $tpl,$ilias,$lng;

	if (!empty($_SESSION["infopanel"]) and is_array($_SESSION["infopanel"]))
	{
		$tpl->addBlockFile("INFOPANEL", "infopanel", "tpl.infopanel.html");
		$tpl->setCurrentBlock("infopanel");

		if (!empty($_SESSION["infopanel"]["text"]))
		{
			$link = "<a href=\"".$dir.$_SESSION["infopanel"]["link"]."\" target=\"".
				ilFrameTargetInfo::_getFrame("MainContent").
				"\">";
			$link .= $lng->txt($_SESSION["infopanel"]["text"]);
			$link .= "</a>";
		}

		// deactivated
		if (!empty($_SESSION["infopanel"]["img"]))
		{
			$link .= "<td><a href=\"".$_SESSION["infopanel"]["link"]."\" target=\"".
				ilFrameTargetInfo::_getFrame("MainContent").
				"\">";
			$link .= "<img src=\"".$ilias->tplPath.$ilias->account->prefs["skin"]."/images/".
				$_SESSION["infopanel"]["img"]."\" border=\"0\" vspace=\"0\"/>";
			$link .= "</a></td>";
		}

		$tpl->setVariable("INFO_ICONS",$link);
		$tpl->parseCurrentBlock();
	}

	//if (!$a_keep)
	//{
			session_unregister("infopanel");
	//}
}
?>
