<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

include_once("./classes/class.ilExplorer.php");

/**
* Class ilStyleScopyExplorer
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
*/

class ilStyleScopeExplorer extends ilExplorer
{
	/**
	* Constructor
	* @access	public
	* @param	string	scriptname
	*/
	function ilStyleScopeExplorer($a_target)
	{
		if ($_POST["id"][0] > 0)
		{
			$this->style_id = $_POST["id"][0];
		}
		else
		{
			$this->style_id = $_GET["stlye_id"];
		}
		
		parent::ilExplorer($a_target);
	}
	
	function formatHeader(&$tpl,$a_obj_id,$a_option)
	{
		global $lng, $ilias, $ilCtrl;

		$tpl->setCurrentBlock("icon");
		$tpl->setVariable("ICON_IMAGE", ilUtil::getImagePath("icon_root.gif"));
		$tpl->setVariable("TXT_ALT_IMG", $lng->txt("repository"));
		$tpl->parseCurrentBlock();

		$ilCtrl->setParameterByClass("ilobjstylesettingsgui",
			"cat", 0);
		$ilCtrl->setParameterByClass("ilobjstylesettingsgui",
			"style_id", $this->style_id);

		$tpl->setCurrentBlock("link");
		$tpl->setVariable("TITLE", $lng->txt("repository"));
		$tpl->setVariable("LINK_TARGET", $ilCtrl->getLinkTargetByClass("ilobjstylesettingsgui",
			"saveScope"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("element");
		$tpl->parseCurrentBlock();
	}

	/**
	* get link target
	*/
	function buildLinkTarget($a_node_id, $a_type)
	{
		global $ilCtrl;
		
		$ilCtrl->setParameterByClass("ilobjstylesettingsgui",
			"cat", $a_node_id);
		$ilCtrl->setParameterByClass("ilobjstylesettingsgui",
			"style_id", $this->style_id);
		
		return $ilCtrl->getLinkTargetByClass("ilobjstylesettingsgui",
			"saveScope");
	}

} // END class.ilExplorer
?>
