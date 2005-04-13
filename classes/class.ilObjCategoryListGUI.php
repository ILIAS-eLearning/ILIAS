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
* Class ilObjCategoryListGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @extends ilObjectListGUI
*/

//
require_once "class.ilObjectCommands.php";

//require_once "class.ilObjectListGUI.php";
class ilObjCategoryListGUI
{
	var $ctrl;

	function ilObjCategoryListGUI($a_container_obj)
	{
		global $rbacsystem, $ilCtrl, $lng, $ilias;

		$this->rbacsystem = $rbacsystem;
		$this->ilias = $ilias;
		$this->ctrl = $ilCtrl;
		$this->lng = $lng;
		$this->container_obj = $a_container_obj;

		$this->init();
	}

	function init()
	{
		$this->cut_enabled = true;
		$this->subscribe_enabled = true;
		$this->type = "cat";
		$this->gui_class_name = "ilobjcategorygui";

		// general commands array
		$this->commands = array
		(
			array("permission" => "read", "cmd" => "view", "lang_var" => "show"),
			array("permission" => "write", "cmd" => "edit", "lang_var" => "edit"),
			array("permission" => "delete", "cmd" => "delete", "lang_var" => "delete")
		);
	}

	/**
	* get Commands for ref id
	*/
	function getCommands($a_ref_id)
	{

		$ref_commands = array();
		foreach($this->commands as $command)
		{
			$permission = $command["permission"];
			$cmd = $command["cmd"];
			$lang_var = $command["lang_var"];

			if ($this->rbacsystem->checkAccess($command["permission"], $a_ref_id))
			{
				// separate method for this line
				$cmd_link = $this->getCommandLink($a_ref_id, $command["cmd"]);

				$cmd_frame = "";				// todo;
				$access_granted = true;			// todo: check additional conditions
			}
			else
			{
				$access_granted = false;
				$info_object = "";				// todo: implement info object, why access is not granted
			}

			$ref_commands[] = array(
				"permission" => $permission,
				"cmd" => $cmd,
				"link" => $cmd_link,
				"frame" => $cmd_frame,
				"lang_var" => $lang_var,
				"granted" => $access_granted,
				"access_info" => $info_object
				);
		}

		return $ref_commands;
	}

	/**
	* overwrite this method, if link target is not build by ctrl class
	* (e.g. "lm_presentation.php", "forum.php")
	*
	* (clearify how to handle this in other scripts than repository)
	*/
	function getCommandLink($a_ref_id, $a_cmd)
	{
		// separate method for this line
		$cmd_link = $this->ctrl->getLinkTargetByClass($this->gui_class_name,
			$a_cmd);
	}

	function insertTitle(&$a_tpl, $a_title)
	{
		$a_tpl->setCurrentBlock("item_title");
		$a_tpl->setVariable("TXT_TITLE", $a_title);
		$a_tpl->parseCurrentBlock();
	}

	function insertDescription(&$a_tpl, $a_desc)
	{
		$a_tpl->setCurrentBlock("item_description");
		$a_tpl->setVariable("TXT_DESC", $a_desc);
		$a_tpl->parseCurrentBlock();
	}

	function insertCommand(&$a_tpl, $a_href, $a_text, $a_frame = "")
	{
		if ($a_frame != "")
		{
			$a_tpl->setCurrentBlock("item_frame");
			$a_tpl->setVariable("TARGET_COMMAND", $a_frame);
			$a_tpl->parseCurrentBlock();
		}

		$a_tpl->setCurrentBlock("item_command");
		$a_tpl->setVariable("HREF_COMMAND", $a_href);
		$a_tpl->setVariable("TXT_COMMAND", $a_text);
		$a_tpl->parseCurrentBlock();
	}

	function insertCutCommand(&$a_tpl, $a_ref_id)
	{
		if ($this->rbacsystem->checkAccess("delete", $a_ref_id))
		{
			$ilCtrl->setParameter($this->container_obj, "item_ref_id", $a_ref_id);
			$cmd_link = $ilCtrl->getLinkTarget($this->container_obj, "cut");
			$this->insertCommand($a_tpl, $cmd_link, $this->lng->txt("move"));
		}
	}

	function insertSubscribeCommand(&$a_tpl, $a_ref_id)
	{
		if ($this->ilias->account->getId() != ANONYMOUS_USER_ID &&
			!$this->ilias->account->isDesktopItem($a_ref_id, $this->type))
		{
			if ($this->rbacsystem->checkAccess("read", $a_ref_id))
			{
				$cmd_link = $ilCtrl->getLinkTargetByClass($this->gui_class_name, "subscribe");
				$this->insertCommand($tpl, $cmd_link, $this->lng->txt("subscribe"));
			}
		}
	}

	/**
	* insert all commands into html code
	*/
	function insertCommands(&$a_tpl, $a_ref_id)
	{
		$this->ctrl->setParameterByClass($this->gui_class_name, "ref_id", $a_ref_id);

		foreach($this->commands as $command)
		{
			if ($this->rbacsystem->checkAccess($command["permission"], $a_ref_id))
			{
				$cmd_link = $this->ctrl->getLinkTargetByClass($this->gui_class_name,
					$command["cmd"]);
				$this->insertCommand($a_tpl, $cmd_link, $this->lng->txt($command["lang_var"]));
			}
		}

		// cut
		if ($this->cut_enabled)
		{
			$this->insertCutCommand($a_tpl, $a_ref_id);
		}

		// subscribe
		if ($this->subscribe_enabled)
		{
			$this->insertSubscribeCommand($a_tpl, $a_ref_id);
		}
	}

	/**
	* Get all item information (title, commands, description) in HTML
	*/
	function getListItemHTML($a_ref_id, $a_obj_id, $a_title, $a_description)
	{
		$tpl =& new ilTemplate ("tpl.container_list_item.html", true, true);

		$this->insertTitle($tpl, $a_title);
		$this->insertDescription($tpl, $a_description);
		$this->insertCommands($tpl, $a_ref_id, $a_obj_id);

		return $tpl->get();
	}

} // END class.ilObjCategoryGUI
?>
