<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2008 ILIAS open source, University of Cologne            |
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

include_once("Services/Table/classes/class.ilTable2GUI.php");

define ("IL_CMPS_MODULES", "mod");
define ("IL_CMPS_SERVICES", "ser");

/**
* TableGUI class for components listing
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ingroup ServicesComponent
*/
class ilComponentsTableGUI extends ilTable2GUI
{
	private $mode;
	
	function ilComponentsTableGUI($a_parent_obj, $a_parent_cmd = "",
		$a_mode = IL_CMPS_MODULES)
	{
		global $ilCtrl, $lng;
		
		$this->mode = $a_mode;
		
		parent::__construct($a_parent_obj, $a_parent_cmd);
		
		$this->addColumn($lng->txt("cmps_module"));
		$this->addColumn($lng->txt("cmps_rep_object"));
		$this->setEnableHeader(true);
		$this->setFormAction($ilCtrl->getFormAction($a_parent_obj));
		$this->setRowTemplate("tpl.table_row_component.html",
			"Services/Component");
		$this->getComponents();
		$this->setDefaultOrderField("subdir");
		$this->setLimit(10000);
		
		// save options command
		$this->addCommandButton("saveOptions", $lng->txt("cmps_save_options"));

		if ($this->mode == IL_CMPS_MODULES)
		{
			$this->setTitle($lng->txt("cmps_modules"));
		}
		else
		{
			$this->setTitle($lng->txt("cmps_services"));
		}
	}
	
	/**
	* Get pages for list.
	*/
	function getComponents()
	{
		include_once("./Services/Component/classes/class.ilModule.php");

		if ($this->mode == IL_CMPS_MODULES)
		{
			$modules = ilModule::getAvailableCoreModules();
			$this->setData($modules);
		}
		else
		{
			$services = ilService::getAvailableCoreServices();
			$this->setData($services);
		}
	}
	
	/**
	* Standard Version of Fill Row. Most likely to
	* be overwritten by derived class.
	*/
	protected function fillRow($a_set)
	{
		global $lng, $ilCtrl, $ilSetting, $objDefinition;
		
		$rep_types = 
			ilComponent::getRepositoryObjectTypesForComponent(IL_COMP_MODULE, $a_set["subdir"]);
		foreach ($rep_types as $rt)
		{
			// group
			if ($rt["grp"] != "")
			{
				$this->tpl->setCurrentBlock("group");
				$this->tpl->setVariable("TXT_GROUP", $lng->txt("cmps_group"));
				$gi = $objDefinition->getGroup($rt["grp"]);
				$this->tpl->setVariable("VAL_GROUP", $gi["name"]);
				$this->tpl->setVariable("VAL_GROUP_ID", $rt["grp"]);
				$this->tpl->parseCurrentBlock();
			}
			
			$this->tpl->setCurrentBlock("rep_object");
			$this->tpl->setVariable("TXT_REP_OBJECT",
				$rt["class_name"]);
			$this->tpl->setVariable("TXT_REP_OBJECT_ID",
				$rt["id"]);
			$this->tpl->setVariable("IMG_REP_OBJECT",
				ilUtil::getImagePath("icon_".$rt["id"].".gif"));
				
			// add new position
			$this->tpl->setVariable("TXT_ADD_NEW_POS",
				$lng->txt("cmps_add_new_rank"));
			$this->tpl->setVariable("VAR_POS",
				"obj_pos[".$rt["id"]."]");
			$pos = ($ilSetting->get("obj_add_new_pos_".$rt["id"]) > 0)
				? $ilSetting->get("obj_add_new_pos_".$rt["id"])
				: $rt["default_pos"];
			$this->tpl->setVariable("VAL_POS",
				ilUtil::prepareFormOutput($pos));
				
			// disable creation
			$this->tpl->setVariable("TXT_DISABLE_CREATION",
				$lng->txt("cmps_disable_creation"));
			$this->tpl->setVariable("VAR_DISABLE_CREATION",
				"obj_dis_creation[".$rt["id"]."]");
			if ($ilSetting->get("obj_dis_creation_".$rt["id"]))
			{
				$this->tpl->setVariable("CHECKED_DISABLE_CREATION",
					' checked="checked" ');
			}
			
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable("TXT_MODULE_NAME", $a_set["subdir"]);
	}

}
?>
