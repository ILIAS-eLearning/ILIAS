<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


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
	

	/**
	* DEPRECATED.
	*
	* Use addTab/addSubTab and activateTab/activateSubTab.
	*
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
			"activate" => $a_activate, "dir_text" => $a_dir_text, "id" => $a_text);
	}
	
	/**
	* Add a Tab
	*
	* @param	string		id
	* @param	string		text (no lang var!)
	* @param	string		link
	* @param	string		frame target
	*/
	function addTab($a_id, $a_text, $a_link, $a_frame = "")
	{
		$this->target[] = array("text" => $a_text, "link" => $a_link,
			"frame" => $a_frame, "dir_text" => true, "id" => $a_id, "cmdClass" => array());
	}
	
	/**
	 * Remove a tab identified by its id.
	 *
	 * @param 	string	$a_id	Id of tab to remove
	 * @return bool	false if tab wasn't found
	 * @access public
	 */
	public function removeTab($a_id)
	{
		for($i = 0; $i < count($this->target); $i++)
		{
			if($this->target[$i]['id'] == $a_id)
			{
				$this->target = array_slice($this->target, $i - 1, 1);
				return true;
			}
		}
		return false;
	}
	
	/**
	 * Replace a tab.
	 * In contrast to a combination of removeTab and addTab, the position is kept. 
	 * 
	 * @param string $a_old_id				old id of tab
	 * @param string $a_new_id				new id if tab
	 * @param string $a_text				tab text
	 * @param string $a_link				tab link
	 * @param string $a_frame[optional]		frame
	 * @return bool
	 */
	public function replaceTab($a_old_id,$a_new_id,$a_text,$a_link,$a_frame = '')
	{
		for($i = 0; $i < count($this->target); $i++)
		{
			if($this->target[$i]['id'] == $a_old_id)
			{
				$this->target[$i] = array();
				$this->target[$i] = array(
					"text" => $a_text, 
					"link" => $a_link,
					"frame" => $a_frame, 
					"dir_text" => true, 
					"id" => $a_new_id, 
					"cmdClass" => array());
				return true;
			}
		}
		return false;
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
	* DEPRECATED.
	*
	* Use addTab/addSubTab and activateTab/activateSubTab.
	*
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
			"activate" => $a_activate, "dir_text" => $a_dir_text, "id" => $a_text);
	}

	/**
	* Add a Subtab
	*
	* @param	string		id
	* @param	string		text (no lang var!)
	* @param	string		link
	* @param	string		frame target
	*/
	function addSubTab($a_id, $a_text, $a_link, $a_frame = "")
	{
		$this->sub_target[] = array("text" => $a_text, "link" => $a_link,
			"frame" => $a_frame, "dir_text" => true, "id" => $a_id, "cmdClass" => array());
	}

	/**
	* DEPRECATED.
	*
	* Use addTab/addSubTab and activateTab/activateSubTab.
	*
	* Activate a specific tab identified by name
	* This method overrides the definition in YOUR_OBJECT::getTabs() and deactivates all other tabs.
	*
	* @param	string		$a_text			menu item text		
	*/
	function setTabActive($a_id)
	{
		for($i = 0; $i < count($this->target);$i++)
		{
			$this->target[$i]['activate'] = $this->target[$i]['id'] == $a_id;
		}
		if ($a_id != "")
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
	* Activate a specific tab identified its id
	*
	* @param	string		$a_text			menu item text		
	*/
	function activateTab($a_id)
	{
		$this->setTabActive($a_id);
	}

	/**
	* DEPRECATED.
	*
	* Use addTab/addSubTab and activateTab/activateSubTab.
	*
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
			$this->sub_target[$i]['activate'] = $this->sub_target[$i]['id'] == $a_text;
		}
		$this->subtab_manual_activation = true;
		return true;
	}

	/**
	* Activate a specific subtab identified its id
	*
	* @param	string		$a_text			menu item text		
	*/
	function activateSubTab($a_id)
	{
		$this->setSubTabActive($a_id);
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
	function getHTML($a_after_tabs_anchor = false)
	{
		return $this->__getHTML(false,$this->manual_activation, $a_after_tabs_anchor);
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
	function __getHTML($a_get_sub_tabs,$a_manual, $a_after_tabs_anchor = false)
	{
		global $ilCtrl, $lng, $ilUser;

		$cmd = $ilCtrl->getCmd();
		$cmdClass = $ilCtrl->getCmdClass();

		if ($a_get_sub_tabs)
		{
			$tpl = new ilTemplate("tpl.sub_tabs.html", true, true);
			$pre = "sub";
			$pre2 = "SUB_";
			$sr_pre = "sub_";
		}
		else
		{
			$tpl = new ilTemplate("tpl.tabs.html", true, true);
			if ($a_after_tabs_anchor)
			{
				$tpl->touchBlock("after_tabs");
			}
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

        // do not display one tab only
        if ((count($targets) > 1) || ($this->back_title != "" && !$a_get_sub_tabs))
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
				
				if ($tabtype == "tabactive" || $tabtype == "subtabactive")
				{
					$tpl->setCurrentBlock("sel_text");
					$tpl->setVariable("TXT_SELECTED", $lng->txt("stat_selected"));
					$tpl->parseCurrentBlock();
				}
	
				$tpl->setCurrentBlock($pre."tab");
				$tpl->setVariable($pre2."TAB_TYPE", $tabtype);
				$hash = ($ilUser->prefs["screen_reader_optimization"])
					? "#after_".$sr_pre."tabs"
					: "";
				
				$tpl->setVariable($pre2."TAB_LINK", $target["link"].$hash);
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
			
			if ($a_get_sub_tabs)
			{
				$tpl->setVariable("TXT_SUBTABS", $lng->txt("subtabs"));
			}
			else
			{
				$tpl->setVariable("TXT_TABS", $lng->txt("tabs"));
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
