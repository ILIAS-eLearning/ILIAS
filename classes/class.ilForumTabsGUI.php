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

include_once("classes/class.ilTabsGUI.php");

/**
* Class ilForumTabsGUI
*
* The whole forum implementation should be made more oo someday
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package ilias-forum
*/
class ilForumTabsGUI
{
	var $lng;
	var $tpl;
	var $frm;
	var $ref_id;

	function ilForumTabsGUI()
	{
		global $lng, $tpl, $tree;

		$this->lng =& $lng;
		$this->tpl =& $tpl;
		$this->tabs_gui =& new ilTabsGUI();
	}

	function setTemplateVariable($a_temp_var)
	{
		$this->temp_var = $a_temp_var;
	}

	function setForum(&$a_frm)
	{
		$this->frm =& $a_frm;
	}

	function setRefId($a_ref_id)
	{
		$this->ref_id =& $a_ref_id;
	}

	/**
	* output tabs
	*/
	function setTabs()
	{
		$this->getTabs($this->tabs_gui);
		$this->tpl->setVariable("TABS", $this->tabs_gui->getHTML());
	}

	/**
	* adds tabs to tab gui object
	*
	* @param	object		$tabs_gui		ilTabsGUI object
	*/
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem;

		// properties
		if ($rbacsystem->checkAccess("write", $_GET["ref_id"]))
		{
			$tabs_gui->addTarget("edit_properties",
				"repository.php?cmd=properties&ref_id=".$_GET["ref_id"],
				"properties");
		}

		// edit permission
		if ($rbacsystem->checkAccess("edit_permission", $_GET["ref_id"]))
		{
			$tabs_gui->addTarget("perm_settings",
				"repository.php?cmd=permissions&ref_id=".$_GET["ref_id"],
				"permissions");
		}
	}


} // END class ilForumLocatorGUI
