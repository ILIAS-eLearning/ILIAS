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

	/*
	function setTemplateVariable($a_temp_var)
	{
		$this->temp_var = $a_temp_var;
	}

	function setObjectType($a_type)
	{
		$this->obj_type = $a_type;
	}

	function setTargetScript($a_script)
	{
		$this->target_script = $a_script;
	}

	function getTargetScript()
	{
		return $this->target_script;
	}

	function setTabs($a_tabs)
	{
		$this->tabs = $a_tabs;
	}*/


	/*
	function display()
	{

		$tabs = array();
		$this->tpl->addBlockFile($this->temp_var, "tabs", "tpl.tabs.html");

		if(!is_array($this->tabs))
		{
			$d = $this->objDefinition->getProperties($this->obj_type);

			foreach ($d as $key => $row)
			{
				$tabs[] = array($row["lng"], $row["name"]);
			}
		}
		else
		{
			$tabs = $this->tabs;
		}

		if ($_GET["cmd"] == "")
		{
			if (is_array($_POST["cmd"]))
			{
				$cmd = key($_POST["cmd"]);
			}
		}
		else if ($_GET["cmd"] == "edpost")
		{
			$cmd_arr = explode("_", key($_POST["cmd"]));
			$cmd = $_POST["command".$cmd_arr[1]];
		}
		else
		{
			$cmd = $_GET["cmd"];
		}

		foreach ($tabs as $row)
		{
			$i++;
			if ($row[1] == $cmd)
			{
				$tabtype = "tabactive";
				$tab = $tabtype;
			}
			else
			{
				$tabtype = "tabinactive";
				$tab = "tab";
			}

			$this->tpl->setCurrentBlock("tab");
			$this->tpl->setVariable("TAB_TYPE", $tabtype);
			$this->tpl->setVariable("TAB_TYPE2", $tab);
			$this->tpl->setVariable("IMG_LEFT", ilUtil::getImagePath("eck_l.gif"));
			$this->tpl->setVariable("IMG_RIGHT", ilUtil::getImagePath("eck_r.gif"));
			$this->tpl->setVariable("TAB_LINK",
				ilUtil::appendUrlParameterString($this->target_script, "cmd=".$row[1]));
			$this->tpl->setVariable("TAB_TEXT", $this->lng->txt($row[0]));
			$this->tpl->parseCurrentBlock();
		}
	}*/

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
			"cmd" => $a_cmd, "cmdClass" => $a_cmdClass);
//echo "<br>addTarget:".$a_link."::";
	}

	function getHTML()
	{
		global $ilCtrl, $lng;

		$cmd = $ilCtrl->getCmd();
		$cmdClass = $ilCtrl->getCmdClass();

		$tpl = new ilTemplate("tpl.tabs.html", true, true);
		
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




}
?>
