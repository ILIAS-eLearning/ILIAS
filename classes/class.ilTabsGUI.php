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
		
		$this->temp_var = "TABS";
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

	function addTarget($a_text, $a_link, $a_cmd = "", $a_cmdClass = "", $a_frame = "")
	{
		$a_cmdClass = strtolower($a_cmdClass);

		$this->target[] = array("text" => $a_text, "link" => $a_link,
			"cmd" => $a_cmd, "cmdClass" => $a_cmdClass, "frame" => $a_frame);
//echo "<br>addTarget:".$a_link."::";
	}

	function getHTML()
	{
		global $ilCtrl, $lng;

		$cmd = $ilCtrl->getCmd();
		$cmdClass = $ilCtrl->getCmdClass();

		$tpl = new ilTemplate("tpl.tabs.html", true, true);

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
	
//echo "<br>-".$target["cmd"]."-".$cmd."-";
				if (in_array($cmd, $target["cmd"]) &&
					($target["cmdClass"] == $cmdClass || $target["cmdClass"] == ""))
				{
					$tabtype = "tabactive";
				}
				else
				{
					$tabtype = "tabinactive";
				}
	
				$tpl->setCurrentBlock("tab");
				$tpl->setVariable("TAB_TYPE", $tabtype);
				$tpl->setVariable("TAB_LINK", $target["link"]);
				$tpl->setVariable("TAB_TEXT", $lng->txt($target["text"]));
				$tpl->setVariable("TAB_TARGET", $target["frame"]);
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
