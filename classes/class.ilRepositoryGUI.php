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

include_once("classes/class.ilTableGUI.php");
include_once("classes/class.ilObjGroupGUI.php");
include_once("classes/class.ilObjFolderGUI.php");
include_once("classes/class.ilObjFolder.php");
include_once("classes/class.ilObjFileGUI.php");
include_once("classes/class.ilObjFile.php");
include_once("classes/class.ilTabsGUI.php");

/**
* Class ilRepositoryGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @package core
*/
class ilRepositoryGUI
{
	var $lng;
	var $ilias;
	var $tpl;
	var $tree;
	var $rbacsystem;
	var $cur_ref_id;
	var $cmd;
	var $mode;
	var $ctrl;

	/**
	* Constructor
	* @access	public
	*/
	function ilRepositoryGUI()
	{
		global $lng, $ilias, $tpl, $tree, $rbacsystem, $objDefinition,
			$_GET, $ilCtrl;

		$this->lng =& $lng;
		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->tree =& $tree;
		$this->rbacsystem =& $rbacsystem;
		$this->objDefinition =& $objDefinition;
		$this->ctrl =& $ilCtrl;

		$this->ctrl->saveParameter($this, array("ref_id"));
		$this->ctrl->setReturn($this,"ShowList");

		// determine current ref id and mode
		if (!empty($_GET["ref_id"]) && empty($_GET["getlast"]))
		{
			$this->cur_ref_id = $_GET["ref_id"];
		}
		else
		{
			if (!empty($_SESSION["il_rep_ref_id"]))
			{
				$this->cur_ref_id = $_SESSION["il_rep_ref_id"];
			}
			else
			{
				$this->cur_ref_id = $this->tree->getRootId();
			}
		}
		
		if (!empty($_GET["set_mode"]))
		{
			$_SESSION["il_rep_mode"] = $_GET["set_mode"];
		}

		$this->mode = ($_SESSION["il_rep_mode"] != "")
			? $_SESSION["il_rep_mode"]
			: "flat";

		if (($this->mode == "tree") || !$tree->isInTree($this->cur_ref_id))
		{
			$this->cur_ref_id = $this->tree->getRootId();
		}

		//if ($_GET["cmd"] != "delete" && $_GET["cmd"] != "edit"
		//	&& ($this->object->getType() == "cat" || $this->object->getType() == "root" || $this->object->getType() == "grp"))
		{
			$_SESSION["il_rep_ref_id"] = $this->cur_ref_id;
		}
		
		// set no limit for hits/page
		$_GET["limit"] = 9999;

		$this->categories = array();
		$this->learning_resources = array();
		$this->forums = array();
		$this->groups = array();
		$this->glossaries = array();
		$this->exercises = array();
		$this->questionpools = array();
		$this->surveys = array();
		$this->surveyquestionpools = array();
		$this->tests = array();
		$this->files = array();
		$this->folders = array();
		$this->media_pools = array();
	}

	/**
	* get forward scripts
	*/
	function _forwards()
	{
		return array("ilObjGroupGUI","ilObjFolderGUI");
	}
	
	/**
	* execute command
	*/
	function &executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);

		/*if (empty($next_class))
		{
			// get object of current ref id
			//include_once("classes/class.ilObjectFactory.php");
			$obj = $this->ilias->obj_factory->getInstanceByRefId($this->cur_ref_id);
			$next_class = get_class($obj)."gui";
			$obj_type = $obj->getType();
		}*/

		$cmd = $this->ctrl->getCmd();

		switch ($next_class)
		{
			case "ilobjgroupgui":
				include_once("./classes/class.ilObjGroupGUI.php");
				$this->gui_obj = new ilObjGroupGUI("", $this->cur_ref_id, true, false);

				$this->prepareOutput();

				$this->gui_obj->executeCommand();
				$this->tpl->show();
				break;

			default:

				if (!isset($obj_type))
				{
					$obj_type = ilObject::_lookupType($this->cur_ref_id,true);
				}

				// get GUI of current object
				$class_name = $this->objDefinition->getClassName($obj_type);
				$module = $this->objDefinition->getModule($obj_type);
				$module_dir = ($module == "") ? "" : $module."/";
				$class_constr = "ilObj".$class_name."GUI";
				include_once("./".$module_dir."classes/class.ilObj".$class_name."GUI.php");
				$this->gui_obj = new $class_constr("", $this->cur_ref_id, true, false);

				// execute repository cmd
				if (empty($cmd))
				{
					$cmd = $this->ctrl->getCmd("ShowList");
					//$next_class = "";
				}
				$this->cmd = $cmd;
				$this->$cmd();
				break;
		}
	}

	function prepareOutput()
	{
		// output objects
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.repository.html");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

		// output tabs
		//$this->setTabs();

		// output locator
		$this->setLocator();

		// output message
		if ($this->message)
		{
			sendInfo($this->message);
		}

		// display infopanel if something happened
		infoPanel();

		// set header
		$this->setHeader();
	}

	/**
	* show list (tree or flat, depending on current mode)
	*/
	function showList()
	{
		switch ($this->mode)
		{
			case "tree":
				$this->showTree();
				break;

			default:
				$this->showFlatList();
				break;
		}
	}


	/**
	* display tree view
	*/
	function showTree()
	{
		$this->prepareOutput();
		
		$this->tpl->setCurrentBlock("content");
		$this->tpl->addBlockFile("OBJECTS", "objects", "tpl.rep_explorer.html");

		include_once ("classes/class.ilRepositoryExplorer.php");
		$exp = new ilRepositoryExplorer("repository.php?cmd=goto");
		$exp->setTargetGet("ref_id");

		if ($_GET["repexpand"] == "")
		{
			$expanded = $this->tree->readRootId();
		}
		else
		{
			$expanded = $_GET["repexpand"];
		}

		$exp->setExpand($expanded);

		// build html-output
		$exp->setOutput(0);
		$output = $exp->getOutput();

		$this->tpl->setCurrentBlock("objects");
		//$this->tpl->setVariable("TXT_EXPLORER_HEADER", $this->lng->txt("cont_chap_and_pages"));
		$this->tpl->setVariable("EXPLORER", $output);
		//$this->tpl->setVariable("ACTION", "repository.php?repexpand=".$_GET["repexpand"]);
		$this->tpl->parseCurrentBlock();
		$this->tpl->show();
	}

	/**
	* display flat list
	*/
	function showFlatList()
	{
		global $objDefinition, $ilBench;

		$ilBench->start("Repository", "FlatList");

		$this->prepareOutput();

		// get all objects of current node
		$ilBench->start("Repository", "FlatList_01getChilds");
		$objects = $this->tree->getChilds($this->cur_ref_id, "title");
		$ilBench->stop("Repository", "FlatList_01getChilds");

		$ilBench->start("Repository", "FlatList_02collectChilds");
		
		$found = false;
		
		foreach ($objects as $key => $object)
		{
			if (!$this->rbacsystem->checkAccess('visible',$object["child"]))
			{
				continue;
			}

			// hide object types in devmode
			if ($objDefinition->getDevMode($object["type"]))
			{
				continue;
			}

			switch ($object["type"])
			{
				// categories
				case "cat":
					$this->categories[$key] = $object;
					break;

				// test&assessment
				case "tst":
					$this->tests[$key] = $object;
					break;

				case "qpl":
					$this->questionpools[$key] = $object;
					break;

				// survey tool
				case "svy":
					$this->surveys[$key] = $object;
					break;

				case "spl":
					$this->surveyquestionpools[$key] = $object;
					break;


				// media pools
				case "mep":
					$this->media_pools[$key] = $object;
					break;

				// learning resources
				case "lm":
				case "slm":
				case "dbk":
				case "htlm":
					$this->learning_resources[$key] = $object;

					// check if lm is online
					if ($object["type"] == "lm")
					{
						include_once("content/classes/class.ilObjLearningModule.php");
						$lm_obj =& new ilObjLearningModule($object["ref_id"]);
						if((!$lm_obj->getOnline()) && (!$this->rbacsystem->checkAccess('write',$object["child"])))
						{
							unset ($this->learning_resources[$key]);
						}
					}
					// check if fblm is online
					if ($object["type"] == "htlm")
					{
						include_once("content/classes/class.ilObjFileBasedLM.php");
						$lm_obj =& new ilObjFileBasedLM($object["ref_id"]);
						if((!$lm_obj->getOnline()) && (!$this->rbacsystem->checkAccess('write',$object["child"])))
						{
							unset ($this->learning_resources[$key]);
						}
					}
					// check if scorm is online
					if ($object["type"] == "slm")
					{
						include_once("classes/class.ilObjSCORMLearningModule.php");
						$lm_obj =& new ilObjSCORMLearningModule($object["ref_id"]);
						if((!$lm_obj->getOnline()) && (!$this->rbacsystem->checkAccess('write',$object["child"])))
						{
							unset ($this->learning_resources[$key]);
						}
					}
					break;

				// forums
				case "frm":
					$this->forums[$key] = $object;
					break;

				// groups
				case "grp":
					$this->groups[$key] = $object;
					break;

				// glossary
				case "glo":
					$this->glossaries[$key] = $object;
					break;

				//
				case "exc":
					$this->exercises[$key] = $object;
					break;

				case "chat":
					$this->chats[$key] = $object;
					break;

				// files
				case "file":
					$this->files[$key] = $object;
					break;

				// folders
				case "fold":
					$this->folders[$key] = $object;
					break;
			}
		}
		$ilBench->stop("Repository", "FlatList_02collectChilds");

		// output objects
		/*$this->tpl->addBlockFile("CONTENT", "content", "tpl.repository.html");
		$this->tpl->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

		// output tabs
		//$this->setTabs();

		// output locator
		$this->setLocator();

		// output message
		if($this->message)
		{
			sendInfo($this->message);
		}

		// display infopanel if something happened
		infoPanel();

		// set header
		$this->setHeader();*/

		$this->tpl->setCurrentBlock("content");
		$this->tpl->addBlockFile("OBJECTS", "objects", "tpl.repository_lists.html");

		$ilBench->start("Repository", "FlatList_03showObjects");

		// (sub)categories
		$ilBench->start("Repository", "FlatList_04showCategories");
		if (count($this->categories))
		{
			$this->showCategories();
		}
		$ilBench->stop("Repository", "FlatList_04showCategories");

		// test&assessment
		if (count($this->questionpools))
		{
			$this->showQuestionPools();
		}

		if (count($this->tests))
		{
			$this->showTests();
		}
		
		// survey tool
		if (count($this->survey))
		{
			$this->showSurveys();
		}
		
		if (count($this->surveyquestionpools))
		{
			$this->showSurveyquestionpools();
		}

		// learning resources
		if (count($this->learning_resources))
		{
			$this->showLearningResources();
		}

		// glossaries
		if (count($this->glossaries))
		{
			$this->showGlossaries();
		}

		// forums
		if (count($this->forums))
		{
			$this->showForums();
		}

		// groups
		if (count($this->groups))
		{
			$this->showGroups();
		}

		// exercises
		if (count($this->exercises))
		{
			$this->showExercises();
		}

		// files
		if (count($this->files))
		{
			$this->showFiles();
		}

		// folders
		if (count($this->folders))
		{
			$this->showFolders();
		}

		// chat
		if (count($this->chats))
		{
			$this->showChats();
		}

		// media pools
		if (count($this->media_pools))
		{
			$this->showMediaPools();
		}

		$this->tpl->show();

		$ilBench->stop("Repository", "FlatList_03showObjects");

		$ilBench->stop("Repository", "FlatList");
	}


	/**
	* display header section (header, description, tree/flat icon)
	*/
	function setHeader()
	{
		if ($this->cur_ref_id == $this->tree->getRootId())
		{
			$this->tpl->setVariable("HEADER",  $this->lng->txt("repository"));
			if($this->mode != "tree")
			{
				$this->showPossibleSubObjects("root");
			}
		}
		else
		{
			$this->tpl->setVariable("HEADER",  $this->gui_obj->object->getTitle());

			$desc = ($this->gui_obj->object->getDescription())
				? $this->gui_obj->object->getDescription()
				: "";
			$this->tpl->setVariable("H_DESCRIPTION",  $desc);
			//if ($_GET["cmd"] != "delete" && $_GET["cmd"] != "edit")
			{
				$this->showPossibleSubObjects($this->gui_obj->object->getType());
			}
		}

		$this->tpl->setVariable("H_FORMACTION",  "repository.php?ref_id=".$this->cur_ref_id.
			"&cmd=post");

		if ($this->cur_ref_id != $this->tree->getRootId())
		{
			$par_id = $this->tree->getParentId($this->cur_ref_id);
			$this->tpl->setCurrentBlock("top");
			$this->tpl->setVariable("LINK_TOP", "repository.php?ref_id=".$par_id);
			$this->tpl->setVariable("IMG_TOP",ilUtil::getImagePath("ic_top.gif"));
			$this->tpl->parseCurrentBlock();
		}
		
		$this->setAdminTabs();
		
		//$this->tpl->setCurrentBlock("content");

		//if ($_GET["cmd"] != "delete" && $_GET["cmd"] != "edit")
		{
			$this->tpl->setVariable("LINK_FLAT", "repository.php?set_mode=flat&ref_id=".$this->cur_ref_id);
			$this->tpl->setVariable("IMG_FLAT",ilUtil::getImagePath("ic_flatview.gif"));

			$this->tpl->setVariable("LINK_TREE", "repository.php?set_mode=tree&ref_id=".$this->cur_ref_id);
			$this->tpl->setVariable("IMG_TREE",ilUtil::getImagePath("ic_treeview.gif"));
		}
	}
	
	/**
	* set admin tabs
	*/
	function setAdminTabs()
	{
		if (is_object($this->gui_obj))
		{
			$tabs_gui =& new ilTabsGUI();
			$this->gui_obj->getTabs($tabs_gui);

			// output tabs
			$this->tpl->setVariable("TABS", $tabs_gui->getHTML());
		}
	}

	/**
	* show categories
	*/
	function showCategories()
	{
		global $ilBench;

		$tpl =& new ilTemplate ("tpl.table.html", true, true);

		$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.rep_cat_row.html");

		$maxcount = count($this->categories);
		$cats = array_slice($this->categories, $_GET["offset"], $_GET["limit"]);

		// render table content data
		$ilBench->start("Repository", "showCategories_01Rows");
		if (count($cats) > 0)
		{
			$ilBench->start("Repository", "showCategories_01Rows_AddBlockFile");
			$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.rep_cat_row.html");
			$ilBench->stop("Repository", "showCategories_01Rows_AddBlockFile");

			// counter for rowcolor change
			$num = 0;

			foreach ($cats as $cat)
			{
				// get category object
				$ilBench->start("Repository", "showCategories_00Rows_getCategoryObject");
				include_once("classes/class.ilObjCategory.php");
				$cat_obj =& new ilObjCategory($cat["ref_id"], true);
				$obj_link = "repository.php?ref_id=".$cat["ref_id"];
				$ilBench->stop("Repository", "showCategories_00Rows_getCategoryObject");

				//$tpl->setVariable("CHECKBOX",ilUtil::formCheckBox("", "items[]", $cat["ref_id"]));

				// read
				$ilBench->start("Repository", "showCategories_01Rows_ReadLink");
				if ($this->rbacsystem->checkAccess('read',$cat["ref_id"]))
				{
					$tpl->setCurrentBlock("cat_link");
					$tpl->setVariable("CAT_LINK", $obj_link);
					$tpl->setVariable("TITLE", $cat["title"]);
					$tpl->parseCurrentBlock();
				}
				else
				{
					$tpl->setCurrentBlock("cat_show");
					$tpl->setVariable("STITLE", $cat["title"]);
					$tpl->parseCurrentBlock();
				}
				$ilBench->stop("Repository", "showCategories_01Rows_ReadLink");

				// edit
				$ilBench->start("Repository", "showCategories_01Rows_EditLink");
				if ($this->rbacsystem->checkAccess('write',$cat["ref_id"]))
				{
					$tpl->setCurrentBlock("cat_edit");
					$tpl->setVariable("EDIT_LINK","repository.php?cmd=edit&ref_id=".$cat["ref_id"]);
					$tpl->setVariable("TXT_EDIT", $this->lng->txt("edit"));
					$tpl->parseCurrentBlock();
				}
				$ilBench->stop("Repository", "showCategories_01Rows_EditLink");

				// delete
				$ilBench->start("Repository", "showCategories_01Rows_DeleteLink");
				if ($this->rbacsystem->checkAccess('delete', $cat["ref_id"]))
				{
					$tpl->setCurrentBlock("cat_delete");
					$tpl->setVariable("DELETE_LINK","repository.php?cmd=delete&ref_id=".$cat["ref_id"]);
					$tpl->setVariable("TXT_DELETE", $this->lng->txt("delete"));
					$tpl->parseCurrentBlock();
				}
				$ilBench->stop("Repository", "showCategories_01Rows_DeleteLink");

				$ilBench->start("Repository", "showCategories_01Rows_setTemplateVars");
				$tpl->setCurrentBlock("tbl_content");
				// change row color
				$tpl->setVariable("ROWCOL", ilUtil::switchColor($num,"tblrow2","tblrow1"));
				$num++;

				$tpl->setVariable("CAT_IMG", ilUtil::getImagePath("icon_cat.gif"));

				$tpl->setVariable("ALT_IMG", $this->lng->txt("obj_cat"));
				$tpl->setVariable("DESCRIPTION", $cat_obj->getDescription());
				$tpl->setVariable("LAST_CHANGE", ilFormat::formatDate($cat["last_update"]));
				$ilBench->stop("Repository", "showCategories_01Rows_setTemplateVars");

				// parse block
				$ilBench->start("Repository", "showCategories_01Rows_parseBlock");
				$tpl->parseCurrentBlock();
				$ilBench->stop("Repository", "showCategories_01Rows_parseBlock");
			}
		}
		else
		{
			$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.no_objects_row.html");
			$tpl->setCurrentBlock("tbl_content");
			$tpl->setVariable("ROWCOL", "tblrow1");
			$tpl->setVariable("COLSPAN", "2");
			$tpl->setVariable("TXT_NO_OBJECTS",$this->lng->txt("lo_no_content"));
			$tpl->parseCurrentBlock();
		}
		$ilBench->stop("Repository", "showCategories_01Rows");

		// create table
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->lng->txt("categories"),
			"icon_cat_b.gif", $this->lng->txt("categories"));
		//$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		$tbl->setHeaderNames(array($this->lng->txt("type"), $this->lng->txt("title")));
		$tbl->setHeaderVars(array("type", "title"),
			array("ref_id" => $this->cur_ref_id));
		$tbl->setColumnWidth(array("1%", "99%"));

		//$tbl->setOrderColumn($_GET["sort_by"]);
		//$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($maxcount);

		// footer
		$tbl->setFooter("tblfooter", $this->lng->txt("previous"),$this->lng->txt("next"));
		//$tbl->disable("content");
		$tbl->disable("footer");

		// render table
		$tbl->setTemplate($tpl);
		$tbl->render();

		// parse it
		$tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("subcategories");
		$this->tpl->setVariable("CATEGORIES", $tpl->get());
		$this->tpl->parseCurrentBlock();

	}

	/**
	* show learning resources
	*/
	function showLearningResources()
	{
		$maxcount = count($this->learning_resources);
		$lrs = array_slice($this->learning_resources, $_GET["offset"], $_GET["limit"]);

		$tpl =& new ilTemplate ("tpl.table.html", true, true);

		$lr_num = count($lrs);

		// render table content data
		if ($lr_num > 0)
		{
			$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.rep_lres_row.html");

			// counter for rowcolor change
			$num = 0;

			foreach ($lrs as $lr_data)
			{
				$tpl->setCurrentBlock("tbl_content");

				// change row color
				$tpl->setVariable("ROWCOL", ilUtil::switchColor($num,"tblrow2","tblrow1"));
				$num++;

				$obj_icon = "icon_".$lr_data["type"]."_b.gif";

				switch($lr_data["type"])
				{
					case "lm":
					case "dbk":
						$read_link = "content/lm_presentation.php?ref_id=".$lr_data["ref_id"];
						$edit_link = "content/lm_edit.php?ref_id=".$lr_data["ref_id"];
						$desk_type = "lm";
						break;

					case "htlm":
						$read_link = "content/fblm_presentation.php?ref_id=".$lr_data["ref_id"];
						$edit_link = "content/fblm_edit.php?ref_id=".$lr_data["ref_id"];
						$desk_type = "htlm";
						break;

					case "slm":
						$read_link = "content/scorm_presentation.php?ref_id=".$lr_data["ref_id"];
						$edit_link = "content/scorm_edit.php?ref_id=".$lr_data["ref_id"];
						$desk_type = "slm";
						break;

				}

				// learning modules
				if ($lr_data["type"] == "lm" || $lr_data["type"] == "dbk" ||
					$lr_data["type"] == "htlm" || $lr_data["type"] == "slm")
				{

					//$obj_link = "content/lm_presentation.php?ref_id=".$lr_data["ref_id"];
					//$tpl->setVariable("CHECKBOX",ilUtil::formCheckBox("","items[]",$lr_data["ref_id"]));
					if ($this->rbacsystem->checkAccess('read',$lr_data["ref_id"]))
					{
						$tpl->setCurrentBlock("lres_read");
						$tpl->setVariable("VIEW_LINK", $read_link);
						$tpl->setVariable("VIEW_TARGET", "ilContObj".$lr_data["obj_id"]);
						$tpl->setVariable("R_TITLE", $lr_data["title"]);
//echo "LM_Title:".$lr_data["title"].":<br>";
						$tpl->parseCurrentBlock();
					}
					else
					{
						$tpl->setCurrentBlock("lres_visible");
						$tpl->setVariable("V_TITLE", $lr_data["title"]);
//echo "LM_Title:".$lr_data["title"].":<br>";
						$tpl->parseCurrentBlock();
					}

					$tpl->setCurrentBlock("tbl_content");

					if ($this->rbacsystem->checkAccess('write',$lr_data["ref_id"]))
					{
						$tpl->setCurrentBlock("lres_edit");
						$tpl->setVariable("EDIT_LINK", $edit_link);
						$tpl->setVariable("EDIT_TARGET","bottom");
						$tpl->setVariable("TXT_EDIT", $this->lng->txt("edit"));
						$tpl->parseCurrentBlock();
					}

					if ($this->rbacsystem->checkAccess('delete', $lr_data["ref_id"]))
					{
						$tpl->setCurrentBlock("lres_delete");
						$tpl->setVariable("DELETE_LINK","repository.php?cmd=delete&ref_id=".$lr_data["ref_id"]);
						$tpl->setVariable("TXT_DELETE", $this->lng->txt("delete"));
						$tpl->parseCurrentBlock();
					}
					$tpl->setCurrentBlock("tbl_content");

					if (!$this->ilias->account->isDesktopItem($lr_data["ref_id"], $desk_type))
					{
						if ($this->rbacsystem->checkAccess('read', $lr_data["ref_id"]))
						{
							$tpl->setCurrentBlock("lres_desklink");
							$tpl->setVariable("TO_DESK_LINK", "repository.php?cmd=addToDesk&ref_id=".$this->cur_ref_id.
								"&item_ref_id=".$lr_data["ref_id"].
								"&type=".$desk_type."&offset=".$_GET["offset"]."&sort_order=".$_GET["sort_order"].
								"&sort_by=".$_GET["sort_by"]);
							$tpl->setVariable("TXT_TO_DESK", $this->lng->txt("to_desktop"));
							$tpl->parseCurrentBlock();
						}
					}
					$tpl->setCurrentBlock("tbl_content");
				}


				$tpl->setVariable("LRES_IMG", ilUtil::getImagePath("icon_".$lr_data["type"].".gif"));
				$tpl->setVariable("ALT_IMG", $this->lng->txt("obj_".$lr_data["type"]));
				$tpl->setVariable("DESCRIPTION", $lr_data["description"]);
				$tpl->setVariable("LAST_CHANGE", ilFormat::formatDate($lr_data["last_update"]));
				$tpl->parseCurrentBlock();
			}

		}
		else
		{
			$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.no_objects_row.html");
			$tpl->setCurrentBlock("tbl_content");
			$tpl->setVariable("ROWCOL", "tblrow1");
			$tpl->setVariable("COLSPAN", "3");
			$tpl->setVariable("TXT_NO_OBJECTS",$this->lng->txt("lo_no_content"));
			$tpl->parseCurrentBlock();
		}

		//$this->showPossibleSubObjects("lrs");

		// create table
		$tbl = new ilTableGUI();
		$tbl->setTemplate($tpl);

		// title & header columns
		$tbl->setTitle($this->lng->txt("learning_resources"),"icon_lm_b.gif",$this->lng->txt("learning_resources"));
		//$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		$tbl->setHeaderNames(array($this->lng->txt("type"), $this->lng->txt("title")));
		$tbl->setHeaderVars(array("type", "title"),
			array("ref_id" => $this->cur_ref_id));
		$tbl->setColumnWidth(array("1%", "99%"));

		// control
		//$tbl->setOrderColumn($_GET["sort_by"]);
		//$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($maxcount);

		// footer
		//$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->disable("footer");
		//$tbl->disable("title");

		// render table
		$tbl->render();
		$tpl->parseCurrentBlock();


		$this->tpl->setCurrentBlock("learning_resources");
		$this->tpl->setVariable("LEARNING_RESOURCES", $tpl->get());
		//$this->tpl->setVariable("LEARNING_RESOURCES", "hh");
		$this->tpl->parseCurrentBlock();

	}

	/**
	* show glossaries
	*/
	function showGlossaries()
	{
		$maxcount = count($this->glossaries);
		$glos = array_slice($this->glossaries, $_GET["offset"], $_GET["limit"]);

		$tpl =& new ilTemplate ("tpl.table.html", true, true);

		$glo_num = count($glos);

		// render table content data
		if ($glo_num > 0)
		{
			$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.rep_glo_row.html");

			// counter for rowcolor change
			$num = 0;

			foreach ($glos as $gl_data)
			{

				$obj_icon = "icon_glo_b.gif";
				$obj_link = "content/glossary_presentation.php?ref_id=".$gl_data["ref_id"];

				if ($this->rbacsystem->checkAccess('read',$gl_data["ref_id"]))
				{
					$tpl->setCurrentBlock("glo_read");
					$tpl->setVariable("VIEW_LINK", $obj_link);
					$tpl->setVariable("VIEW_TARGET", "bottom");
					$tpl->setVariable("R_TITLE", $gl_data["title"]);
					$tpl->parseCurrentBlock();
				}
				else
				{
					$tpl->setCurrentBlock("glo_visible");
					$tpl->setVariable("V_TITLE", $gl_data["title"]);
					$tpl->parseCurrentBlock();
				}

				if ($this->rbacsystem->checkAccess('write',$gl_data["ref_id"]))
				{
					$tpl->setCurrentBlock("glo_edit");
					$tpl->setVariable("EDIT_LINK","content/glossary_edit.php?cmd=listTerms&ref_id=".$gl_data["ref_id"]);
					$tpl->setVariable("EDIT_TARGET","bottom");
					$tpl->setVariable("TXT_EDIT", $this->lng->txt("edit"));
					$tpl->parseCurrentBlock();
				}

				if (!$this->ilias->account->isDesktopItem($gl_data["ref_id"], "glo"))
				{
					if ($this->rbacsystem->checkAccess('read', $gl_data["ref_id"]))
					{
						$tpl->setCurrentBlock("glo_delete");
						$tpl->setVariable("TO_DESK_LINK", "repository.php?cmd=addToDesk&ref_id=".$this->cur_ref_id.
							"&item_ref_id=".$gl_data["ref_id"].
							"&type=glo&offset=".$_GET["offset"]."&sort_order=".$_GET["sort_order"].
							"&sort_by=".$_GET["sort_by"]);
						$tpl->setVariable("TXT_TO_DESK", $this->lng->txt("to_desktop"));
						$tpl->parseCurrentBlock();
					}
				}

				$tpl->setCurrentBlock("tbl_content");

				// change row color
				$tpl->setVariable("ROWCOL", ilUtil::switchColor($num,"tblrow2","tblrow1"));
				$num++;

				//$tpl->setVariable("CHECKBOX",ilUtil::formCheckBox("","items[]",$gl_data["ref_id"]));
				$tpl->setVariable("DESCRIPTION", $gl_data["description"]);
				$tpl->setVariable("LAST_CHANGE", ilFormat::formatDate($gl_data["last_update"]));
				$tpl->parseCurrentBlock();
			}

		}
		else
		{
			$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.no_objects_row.html");
			$tpl->setCurrentBlock("tbl_content");
			$tpl->setVariable("ROWCOL", "tblrow1");
			$tpl->setVariable("COLSPAN", "3");
			$tpl->setVariable("TXT_NO_OBJECTS",$this->lng->txt("lo_no_content"));
			$tpl->parseCurrentBlock();
		}

		//$this->showPossibleSubObjects("lrs");

		// create table
		$tbl = new ilTableGUI();
		$tbl->setTemplate($tpl);

		// title & header columns
		$tbl->setTitle($this->lng->txt("glossaries"),"icon_glo_b.gif",$this->lng->txt("glossaries"));
		//$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		$tbl->setHeaderNames(array($this->lng->txt("title")));
		$tbl->setHeaderVars(array("title"),
			array("ref_id" => $this->cur_ref_id));
		$tbl->setColumnWidth(array("1%", "99%"));

		// control
		//$tbl->setOrderColumn($_GET["sort_by"]);
		//$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($maxcount);

		// footer
		//$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->disable("footer");
		//$tbl->disable("title");

		// render table
		$tbl->render();
		$tpl->parseCurrentBlock();


		$this->tpl->setCurrentBlock("glossaries");
		$this->tpl->setVariable("GLOSSARIES", $tpl->get());
		//$this->tpl->setVariable("LEARNING_RESOURCES", "hh");
		$this->tpl->parseCurrentBlock();

	}

	/**
	* show media pools
	*/
	function showMediaPools()
	{
		$maxcount = count($this->media_pools);
		$meps = array_slice($this->media_pools, $_GET["offset"], $_GET["limit"]);

		$tpl =& new ilTemplate ("tpl.table.html", true, true);

		$mep_num = count($meps);

		// render table content data
		if ($mep_num > 0)
		{
			$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.rep_mep_row.html");

			// counter for rowcolor change
			$num = 0;

			foreach ($meps as $mep_data)
			{

				$obj_icon = "icon_mep_b.gif";
				$obj_link = "content/mep_edit.php?ref_id=".$mep_data["ref_id"];

				if ($this->rbacsystem->checkAccess('write',$mep_data["ref_id"]))
				{
					$tpl->setCurrentBlock("mep_edit");
					$tpl->setVariable("VIEW_LINK", $obj_link);
					$tpl->setVariable("VIEW_TARGET", "bottom");
					$tpl->setVariable("R_TITLE", $mep_data["title"]);
					$tpl->parseCurrentBlock();
				}
				else
				{
					$tpl->setCurrentBlock("mep_visible");
					$tpl->setVariable("V_TITLE", $mep_data["title"]);
					$tpl->parseCurrentBlock();
				}

				if (!$this->ilias->account->isDesktopItem($mep_data["ref_id"], "mep"))
				{
					if ($this->rbacsystem->checkAccess('write', $mep_data["ref_id"]))
					{
						$tpl->setCurrentBlock("mep_desklink");
						$tpl->setVariable("TO_DESK_LINK", "repository.php?cmd=addToDesk&ref_id=".$this->cur_ref_id.
							"&item_ref_id=".$mep_data["ref_id"].
							"&type=mep&offset=".$_GET["offset"]."&sort_order=".$_GET["sort_order"].
							"&sort_by=".$_GET["sort_by"]);
						$tpl->setVariable("TXT_TO_DESK", $this->lng->txt("to_desktop"));
						$tpl->parseCurrentBlock();
					}
				}

				$tpl->setCurrentBlock("tbl_content");

				// change row color
				$tpl->setVariable("ROWCOL", ilUtil::switchColor($num,"tblrow2","tblrow1"));
				$num++;

				//$tpl->setVariable("CHECKBOX",ilUtil::formCheckBox("","items[]",$gl_data["ref_id"]));
				$tpl->setVariable("DESCRIPTION", $mep_data["description"]);
				$tpl->setVariable("LAST_CHANGE", ilFormat::formatDate($mep_data["last_update"]));
				$tpl->parseCurrentBlock();
			}

		}
		else
		{
			$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.no_objects_row.html");
			$tpl->setCurrentBlock("tbl_content");
			$tpl->setVariable("ROWCOL", "tblrow1");
			$tpl->setVariable("COLSPAN", "3");
			$tpl->setVariable("TXT_NO_OBJECTS",$this->lng->txt("lo_no_content"));
			$tpl->parseCurrentBlock();
		}

		//$this->showPossibleSubObjects("lrs");

		// create table
		$tbl = new ilTableGUI();
		$tbl->setTemplate($tpl);

		// title & header columns
		$tbl->setTitle($this->lng->txt("objs_mep"),"icon_mep_b.gif",$this->lng->txt("objs_mep"));
		//$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		$tbl->setHeaderNames(array($this->lng->txt("title")));
		$tbl->setHeaderVars(array("title"),
			array("ref_id" => $this->cur_ref_id));
		$tbl->setColumnWidth(array("1%", "99%"));

		// control
		//$tbl->setOrderColumn($_GET["sort_by"]);
		//$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($maxcount);

		// footer
		//$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->disable("footer");
		//$tbl->disable("title");

		// render table
		$tbl->render();
		$tpl->parseCurrentBlock();


		$this->tpl->setCurrentBlock("media_pools");
		$this->tpl->setVariable("MEDIA_POOLS", $tpl->get());
		//$this->tpl->setVariable("LEARNING_RESOURCES", "hh");
		$this->tpl->parseCurrentBlock();

	}


	/**
	* show forums
	*/
	function showForums()
	{
		global $lng, $rbacsystem, $ilias, $rbacreview;

		include_once "classes/class.ilForum.php";
		$frm =& new ilForum();
		$lng->loadLanguageModule("forum");

		$tpl =& new ilTemplate ("tpl.rep_forums.html", true, true);

		// get forums
		foreach($this->forums as $data)
		{
			unset($topicData);

			$frm->setWhereCondition("top_frm_fk = ".$data["obj_id"]);
			$topicData = $frm->getOneTopic();

			if ($topicData["top_num_threads"] > 0)
			{
				$thr_page = "liste";
			}
			else
			{
				$thr_page = "new";
			}


			$tpl->setCurrentBlock("forum_row");

			$tpl->setVariable("TXT_FORUMPATH", $lng->txt("context"));

			//$rowCol = ilUtil::switchColor($z,"tblrow2","tblrow1");
			//$tpl->setVariable("ROWCOL", $rowCol);

			$moderators = "";
			$lpCont = "";
			$lastPost = "";

			// get last-post data
			if ($topicData["top_last_post"] != "")
			{
				$lastPost = $frm->getLastPost($topicData["top_last_post"]);
				$lastPost["pos_message"] = $frm->prepareText($lastPost["pos_message"]);
			}
			// read-access
			// TODO: this will not work :-(
			// We have no ref_id at this point
			if ($rbacsystem->checkAccess("read", $data["ref_id"]))
			{

				// forum title
				if ($topicData["top_num_threads"] < 1 && (!$rbacsystem->checkAccess("write", $data["ref_id"])))
				{
					$tpl->setVariable("TITLE","<b>".$topicData["top_name"]."</b>");
				}
				else
				{
					$tpl->setVariable("TITLE","<a href=\"forums_threads_".$thr_page.".php?ref_id=".
									  $data["ref_id"]."&backurl=forums\">".$topicData["top_name"]."</a>");
				}
				// add to desktop link
				if (!$ilias->account->isDesktopItem($data["ref_id"], "frm"))
				{
					$tpl->setVariable("TO_DESK_LINK", "repository.php?cmd=addToDesk&ref_id=".$this->cur_ref_id.
						"&item_ref_id=".$data["ref_id"].
						"&type=frm&offset=".$_GET["offset"]."&sort_order=".$_GET["sort_order"].
						"&sort_by=".$_GET["sort_by"]);

					$tpl->setVariable("TXT_TO_DESK", $lng->txt("to_desktop"));
				}
				// create-dates of forum
				if ($topicData["top_usr_id"] > 0)
				{
					$moderator = $frm->getUser($topicData["top_usr_id"]);

					$tpl->setVariable("TXT_MODERATORS", $lng->txt("forums_moderators"));
				}

				// when forum was changed ...
				if ($topicData["update_user"] > 0)
				{
					$moderator = $frm->getUser($topicData["update_user"]);

					$tpl->setVariable("LAST_UPDATE_TXT1", $lng->txt("last_change"));
					$tpl->setVariable("LAST_UPDATE_TXT2", strtolower($lng->txt("by")));
					$tpl->setVariable("LAST_UPDATE", $frm->convertDate($topicData["top_update"]));
					$tpl->setVariable("LAST_UPDATE_USER","<a href=\"forums_user_view.php?ref_id=".
									  $this->cur_ref_id."&user=".$topicData["update_user"]."&backurl=repository&offset=".
									  $Start."\">".$moderator->getLogin()."</a>");
				}

				// show content of last-post
				if (is_array($lastPost))
				{
					$last_user = $frm->getUserData($lastPost["pos_usr_id"],$lastPost["import_name"]);
					#if($lastPost["pos_usr_id"])
					#{
					#	$last_user = $frm->getUserData($lastPost["pos_usr_id"],$lastPost["import_name"]);
					#}

					$lpCont = "<a href=\"forums_frameset.php?target=true&pos_pk=".
						$lastPost["pos_pk"]."&thr_pk=".$lastPost["pos_thr_fk"]."&ref_id=".
						$data["ref_id"]."#".$lastPost["pos_pk"]."\">".$lastPost["pos_message"]."</a><br/>".
						strtolower($lng->txt("from"))."&nbsp;";

					if($lastPost["pos_usr_id"])
					{
						$lpCont .= "<a href=\"forums_user_view.php?ref_id=".$this->cur_ref_id."&user=".
							$last_user["usr_id"]."&backurl=repository&offset=".$Start."\">".$last_user["login"]."</a><br/>";
						$lpCont .= $lastPost["pos_date"];
					}
					else
					{
						$lpCont .= $last_user["login"];
						#$lpCont .= $lng->txt("unknown");
					}
				}

				$tpl->setVariable("LAST_POST", $lpCont);

				// get dates of moderators
				if ($topicData["top_mods"] > 0)
				{
					$MODS = $rbacreview->assignedUsers($topicData["top_mods"]);

					for ($i = 0; $i < count($MODS); $i++)
					{
						unset($moderator);
						$moderator = $frm->getUser($MODS[$i]);

						if ($moderators != "")
						{
							$moderators .= ", ";
						}

						$moderators .= "<a href=\"forums_user_view.php?ref_id=".$this->cur_ref_id."&user=".
							$MODS[$i]."&backurl=repository&offset=".$Start."\">".$moderator->getLogin()."</a>";
					}
				}
				$tpl->setVariable("MODS",$moderators);
				$tpl->setVariable("TXT_MODERATORS", $lng->txt("forums_moderators"));

				$tpl->setVariable("FORUM_ID", $topicData["top_pk"]);

			} // if ($rbacsystem->checkAccess("read", $data["ref_id"]))
			else
			{
				// only visible-access
				$tpl->setVariable("TITLE","<b>".$topicData["top_name"]."</b>");

				if (is_array($lastPost))
				{
					$lpCont = $lastPost["pos_message"]."<br/>".$lng->txt("from")." ".$lastPost["lastname"]."<br/>".$lastPost["pos_date"];
				}

				$tpl->setVariable("LAST_POST", $lpCont);

				if ($topicData["top_mods"] > 0)
				{
					$MODS = $rbacreview->assignedUsers($topicData["top_mods"]);

					for ($i = 0; $i < count($MODS); $i++)
					{
						unset($moderator);
						$moderator = $frm->getUser($MODS[$i]);

						if ($moderators != "")
						{
							$moderators .= ", ";
						}

						$moderators .= $moderator->getLogin();
					}
				}
				$tpl->setVariable("MODS",$moderators);
				$tpl->setVariable("TXT_MODERATORS", $lng->txt("forums_moderators"));
			} // else

			// get context of forum
			$PATH = $frm->getForumPath($data["ref_id"]);
			$tpl->setVariable("FORUMPATH",$PATH);

			$tpl->setVariable("DESCRIPTION",$topicData["top_description"]);
			$tpl->setVariable("NUM_THREADS",$topicData["top_num_threads"]);
			$tpl->setVariable("NUM_POSTS",$topicData["top_num_posts"]);
			$tpl->setVariable("NUM_VISITS",$topicData["visits"]);

			$tpl->parseCurrentBlock();

		} // foreach($frm_obj as $data)

		/*
		$tpl->setCurrentBlock("forum_options");
		$tpl->setVariable("TXT_SELECT_ALL", $lng->txt("select_all"));
		$tpl->setVariable("IMGPATH", $tpl->tplPath);
		$tpl->setVariable("FORMACTION", basename($_SERVER["PHP_SELF"])."?ref_id=".$_GET["ref_id"]);
		$tpl->setVariable("TXT_OK",$lng->txt("ok"));
		//$tpl->setVariable("TXT_PRINT", $lng->txt("print"));
		$tpl->setVariable("TXT_EXPORT_HTML", $lng->txt("export_html"));
		$tpl->setVariable("TXT_EXPORT_XML", $lng->txt("export_xml"));
		$tpl->parseCurrentBlock();*/

		$tpl->setCurrentBlock("forums");
		$tpl->setVariable("COUNT_FORUM", $lng->txt("forums_count").": ".$frmNum);
		$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("forums_overview"));
		$tpl->setVariable("TXT_FORUM", $lng->txt("forum"));
		$tpl->setVariable("TXT_TITLE", $lng->txt("title"));
		$tpl->setVariable("TXT_DESCRIPTION", $lng->txt("description"));
		$tpl->setVariable("TXT_NUM_THREADS", $lng->txt("forums_threads"));
		$tpl->setVariable("TXT_NUM_POSTS", $lng->txt("forums_articles"));
		$tpl->setVariable("TXT_NUM_VISITS", $lng->txt("visits"));
		$tpl->setVariable("TXT_LAST_POST", $lng->txt("forums_last_post"));
		$tpl->setVariable("TXT_MODS", $lng->txt("forums_moderators"));
		$tpl->setVariable("FRM_IMG", ilUtil::getImagePath("icon_frm_b.gif"));
		$tpl->setVariable("FRM_TITLE", $lng->txt("forums"));
		$tpl->parseCurrentBlock();

		$this->tpl->setCurrentBlock("forums");
		$this->tpl->setVariable("FORUMS", $tpl->get());
		$this->tpl->parseCurrentBlock();
	}


	/**
	* show groups
	*/
	function showGroups()
	{
		global  $tree, $rbacsystem, $ilias, $lng;

		$tpl =& new ilTemplate("tpl.table.html", true, true);

		$tpl->setVariable("FORMACTION", "obj_location_new.php?new_type=grp&from=grp_list.php");
		$tpl->setVariable("FORM_ACTION_METHOD", "post");
		$tpl->setVariable("ACTIONTARGET", "bottom");

		$maxcount = count($this->groups);

		$cont_arr = array_slice($this->groups, $_GET["offset"], $_GET["limit"]);

		$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.rep_grp_row.html");

		$cont_num = count($cont_arr);

		// render table content data
		if ($cont_num > 0)
		{
			// counter for rowcolor change
			$num = 0;
			foreach ($cont_arr as $cont_data)
			{
				$tpl->setCurrentBlock("tbl_content");
				$newuser = new ilObjUser($cont_data["owner"]);
				// change row color
				$tpl->setVariable("ROWCOL", ilUtil::switchColor($num,"tblrow2","tblrow1"));
				$num++;
				$this->ctrl->setParameterByClass("ilObjGroupGUI", "ref_id", $cont_data["ref_id"]);
				$obj_link = $this->ctrl->getLinkTargetByClass("ilObjGroupGUI");

				$obj_icon = "icon_".$cont_data["type"]."_b.gif";
				$tpl->setVariable("TITLE", $cont_data["title"]);
				$tpl->setVariable("LINK", $obj_link);
				$tpl->setVariable("LINK_TARGET", "bottom");

				// add to desktop link
				if (!$ilias->account->isDesktopItem($cont_data["ref_id"], "grp"))
				{
					$tpl->setVariable("TO_DESK_LINK", "repository.php?cmd=addToDesk&ref_id=".$this->cur_ref_id.
						"&item_ref_id=".$cont_data["ref_id"].
						"&type=grp&offset=".$_GET["offset"]."&sort_order=".$_GET["sort_order"].
						"&sort_by=".$_GET["sort_by"]);

					$tpl->setVariable("TXT_TO_DESK", $lng->txt("to_desktop"));
				}
				//$tpl->setVariable("CHECKBOX",ilUtil::formCheckBox("", "items[]", $cont_data["ref_id"]));
				//$tpl->setVariable("IMG", $obj_icon);
				//$tpl->setVariable("ALT_IMG", $this->lng->txt("obj_".$cont_data["type"]));
				$tpl->setVariable("DESCRIPTION", $cont_data["description"]);
				$tpl->setVariable("OWNER", $newuser->getFullName($cont_data["owner"]));
				//$tpl->setVariable("LAST_CHANGE", $cont_data["last_update"]);
				//$tpl->setVariable("CONTEXTPATH", $this->getContextPath($cont_data["ref_id"]));
				$tpl->parseCurrentBlock();
			}
		}
		else
		{
			$tpl->setCurrentBlock("no_content");
			$tpl->setVariable("TXT_MSG_NO_CONTENT",$this->lng->txt("group_not_available"));
			$tpl->parseCurrentBlock("no_content");
		}

		// create table
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->lng->txt("groups"),"icon_grp_b.gif",$this->lng->txt("groups"));
		$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		$tbl->setHeaderNames(array($this->lng->txt("title"),$this->lng->txt("owner")));
		$tbl->setHeaderVars(array("title","owner"), array("ref_id" => $this->cur_ref_id));
		$tbl->setColumnWidth(array("90%","10%"));
		//$tbl->setOrderColumn($_GET["sort_by"]);
		//$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($maxcount);
		//$this->showActions(true);

		// footer
		//$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->disable("footer");

		// render table
		$tbl->setTemplate($tpl);
		$tbl->render();

		$this->tpl->setCurrentBlock("groups");
		$this->tpl->setVariable("GROUPS", $tpl->get());
		$this->tpl->parseCurrentBlock();
	}

	/**
	* show exercises
	*/
	function showExercises()
	{
		global  $tree, $rbacsystem;

		$tpl =& new ilTemplate("tpl.table.html", true, true);

		$tpl->setVariable("FORMACTION", "obj_location_new.php?new_type=grp&from=grp_list.php");
		$tpl->setVariable("FORM_ACTION_METHOD", "post");
		$tpl->setVariable("ACTIONTARGET", "bottom");

		$maxcount = count($this->exercises);

		$cont_arr = array_slice($this->exercises, $_GET["offset"], $_GET["limit"]);

		$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.rep_exc_row.html");

		$cont_num = count($cont_arr);

		// render table content data
		if ($cont_num > 0)
		{
			// counter for rowcolor change
			$num = 0;
			foreach ($cont_arr as $cont_data)
			{
				$tpl->setCurrentBlock("tbl_content");
				$newuser = new ilObjUser($cont_data["owner"]);
				// change row color
				$tpl->setVariable("ROWCOL", ilUtil::switchColor($num,"tblrow2","tblrow1"));
				$num++;
				$obj_link = "exercise.php?cmd=view&ref_id=".$cont_data["ref_id"];
				$obj_icon = "icon_".$cont_data["type"]."_b.gif";
				$tpl->setVariable("TITLE", $cont_data["title"]);
				$tpl->setVariable("LINK", $obj_link);
				$tpl->setVariable("LINK_TARGET", "bottom");
				//$tpl->setVariable("CHECKBOX",ilUtil::formCheckBox("", "items[]", $cont_data["ref_id"]));
				//$tpl->setVariable("IMG", $obj_icon);
				//$tpl->setVariable("ALT_IMG", $this->lng->txt("obj_".$cont_data["type"]));
				$tpl->setVariable("DESCRIPTION", $cont_data["description"]);
				$tpl->setVariable("OWNER", $newuser->getFullName($cont_data["owner"]));
				$tpl->setVariable("LAST_CHANGE", $cont_data["last_update"]);
				//$tpl->setVariable("CONTEXTPATH", $this->getContextPath($cont_data["ref_id"]));
				$tpl->parseCurrentBlock();
			}
		}
		else
		{
			$tpl->setCurrentBlock("no_content");
			$tpl->setVariable("TXT_MSG_NO_CONTENT",$this->lng->txt("group_not_available"));
			$tpl->parseCurrentBlock("no_content");
		}

		// create table
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->lng->txt("excs"),"icon_exc_b.gif",$this->lng->txt("excs"));
		$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		$tbl->setHeaderNames(array($this->lng->txt("title"),$this->lng->txt("owner")));
		$tbl->setHeaderVars(array("title","owner"), array("ref_id" => $this->cur_ref_id));
		$tbl->setColumnWidth(array("90%","10%"));
		//$tbl->setOrderColumn($_GET["sort_by"]);
		//$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($maxcount);
		//$this->showActions(true);

		// footer
		#$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->disable("footer");

		// render table
		$tbl->setTemplate($tpl);
		$tbl->render();

		$this->tpl->setCurrentBlock("exercises");
		$this->tpl->setVariable("EXERCISES", $tpl->get());
		$this->tpl->parseCurrentBlock();
	}
	
	function showChats()
	{
		global  $tree, $rbacsystem, $ilias;

		$this->lng->loadLanguageModule("chat");

		$tpl =& new ilTemplate("tpl.table.html", true, true);

		$tpl->setVariable("FORMACTION", "obj_location_new.php?new_type=grp&from=grp_list.php");
		$tpl->setVariable("FORM_ACTION_METHOD", "post");
		$tpl->setVariable("ACTIONTARGET", "bottom");

		$maxcount = count($this->chats);

		$cont_arr = array_slice($this->chats, $_GET["offset"], $_GET["limit"]);

		$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.rep_chat_row.html");

		$cont_num = count($cont_arr);

		// render table content data
		if ($cont_num > 0)
		{
			// counter for rowcolor change
			$num = 0;
			foreach ($cont_arr as $cont_data)
			{
				if ($this->rbacsystem->checkAccess('delete', $cont_data["ref_id"]))
				{
					$tpl->setVariable("DELETE_LINK","repository.php?cmd=delete&ref_id=".$cont_data["ref_id"]);
					$tpl->setVariable("TXT_DELETE", $this->lng->txt("delete"));
				}
				if (!$ilias->account->isDesktopItem($cont_data["ref_id"], "chat"))
				{
					$tpl->setVariable("TO_DESK_LINK", "repository.php?cmd=addToDesk&ref_id=".$this->cur_ref_id.
						"&item_ref_id=".$cont_data["ref_id"].
						"&type=chat&offset=".$_GET["offset"]."&sort_order=".$_GET["sort_order"].
						"&sort_by=".$_GET["sort_by"]);

					$tpl->setVariable("TXT_TO_DESK", $this->lng->txt("to_desktop"));
				}

				$tpl->setCurrentBlock("tbl_content");
				$newuser = new ilObjUser($cont_data["owner"]);
				// change row color
				$tpl->setVariable("ROWCOL", ilUtil::switchColor($num,"tblrow2","tblrow1"));
				$num++;
				$obj_link = "chat/chat_rep.php?cmd=view&ref_id=".$cont_data["ref_id"];
				$obj_icon = "icon_".$cont_data["type"]."_b.gif";
				$tpl->setVariable("TITLE", $cont_data["title"]);
				$tpl->setVariable("LINK", $obj_link);
				$tpl->setVariable("LINK_TARGET", "bottom");

				//$tpl->setVariable("CHECKBOX",ilUtil::formCheckBox("", "items[]", $cont_data["ref_id"]));
				//$tpl->setVariable("IMG", $obj_icon);
				//$tpl->setVariable("ALT_IMG", $this->lng->txt("obj_".$cont_data["type"]));
				$tpl->setVariable("DESCRIPTION", $cont_data["description"]);
				$tpl->setVariable("OWNER", $newuser->getFullName($cont_data["owner"]));
				$tpl->setVariable("LAST_CHANGE", $cont_data["last_update"]);
				//$tpl->setVariable("CONTEXTPATH", $this->getContextPath($cont_data["ref_id"]));
				$tpl->parseCurrentBlock();
			}
		}
		else
		{
			$tpl->setCurrentBlock("no_content");
			$tpl->setVariable("TXT_MSG_NO_CONTENT",$this->lng->txt("group_not_available"));
			$tpl->parseCurrentBlock("no_content");
		}

		// create table
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->lng->txt("chats"),"icon_chat_b.gif",$this->lng->txt("chat"));
		$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		$tbl->setHeaderNames(array($this->lng->txt("title"),$this->lng->txt("owner")));
		$tbl->setHeaderVars(array("title","owner"), array("ref_id" => $this->cur_ref_id));
		$tbl->setColumnWidth(array("90%","10%"));
		//$tbl->setOrderColumn($_GET["sort_by"]);
		//$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($maxcount);
		//$this->showActions(true);

		// footer
		#$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->disable("footer");

		// render table
		$tbl->setTemplate($tpl);
		$tbl->render();

		$this->tpl->setCurrentBlock("chats");
		$this->tpl->setVariable("CHATS", $tpl->get());
		$this->tpl->parseCurrentBlock();
	}
		


	function showTests()
  {
		global $ilias;
		
		$this->lng->loadLanguageModule("assessment");
		
		$maxcount = count($this->tests);
		$tests = array_slice($this->tests, $_GET["offset"], $_GET["limit"]);

		$tpl =& new ilTemplate ("tpl.table.html", true, true);

		$test_num = count($tests);

		// render table content data
		if ($test_num > 0)
		{
			$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.rep_tst_row.html");
			// counter for rowcolor change
			$num = 0;
			global $ilDB;
			foreach ($tests as $key => $tst_data) {
				$q = sprintf("SELECT * FROM tst_tests WHERE ref_fi=%s",
					$ilDB->quote($tst_data["ref_id"])
				);
				$result = $ilDB->query($q);
				if ($result->numRows() == 1) {
					$row = $result->fetchRow(DB_FETCHMODE_OBJECT);
					$tests[$key]["complete"] = $row->complete;
				}
				if ($row->test_type_fi == 1) {
					// assessment test. check starting time
					if ($row->starting_time) {
						preg_match("/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/", $row->starting_time, $matches);
						$epoch_time = mktime($matches[4], $matches[5], $matches[6], $matches[2], $matches[3], $matches[1]);
						$now = mktime();
						if ($now < $epoch_time) {
							$tests[$key]["starting_time_not_reached"] = 1;
						}
					}
				}
			}
			
			foreach ($tests as $tst_data)
			{
          $obj_link = "assessment/test.php?cmd=run&ref_id=".$tst_data["ref_id"];

				if ($this->rbacsystem->checkAccess('read',$tst_data["ref_id"]) and ($tst_data["complete"]) and ($tst_data["starting_time_not_reached"] != 1))
				{
					$tpl->setCurrentBlock("tst_read");
					$tpl->setVariable("VIEW_LINK", $obj_link);
					$tpl->setVariable("VIEW_TARGET", "bottom");
					$tpl->setVariable("R_TITLE", $tst_data["title"]);
					$tpl->parseCurrentBlock();
				}
				else
				{
					$tpl->setCurrentBlock("tst_visible");
					$tpl->setVariable("V_TITLE", $tst_data["title"]);
					$tpl->parseCurrentBlock();
				}

				if ($this->rbacsystem->checkAccess('write',$tst_data["ref_id"]))
				{
					$tpl->setCurrentBlock("tst_edit");
					$tpl->setVariable("EDIT_LINK","assessment/test.php?ref_id=".$tst_data["ref_id"]);
					$tpl->setVariable("EDIT_TARGET","bottom");
					$tpl->setVariable("TXT_EDIT", $this->lng->txt("edit"));
					$tpl->parseCurrentBlock();
				}

				if ($this->rbacsystem->checkAccess('delete', $tst_data["ref_id"]))
				{
					$tpl->setCurrentBlock("tst_delete");
					$tpl->setVariable("DELETE_LINK","repository.php?cmd=delete&ref_id=".$tst_data["ref_id"]);
					$tpl->setVariable("TXT_DELETE", $this->lng->txt("delete"));
					$tpl->parseCurrentBlock();
				}

				// add to desktop link
				if (!$ilias->account->isDesktopItem($tst_data["ref_id"], "tst") and ($tst_data["complete"]))
				{
					$tpl->setCurrentBlock("tst_subscribe");
					$tpl->setVariable("SUBSCRIBE_LINK", "repository.php?cmd=addToDesk&ref_id=".$this->cur_ref_id.
						"&item_ref_id=".$tst_data["ref_id"]."&type=tst");

					$tpl->setVariable("TXT_SUBSCRIBE", $this->lng->txt("to_desktop"));
					$tpl->parseCurrentBlock();
				}

				// add anonymous aggregated test results link
				if ($this->rbacsystem->checkAccess('write',$tst_data["ref_id"]) and ($tst_data["complete"]))
				{
					$tpl->setCurrentBlock("tst_anon_eval");
					$tpl->setVariable("ANON_EVAL_LINK", "assessment/test.php?cmd=eval_a&ref_id=".$tst_data["ref_id"]);
					$tpl->setVariable("TXT_ANON_EVAL", $this->lng->txt("tst_anon_eval"));
					$tpl->parseCurrentBlock();
				}

				// add statistical evaluation tool
				if ($this->rbacsystem->checkAccess('write',$tst_data["ref_id"]) and ($tst_data["complete"]))
				{
					$tpl->setCurrentBlock("tst_statistical_evaluation");
					$tpl->setVariable("STATISTICAL_EVALUATION_LINK", "assessment/test.php?cmd=eval_stat&ref_id=".$tst_data["ref_id"]);
					$tpl->setVariable("TXT_STATISTICAL_EVALUATION", $this->lng->txt("tst_statistical_evaluation"));
					$tpl->parseCurrentBlock();
				}

				$tpl->setCurrentBlock("tbl_content");

				// change row color
				$tpl->setVariable("ROWCOL", ilUtil::switchColor($num,"tblrow2","tblrow1"));
				$num++;

				$tpl->setVariable("DESCRIPTION", $tst_data["description"]);
				$tpl->setVariable("LAST_CHANGE", ilFormat::formatDate($tst_data["last_update"]));
				$tpl->parseCurrentBlock();
			}
		}
		else
		{
			$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.no_objects_row.html");
			$tpl->setCurrentBlock("tbl_content");
			$tpl->setVariable("ROWCOL", "tblrow1");
			$tpl->setVariable("COLSPAN", "3");
			$tpl->setVariable("TXT_NO_OBJECTS",$this->lng->txt("lo_no_content"));
			$tpl->parseCurrentBlock();
		}

		//$this->showPossibleSubObjects("lrs");

		// create table
		$tbl = new ilTableGUI();
		$tbl->setTemplate($tpl);

		// title & header columns
		$tbl->setTitle($this->lng->txt("tests"),"icon_tst_b.gif", $this->lng->txt("tests"));
		//$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		$tbl->setHeaderNames(array($this->lng->txt("title")));
		$tbl->setHeaderVars(array("title"),
			array("ref_id" => $this->cur_ref_id));
		$tbl->setColumnWidth(array("100%"));

		// control
		//$tbl->setOrderColumn($_GET["sort_by"]);
		//$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($maxcount);

		// footer
		//$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->disable("footer");
		//$tbl->disable("title");

		// render table
		$tbl->render();
		$tpl->parseCurrentBlock();


		$this->tpl->setCurrentBlock("tests");
		$this->tpl->setVariable("TESTS", $tpl->get());
		//$this->tpl->setVariable("LEARNING_RESOURCES", "hh");
		$this->tpl->parseCurrentBlock();
  }
  
	function showSurveys() {
	}
	
	function showSurveyquestionpools() {
	}

  function showQuestionPools()
  {
		$maxcount = count($this->questionpools);
		$qpool = array_slice($this->questionpools, $_GET["offset"], $_GET["limit"]);

		$tpl =& new ilTemplate ("tpl.table.html", true, true);

		$qpool_num = count($qpool);

		// render table content data
		if ($qpool_num > 0)
		{
			$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.rep_qpl_row.html");
			// counter for rowcolor change
			$num = 0;

			foreach ($qpool as $qpl_data)
			{
          $obj_link = "assessment/questionpool.php?ref_id=" . $qpl_data["ref_id"];

				//if ($this->rbacsystem->checkAccess('read',$qpl_data["ref_id"]))
				//{
				//	$tpl->setCurrentBlock("qpl_read");
				//	$tpl->setVariable("VIEW_LINK", $obj_link);
				//	$tpl->setVariable("VIEW_TARGET", "bottom");
				//	$tpl->setVariable("R_TITLE", $qpl_data["title"]);
				//	$tpl->parseCurrentBlock();
				//}
				//else
				//{
					$tpl->setCurrentBlock("qpl_visible");
					$tpl->setVariable("V_TITLE", $qpl_data["title"]);
					$tpl->parseCurrentBlock();
				//}

				if ($this->rbacsystem->checkAccess('write',$qpl_data["ref_id"]))
				{
					$tpl->setCurrentBlock("qpl_edit");
					$tpl->setVariable("EDIT_LINK","assessment/questionpool.php?ref_id=".$qpl_data["ref_id"]);
					$tpl->setVariable("EDIT_TARGET","bottom");
					$tpl->setVariable("TXT_EDIT", $this->lng->txt("edit"));
					$tpl->parseCurrentBlock();
				}

				if ($this->rbacsystem->checkAccess('delete', $qpl_data["ref_id"]))
				{
					$tpl->setCurrentBlock("qpl_delete");
					$tpl->setVariable("DELETE_LINK","repository.php?cmd=delete&ref_id=".$qpl_data["ref_id"]);
					$tpl->setVariable("TXT_DELETE", $this->lng->txt("delete"));
					$tpl->parseCurrentBlock();
				}
        
				$tpl->setCurrentBlock("tbl_content");

				// change row color
				$tpl->setVariable("ROWCOL", ilUtil::switchColor($num,"tblrow2","tblrow1"));
				$num++;

				$tpl->setVariable("DESCRIPTION", $qpl_data["description"]);
				$tpl->setVariable("LAST_CHANGE", ilFormat::formatDate($qpl_data["last_update"]));
				$tpl->parseCurrentBlock();
			}
		}
		else
		{
			$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.no_objects_row.html");
			$tpl->setCurrentBlock("tbl_content");
			$tpl->setVariable("ROWCOL", "tblrow1");
			$tpl->setVariable("COLSPAN", "3");
			$tpl->setVariable("TXT_NO_OBJECTS",$this->lng->txt("lo_no_content"));
			$tpl->parseCurrentBlock();
		}

		//$this->showPossibleSubObjects("lrs");

		// create table
		$tbl = new ilTableGUI();
		$tbl->setTemplate($tpl);

		// title & header columns
		$tbl->setTitle($this->lng->txt("question_pools"),"icon_qpl_b.gif", $this->lng->txt("question_pools"));
		//$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		$tbl->setHeaderNames(array($this->lng->txt("title")));
		$tbl->setHeaderVars(array("title"),
			array("ref_id" => $this->cur_ref_id));
		$tbl->setColumnWidth(array("100%"));

		// control
		//$tbl->setOrderColumn($_GET["sort_by"]);
		//$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($maxcount);

		// footer
		//$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->disable("footer");
		//$tbl->disable("title");

		// render table
		$tbl->render();
		$tpl->parseCurrentBlock();


		$this->tpl->setCurrentBlock("questionpools");
		$this->tpl->setVariable("QUESTIONPOOLS", $tpl->get());
		//$this->tpl->setVariable("LEARNING_RESOURCES", "hh");
		$this->tpl->parseCurrentBlock();
  }


	/**
	* show Files
	*/
	function showFiles()
	{
		global  $tree, $rbacsystem;

		$tpl =& new ilTemplate("tpl.table.html", true, true);

		$tpl->setVariable("FORMACTION", "obj_location_new.php?new_type=grp&from=grp_list.php");
		$tpl->setVariable("FORM_ACTION_METHOD", "post");
		$tpl->setVariable("ACTIONTARGET", "bottom");

		$maxcount = count($this->files);

		$cont_arr = array_slice($this->files, $_GET["offset"], $_GET["limit"]);

		$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.rep_file_row.html");

		$cont_num = count($cont_arr);

		// render table content data
		if ($cont_num > 0)
		{
			// counter for rowcolor change
			$num = 0;
			foreach ($cont_arr as $cont_data)
			{
				$tpl->setCurrentBlock("tbl_content");
				$newuser = new ilObjUser($cont_data["owner"]);
				// change row color
				$tpl->setVariable("ROWCOL", ilUtil::switchColor($num,"tblrow2","tblrow1"));
				$num++;
				$obj_link = "repository.php?cmd=sendfile&ref_id=".$cont_data["ref_id"];
				$obj_icon = "icon_".$cont_data["type"]."_b.gif";
				$tpl->setVariable("TITLE", $cont_data["title"]);
				$tpl->setVariable("LINK", $obj_link);
				$tpl->setVariable("LINK_TARGET", "bottom");
				//$tpl->setVariable("CHECKBOX",ilUtil::formCheckBox("", "items[]", $cont_data["ref_id"]));
				//$tpl->setVariable("IMG", $obj_icon);
				//$tpl->setVariable("ALT_IMG", $this->lng->txt("obj_".$cont_data["type"]));
				$tpl->setVariable("DESCRIPTION", $cont_data["description"]);
				$tpl->setVariable("OWNER", $newuser->getFullName($cont_data["owner"]));
				$tpl->setVariable("LAST_CHANGE", $cont_data["last_update"]);
				//$tpl->setVariable("CONTEXTPATH", $this->getContextPath($cont_data["ref_id"]));
				$tpl->parseCurrentBlock();
			}
		}
		else
		{
			$tpl->setCurrentBlock("no_content");
			$tpl->setVariable("TXT_MSG_NO_CONTENT",$this->lng->txt("group_not_available"));
			$tpl->parseCurrentBlock("no_content");
		}

		// create table
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->lng->txt("files"),"icon_file_b.gif",$this->lng->txt("files"));
		$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		$tbl->setHeaderNames(array($this->lng->txt("title"),$this->lng->txt("owner")));
		$tbl->setHeaderVars(array("title","owner"), array("ref_id" => $this->cur_ref_id));
		$tbl->setColumnWidth(array("90%","10%"));
		//$tbl->setOrderColumn($_GET["sort_by"]);
		//$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($maxcount);
		//$this->showActions(true);

		// footer
		#$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->disable("footer");

		// render table
		$tbl->setTemplate($tpl);
		$tbl->render();

		$this->tpl->setCurrentBlock("files");
		$this->tpl->setVariable("FILES", $tpl->get());
		$this->tpl->parseCurrentBlock();
	}

	/**
	* show Folders
	*/
	function showFolders()
	{
		global  $tree, $rbacsystem;

		$tpl =& new ilTemplate("tpl.table.html", true, true);

		$tpl->setVariable("FORMACTION", "obj_location_new.php?new_type=grp&from=grp_list.php");
		$tpl->setVariable("FORM_ACTION_METHOD", "post");
		$tpl->setVariable("ACTIONTARGET", "bottom");

		$maxcount = count($this->folders);

		$cont_arr = array_slice($this->folders, $_GET["offset"], $_GET["limit"]);

		$tpl->addBlockfile("TBL_CONTENT", "tbl_content", "tpl.rep_fold_row.html");

		$cont_num = count($cont_arr);

		// render table content data
		if ($cont_num > 0)
		{
			// counter for rowcolor change
			$num = 0;
			foreach ($cont_arr as $cont_data)
			{
				$tpl->setCurrentBlock("tbl_content");
				$newuser = new ilObjUser($cont_data["owner"]);
				// change row color
				$tpl->setVariable("ROWCOL", ilUtil::switchColor($num,"tblrow2","tblrow1"));
				$num++;
				$this->ctrl->setParameterByClass("ilObjFolderGUI", "ref_id", $cont_data["ref_id"]);
				$obj_link = $this->ctrl->getLinkTargetByClass("ilObjFolderGUI");
				$obj_icon = "icon_".$cont_data["type"]."_b.gif";
				$tpl->setVariable("TITLE", $cont_data["title"]);
				$tpl->setVariable("LINK", $obj_link);
				$tpl->setVariable("LINK_TARGET", "bottom");
				//$tpl->setVariable("CHECKBOX",ilUtil::formCheckBox("", "items[]", $cont_data["ref_id"]));
				//$tpl->setVariable("IMG", $obj_icon);
				//$tpl->setVariable("ALT_IMG", $this->lng->txt("obj_".$cont_data["type"]));
				$tpl->setVariable("DESCRIPTION", $cont_data["description"]);
				$tpl->setVariable("OWNER", $newuser->getFullName($cont_data["owner"]));
				$tpl->setVariable("LAST_CHANGE", $cont_data["last_update"]);
				//$tpl->setVariable("CONTEXTPATH", $this->getContextPath($cont_data["ref_id"]));
				$tpl->parseCurrentBlock();
			}
		}
		else
		{
			$tpl->setCurrentBlock("no_content");
			$tpl->setVariable("TXT_MSG_NO_CONTENT",$this->lng->txt("group_not_available"));
			$tpl->parseCurrentBlock("no_content");
		}

		// create table
		$tbl = new ilTableGUI();

		// title & header columns
		$tbl->setTitle($this->lng->txt("folders"),"icon_fold_b.gif",$this->lng->txt("folders"));
		$tbl->setHelp("tbl_help.php","icon_help.gif",$this->lng->txt("help"));
		$tbl->setHeaderNames(array($this->lng->txt("title"),$this->lng->txt("owner")));
		$tbl->setHeaderVars(array("title","owner"), array("ref_id" => $this->cur_ref_id));
		$tbl->setColumnWidth(array("90%","10%"));
		//$tbl->setOrderColumn($_GET["sort_by"]);
		//$tbl->setOrderDirection($_GET["sort_order"]);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setOffset($_GET["offset"]);
		$tbl->setMaxCount($maxcount);
		//$this->showActions(true);

		// footer
		#$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->disable("footer");

		// render table
		$tbl->setTemplate($tpl);
		$tbl->render();

		$this->tpl->setCurrentBlock("folders");
		$this->tpl->setVariable("FOLDERS", $tpl->get());
		$this->tpl->parseCurrentBlock();
	}

	/**
	* set Locator
	*/
	function setLocator()
	{
		global $ilias_locator;

		$a_tree =& $this->tree;
		$a_id = $this->cur_ref_id;

		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

		$path = $a_tree->getPathFull($a_id);

		// this is a stupid workaround for a bug in PEAR:IT
		$modifier = 1;

		if (!empty($_GET["obj_id"]))
		{
			$modifier = 0;
		}

		// ### AA 03.11.10 added new locator GUI class ###
		$i = 1;

		if ($this->gui_obj->object->getType() != "grp" && ($_GET["cmd"] == "delete" || $_GET["cmd"] == "edit"))
		{
			unset($path[count($path) - 1]);
		}

		foreach ($path as $key => $row)
		{

			if ($key < count($path) - $modifier)
			{
				$this->tpl->touchBlock("locator_separator");
			}

			$this->tpl->setCurrentBlock("locator_item");
			if ($row["child"] != $a_tree->getRootId())
			{
				$this->tpl->setVariable("ITEM", $row["title"]);
			}
			else
			{
				$this->tpl->setVariable("ITEM", $this->lng->txt("repository"));
			}

			$this->tpl->setVariable("LINK_ITEM", "repository.php?ref_id=".$row["child"]);

			$this->tpl->parseCurrentBlock();

			$this->tpl->setCurrentBlock("locator");

			// ### AA 03.11.10 added new locator GUI class ###
			// navigate locator
			if ($row["child"] != $a_tree->getRootId())
			{
				$ilias_locator->navigate($i++,$row["title"],"repository.php?ref_id=".$row["child"],"bottom");
			}
			else
			{
				$ilias_locator->navigate($i++,$this->lng->txt("repository"),"repository.php?ref_id=".$row["child"],"bottom");
			}
		}

		$this->tpl->setVariable("TXT_LOCATOR",$debug.$this->lng->txt("locator"));
		$this->tpl->parseCurrentBlock();
	}


	/**
	* show possible subobjects (pulldown menu)
	*
	* @access	public
	*/
	function showPossibleSubObjects($type)
	{
		$found = false;
		
		$d = $this->objDefinition->getCreatableSubObjects($type);

		if (count($d) > 0)
		{
			foreach ($d as $row)
			{
			    $count = 0;
				if ($row["max"] > 0)
				{
					//how many elements are present?
					for ($i=0; $i<count($this->data["ctrl"]); $i++)
					{
						if ($this->data["ctrl"][$i]["type"] == $row["name"])
						{
						    $count++;
						}
					}
				}

				if ($row["max"] == "" || $count < $row["max"])
				{
					if (in_array($row["name"], array("slm", "lm", "grp", "frm", "mep",
						"cat", "glo", "exc", "qpl", "tst", "svy", "spl", "chat", "htlm","fold","file")))
					{
						if ($this->rbacsystem->checkAccess("create", $this->cur_ref_id, $row["name"]))
						{
							$subobj[] = $row["name"];
						}
					}
				}
			}
		}

		if (is_array($subobj))
		{
			$this->tpl->parseCurrentBlock("commands");
			// possible subobjects
			$opts = ilUtil::formSelect("", "new_type", $subobj);
			$this->tpl->setVariable("SELECT_OBJTYPE_REPOS", $opts);
			$this->tpl->setVariable("BTN_NAME_REPOS", "create");
			$this->tpl->setVariable("TXT_ADD_REPOS", $this->lng->txt("add"));
			$this->tpl->parseCurrentBlock();
		}
	}

	function showActionSelect(&$subobj)
	{
		$actions = array("edit" => $this->lng->txt("edit"),
						"addToDesk" => $this->lng->txt("to_desktop"),
						"export" => $this->lng->txt("export"));

		if (is_array($subobj))
		{
			if (in_array("dbk",$subobj) or in_array("lm",$subobj))
			{
				$this->tpl->setVariable("TPLPATH",$this->tpl->tplPath);

				$this->tpl->setCurrentBlock("tbl_action_select");
				$this->tpl->setVariable("SELECT_ACTION",ilUtil::formSelect("","action_type",$actions,false,true));
				$this->tpl->setVariable("BTN_NAME","action");
				$this->tpl->setVariable("BTN_VALUE",$this->lng->txt("submit"));
				$this->tpl->parseCurrentBlock();
			}
		}
	}

	function addToDesk()
	{
		if ($_GET["item_ref_id"] and $_GET["type"])
		{
			$this->ilias->account->addDesktopItem($_GET["item_ref_id"], $_GET["type"]);
			$this->showList();
		}
		else
		{
			if ($_POST["items"])
			{
				foreach ($_POST["items"] as $item)
				{
					$tmp_obj =& $this->ilias->obj_factory->getInstanceByRefId($item);
					$this->ilias->account->addDesktopItem($item, $tmp_obj->getType());
					unset($tmp_obj);
				}
			}

			$this->showList();
		}
	}

	function executeAdminCommand()
	{
		$this->prepareOutput();

		$id = $this->cur_ref_id;
		$cmd = $this->cmd;
		$new_type = $_POST["new_type"]
			? $_POST["new_type"]
			: $_GET["new_type"];

		if (!empty($new_type))	// creation
		{
			if (!$this->rbacsystem->checkAccess("create", $this->cur_ref_id, $new_type))
			{
				$this->ilias->raiseError($this->lng->txt("msg_no_perm_create_object1")." ".$this->lng->txt($new_type."_a")." ".$this->lng->txt("msg_no_perm_create_object2"),$this->ilias->error_obj->MESSAGE);
			}
			else
			{
				$class_name = $this->objDefinition->getClassName($new_type);
				$module = $this->objDefinition->getModule($new_type);
				$module_dir = ($module == "") ? "" : $module."/";
				$class_constr = "ilObj".$class_name."GUI";
				include_once("./".$module_dir."classes/class.ilObj".$class_name."GUI.php");

				$obj =& new $class_constr($data, $id, true, false);

				$method = $cmd."Object";
				$obj->setReturnLocation("save", "repository.php?ref_id=".$this->cur_ref_id);
				$obj->setReturnLocation("cancel", "repository.php?ref_id=".$this->cur_ref_id);
				$obj->setReturnLocation("addTranslation",
					"repository.php?cmd=".$_GET["mode"]."&entry=0&mode=session&ref_id=".$this->cur_ref_id."&new_type=".$_GET["new_type"]);

				$obj->setFormAction("save","repository.php?cmd=post&mode=$cmd&ref_id=".$this->cur_ref_id."&new_type=".$new_type);
				$obj->setTargetFrame("save", "bottom");
//echo "<br>meth:".$method;
				$obj->$method();
			}
		}
		else	// all other commands
		{

		/*	$obj =& ilObjectFactory::getInstanceByRefId($this->cur_ref_id);
			$class_name = $objDefinition->getClassName($obj->getType());
			$module = $objDefinition->getModule($obj->getType());
			$module_dir = ($module == "")
				? ""
				: $module."/";

			$class_constr = "ilObj".$class_name."GUI";
			include_once("./".$module_dir."classes/class.ilObj".$class_name."GUI.php");
			$obj_gui =& new $class_constr("", $this->cur_ref_id, true, false);*/

			switch($cmd)
			{
				case "delete":
					$_POST["id"] = array($this->cur_ref_id);
					$this->gui_obj->setFormAction("delete", "repository.php?cmd=post&ref_id=".$this->cur_ref_id);
					$this->gui_obj->deleteObject();
					break;

				case "cancelDelete":
					$node = $this->tree->getNodeData($this->cur_ref_id);
					$this->gui_obj->setReturnLocation("cancelDelete", "repository.php?ref_id=".$node["parent"]);
					$this->gui_obj->cancelDeleteObject();
					break;

				case "confirmedDelete":
					$node = $this->tree->getNodeData($this->cur_ref_id);
					$this->gui_obj->setReturnLocation("confirmedDelete", "repository.php?ref_id=".$node["parent"]);
					$this->gui_obj->confirmedDeleteObject();
					break;

				case "edit":
					$node = $this->tree->getNodeData($this->cur_ref_id);
					$_POST["id"] = array($this->cur_ref_id);
					$_GET["type"] = $this->gui_obj->object->getType();
					$this->gui_obj->setFormAction("update", "repository.php?cmd=post&mode=$cmd&ref_id=".$this->cur_ref_id);
					$this->gui_obj->editObject();
					break;

				case "cancel":
					$node = $this->tree->getNodeData($this->cur_ref_id);
					$this->gui_obj->setReturnLocation("cancel", "repository.php?ref_id=".$node["parent"]);
					$this->gui_obj->cancelObject();
					break;

				case "update":
					$node = $this->tree->getNodeData($this->cur_ref_id);
					$this->gui_obj->setReturnLocation("update", "repository.php?ref_id=".$node["parent"]);
					$this->gui_obj->updateObject();
					break;

				case "addTranslation":
					$this->gui_obj->setReturnLocation("addTranslation",
						"repository.php?cmd=".$_GET["mode"]."&entry=0&mode=session&ref_id=".$this->cur_ref_id);
					$this->gui_obj->addTranslationObject();
					break;

				case "sendfile":
					$this->gui_obj->object->sendfile();
					break;
			}
		}

		$this->tpl->show();
	}

	function save()
	{
		$this->executeAdminCommand();
	}

	function create()
	{
		$this->executeAdminCommand();
	}

	function cancel()
	{
		$this->executeAdminCommand();
	}

	function delete()
	{
		$this->executeAdminCommand();
	}

	function cancelDelete()
	{
		$this->executeAdminCommand();
	}

	function confirmedDelete()
	{
		$this->executeAdminCommand();
	}

	function addTranslation()
	{
		$this->executeAdminCommand();
	}

	function sendfile()
	{
		$this->executeAdminCommand();
	}
	
	function edit()
	{
		$this->executeAdminCommand();
	}

	function update()
	{
		$this->executeAdminCommand();
	}
} // END class.ilRepository

?>
