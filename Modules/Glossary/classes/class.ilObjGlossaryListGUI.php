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


/**
* Class ilObjGlossaryListGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @ingroup ModulesGlossary
*/


include_once "classes/class.ilObjectListGUI.php";

class ilObjGlossaryListGUI extends ilObjectListGUI
{
	/**
	* constructor
	*
	*/
	function ilObjGlossaryListGUI()
	{
		$this->ilObjectListGUI();
	}

	/**
	* initialisation
	*
	* this method should be overwritten by derived classes
	*/
	function init()
	{
		$this->static_link_enabled = true;
		$this->delete_enabled = true;
		$this->cut_enabled = true;
		$this->copy_enabled = false;
		$this->subscribe_enabled = true;
		$this->link_enabled = true;
		$this->payment_enabled = false;
		$this->info_screen_enabled = true;
		$this->type = "glo";
		$this->gui_class_name = "ilobjglossarygui";
		
		// general commands array
		include_once("./Modules/Glossary/classes/class.ilObjGlossaryAccess.php");
		$this->commands = ilObjGlossaryAccess::_getCommands();
	}

	/**
	* Overwrite this method, if link target is not build by ctrl class
	* (e.g. "lm_presentation.php", "forum.php"). This is the case
	* for all links now, but bringing everything to ilCtrl should
	* be realised in the future.
	*
	* @param	string		$a_cmd			command
	*
	*/
	function getCommandLink($a_cmd)
	{
		switch($a_cmd)
		{
			case "view":
				$cmd_link = "ilias.php?baseClass=ilGlossaryPresentationGUI&amp;ref_id=".$this->ref_id;
				break;

			case "edit":
				$cmd_link = "ilias.php?baseClass=ilGlossaryEditorGUI&amp;ref_id=".$this->ref_id;
				break;

			case "infoScreen":
				$cmd_link = "ilias.php?baseClass=ilGlossaryPresentationGUI&amp;cmd=infoScreen&amp;ref_id=".$this->ref_id;
				break;
				
			default:
				$cmd_link = "repository.php?ref_id=".$this->ref_id."&cmd=$a_cmd";
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

		include_once("./Modules/Glossary/classes/class.ilObjGlossaryAccess.php");

		if (!ilObjGlossaryAccess::_lookupOnline($this->obj_id))
		{
			$props[] = array("alert" => true, "property" => $lng->txt("status"),
				"value" => $lng->txt("offline"));
		}
		return $props;
	}


} // END class.ilObjCategoryGUI
?>
