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

include_once("classes/class.ilTableGUI.php");
//include_once("classes/class.ilObjGroupGUI.php");	// costs 887816 Bytes
//include_once("classes/class.ilObjFolderGUI.php");	// costs 137416 Bytes
//include_once("classes/class.ilObjFolder.php");
//include_once("classes/class.ilObjFileGUI.php");		// costs 122224 Bytes
//include_once("classes/class.ilObjFile.php");
include_once("classes/class.ilTabsGUI.php");
//include_once("classes/class.ilObjUserGUI.php");		// costs 789880 Bytes
//include_once("classes/class.ilObjUserFolderGUI.php");	// costs 737696 Bytes
//include_once("classes/class.ilObjRoleGUI.php");			// costs 827840 Bytes
include_once("payment/classes/class.ilPaymentObject.php");
//include_once("./ilinc/classes/class.ilObjiLincCourseGUI.php");		// costs 715832 Bytes
//include_once("./ilinc/classes/class.ilObjiLincClassroomGUI.php");	// costs > 348488 Bytes


/**
* Class ilRepositoryGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilRepositoryGUI: ilObjGroupGUI, ilObjFolderGUI, ilObjFileGUI, ilObjCourseGUI, ilCourseObjectivesGUI
* @ilCtrl_Calls ilRepositoryGUI: ilObjSAHSLearningModuleGUI, ilObjChatGUI, ilObjForumGUI
* @ilCtrl_Calls ilRepositoryGUI: ilObjLearningModuleGUI, ilObjDlBookGUI, ilObjGlossaryGUI
* @ilCtrl_Calls ilRepositoryGUI: ilObjQuestionPoolGUI, ilObjSurveyQuestionPoolGUI, ilObjTestGUI
* @ilCtrl_Calls ilRepositoryGUI: ilObjSurveyGUI, ilObjExerciseGUI, ilObjMediaPoolGUI, ilObjFileBasedLMGUI
* @ilCtrl_Calls ilRepositoryGUI: ilObjCategoryGUI, ilObjUserGUI, ilObjRoleGUI, ilObjUserFolderGUI
* @ilCtrl_Calls ilRepositoryGUI: ilObjiLincCourseGUI, ilObjiLincClassroomGUI
* @ilCtrl_Calls ilRepositoryGUI: ilObjRootFolderGUI
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
			$_GET, $ilCtrl, $ilLog;;
//var_dump($_SESSION['il_rep_clipboard']);
		$this->lng =& $lng;
		$this->ilias =& $ilias;
		$this->tpl =& $tpl;
		$this->tree =& $tree;
		$this->rbacsystem =& $rbacsystem;
		$this->objDefinition =& $objDefinition;

		$this->ctrl =& $ilCtrl;

		$this->ctrl->saveParameter($this, array("ref_id"));
		if (!ilUtil::isAPICall())
			$this->ctrl->setReturn($this,"");

		// determine current ref id and mode
		//if (!empty($_GET["ref_id"]) && empty($_GET["getlast"]))
//echo "-".$this->ctrl->getCmd()."-".$_GET["cmd"]."-";
		if (!empty($_GET["ref_id"]) || $this->ctrl->getCmd() == "showTree")
		{
//echo "5";
			$this->cur_ref_id = $_GET["ref_id"];
		}
		else
		{
//echo "1";
			if (!empty($_SESSION["il_rep_ref_id"]) && !empty($_GET["getlast"]))
			{
				$this->cur_ref_id = $_SESSION["il_rep_ref_id"];
			}
			else
			{
				$this->cur_ref_id = $this->tree->getRootId();

				if ($_GET["cmd"] != "" && $_GET["cmd"] != "frameset")
				{
//echo "hhh";
					$get_str = $post_str = "";
					foreach($_GET as $key => $value)
					{
						$get_str.= "-$key:$value";
					}
					foreach($_POST as $key => $value)
					{
						$post_str.= "-$key:$value";
					}
					$ilLog->write("Repository: command called without ref_id.".
						"GET:".$get_str."-POST:".$post_str, $ilLog->WARNING);
				}
				//if ($_GET["cmd"] != "frameset")
				//{
					$_GET = array();
				//}
				//else
				//{
				//	$_GET = array("cmd" => "frameset");
				//}
				$_POST = array();
				$this->ctrl->setCmd("frameset");
			}
		}

		// set current repository view mode
		if (!empty($_GET["set_mode"]))
		{
			$_SESSION["il_rep_mode"] = $_GET["set_mode"];
			if ($this->ilias->account->getId() != ANONYMOUS_USER_ID)
			{
				$this->ilias->account->writePref("il_rep_mode", $_GET["set_mode"]);
			}
		}

		// get user setting
		if ($_SESSION["il_rep_mode"] == "")
		{
			if ($this->ilias->account->getId() != ANONYMOUS_USER_ID)
			{
				$_SESSION["il_rep_mode"] = $this->ilias->account->getPref("il_rep_mode");
			}
		}

		// if nothing set, get default view
		if ($_SESSION["il_rep_mode"] == "")
		{
			$_SESSION["il_rep_mode"] = $this->ilias->getSetting("default_repository_view");
		}

		$this->mode = ($_SESSION["il_rep_mode"] != "")
			? $_SESSION["il_rep_mode"]
			: "flat";

		if (!$tree->isInTree($this->cur_ref_id) && $this->ctrl->getCmd() != "showTree")
		{
//echo "-".$this->cur_ref_id."-";
			$this->cur_ref_id = $this->tree->getRootId();
//echo "-".$this->cur_ref_id."-";
			// check wether command has been called with
			// item that is not in tree
			if ($_GET["cmd"] != "" && $_GET["cmd"] != "frameset")
			{
				$get_str = $post_str = "";
				foreach($_GET as $key => $value)
				{
					$get_str.= "-$key:$value";
				}
				foreach($_POST as $key => $value)
				{
					$post_str.= "-$key:$value";
				}
				$ilLog->write("Repository: command called with ref_id that is not in tree.".
					"GET:".$get_str."-POST:".$post_str, $ilLog->WARNING);
			}
			$_GET = array();
			$_POST = array();
			$this->ctrl->setCmd("frameset");
		}

		//if ($_GET["cmd"] != "delete" && $_GET["cmd"] != "edit"
		//	&& ($this->object->getType() == "cat" || $this->object->getType() == "root" || $this->object->getType() == "grp"))
		if ($this->ctrl->getCmd() != "showTree" &&
			$rbacsystem->checkAccess("read", $this->cur_ref_id))
		{
			$type = ilObject::_lookupType($this->cur_ref_id, true);
			if ($type == "cat" || $type == "grp" || $type == "crs"
				|| $type == "root")
			{
				$_SESSION["il_rep_ref_id"] = $this->cur_ref_id;
			}
		}

		$this->categories = array();
		$this->learning_resources = array();
		$this->forums = array();
		$this->groups = array();
		$this->courses = array();
		$this->glossaries = array();
		$this->exercises = array();
		$this->questionpools = array();
		$this->surveys = array();
		$this->surveyquestionpools = array();
		$this->tests = array();
		$this->files = array();
		$this->folders = array();
		$this->media_pools = array();
		$this->ilinc_courses = array();
		$this->link_resources = array();
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $tree, $rbacsystem, $ilias, $lng;

		$next_class = $this->ctrl->getNextClass($this);

		// check creation
		$new_type = $_POST["new_type"]
			? $_POST["new_type"]
			: $_GET["new_type"];

		if (!empty($new_type))
		{
			$obj_type = $new_type;
			$class_name = $this->objDefinition->getClassName($obj_type);
			$next_class = strtolower("ilObj".$class_name."GUI");
		}
		else if (empty($next_class))
		{
			// get GUI of current object
			$obj_type = ilObject::_lookupType($this->cur_ref_id,true);
			$class_name = $this->objDefinition->getClassName($obj_type);
			$next_class = strtolower("ilObj".$class_name."GUI");
		}

		$cmd = $this->ctrl->getCmd();
//echo "-$cmd-".$_GET["cmd"];
		if (($cmd == "frameset" || $_GET["rep_frame"] == 1) && $_SESSION["il_rep_mode"] == "tree")
		{
			$next_class = "";
			$cmd = "frameset";
		}
		else if ($cmd == "frameset" && $_SESSION["il_rep_mode"] != "tree")
		{
			$this->ctrl->setCmd("");
			$cmd = "";
		}
		
		if ($cmd == "showTree" || $cmd == "linkSelector" || $cmd == "linkChilds")
		{
			$next_class = "";
		}
		
//echo "<br>cmd:$cmd:nextclass:$next_class:";
		switch ($next_class)
		{
			case "ilobjusergui":
				include_once("./classes/class.ilObjUserGUI.php");

				if(!$_GET['obj_id'])
				{
					$this->gui_obj = new ilObjUserGUI("",$_GET['ref_id'],true, false);

					$this->prepareOutput(false);
					$ret =& $this->ctrl->forwardCommand($this->gui_obj);
				}
				else
				{
					$this->gui_obj = new ilObjUserGUI("", $_GET['obj_id'],false, false);

					$this->prepareOutput(false);
					$ret =& $this->ctrl->forwardCommand($this->gui_obj);
				}
				$this->tpl->show();
				break;
				
			case "ilobjuserfoldergui":
				include_once("./classes/class.ilObjUserFolderGUI.php");

				$this->gui_obj = new ilObjUserFolderGUI("", $_GET['ref_id'],true, false);

				$this->prepareOutput(false);
				$ret =& $this->ctrl->forwardCommand($this->gui_obj);
				$this->tpl->show();
				break;
				
			case "ilobjrootfoldergui":
			case "ilobjcategorygui":
			case "ilobjgroupgui":
			case "ilobjcoursegui":
			case "ilobjfilegui":
			case "ilobjforumgui":
			case "ilobjfoldergui":
			case "ilobjilinccoursegui":
			case "ilobjfilegui":

			//case "ilobjmediapoolgui":					// doesnt work, why?
				// get file path for class name

				$class_path = $this->ctrl->lookupClassPath($next_class);

				// get gui class instance
				include_once($class_path);
				$class_name = $this->ctrl->getClassForClasspath($class_path);
				$this->gui_obj = new $class_name("", $this->cur_ref_id, true, false);

				// special treatment for old admin compliance
				// to do: get rid of it...
				$this->cmd_admin_compliance($cmd, false);

				$tabs_out = ($new_type == "")
					? true
					: false;
					
				// forward command
				if ($_GET["mode"] == "create" || $cmd == "linkSelector")
				{
					$tabs_out = false;
				}
				$this->prepareOutput($tabs_out);

				$ret =& $this->ctrl->forwardCommand($this->gui_obj);

				$html = $this->gui_obj->getHTML();
				if ($html != "")
				{
					$this->tpl->setVariable("OBJECTS", $html);
				}
				$this->tpl->show();

				break;

			/*
			case "ilobjmediapoolgui":
				include_once("./content/classes/class.ilObjMediaPoolGUI.php");
				$this->gui_obj = new ilObjMediaPoolGUI("", $this->cur_ref_id, true, false);

				$this->ctrl->setCmd($this->ctrl->getCmd()."Object");
				$this->prepareOutput(false);
				$ret =& $this->ctrl->forwardCommand($this->gui_obj);

				$this->tpl->show();
				break;*/

			default:

				// process repository frameset
				if ($cmd == "frameset")
				{
					if ($_SESSION["il_rep_mode"] == "tree")
					{
						$this->frameset();
						return;
					}
					$cmd = "";
					$this->ctrl->setCmd("");
				}
				
				if ($cmd == "showTree")
				{
					$this->showTree();
					return;
				}
				
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
//echo "0-$cmd-$obj_type-";
				if (empty($cmd))
				{
					if($obj_type == "crs" or $obj_type == 'fold' or $obj_type == 'grp' or
					   $obj_type == 'frm' or $obj_type == 'crsg')
					{
//echo "1-$obj_type-";
						$this->prepareOutput();
						$this->ctrl->forwardCommand($this->gui_obj);
						$this->tpl->show();
						break;
					}
					else
					{
//echo "A-$cmd-$obj_type-";
						$cmd = $this->ctrl->getCmd("");
					}
					//$next_class = "";
				}
//echo "2-$cmd-$obj_type-";
				// check read access for category
				if ($this->cur_ref_id > 0 && !$rbacsystem->checkAccess("read", $this->cur_ref_id))
				{
//echo "2";
					$_SESSION["il_rep_ref_id"] = "";
					$ilias->raiseError($lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
					$this->tpl->show();
				}
				else
				{
					$this->cmd = $cmd;
					$this->$cmd();
				}
				break;
		}
	}

	/**
	* output tree frameset
	*/
	function frameset()
	{
		$this->tpl = new ilTemplate("tpl.rep_frameset.html", false, false);
		if ($_GET["rep_frame"] == 1)
		{
			$this->tpl->setVariable("SRC_CONTENT",
				str_replace("rep_frame", "rep_frame_done", $_SERVER["REQUEST_URI"]));
		}
		else
		{
			$this->tpl->setVariable("SRC_CONTENT",
				"repository.php?getlast=true&ref_id=".$this->cur_ref_id);
		}
		$this->tpl->setVariable("REF_ID",$this->cur_ref_id);
		$this->tpl->show();
	}

	function prepareOutput($a_tabs_out = true)
	{
		// output objects
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
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
		$this->setHeader($a_tabs_out);
	}


	/**
	* display tree view
	*/
	function showTree()
	{
		$this->tpl = new ilTemplate("tpl.main.html", true, true);
		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());

		//$this->tpl = new ilTemplate("tpl.explorer.html", false, false);
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");

		include_once ("classes/class.ilRepositoryExplorer.php");
		$exp = new ilRepositoryExplorer("repository.php?cmd=goto");
		$exp->setExpandTarget("repository.php?cmd=showTree");
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

		$this->tpl->setCurrentBlock("content");
		//$this->tpl->setVariable("TXT_EXPLORER_HEADER", $this->lng->txt("repository"));
		$this->tpl->setVariable("EXP_REFRESH", $this->lng->txt("refresh"));
		$this->tpl->setVariable("EXPLORER", $output);
		//$this->tpl->setVariable("ACTION", "repository.php?repexpand=".$_GET["repexpand"]);
		$this->tpl->parseCurrentBlock();
		$this->tpl->show(false);
	}


	/**
	* display header section (header, description, tree/flat icon)
	*/
	function setHeader($a_tabs_out = true)
	{
		if ($this->cur_ref_id == $this->tree->getRootId())
		{
			$this->tpl->setVariable("HEADER",  $this->lng->txt("repository"));
			if ($a_tabs_out)
			{
				$this->showPossibleSubObjects("root");
			}
		}
		else
		{
			$this->tpl->setVariable("HEADER",  $this->gui_obj->object->getTitle());

			$desc = ($this->gui_obj->object->getLongDescription())
				? $this->gui_obj->object->getLongDescription()
				: "";
			$desc = nl2br($desc);

			$this->tpl->setVariable("H_DESCRIPTION",  $desc);
			if ($a_tabs_out)
			{
				$this->showPossibleSubObjects($this->gui_obj->object->getType());
			}
		}
		
		// header icon
		$this->tpl->setCurrentBlock("header_image");
		$ic_type = ilObject::_lookupType($this->cur_ref_id, true);
		$icon = ilUtil::getImagePath("icon_".$ic_type."_b.gif");

		// custom icon
		if ($this->ilias->getSetting("custom_icons") &&
			in_array($ic_type, array("cat","grp","crs")))
		{
			require_once("classes/class.ilContainer.php");
			if (($path = ilContainer::_lookupIconPath(ilObject::_lookupObjId($this->cur_ref_id), "big")) != "")
			{
				$icon = $path;
			}
		}

		$this->tpl->setVariable("IMG_HEADER", $icon);

		$this->tpl->parseCurrentBlock();
		$this->tpl->setCurrentBlock("content");

		if ($this->cur_ref_id != $this->tree->getRootId() && $a_tabs_out &&
			($this->gui_obj->object->getType() != "frm"))
		{
			$par_id = $this->tree->getParentId($this->cur_ref_id);
			$this->tpl->setCurrentBlock("top");
			$this->tpl->setVariable("LINK_TOP", "repository.php?ref_id=".$par_id);
			$this->tpl->setVariable("IMG_TOP",ilUtil::getImagePath("ic_top.gif"));
			$this->tpl->parseCurrentBlock();
		}

		if ($a_tabs_out && ($this->gui_obj->object->getType() != "frm"))
		{
			$this->setAdminTabs();
			
			$s_mode = ($_SESSION["il_rep_mode"] == "flat")
				? "tree"
				: "flat";
			$this->tpl->setCurrentBlock("tree_mode");
			$this->tpl->setVariable("LINK_MODE", "repository.php?cmd=frameset&set_mode=".$s_mode."&ref_id=".$this->cur_ref_id);
			$this->tpl->setVariable("IMG_TREE",ilUtil::getImagePath("ic_".$s_mode."view.gif"));
			$this->tpl->parseCurrentBlock();
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
			// add info tab to all objects
			//$tabs_gui->addTarget("info_short",$this->ctrl->getLinkTarget($this->gui_obj, "info"), "info", get_class($this->gui_obj));

			// output tabs
			$this->tpl->setVariable("TABS", $tabs_gui->getHTML());
		}
	}

	/**
	* get "visible only" item explanation link
	*
	* (html due to performance reasons)
	*/
	function getVisibleOnly()
	{
		$this->visible_only_items = true;
		return "<a href=\"#visible_only_expl\">[*]</a>";
	}

	/**
	* show explanation message for items that are
	* only visible but not readable
	*/
	function showVisibleOnlyMessage()
	{
		$this->tpl->setCurrentBlock("visible_items_message");
		if ($this->ilias->account->getId() != ANONYMOUS_USER_ID)
		{
			$this->tpl->setVariable("VIS_ITEMS_MESSAGE", $this->lng->txt("no_access_item"));
		}
		else
		{
			$this->tpl->setVariable("VIS_ITEMS_MESSAGE", $this->lng->txt("no_access_item_public"));
		}
		$this->tpl->parseCurrentBlock();
	}

	/**
	* set Locator
	*/
	function setLocator()
	{
		global $ilias_locator;

		
		// special forum handling (forums should complete go to
		// a separate module and not use repository.php)
		if ($this->gui_obj->object->getType() == "frm")
		{
			require_once("classes/class.ilForumLocatorGUI.php");
			$frm_loc =& new ilForumLocatorGUI();
			$frm_loc->setRefId($this->cur_ref_id);
			$frm_loc->setForum($frm);
			$frm_loc->display();
			return;
		}
		
		$a_tree =& $this->tree;
		$a_id = $this->cur_ref_id;

		$this->tpl->addBlockFile("LOCATOR", "locator", "tpl.locator.html");

		$path = $a_tree->getPathFull($a_id);

		// this is a stupid workaround for a bug in PEAR:IT
		$modifier = 1;

		// ... and to make it more stupid ...
		if (!empty($_GET["obj_id"]) and $_GET['cmdClass'] != 'ilobjusergui' and $_GET['cmdClass'] != 'ilobjcategorygui')
		{
			$modifier = 0;
		}

		// ### AA 03.11.10 added new locator GUI class ###
		$i = 1;

		/* possible deprecated
		if ($this->gui_obj->object->getType() != "grp" && ($_GET["cmd"] == "delete" || $_GET["cmd"] == "edit"))
		{
			unset($path[count($path) - 1]);
		}*/

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

			// NOT NICE 
			if($row["type"] == "crs")
			{
				$this->ctrl->setParameterByClass("ilObjCourseGUI","ref_id",$row["child"]);
				$this->tpl->setVariable("LINK_ITEM",$this->ctrl->getLinkTargetByClass("ilObjCourseGUI"));
			}
			else
			{
				$this->tpl->setVariable("LINK_ITEM","repository.php?ref_id=".$row["child"]);
			}
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
		$cmd = ($this->cmd != "")
			? $this->cmd
			: $this->ctrl->getCmd();

		if ($cmd != "" && $cmd != "showList" && $cmd != "render"
			&& $cmd != "view")
		{
			return;
		}

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
					if (in_array($row["name"], array("sahs", "alm", "hlm", "lm", "grp", "frm", "mep","crs",
													 "cat", "glo", "dbk","exc", "qpl", "tst", "svy", "spl", "chat", 
													 "htlm","fold","linkr","file","icrs","icla","crsg",'webr')))
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
			$this->tpl->parseCurrentBlock("add_commands");
			$this->tpl->setVariable("H_FORMACTION",  "repository.php?ref_id=".$this->cur_ref_id.
				"&cmd=post");
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

	function addToDeskCourse()
	{
		global $tree;

		/*$tmp_obj =& $this->ilias->obj_factory->getInstanceByRefId($this->cur_ref_id);

		while ($tmp_obj->getType() != 'crs')
		{
		    $parent_ref_id = $tree->getParentId($tmp_obj->getRefId());
			$tmp_obj =& $this->ilias->obj_factory->getInstanceByRefId($parent_ref_id);
		}*/

		if ($_GET["item_ref_id"] and $_GET["type"])
		{
			$this->ilias->account->addDesktopItem($_GET["item_ref_id"], $_GET["type"]);
			//$this->showList();
		}
		else
		{
			if ($_POST["items"])
			{
				foreach ($_POST["items"] as $item)
				{
					$tmp_obj_item =& $this->ilias->obj_factory->getInstanceByRefId($item);
					$this->ilias->account->addDesktopItem($item, $tmp_obj_item->getType());
					unset($tmp_obj_item);
				}
			}
			//$this->showList();
		}
//		include_once("./course/classes/class.ilObjCourseGUI.php");
//var_dump($_GET["item_ref_id"],$this->cur_ref_id);exit;
//		$this->gui_obj =& new ilObjCourseGUI("",$tmp_obj->getRefId(),true,false);

		$this->prepareOutput();
		$ret =& $this->gui_obj->viewObject();
		$this->tpl->show();
	}

	/**
	* execute administration command
	*/
	function executeAdminCommand()
	{
		$cmd = $this->cmd;
		$tabs_out = true;

		if ($cmd == "delete" || $cmd == "cancelDelete" || $cmd == "confirmedDelete" ||
			$cmd == "create" || $cmd == "save" || $cmd=="importFile" ||
			$cmd == "cloneAll" || $_GET["mode"] == "create" ||
			$cmd == "linkSelector" || $cmd == "importVerifiedFile" ||  
			$cmd == "cancelImport" )
		{
			$tabs_out = false;
		}
		$this->prepareOutput($tabs_out);


		$id = $this->cur_ref_id;
		$new_type = $_POST["new_type"]
			? $_POST["new_type"]
			: $_GET["new_type"];

		if (!empty($new_type))	// creation
		{
			if (!$this->rbacsystem->checkAccess("create", $this->cur_ref_id, $new_type))
			{
				$this->ilias->raiseError($this->lng->txt("msg_no_perm_create_object1")." ".
										 $this->lng->txt($new_type."_a")." ".$this->lng->txt("msg_no_perm_create_object2"),
										 $this->ilias->error_obj->MESSAGE);
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
				//$obj->setReturnLocation("save", "repository.php?ref_id=".$this->cur_ref_id);
				$obj->setReturnLocation("cancel", "repository.php?ref_id=".$this->cur_ref_id);
				$obj->setReturnLocation("addTranslation",
					"repository.php?cmd=".$_GET["mode"]."&entry=0&mode=session&ref_id=".$this->cur_ref_id."&new_type=".$_GET["new_type"]);

				$obj->setFormAction("save","repository.php?cmd=post&mode=$cmd&ref_id=".$this->cur_ref_id."&new_type=".$new_type);
//$obj->setTargetFrame("save", "bottom");
				//$this->ctrl->setCmdClass(strtolower("Obj".$class_name."GUI"));
				//$this->ctrl->setCmd($method);
				//$this->ctrl->forwardCommand($obj);
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

			$this->cmd_admin_compliance($cmd);

		}

		$this->tpl->show();
	}


	/**
	* old admin command handling compliance
	*/
	function cmd_admin_compliance($cmd, $execute = true)
	{
		// object creation
		$new_type = $_POST["new_type"]
			? $_POST["new_type"]
			: $_GET["new_type"];
		if (!empty($new_type))
		{
			if (!$this->rbacsystem->checkAccess("create", $this->cur_ref_id, $new_type))
			{
				$this->ilias->raiseError($this->lng->txt("msg_no_perm_create_object1")." ".
										 $this->lng->txt($new_type."_a")." ".$this->lng->txt("msg_no_perm_create_object2"),
										 $this->ilias->error_obj->MESSAGE);
			}
			else
			{
				//$this->gui_obj->setReturnLocation("save", "repository.php?ref_id=".$this->cur_ref_id);
				$this->gui_obj->setReturnLocation("cancel", "repository.php?ref_id=".$this->cur_ref_id);
				$this->gui_obj->setReturnLocation("addTranslation",
					"repository.php?cmd=".$_GET["mode"]."&entry=0&mode=session&ref_id=".$this->cur_ref_id."&new_type=".$_GET["new_type"]);

				$this->gui_obj->setFormAction("save","repository.php?cmd=post&mode=$cmd&ref_id=".$this->cur_ref_id."&new_type=".$new_type);
			}
		}

		// other commands
		switch($cmd)
		{
			case "cut":
				$_POST["cmd"]["cut"] = "cut";
				$_POST["id"] = ($_GET["item_ref_id"] != "")
					? array($_GET["item_ref_id"])
					: $_POST["rep_item_id"];
				$this->gui_obj->setReturnLocation("cut", "repository.php?ref_id=".$this->cur_ref_id);
				break;

			case "link":
				$_POST["cmd"]["link"] = "link";
				$_POST["id"] = ($_GET["item_ref_id"] != "")
					? array($_GET["item_ref_id"])
					: $_POST["rep_item_id"];
				$this->gui_obj->setReturnLocation("link", "repository.php?ref_id=".$this->cur_ref_id);
				break;

			case "delete":
				$_POST["id"] = ($_GET["item_ref_id"] != "")
					? array($_GET["item_ref_id"])
					: $_POST["rep_item_id"];
				$this->gui_obj->setFormAction("delete", "repository.php?cmd=post&ref_id=".$this->cur_ref_id);
				if ($execute)
				{
					$this->gui_obj->deleteObject();
				}
				break;

			case "cancelDelete":
				$this->gui_obj->setReturnLocation("cancelDelete", "repository.php?ref_id=".$this->cur_ref_id);
				if ($execute)
				{
					$this->gui_obj->cancelDeleteObject();
				}
				break;

			case "confirmedDelete":
				//$node = $this->tree->getNodeData($this->cur_ref_id);
				$this->gui_obj->setReturnLocation("confirmedDelete", "repository.php?ref_id=".$this->cur_ref_id);
				if ($execute)
				{
					$this->gui_obj->confirmedDeleteObject();
				}
				break;

			case "edit":
				$node = $this->tree->getNodeData($this->cur_ref_id);
				$_POST["id"] = array($this->cur_ref_id);
				$_GET["type"] = $this->gui_obj->object->getType();
				$this->gui_obj->setFormAction("update", "repository.php?cmd=post&mode=$cmd&ref_id=".$this->cur_ref_id);
				if ($execute)
				{
					$this->gui_obj->editObject();
				}
				break;

			case "cancel":
				$node = $this->tree->getNodeData($this->cur_ref_id);
				//$this->gui_obj->setReturnLocation("cancel", "repository.php?ref_id=".$node["parent"]);
				if ($execute)
				{
					$this->gui_obj->cancelObject(true);
				}
				break;

			case "update":
				$node = $this->tree->getNodeData($this->cur_ref_id);
				//$this->gui_obj->setReturnLocation("update", "repository.php?ref_id=".$node["parent"]);
				if ($execute)
				{
					$this->gui_obj->updateObject();
				}
				break;

			case "clear":
				$this->gui_obj->setReturnLocation("clear", "repository.php?ref_id=".$this->cur_ref_id);
				if ($execute)
				{
					$this->gui_obj->clearObject();
				}
				break;

			case "paste":
				$this->gui_obj->setReturnLocation("paste", "repository.php?ref_id=".$this->cur_ref_id);
				if ($execute)
				{
					$this->gui_obj->pasteObject();
				}
				break;

			case "addTranslation":
				$this->gui_obj->setReturnLocation("addTranslation",
					"repository.php?cmd=".$_GET["mode"]."&entry=0&mode=session&ref_id=".$this->cur_ref_id);
				if ($execute)
				{
					$this->gui_obj->addTranslationObject();
				}
				break;

			case "sendfile":
				// PAYMENT STUFF
				// check if object is purchased
				include_once './payment/classes/class.ilPaymentObject.php';
				include_once './classes/class.ilSearch.php';

				if(!ilPaymentObject::_hasAccess($_GET['ref_id']))
				{
					ilUtil::redirect('./payment/start_purchase.php?ref_id='.$_GET['ref_id']);
				}
				if(!ilSearch::_checkParentConditions($_GET['ref_id']))
				{
					$this->ilias->raiseError($this->lng->txt('access_denied'),$ilias->error_obj->WARNING);
				}
				$this->gui_obj->object->sendfile($_GET["hist_id"]);
				break;
		}
	}

	function save()
	{
		$this->executeAdminCommand();
	}

	function create()
	{
		$this->executeAdminCommand();
	}

	function importFile()
	{
		$this->executeAdminCommand();
	}

	function importVerifiedFile()
	{
		$this->executeAdminCommand();
	}

	function cloneAll()
	{
		$this->executeAdminCommand();
	}

	function import()
	{
		$this->executeAdminCommand();
	}

	function cancel()
	{
		$this->executeAdminCommand();
	}
	
	function cancelImport()
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

	function clear()
	{
		$this->executeAdminCommand();
	}

	function paste()
	{
		$this->executeAdminCommand();
	}

	function update()
	{
		$this->executeAdminCommand();
	}

	function copySelector()
	{
		if(is_array($_POST["cmd"]))
		{
			$_SESSION["copy_new_type"] = $_POST["new_type"];
		}
		if($_POST['new_type'] == 'cat')
		{
			$this->lng->loadLanguageModule('crs');

			sendInfo($this->lng->txt('crs_copy_cat_not_allowed'),true);
			$this->ctrl->redirect($this);
		}


		include_once ("classes/class.ilRepositoryCopySelector.php");

		$this->prepareOutput();
		$this->tpl->setCurrentBlock("content");
		$this->tpl->addBlockFile("OBJECTS", "objects", "tpl.rep_copy_selector.html");

		sendInfo($this->lng->txt("select_object_to_copy"));

		$exp = new ilRepositoryCopySelector($this->ctrl->getLinkTarget($this,'copySelector'));
		$exp->setExpand($_GET["rep_copy_expand"] ? $_GET["rep_copy_expand"] : $this->tree->readRootId());
		$exp->setExpandTarget($this->ctrl->getLinkTarget($this,'copySelector'));
		$exp->setTargetGet("ref_id");
		$exp->setRefId($this->cur_ref_id);
		$exp->addFilter($_SESSION["copy_new_type"]);
		$exp->setSelectableType($_SESSION["copy_new_type"]);

		// build html-output
		$exp->setOutput(0);

		$this->tpl->setCurrentBlock("objects");
		$this->tpl->setVariable("EXPLORER",$exp->getOutput());
		$this->tpl->parseCurrentBlock();
		$this->tpl->show();
	}

	function linkSelector()
	{
		if(is_array($_POST["cmd"]))
		{
			$_SESSION["link_new_type"] = $_POST["new_type"];
		}
		if($_POST['new_type'] == 'cat' or $_POST['new_type'] == 'grp' or $_POST['new_type'] == 'crs' or 
			$_POST['new_type'] == 'fold')
		{
			$this->lng->loadLanguageModule('crs');

			sendInfo($this->lng->txt('crs_container_link_not_allowed'),true);
			ilUtil::redirect('repository.php?ref_id='.$this->cur_ref_id);
		}


		include_once ("classes/class.ilRepositoryLinkSelector.php");

		$this->prepareOutput(false);
		$this->tpl->setCurrentBlock("content");
		$this->tpl->addBlockFile("OBJECTS", "objects", "tpl.rep_copy_selector.html");

		sendInfo($this->lng->txt("select_object_to_link"));

		$exp = new ilRepositoryLinkSelector($this->ctrl->getLinkTarget($this,'linkSelector'));
		$exp->setExpand($_GET["rep_link_expand"] ? $_GET["rep_link_expand"] : $this->tree->readRootId());
		$exp->setExpandTarget($this->ctrl->getLinkTarget($this,'linkSelector'));
		$exp->setTargetGet("ref_id");
		$exp->setRefId($this->cur_ref_id);
		$exp->addFilter($_SESSION["link_new_type"]);
		$exp->setSelectableType($_SESSION["link_new_type"]);

		// build html-output
		$exp->setOutput(0);

		$this->tpl->setCurrentBlock("objects");
		$this->tpl->setVariable("EXPLORER",$exp->getOutput());
		$this->tpl->parseCurrentBlock();
		$this->tpl->show();
	}

	function linkChilds()
	{
		if($_GET['source_id'])
		{
			$this->linkObject($this->cur_ref_id,(int) $_GET['source_id']);
			
			sendInfo($this->lng->txt('linked_object'),true);
			ilUtil::redirect("./repository.php?ref_id=".$this->cur_ref_id);
		}


	}

	function copyChilds()
	{
		global $objDefinition;

		$this->prepareOutput();
		$this->tpl->setCurrentBlock("content");
		$this->tpl->addBlockFile("OBJECTS", "objects", "tpl.rep_copy_childs.html");

		if(!count($this->tree->getFilteredChilds(array('rolf'),(int) $_GET["source_id"])))
		{
			$tmp_target =& ilObjectFactory::getInstanceByRefId($this->cur_ref_id);
			$tmp_source =& ilObjectFactory::getInstanceByRefId((int) $_GET["source_id"]);
		
			$info = $this->lng->txt("copy").": '".$tmp_source->getTitle()."' ".
				$this->lng->txt("copy_to")." '".$tmp_target->getTitle()."' ?";
			sendInfo($info);

			$this->tpl->setCurrentBlock("confirm");
			$this->tpl->setVariable("CMD_CONFIRM_CANCEL",'cancel');
			$this->tpl->setVariable("TXT_CONFIRM_CANCEL",$this->lng->txt("cancel"));
			$this->tpl->setVariable("CMD_CONFIRM_COPY",'performCopy');
			$this->tpl->setVariable("TXT_CONFIRM_COPY",$this->lng->txt("copy"));
			$this->tpl->parseCurrentBlock();

			unset($tmp_source);
			unset($tmp_target);
		}
		else
		{
			// OBJECT is grp,crs or folder
			$tmp_source =& ilObjectFactory::getInstanceByRefId((int) $_GET["source_id"]);
			$sub_types = $this->tree->getSubTreeTypes((int) $_GET["source_id"],array('rolf'));

			foreach($sub_types as $type)
			{
				$pos_actions = $objDefinition->getActions($type);

				$actions = array();
				if($objDefinition->allowLink($type))
				{
					$actions['link'] = $this->lng->txt("link");
				}
				#if(isset($pos_actions['copy']))
				if(1)
				{
					$actions['copy'] = $this->lng->txt("copy");
				}
				if($type == 'grp' or $type == 'frm')
				{
					$actions['no_content'] = $this->lng->txt('crs_no_content');
				}
				

				$this->tpl->setCurrentBlock("object_options");
				$this->tpl->setVariable("OBJECT_TYPE",$this->lng->txt("obj_".$type));
				$this->tpl->setVariable("SELECT_OBJ",ilUtil::formSelect('copy',"action[$type]",$actions,false,true));
				$this->tpl->parseCurrentBlock();
			}
			
			$this->tpl->setCurrentBlock("container");
			$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt("cancel"));
			$this->tpl->setVariable("CMD_SUBMIT",'performCopy');
			$this->tpl->setVariable("TXT_SUBMIT",$this->lng->txt("copy"));
			$this->tpl->setVariable("TYPE_IMG",ilUtil::getImagePath("icon_".$tmp_source->getType()."_b.gif"));
			$this->tpl->setVariable("ALT_IMG",$this->lng->txt("obj_".$tmp_source->getType()));
			$this->tpl->setVariable("TITLE",$this->lng->txt("options_for_subobjects"));
		}
			
		$this->ctrl->setParameterByClass('ilrepositorygui','source_id',(int) $_GET['source_id']);
		$this->tpl->setVariable("COPY_ACTION",$this->ctrl->getFormAction($this));
		$this->tpl->show();
	}

	function performCopy()
	{
		if(!count($this->tree->getFilteredChilds(array('rolf'),(int) $_GET["source_id"])))
		{
			$this->copyObject($this->cur_ref_id,(int) $_GET["source_id"]);
		}
		else
		{
			if(in_array("link",$_POST["action"]))
			{
				if(!$this->checkDuplicateAccess($_POST["action"],$_GET["source_id"]))
				{
					sendInfo($this->lng->txt("no_access_link_object"),true);
					
					$this->ctrl->redirect($this,'');
				}
			}
			// call recursive copy link method
			$this->duplicate($_POST["action"],$this->cur_ref_id,(int) $_GET["source_id"]);
		}
		sendInfo($this->lng->txt("copied_object"),true);
		ilUtil::redirect("./repository.php?ref_id=".$this->cur_ref_id);
	}

	function duplicate($post,$a_target,$a_source)
	{
		$stop_recursion = false;

		$tmp_object =& ilObjectFactory::getInstanceByRefId($a_source);
		$type = $tmp_object->getType();

		switch($post[$type])
		{
			case "copy":
				$new_ref = $this->copyObject($a_target,$a_source);
				break;
			case "link":
				$new_ref = $this->linkObject($a_target,$a_source);
				break;
			case "no_content":
				switch($type)
				{
					case 'grp':
						$stop_recursion = true;
						$new_ref = $this->copyObject($a_target,$a_source);
						break;

					case 'frm':
						$new_ref = $this->copyObject($a_target,$a_source,false);
						break;
				}
				break;

			default:
				echo "ilRepositoryGUI:: duplicate(): not possible";
		}

		if($stop_recursion)
		{
			return true;
		}
		foreach($this->tree->getFilteredChilds(array('rolf'),$a_source) as $child)
		{
			$this->duplicate($post,$new_ref,$child["child"]);
		}
		return true;
	}
	
	function copyObject($a_target,$a_source,$a_with_content = true)
	{
		$tmp_source =& ilObjectFactory::getInstanceByRefId($a_source);

		if($a_with_content)
		{
			$new_ref = $tmp_source->ilClone($a_target);
		}
		else
		{
			$new_ref = $tmp_source->ilClone($a_target,false);
		}
		unset($tmp_source);

		return $new_ref;
	}

	function linkObject($a_target,$a_source)
	{
		$tmp_source =& ilObjectFactory::getInstanceByRefId($a_source);

		$new_ref = $tmp_source->createReference();

		$this->tree->insertNode($new_ref,$a_target);
		$tmp_source->setPermissions($new_ref);
		$tmp_source->initDefaultRoles();

		return $new_ref;
	}
	function checkDuplicateAccess($a_types,$a_source_id)
	{
		foreach($this->tree->getSubTree($this->tree->getNodeData($a_source_id)) as $node)
		{
			if($node["type"] == 'rolf')
			{
				continue;
			}
			if($a_types["$node[type]"] == 'link')
			{
				if(!$this->rbacsystem->checkAccess('write',$node["child"]))
				{
					return false;
				}
			}
		}
		return true;
	}

/*
	function addToClipboard()
	{
		// check preconditions (dirty implementation, should merged with linkObject & cutObject in ilObjectGUI)
		// CHECK LINK OPERATION
		if ($_GET['act'] == 'link')
		{
			if (!$this->rbacsystem->checkAccess('delete',$_GET['item_ref_id']))
			{
				$no_cut[] = $ref_id;
			}

			$object =& $this->ilias->obj_factory->getInstanceByRefId($_GET['item_ref_id']);

			if (!$this->objDefinition->allowLink($object->getType()))
			{
				$no_link[] = $object->getType();
			}

			// NO ACCESS
			if (count($no_cut))
			{
				$this->ilias->raiseError($this->lng->txt("msg_no_perm_link")." ".
										 implode(',',$no_cut),$this->ilias->error_obj->MESSAGE);
			}

			if (count($no_link))
			{
				$no_link = array_unique($no_link);

				foreach ($no_link as $type)
				{
					$txt_objs[] = $this->lng->txt("objs_".$type);
				}

				$this->ilias->raiseError(implode(', ',$txt_objs)." ".$this->lng->txt("msg_obj_no_link"),$this->ilias->error_obj->MESSAGE);
			}

			$message = "msg_link_clipboard";
		}
		// CHECK CUT OPERATION
		elseif ($_GET['act'] == 'cut')
		{
			// FOR ALL OBJECTS THAT SHOULD BE COPIED
			// GET COMPLETE NODE_DATA OF ALL SUBTREE NODES
			$node_data = $this->tree->getNodeData($_GET['item_ref_id']);
			$subtree_nodes = $this->tree->getSubTree($node_data);

			$all_node_data[] = $node_data;
			$all_subtree_nodes[] = $subtree_nodes;

			// CHECK DELETE PERMISSION OF ALL OBJECTS IN ACTUAL SUBTREE
			foreach ($subtree_nodes as $node)
			{
				if (!$this->rbacsystem->checkAccess('delete',$node["ref_id"]))
				{
					$no_cut[] = $node["ref_id"];
				}
			}

			// IF THERE IS ANY OBJECT WITH NO PERMISSION TO 'delete'
			if (count($no_cut))
			{
				$this->ilias->raiseError($this->lng->txt("msg_no_perm_cut")." ".implode(',',$this->getTitlesByRefId($no_cut)),
										 $this->ilias->error_obj->MESSAGE);
			}

			$message = "msg_cut_clipboard";
		}

		// clear clipboard (only one object is possible for now)
		$_SESSION['il_rep_clipboard'] = "";

		// all okay. store selected object in clipboard
		$_SESSION['il_rep_clipboard'][] = array('ref_id' => $_GET['item_ref_id'],'act' => $_GET['act']);
		sendInfo($this->lng->txt($message),true);
		$this->showList();
	}
*/

} // END class.ilRepository


?>
