<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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

include_once ("./Modules/LearningModule/classes/class.ilLMObjectFactory.php");
include_once ("./Services/Utilities/classes/class.ilDOMUtil.php");
include_once ("./Services/COPage/classes/class.ilPageEditorGUI.php");
include_once ("./Services/Style/classes/class.ilObjStyleSheet.php");
include_once ("./Modules/LearningModule/classes/class.ilEditClipboard.php");


/**
* GUI class for learning module editor
*
* @author Alex Killing <alex.killing@gmx.de>
*
* @version $Id$
*
* @ilCtrl_Calls ilLMEditorGUI: ilObjDlBookGUI, ilObjLearningModuleGUI
*
* @ingroup ModulesIliasLearningModule
*/
class ilLMEditorGUI
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
	var $ref_id;
	var $lm_obj;

	var $tree;
	var $obj_id;

	/**
	* Constructor
	* @access	public
	*/
	function ilLMEditorGUI()
	{
		global $ilias, $tpl, $lng, $objDefinition, $ilCtrl,
			$rbacsystem, $ilNavigationHistory;
		
		// init module (could be done in ilctrl)
		//define("ILIAS_MODULE", "content");
		$lng->loadLanguageModule("content");

		// check write permission
		if (!$rbacsystem->checkAccess("write", $_GET["ref_id"]))
		{
			$ilias->raiseError($lng->txt("permission_denied"),$ilias->error_obj->MESSAGE);
		}


		$this->ctrl =& $ilCtrl;

		//$this->ctrl->saveParameter($this, array("ref_id", "obj_id"));
		$this->ctrl->saveParameter($this, array("ref_id"));

		// initiate variables
		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->objDefinition =& $objDefinition;
		$this->ref_id = $_GET["ref_id"];
		$this->obj_id = $_GET["obj_id"];

		$this->lm_obj =& $this->ilias->obj_factory->getInstanceByRefId($this->ref_id);
		$this->tree = new ilTree($this->lm_obj->getId());
		$this->tree->setTableNames('lm_tree','lm_data');
		$this->tree->setTreeTablePK("lm_id");
		
		$ilNavigationHistory->addItem($_GET["ref_id"],
			"ilias.php?baseClass=ilLMEditorGUI&ref_id=".$_GET["ref_id"], "lm");

	}

	/**
	* execute command
	*/
	function &executeCommand()
	{

		global $ilHelp;
		
		$ilHelp->setTarget("lm_intro");
		
		$cmd = $this->ctrl->getCmd("frameset");

		$next_class = $this->ctrl->getNextClass($this);
//echo "lmeditorgui:$next_class:".$this->ctrl->getCmdClass().":$cmd:<br>";
		$cmd = $this->ctrl->getCmd("frameset");

		if ($next_class == "" && ($cmd != "explorer") && ($cmd != "frameset")
			&& ($cmd != "showImageMap"))
		{
			switch($this->lm_obj->getType())
			{
				case "lm":
					//$this->ctrl->setCmdClass("ilObjLearningModuleGUI");
					$next_class = "ilobjlearningmodulegui";
					break;

				case "dbk":
					//$this->ctrl->setCmdClass("ilObjDlBookGUI");
					$next_class = "ilobjdlbookgui";
					break;
			}
			//$next_class = $this->ctrl->getNextClass($this);
		}

		// show footer
		$show_footer = ($cmd == "explorer")
			? false
			: true;
			
// if ($this->lm_obj->getType()
		switch($next_class)
		{
			case "ilobjdlbookgui":
				include_once ("./Modules/LearningModule/classes/class.ilObjDlBook.php");
				include_once ("./Modules/LearningModule/classes/class.ilObjDlBookGUI.php");

				$this->main_header($this->lm_obj->getType());
				$book_gui =& new ilObjDlBookGUI("", $_GET["ref_id"], true, false);
				//$ret =& $book_gui->executeCommand();
				$ret =& $this->ctrl->forwardCommand($book_gui);
				if (strcmp($cmd, "explorer") != 0)
				{
					// don't call the locator in the explorer frame
					// this prevents a lot of log errors
					// Helmut Schottmüller, 2006-07-21
					$this->displayLocator();
				}

				// (horrible) workaround for preventing template engine
				// from hiding paragraph text that is enclosed
				// in curly brackets (e.g. "{a}", see ilPageObjectGUI::showPage())
				$this->tpl->fillTabs();
				$output =  $this->tpl->get("DEFAULT", true, true, $show_footer,true);
				$output = str_replace("&#123;", "{", $output);
				$output = str_replace("&#125;", "}", $output);
				header('Content-type: text/html; charset=UTF-8');
				echo $output;
				break;

			case "ilobjlearningmodulegui":
				include_once ("./Modules/LearningModule/classes/class.ilObjLearningModule.php");
				include_once ("./Modules/LearningModule/classes/class.ilObjLearningModuleGUI.php");
				$this->main_header($this->lm_obj->getType());
				$lm_gui =& new ilObjLearningModuleGUI("", $_GET["ref_id"], true, false);
				//$ret =& $lm_gui->executeCommand();
				$ret =& $this->ctrl->forwardCommand($lm_gui);
				if (strcmp($cmd, "explorer") != 0)
				{
					// don't call the locator in the explorer frame
					// this prevents a lot of log errors
					// Helmut Schottmüller, 2006-07-21
					$this->displayLocator();
				}

				// (horrible) workaround for preventing template engine
				// from hiding paragraph text that is enclosed
				// in curly brackets (e.g. "{a}", see ilPageObjectGUI::showPage())
				$this->tpl->fillTabs();
				$output =  $this->tpl->get("DEFAULT", true, true, $show_footer,true);
				$output = str_replace("&#123;", "{", $output);
				$output = str_replace("&#125;", "}", $output);
				header('Content-type: text/html; charset=UTF-8');
				echo $output;
				break;

			default:
				$ret =& $this->$cmd();
				break;
		}
	}

	/**
	* output main frameset of editor
	* left frame: explorer tree of chapters
	* right frame: editor content
	*/
	function frameset()
	{
		include_once("./Services/Frameset/classes/class.ilFramesetGUI.php");
		$fs_gui = new ilFramesetGUI();
		
		$fs_gui->setFramesetTitle($this->lng->txt("editor"));
		$fs_gui->setMainFrameName("content");
		$fs_gui->setSideFrameName("tree");
		$this->ctrl->setParameter($this, "obj_id", $_GET["obj_id"]);
		if ($this->lm_obj->getType() == "dbk")
		{
			$fs_gui->setSideFrameSource(
				$this->ctrl->getLinkTargetByClass("ilobjdlbookgui", "explorer"));
			if ($_GET["to_page"]== 1)
			{
				$fs_gui->setMainFrameSource(
					$this->ctrl->getLinkTargetByClass(
						array("ilobjdlbookgui", "illmpageobjectgui"),
						"view"));
			}
			else
			{
				$fs_gui->setMainFrameSource(
					$this->ctrl->getLinkTargetByClass("ilobjdlbookgui", "properties"));
			}
		}
		else
		{
			$fs_gui->setSideFrameSource(
				$this->ctrl->getLinkTargetByClass("ilobjlearningmodulegui", "explorer"));
						if ($_GET["to_page"]== 1)
			{
				$fs_gui->setMainFrameSource(
					$this->ctrl->getLinkTargetByClass(
						array("ilobjlearningmodulegui", "illmpageobjectgui"),
						"view"));
			}
			else
			{
				$fs_gui->setMainFrameSource(
					$this->ctrl->getLinkTargetByClass("ilobjlearningmodulegui", "properties"));
			}
		}
		$fs_gui->show();
		exit;
	}
	
	

	/**
	* output main header (title and locator)
	*/
	function main_header($a_type)
	{
		global $lng;

		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		//$this->tpl->setVariable("HEADER", $a_header_title);
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		//$this->tpl->setVariable("TXT_LOCATOR",$this->lng->txt("locator"));
		//$this->displayLocator($a_type);

		// content style
		$this->tpl->setCurrentBlock("ContentStyle");
		$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
			ilObjStyleSheet::getContentStylePath($this->lm_obj->getStyleSheetId()));
		$this->tpl->parseCurrentBlock();

		// syntax style
		$this->tpl->setCurrentBlock("SyntaxStyle");
		$this->tpl->setVariable("LOCATION_SYNTAX_STYLESHEET",
			ilObjStyleSheet::getSyntaxStylePath());
		$this->tpl->parseCurrentBlock();

	}


	/**
	* display locator
	*/
	function displayLocator()
	{
		global $lng;

		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

		$modifier = 1;

		$locations = $this->ctrl->getLocations();

		foreach ($locations as $key => $row)
		{
			if ($key < count($locations)-$modifier)
			{
				$this->tpl->touchBlock("locator_separator");
			}

			if ($row["ref_id"]> 0 && $row["ref_id"] != ROOT_FOLDER_ID)
			{
				$oid = ilObject::_lookupObjId($row["ref_id"]);
				$t = ilObject::_lookupType($oid);
				$this->tpl->setCurrentBlock("locator_img");
				$this->tpl->setVariable("IMG_SRC",
					ilUtil::getImagePath("icon_".$t."_s.gif"));
				$this->tpl->setVariable("IMG_ALT",
					$lng->txt("obj_".$type));
				$this->tpl->parseCurrentBlock();
			}

			if ($row["link"] != "")
			{
				$this->tpl->setCurrentBlock("locator_item");
				$this->tpl->setVariable("ITEM", $row["title"]);
				$this->tpl->setVariable("LINK_ITEM", $row["link"]);
				if ($row["target"] != "")
				{
					$this->tpl->setVariable("LINK_TARGET", ' target="'.$row["target"].'" ');
				}
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock("locator_item");
				$this->tpl->setVariable("PREFIX", $row["title"]);
				$this->tpl->parseCurrentBlock();
			}
		}

		$this->tpl->setCurrentBlock("locator");
		$this->tpl->parseCurrentBlock();

	}

}
?>
