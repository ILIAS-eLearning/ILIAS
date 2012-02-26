<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilForumTabsGUI
*
* The whole forum implementation should be made more oo someday
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ModulesForum
*/
class ilForumTabsGUI
{
	var $lng;
	var $tpl;
	var $frm;
	var $ref_id;

	function ilForumTabsGUI()
	{
		global $lng, $tpl, $tree,$ilTabs;

		$this->lng =& $lng;
		$this->tpl =& $tpl;
		$this->tabs_gui =& $ilTabs;
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
		#$this->tpl->setVariable("TABS", $this->tabs_gui->getHTML());
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
