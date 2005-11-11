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
* Tabs GUI
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package core
*/
class ilTabsGUI
{
	var $target_script;
	var $obj_type;
	var $tpl;
	var $lng;
	var $tabs;
	var $objDefinition;
	var $target = array();

	/**
	* Constructor
	* @access	public
	*/
	function ilTabsGUI()
	{
		global $tpl, $objDefinition, $lng;

		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->objDefinition =& $objDefinition;
		$this->manual_activation = false;
		$this->temp_var = "TABS";
		$this->sub_tabs = false;
	}

	function getTargetsByObjectType(&$a_gui_obj, $a_type)
	{
		global $ilCtrl;

		$d = $this->objDefinition->getProperties($a_type);

		foreach ($d as $key => $row)
		{
			$this->addTarget($row["lng"],
				$ilCtrl->getLinkTarget($a_gui_obj, $row["name"]),
				$row["name"], get_class($a_gui_obj));
		}
	}
	
	/**
	* set sub tab outfit
	*/
	function setSubTabs($a_set = true)
	{
		$this->sub_tabs = $a_set;
	}

	/**
	* Add a target to the tabbed menu. If no target has set $a_activate to
	* true, ILIAS tries to determine the current activated menu item
	* automatically using $a_cmd and $a_cmdClass. If one item is set
	* activated (and only one should be activated) the automatism is disabled.
	*
	* @param	string		$a_text			menu item text
	* @param	string		$a_link			menu item link
	* @param	string		$a_cmd			command, used for auto acctivation
	* @param	string		$a_cmdClass		used for auto acctivation. String or array of cmd classes
	* @param	string		$a_frame		frame target
	* @param	boolean		$a_activate		avticate this menu item
	*/
	function addTarget($a_text, $a_link, $a_cmd = "", $a_cmdClass = "", $a_frame = "", $a_activate = false)
	{
		
		if(!$a_cmdClass)
		{
			$a_cmdClass = array();
		}
		$a_cmdClass = !is_array($a_cmdClass) ? array(strtolower($a_cmdClass)) : $a_cmdClass;
		#$a_cmdClass = strtolower($a_cmdClass);

		if ($a_activate)
		{
			$this->manual_activation = true;
		}
		$this->target[] = array("text" => $a_text, "link" => $a_link,
			"cmd" => $a_cmd, "cmdClass" => $a_cmdClass, "frame" => $a_frame,
			"activate" => $a_activate);
	}

	/**
	* Activate a specific tab identified by name
	* This method overrides the definition in YOUR_OBJECT::getTabs() and deactivates all other tabs.
	*
	* @param	string		$a_text			menu item text
	* @param	boolean		
	*/
	function activate($a_text)
	{
		for($i = 0; $i < count($this->target);$i++)
		{
			$this->target[$i]['activate'] = $this->target[$i]['text'] == $a_text;
		}
		return true;
	}


	/**
	* get tabs code as html
	*/
	function getHTML()
	{
		global $ilCtrl, $lng;

		$cmd = $ilCtrl->getCmd();
		$cmdClass = $ilCtrl->getCmdClass();

		if ($this->sub_tabs)
		{
			$tpl = new ilTemplate("tpl.sub_tabs.html", true, true);
			$pre = "sub";
			$pre2 = "SUB_";
		}
		else
		{
			$tpl = new ilTemplate("tpl.tabs.html", true, true);
			$pre = $pre2 = "";
		}

		// do not display one tab only
		if (count($this->target) > 1)
		{
			foreach ($this->target as $target)
			{
				$i++;
				
				if (!is_array($target["cmd"]))
				{
					$target["cmd"] = array($target["cmd"]);
				}

				if (!$this->manual_activation &&
					(in_array($cmd, $target["cmd"]) || ($target["cmd"][0] == "" && count($target["cmd"]) == 1)) &&
					(in_array($cmdClass,$target["cmdClass"]) || !$target["cmdClass"]))
				{
					$tabtype = $pre."tabactive";
				}
				else
				{
					$tabtype = $pre."tabinactive";
				}
				
				if ($this->manual_activation && $target["activate"])
				{
					$tabtype = $pre."tabactive";
				}
	
				$tpl->setCurrentBlock($pre."tab");
				$tpl->setVariable($pre2."TAB_TYPE", $tabtype);
				$tpl->setVariable($pre2."TAB_LINK", $target["link"]);
				$tpl->setVariable($pre2."TAB_TEXT", $lng->txt($target["text"]));
				$tpl->setVariable($pre2."TAB_TARGET", $target["frame"]);
				$tpl->parseCurrentBlock();
			}
			return $tpl->get();
		}
		else
		{
			return "";
		}
	}
}
?>
