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

include_once "Services/Object/classes/class.ilObjectListGUI.php";

/**
* Class ilObjDlBookListGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @ingroup ModulesIliasLearningModule
*/
class ilObjDlBookListGUI extends ilObjectListGUI
{
	/**
	* constructor
	*
	*/
	function ilObjDlBookListGUI()
	{
		parent::__construct();
	}

	/**
	* initialisation
	*
	* this method should be overwritten by derived classes
	*/
	function init()
	{
		$this->copy_enabled = false;
		$this->delete_enabled = true;
		$this->cut_enabled = true;
		$this->subscribe_enabled = true;
		$this->link_enabled = true;
		$this->type = "dbk";
		$this->gui_class_name = "ilobjdlbookgui";
		$this->info_screen_enabled = true;
		
		// general commands array
		include_once('./Modules/LearningModule/classes/class.ilObjDlBookAccess.php');
		$this->commands = ilObjDLBookAccess::_getCommands();
	}

	function initItem($a_ref_id, $a_obj_id, $a_title = "", $a_description = "")
	{
		global $ilUser;

		parent::initItem($a_ref_id, $a_obj_id, $a_title, $a_description);
		
		include_once("./Modules/LearningModule/classes/class.ilObjLearningModuleAccess.php");
		$this->last_accessed_page = 
			ilObjLearningModuleAccess::_getLastAccessedPage($a_ref_id, $ilUser->getId());
		
	}

	/**
	* Overwrite this method, if link target is not build by ctrl class
	* (e.g. "forum.php"). This is the case
	* for all links now, but bringing everything to ilCtrl should
	* be realised in the future.
	*
	* @param	string		$a_cmd			command
	*
	*/
	function getCommandLink($a_cmd)
	{
		global $ilCtrl;
		
		switch($a_cmd)
		{
			case "continue":
				$cmd_link = "ilias.php?baseClass=ilLMPresentationGUI&amp;ref_id=".$this->ref_id.
					"&amp;obj_ud=".$this->last_accessed_page;
				break;

			case "view":
				$cmd_link = "ilias.php?baseClass=ilLMPresentationGUI&amp;ref_id=".$this->ref_id;
				break;

			case "edit":
				$cmd_link = "ilias.php?baseClass=ilLMEditorGUI&amp;ref_id=".$this->ref_id;
				break;

			case "infoScreen":
				$cmd_link = "ilias.php?baseClass=ilLMPresentationGUI&amp;ref_id=".$this->ref_id.
					"&amp;cmd=infoScreen";
				break;
				

			default:
				$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->ref_id);
				$cmd_link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", $a_cmd);
				$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
				break;
		}

		return $cmd_link;
	}


	/**
	* Get command target frame
	*
	* @param	string		$a_cmd			command
	*
	* @return	string		command target frame
	*/
	function getCommandFrame($a_cmd)
	{
		global $ilias;

		switch($a_cmd)
		{
			case "view":
				$frame = ilFrameTargetInfo::_getFrame("MainContent");
				break;

			case "edit":
				$frame = ilFrameTargetInfo::_getFrame("MainContent");
				break;

			default:
				$frame = "";
				break;
		}

		return $frame;
	}


	/**
	* Get item properties
	*
	* @return	array		array of property arrays:
	*						"alert" (boolean) => display as an alert property (usually in red)
	*						"property" (string) => property name
	*						"value" (string) => property value
	*/
	function getProperties()
	{
		global $lng, $rbacsystem;

		$props = array();

		include_once("./Modules/LearningModule/classes/class.ilObjDlBookAccess.php");

		if (ilObjDlBookAccess::_isOffline($this->obj_id))
		{
			$props[] = array("alert" => true, "property" => $lng->txt("status"),
				"value" => $lng->txt("offline"));
		}

		if ($rbacsystem->checkAccess("write", $this->ref_id))
		{
			$props[] = array("alert" => false, "property" => $lng->txt("type"),
				"value" => $lng->txt("dbk"));
		}

		return $props;
	}


} // END class.ilObjCategoryGUI
?>
