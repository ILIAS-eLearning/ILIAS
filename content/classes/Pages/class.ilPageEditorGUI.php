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

//require_once ("classes/class.ilDOMUtil.php");
require_once ("content/classes/Pages/class.ilPageObjectGUI.php");


/**
* Page Editor GUI class
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
*/
class ilPageEditorGUI
{
	/**
	* ilias object
	* @var object ilias
	* @access public
	*/
	var $ilias;
	var $tpl;
	var $lng;
	var $objDefinition;
	var $page;
	var $target_script;
	var $return_location;
	var $header;
	var $tabs;
	var $cont_obj;

	/**
	* Constructor
	* @access	public
	*/
	function ilPageEditorGUI(&$a_page_object)
	{
		global $ilias, $tpl, $lng, $objDefinition;

		// initiate variables
		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->objDefinition = $objDefinition;

		$this->page =& $a_page_object;
	}

	function setTargetScript($a_target_script)
	{
		$this->target_script = $a_target_script;
	}

	function getTargetScript()
	{
		return $this->target_script;
	}

	function setHeader($a_header)
	{
		$this->header = $a_header;
	}

	function getHeader()
	{
		return $this->header;
	}

	function setReturnLocation($a_location)
	{
		$this->return_location = $a_location;
	}

	function getReturnLocation()
	{
		return $this->return_location;
	}

	function setLocator(&$a_locator)
	{
		$this->locator =& $a_locator;
	}

	function setTabs($a_tabs)
	{
		$this->tabs = $a_tabs;
	}

	function returnToContext()
	{
		ilUtil::redirect($this->getReturnLocation());
	}

	function executeCommand()
	{
//echo "execute";
		if (empty($_GET["cmd"]) && !is_array($_POST["cmd"]))
		{
			return;
		}

		$cmd = (empty($_GET["cmd"]))
			? $cmd = key($_POST["cmd"])
			: $_GET["cmd"];

		$hier_id = $_GET["hier_id"];
		if(isset($_POST["new_hier_id"]))
		{
			$hier_id = $_POST["new_hier_id"];
		}
//echo "GEThier_id:".$_GET["hier_id"]."<br>";
//echo "hier_id:".$hier_id."<br>";

		$new_type = (isset($_GET["new_type"]))
			? $_GET["new_type"]
			: $_POST["new_type"];


		if ($cmd == "edpost" || $_GET["hier_id"])
		{
			$type = "content";
			if (isset($_GET["hier_id"]))
			{
				if($cmd == "edpost")
				{
					$cmd = key($_POST["cmd"]);
					$hier_id = $_GET["hier_id"];
				}
			}
			else
			{
				$cmd = explode("_", key($_POST["cmd"]));
				unset($cmd[0]);
				$hier_id = implode($cmd, "_");
				$cmd = $_POST["command".$hier_id];
			}
		}

		if ($cmd == "post")
		{
			$cmd = key($_POST["cmd"]);
		}

		$this->page->buildDom();
		$this->page->addHierIDs();

		// determine command and content object
		$com = explode("_", $cmd);
		$cmd = $com[0];

//echo "type:$type:cmd:$cmd:";

		// determine content type
		if ($cmd == "insert" || $cmd == "create")
		{
			$ctype = $com[1];
		}
		else
		{
			$cont_obj =& $this->page->getContentObject($hier_id);
			$ctype = $cont_obj->getType();
		}

		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

		if ($ctype != "mob" || !is_object ($cont_obj))
		{
			$this->tpl->setVariable("HEADER", $this->getHeader());
			$this->displayLocator();
			$this->setAdminTabs("pg");
		}

		if($cmd == "returnToContext")
		{
			$this->returnToContext();
		}

//echo "2"; exit;
//echo "type:$type:cmd:$cmd:ctype:$ctype:<br>";
		$this->cont_obj =& $cont_obj;

		switch($ctype)
		{
			// Paragraph
			case "par":
				require_once ("content/classes/Pages/class.ilPCParagraphGUI.php");
				$par_gui =& new ilPCParagraphGUI($this->page, $cont_obj, $hier_id);
				$par_gui->setTargetScript($this->getTargetScript());
				$par_gui->setReturnLocation($this->getReturnLocation());
				$par_gui->$cmd();
				break;

			// Table
			case "tab":
				require_once ("content/classes/Pages/class.ilPCTableGUI.php");
				$tab_gui =& new ilPCTableGUI($this->page, $cont_obj, $hier_id);
				$tab_gui->setTargetScript($this->getTargetScript());
				$tab_gui->setReturnLocation($this->getReturnLocation());
				$tab_gui->$cmd();
				break;

			// Table Cell
			case "td":
				require_once ("content/classes/Pages/class.ilPCTableDataGUI.php");
				$td_gui =& new ilPCTableDataGUI($this->page, $cont_obj, $hier_id);
				$td_gui->setTargetScript($this->getTargetScript());
				$td_gui->setReturnLocation($this->getReturnLocation());
				$td_gui->$cmd();
				break;

			// Media Object
			case "mob":
				if (is_object ($cont_obj))
				{
					$this->tpl->setVariable("HEADER", $this->lng->txt("mob").": ".
						$cont_obj->getTitle());
					$this->displayLocator("mob");
					$this->setAdminTabs("mob", $hier_id);
				}

				require_once ("content/classes/Pages/class.ilPCMediaObjectGUI.php");
				$mob_gui =& new ilPCMediaObjectGUI($this->page, $cont_obj, $hier_id);
				$mob_gui->setTargetScript($this->getTargetScript());
				$mob_gui->setReturnLocation($this->getReturnLocation());
				$mob_gui->$cmd();
				break;

			// List
			case "list":
				require_once ("content/classes/Pages/class.ilPCListGUI.php");
				$list_gui =& new ilPCListGUI($this->page, $cont_obj, $hier_id);
				$list_gui->setTargetScript($this->getTargetScript());
				$list_gui->setReturnLocation($this->getReturnLocation());
				$list_gui->$cmd();
				break;

			// List Item
			case "li":
				require_once ("content/classes/Pages/class.ilPCListItemGUI.php");
				$list_item_gui =& new ilPCListItemGUI($this->page, $cont_obj, $hier_id);
				$list_item_gui->setTargetScript($this->getTargetScript());
				$list_item_gui->setReturnLocation($this->getReturnLocation());
				$list_item_gui->$cmd();
				break;

			// File List
			case "flst":
				require_once ("content/classes/Pages/class.ilPCFileListGUI.php");
				$file_list_gui =& new ilPCFileListGUI($this->page, $cont_obj, $hier_id);
				$file_list_gui->setTargetScript($this->getTargetScript());
				$file_list_gui->setReturnLocation($this->getReturnLocation());
				$file_list_gui->$cmd();
				break;

			// File List Item
			case "flit":
				require_once ("content/classes/Pages/class.ilPCFileItemGUI.php");
				$file_item_gui =& new ilPCFileItemGUI($this->page, $cont_obj, $hier_id);
				$file_item_gui->setTargetScript($this->getTargetScript());
				$file_item_gui->setReturnLocation($this->getReturnLocation());
				$file_item_gui->$cmd();
				break;

		}

		//$this->tpl->show();

	}

	function displayLocator()
	{
		/*
		require_once("content/classes/class.ilContObjLocatorGUI.php");
		$contObjLocator =& new ilContObjLocatorGUI($this->tree);
		$contObjLocator->setObject($this->page);*/

		//$cont_obj = ilObjContentObject$this->page->getParentId()
		//$contObjLocator->setContentObject($this->page->getParentId());
		if(is_object($this->locator))
		{
			$this->locator->display();
		}
	}

	/**
	* output main header (title and locator)
	*/
	/*
	function main_header($a_header_title, $a_type)
	{
		global $lng;

		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$this->tpl->setVariable("HEADER", $a_header_title);
		$this->displayLocator();
		$this->setAdminTabs($a_type);
	}*/

	function setAdminTabs($mode = "pg", $a_hier_id = "")
	{
		include_once("classes/class.ilTabsGUI.php");

		$tabs_gui =& new ilTabsGUI;

		/*$tabs_gui->setTargetScript("lm_edit.php?ref_id=".$_GET["ref_id"]."&obj_id=".
			$_GET["obj_id"]);*/
		$tabs_gui->setTargetScript($this->getTargetScript());

		if ($mode != "mob")
		{
			if(empty($this->tabs))
			{
				$tabs_gui->setObjectType($mode);
			}
			else
			{
				// glossary fix
				$tabs_gui->setTargetScript($this->getTargetScript());
				$tabs_gui->setTabs($this->tabs);
				$tabs_gui->setTabs(array());
			}
		}
		else
		{
			$tabs_gui->setTargetScript(
				ilUtil::appendUrlParameterString($tabs_gui->getTargetScript(),
				"hier_id=".$a_hier_id));
			$tabs[] = array("cont_mob_inst_prop", "editAlias");
			$tabs[] = array("cont_mob_prop", "edit");
			$tabs[] = array("cont_mob_files", "editFiles");
			$tabs[] = array("cont_mob_usages", "showUsages");
			if (is_object($this->cont_obj))
			{
				$st_item =& $this->cont_obj->getMediaItem("Standard");
				if (is_object($st_item))
				{
					$format = $st_item->getFormat();
					if (substr($format, 0, 5) == "image")
					{
						$tabs[] = array("cont_map_areas", "editMapAreas");
					}
				}
			}
			$tabs[] = array("cont_back", "returnToContext");
			/*
			$tabs_gui->setTabs(array(array("cont_mob_inst_prop", "editAlias"),
				array("cont_mob_prop", "edit"),
				array("cont_mob_files", "editFiles"),
				array("cont_mob_usages", "showUsages"),
				array("cont_back", "returnToContext")
				));*/
			$tabs_gui->setTabs($tabs);
		}
		$tabs_gui->display();
	}

}
?>
