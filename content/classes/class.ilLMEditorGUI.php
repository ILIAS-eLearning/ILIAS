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

require_once ("content/classes/class.ilLMObjectFactory.php");
require_once ("classes/class.ilDOMUtil.php");
require_once ("content/classes/class.ilObjLearningModule.php");
require_once ("content/classes/class.ilObjLearningModuleGUI.php");
require_once ("content/classes/class.ilObjDlBook.php");
require_once ("content/classes/class.ilObjDlBookGUI.php");
require_once ("content/classes/class.ilLMPageObjectGUI.php");
require_once ("content/classes/class.ilStructureObjectGUI.php");
require_once ("content/classes/Pages/class.ilPageEditorGUI.php");
//require_once ("content/classes/Pages/class.ilMediaItem.php");
require_once ("classes/class.ilObjStyleSheet.php");
require_once ("content/classes/class.ilEditClipboard.php");


/**
* GUI class for learning module editor
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package content
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
		global $ilias, $tpl, $lng, $objDefinition, $ilCtrl;

		$this->ctrl =& $ilCtrl;

		$this->ctrl->saveParameter($this, array("ref_id", "obj_id"));

		// initiate variables
		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->lng =& $lng;
		$this->objDefinition =& $objDefinition;
		$this->ref_id = $_GET["ref_id"];
		$this->obj_id = $_GET["obj_id"];
	}

	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);

		if ($next_class != "")
		{
			$cmd = $this->ctrl->getCmd();
			switch($next_class)
			{
				case "illmpageobjectgui":
					$this->ctrl->setReturn($this, "view");

					$this->lm_obj =& $this->ilias->obj_factory->getInstanceByRefId($this->ref_id);
					//$this->tree = new ilTree($this->lm_obj->getId());
					//$this->tree->setTableNames('lm_tree','lm_data');
					//$this->tree->setTreeTablePK("lm_id");
					//$this->main_header($this->lng->txt($this->lm_obj->getType()).": ".$this->lm_obj->getTitle(),$this->lm_obj->getType());

					if(!empty($_GET["obj_id"]))		// we got a page or structure object
					{
						$obj =& ilLMObjectFactory::getInstance($this->lm_obj, $_GET["obj_id"]);
					}
					$pg_gui =& new ilLMPageObjectGUI($this->lm_obj);
					if (is_object($obj))
					{
						$pg_gui->setLMPageObject($obj);
					}
					$ret =& $pg_gui->executeCommand();
					$this->tpl->show();
					return;
					break;
			}
		}

		$hier_id = $_GET["hier_id"];
		if(isset($_POST["new_hier_id"]))
		{
			$hier_id = $_POST["new_hier_id"];
		}
//echo ":hier_id_a:$hier_id:";
		$cmd = (empty($_GET["cmd"]))
			? "frameset"
			: $_GET["cmd"];

		$new_type = (isset($_GET["new_type"]))
			? $_GET["new_type"]
			: $_POST["new_type"];

		if ($cmd == "post")
		{
			$cmd = key($_POST["cmd"]);
		}

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

//echo ":hier_id_c:$hier_id:";

		if ($cmd == "view" && $this->ctrl->getRedirectSource() == "ilinternallinkgui")
		{
			$cmd = "explorer";
		}
		switch($cmd)
		{
			case "explorer":
			case "frameset":
				$this->$cmd();
				break;

			case "closeLinkHelp":
				$this->explorer();
				break;

			case "showImageMap":
				$this->showImageMap();
				break;

			default:
				$this->lm_obj =& $this->ilias->obj_factory->getInstanceByRefId($this->ref_id);
#				$this->lm_obj =& new ilObjLearningModule($this->ref_id, true);

				$this->tpl->setCurrentBlock("ContentStyle");
				$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
					ilObjStyleSheet::getContentStylePath($this->lm_obj->getStyleSheetId()));
				$this->tpl->parseCurrentBlock();

				$this->tpl->setVariable("TXT_LOCATOR",$this->lng->txt("locator"));

				$this->tree = new ilTree($this->lm_obj->getId());
				$this->tree->setTableNames('lm_tree','lm_data');
				$this->tree->setTreeTablePK("lm_id");
				if(!empty($_GET["obj_id"]))		// we got a page or structure object
				{
					$obj =& ilLMObjectFactory::getInstance($this->lm_obj, $_GET["obj_id"]);
					$this->obj =& $obj;
					if (is_object($obj))
					{
//echo "1c";
						if($type != "content")
						{
							$type = ($cmd == "create" || $cmd == "save")
								? $new_type
								: $obj->getType();
							$this->main_header($this->lng->txt($obj->getType()).": ".$obj->getTitle(),$obj->getType());
						}
					}
				}
				else		// command belongs to learning module
				{
					$this->main_header($this->lng->txt($this->lm_obj->getType()).": ".$this->lm_obj->getTitle(),$this->lm_obj->getType());
					$type = ($cmd == "create" || $cmd == "save")
							? $new_type
							: $this->lm_obj->getType();
				}

//echo "2"; exit;
//echo "type:$type:cmd:$cmd:ctype:$ctype:";
				if($type == "content")
				{
					$pg_gui =& new ilLMPageObjectGUI($this->lm_obj);
					$pg_gui->setLMPageObject($obj);
					$pg_gui->showPageEditor();
				}
				else
				{
//echo "type:$type:cmd:$cmd:<br>";
					switch ($type)
					{
						case "pg":
							$pg_gui =& new ilLMPageObjectGUI($this->lm_obj);
							if (is_object($obj))
							{
								$pg_gui->setLMPageObject($obj);
							}

							$pg_gui->$cmd();
							break;

						case "st":
							if (!is_object($obj))
							{
								$obj =& ilLMObjectFactory::getInstance($this->lm_obj, $this->ref_id);
							}
							$st_gui =& new ilStructureObjectGUI($this->lm_obj, $this->tree);
							$st_gui->setStructureObject($obj);
							$st_gui->$cmd();
							break;

						case "dbk":
							$lm_gui =& new ilObjDlBookGUI("", $_GET["ref_id"], true, false);
							$lm_gui->$cmd();
							break;

						case "lm":
							$lm_gui =& new ilObjLearningModuleGUI("", $_GET["ref_id"], true, false);
							$lm_gui->$cmd();
							break;

						case "meta":
							require_once ("classes/class.ilMetaDataGUI.php");
							$meta_gui =& new ilMetaDataGUI($obj->getMetaData());
							$meta_gui->setLMObject($this->lm_obj);
							$meta_gui->setObject($obj);
							$meta_gui->$cmd();
							break;

					}
				}
				$this->tpl->show();
				break;
		}
	}

	function _forwards()
	{
		return array("ilLMPageObjectGUI", "ilStructureObjectGUI",
			"ilObjDlBookGUI", "ilMetaDataGUI", "ilObjLearningModuleGUI");
	}

	/**
	* output main frameset of editor
	* left frame: explorer tree of chapters
	* right frame: editor content
	*/
	function frameset()
	{
		$this->tpl = new ilTemplate("tpl.lm_edit_frameset.html", false, false, "content");
		$this->tpl->setVariable("REF_ID",$this->ref_id);
		$this->tpl->show();
	}

	/**
	* output explorer tree with bookmark folders
	*/
	function explorer()
	{
		$this->tpl = new ilTemplate("tpl.main.html", true, true);
		// get learning module object
		$this->lm_obj =& new ilObjLearningModule($this->ref_id, true);

		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());

		//$this->tpl = new ilTemplate("tpl.explorer.html", false, false);
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");

		require_once ("content/classes/class.ilLMEditorExplorer.php");
		$exp = new ilLMEditorExplorer("lm_edit.php?cmd=view&ref_id=".$this->lm_obj->getRefId(),$this->lm_obj);
		$exp->setTargetGet("obj_id");

		if ($_GET["lmexpand"] == "")
		{
			$mtree = new ilTree($this->lm_obj->getId());
			$mtree->setTableNames('lm_tree','lm_data');
			$mtree->setTreeTablePK("lm_id");
			$expanded = $mtree->readRootId();
		}
		else
		{
			$expanded = $_GET["lmexpand"];
		}

		$exp->setExpand($expanded);

		// build html-output
		$exp->setOutput(0);
		$output = $exp->getOutput();

		$this->tpl->setCurrentBlock("content");
		$this->tpl->setVariable("TXT_EXPLORER_HEADER", $this->lng->txt("cont_chap_and_pages"));
		$this->tpl->setVariable("EXPLORER",$output);
		$this->tpl->setVariable("ACTION", "lm_edit.php?cmd=explorer&ref_id=".$this->ref_id."&lmexpand=".$_GET["lmexpand"]);
		$this->tpl->parseCurrentBlock();
		$this->tpl->show(false);

	}


	/**
	* show image map
	*/
	function showImageMap()
	{
		$item =& new ilMediaItem($_GET["item_id"]);
		$item->outputMapWorkCopy();
	}


	/**
	* output main header (title and locator)
	*/
	function main_header($a_header_title, $a_type)
	{
		global $lng;

		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
		$this->tpl->setVariable("HEADER", $a_header_title);
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");
		$this->displayLocator();
		$this->setAdminTabs($a_type);
	}


	/**
	* output a cell in object list
	*/
	function add_cell($val, $link = "")
	{
		if(!empty($link))
		{
			$this->tpl->setCurrentBlock("begin_link");
			$this->tpl->setVariable("LINK_TARGET", $link);
			$this->tpl->parseCurrentBlock();
			$this->tpl->touchBlock("end_link");
		}

		$this->tpl->setCurrentBlock("text");
		$this->tpl->setVariable("TEXT_CONTENT", $val);
		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("table_cell");
		$this->tpl->parseCurrentBlock();
	}

	/**
	* display locator
	*/
	function displayLocator()
	{
		require_once("content/classes/class.ilContObjLocatorGUI.php");
		$contObjLocator =& new ilContObjLocatorGUI($this->tree);
		$contObjLocator->setObject($this->obj);
		$contObjLocator->setContentObject($this->lm_obj);
		$contObjLocator->display();
	}

	function setAdminTabs($a_type)
	{
		include_once("classes/class.ilTabsGUI.php");
//echo "HH";
		$tabs_gui =& new ilTabsGUI;
		$tabs_gui->getTargetsByObjectType($this, $a_type);
		$this->tpl->setVariable("TABS", $tabs_gui->getHTML());
	}


}
?>
