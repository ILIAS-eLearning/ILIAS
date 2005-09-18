<?php
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
* ILIAS context sensitive online help class.
*
* An instance of this class is global available via $ilHelp
*
* Usage: $ilHelp->setTarget(<target_name>), example
* 		 $ilHelp->setTarget("lm_editor");
*
* All targets are listed in Services/Help/help_targets.txt
*
* @author	Alex Killing <alex.killing@gmx.de>
* @version	$Id$
*/

class ilHelp
{
	var $help_targets;
	
	/**
	* constructor
	*/
	function ilHelp()
	{
		if (strtolower($_GET["baseClass"]) != "ilhelpgui")
		{
			$_SESSION["il_help_targets"] = array();
		}
	}
	
	
	/**
	* set target help page
	*/
	function setTarget($a_target_name)
	{
		$_SESSION["il_help_targets"][] = $a_target_name;
	}
	
	/**
	* get help page for current target list
	*/
	function getHelpPage()
	{
		global $ilUser;

		// check target files
		for($i = (count($_SESSION["il_help_targets"]) - 1); $i>=0; $i--)
		{
			$file = "./docs/userdoc/".$ilUser->getLanguage().
				"/lm_pg_".$_SESSION["il_help_targets"][$i].".html";

			if (is_file($file))
			{
				return $file;
			}
		}
		
		$file = "./docs/userdoc/".$ilUser->getLanguage().
			"/index.html";

		if (is_file($file))
		{
			return $file;
		}
		
		return false;
	}
}
?>