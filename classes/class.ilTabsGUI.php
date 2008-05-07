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
* Tabs GUI
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
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
	var $sub_target = array();

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
		$this->subtab_manual_activation = false;
		$this->temp_var = "TABS";
		$this->sub_tabs = false;
		$this->back_title = "";
		$this->back_target = "";
		$this->back_2_target = "";
		$this->back_2_title = "";
	}
	
	/**
	* back target for upper context
	*/
	function setBackTarget($a_title, $a_target, $a_frame = "")
	{
		$this->back_title = $a_title;
		$this->back_target = $a_target;
		$this->back_frame = $a_frame;
	}

	/**
	* back target for tow level upper context
	*/
	function setBack2Target($a_title, $a_target, $a_frame = "")
	{
		$this->back_2_title = $a_title;
		$this->back_2_target = $a_target;
		$this->back_2_frame = $a_frame;
	}
	
/*	Deprecated
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
*/

	/**
	* Add a target to the tabbed menu. If no target has set $a_activate to
	* true, ILIAS tries to determine the current activated menu item
	* automatically using $a_cmd and $a_cmdClass. If one item is set
	* activated (and only one should be activated) the automatism is disabled.
	*
	* @param	string		$a_text			menu item text
	* @param	string		$a_link			menu item link
	* @param	string		$a_cmd			command, used for auto activation
	* @param	string		$a_cmdClass		used for auto activation. String or array of cmd classes
	* @param	string		$a_frame		frame target
	* @param	boolean		$a_activate		activate this menu item
	*/
	function addTarget($a_text, $a_link, $a_cmd = "", $a_cmdClass = "", $a_frame = "", $a_activate = false,
		$a_dir_text = false)
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
			"activate" => $a_activate, "dir_text" => $a_dir_text);
	}
	
	/**
	* clear all targets
	*/
	function clearTargets()
	{
		$this->target = array();
		$this->back_title = "";
		$this->back_target = "";
		$this->back_2_target = "";
		$this->back_2_title = "";
		$this->setTabActive("");
		$this->setSubTabActive("");
	}

	/**
	* Add a Subtarget to the tabbed menu. If no target has set $a_activate to
	* true, ILIAS tries to determine the current activated menu item
	* automatically using $a_cmd and $a_cmdClass. If one item is set
	* activated (and only one should be activated) the automatism is disabled.
	*
	* @param	string		$a_text			menu item text
	* @param	string		$a_link			menu item link
	* @param	string		$a_cmd			command, used for auto activation
	* @param	string		$a_cmdClass		used for auto activation. String or array of cmd classes
	* @param	string		$a_frame		frame target
	* @param	boolean		$a_activate		activate this menu item
	* @param	boolean		$a_dir_text		text is direct text, no language variable
	*/
	function addSubTabTarget($a_text, $a_link, $a_cmd = "", $a_cmdClass = "", $a_frame = "", $a_activate = false,
		$a_dir_text = false)
	{
		
		if(!$a_cmdClass)
		{
			$a_cmdClass = array();
		}
		$a_cmdClass = !is_array($a_cmdClass) ? array(strtolower($a_cmdClass)) : $a_cmdClass;
		#$a_cmdClass = strtolower($a_cmdClass);

		if ($a_activate)
		{
			$this->subtab_manual_activation = true;
		}
		$this->sub_target[] = array("text" => $a_text, "link" => $a_link,
			"cmd" => $a_cmd, "cmdClass" => $a_cmdClass, "frame" => $a_frame,
			"activate" => $a_activate, "dir_text" => $a_dir_text);
	}

	/**
	* Activate a specific tab identified by name
	* This method overrides the definition in YOUR_OBJECT::getTabs() and deactivates all other tabs.
	*
	* @param	string		$a_text			menu item text
	* @param	boolean		
	*/
	function setTabActive($a_text)
	{
		for($i = 0; $i < count($this->target);$i++)
		{
			$this->target[$i]['activate'] = $this->target[$i]['text'] == $a_text;
		}
		if ($a_text != "")
		{
			$this->manual_activation = true;
		}
		else
		{
			$this->manual_activation = false;
		}
		return true;
	}

	/**
	* Activate a specific tab identified by name
	* This method overrides the definition in YOUR_OBJECT::getTabs() and deactivates all other tabs.
	*
	* @param	string		$a_text			menu item text
	* @param	boolean		
	*/
	function setSubTabActive($a_text)
	{
		for($i = 0; $i < count($this->sub_target);$i++)
		{
			$this->sub_target[$i]['activate'] = $this->sub_target[$i]['text'] == $a_text;
		}
		$this->subtab_manual_activation = true;
		return true;
	}

	/**
	* Clear all already added sub tabs
	*
	* @param	boolean		
	*/
	function clearSubTabs()
	{
		$this->sub_target = array();
		return true;
	}

	/**
	* get tabs code as html
	*/
	function getHTML()
	{
		return $this->__getHTML(false,$this->manual_activation);
	}
	
	/**
	* get sub tabs code as html
	*/
	function getSubTabHTML()
	{
		return $this->__getHTML(true,$this->subtab_manual_activation);
	}



	/**
	* get tabs code as html
	* @param bool choose tabs or sub tabs
	* @param bool manual activation
	* @access Private
	*/
	function __getHTML($a_get_sub_tabs,$a_manual)
	{
		global $ilCtrl, $lng;

		$cmd = $ilCtrl->getCmd();
		$cmdClass = $ilCtrl->getCmdClass();

		if ($a_get_sub_tabs)
		{
			$tpl = new ilTemplate("tpl.sub_tabs.html", true, true);
			$pre = "sub";
			$pre2 = "SUB_";
		}
		else
		{
			$tpl = new ilTemplate("tpl.tabs.html", true, true);
			$pre = $pre2 = "";
			
			// back 2 tab
			if ($this->back_2_title != "")
			{
				$tpl->setCurrentBlock("back_2_tab");
				$tpl->setVariable("BACK_2_TAB_LINK", $this->back_2_target);
				$tpl->setVariable("BACK_2_TAB_TEXT", $this->back_2_title);
				$tpl->setVariable("BACK_2_TAB_TARGET", $this->back_2_frame);
				$tpl->parseCurrentBlock();
			}
			
			// back tab
			if ($this->back_title != "")
			{
				$tpl->setCurrentBlock("back_tab");
				$tpl->setVariable("BACK_TAB_LINK", $this->back_target);
				$tpl->setVariable("BACK_TAB_TEXT", $this->back_title);
				$tpl->setVariable("BACK_TAB_TARGET", $this->back_frame);
				$tpl->parseCurrentBlock();
			}
		}
		
		$targets = $a_get_sub_tabs ? $this->sub_target : $this->target;
		// display tabs if there is at least one
		if ((count($targets) > 0) || $this->back_title != "")
		{
			foreach ($targets as $target)
			{
				$i++;
				
				if (!is_array($target["cmd"]))
				{
					$target["cmd"] = array($target["cmd"]);
				}
//echo "<br>-$a_manual-$cmd-".$target["cmd"]."-";
				if (!$a_manual &&
					(in_array($cmd, $target["cmd"]) || ($target["cmd"][0] == "" && count($target["cmd"]) == 1)) &&
					(in_array($cmdClass,$target["cmdClass"]) || !$target["cmdClass"]))
				{
					$tabtype = $pre."tabactive";
				}
				else
				{
					$tabtype = $pre."tabinactive";
				}
				
				if ($a_manual && $target["activate"])
				{
					$tabtype = $pre."tabactive";
				}
	
				$tpl->setCurrentBlock($pre."tab");
				$tpl->setVariable($pre2."TAB_TYPE", $tabtype);
				$tpl->setVariable($pre2."TAB_LINK", $target["link"]);
				if ($target["dir_text"])
				{
					$tpl->setVariable($pre2."TAB_TEXT", $target["text"]);
				}
				else
				{
					$tpl->setVariable($pre2."TAB_TEXT", $lng->txt($target["text"]));
				}
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
