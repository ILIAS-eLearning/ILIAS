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
* Class ilObjChatListGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @extends ilObjectListGUI
*/


include_once "classes/class.ilObjectListGUI.php";

class ilObjChatListGUI extends ilObjectListGUI
{
	/**
	* constructor
	*
	* @param	object		$a_container_obj	container gui object, e.g.
	*											an instance of ilObjCategoryGUI or
	*											ilObjCourseGUI
	*/
	function ilObjChatListGUI(&$a_container_obj)
	{
		$this->ilObjectListGUI($a_container_obj);
	}

	/**
	* initialisation
	*
	* this method should be overwritten by derived classes
	*/
	function init()
	{
		$this->delete_enabled = true;
		$this->cut_enabled = true;
		$this->subscribe_enabled = true;
		$this->link_enabled = false;
		$this->payment_enabled = false;
		$this->type = "chat";
		$this->gui_class_name = "ilobjchatgui";

		// general commands array
		$this->commands = array
		(
			array("permission" => "read", "cmd" => "view", "lang_var" => "show"),
			array("permission" => "write", "cmd" => "edit", "lang_var" => "edit"),
		);
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
			case "edit":
			case "view":
				$cmd_link = "chat/chat_rep.php?ref_id=".$this->ref_id."&cmd=$a_cmd";
				break;

			default:
				$cmd_link = "repository.php?ref_id=".$this->ref_id."&cmd=$a_cmd";
				break;
		}

		return $cmd_link;
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

		return $props;
	}


} // END class.ilObjCategoryGUI
?>
