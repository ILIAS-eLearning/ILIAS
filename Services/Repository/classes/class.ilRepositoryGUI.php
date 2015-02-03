<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTableGUI.php");
include_once("Services/Payment/classes/class.ilPaymentObject.php");


/**
* Class ilRepositoryGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilRepositoryGUI: ilObjGroupGUI, ilObjFolderGUI, ilObjFileGUI, ilObjCourseGUI, ilCourseObjectivesGUI
* @ilCtrl_Calls ilRepositoryGUI: ilObjSAHSLearningModuleGUI, ilObjChatroomGUI, ilObjForumGUI
* @ilCtrl_Calls ilRepositoryGUI: ilObjLearningModuleGUI, ilObjDlBookGUI, ilObjGlossaryGUI
* @ilCtrl_Calls ilRepositoryGUI: ilObjQuestionPoolGUI, ilObjSurveyQuestionPoolGUI, ilObjTestGUI
* @ilCtrl_Calls ilRepositoryGUI: ilObjSurveyGUI, ilObjExerciseGUI, ilObjMediaPoolGUI, ilObjFileBasedLMGUI
* @ilCtrl_Calls ilRepositoryGUI: ilObjCategoryGUI, ilObjRoleGUI, ilObjBlogGUI
* @ilCtrl_Calls ilRepositoryGUI: ilObjiLincCourseGUI, ilObjiLincClassroomGUI, ilObjLinkResourceGUI
* @ilCtrl_Calls ilRepositoryGUI: ilObjRootFolderGUI, ilObjMediaCastGUI, ilObjRemoteCourseGUI, ilObjSessionGUI
* @ilCtrl_Calls ilRepositoryGUI: ilObjCourseReferenceGUI, ilObjCategoryReferenceGUI, ilObjDataCollectionGUI
* @ilCtrl_Calls ilRepositoryGUI: ilObjPollGUI, ilObjRemoteCategoryGUI, ilObjRemoteWikiGUI, ilObjRemoteLearningModuleGUI
* @ilCtrl_Calls ilRepositoryGUI: ilObjRemoteGlossaryGUI, ilObjRemoteFileGUI, ilObjRemoteGroupGUI
* @ilCtrl_Calls ilRepositoryGUI: ilObjRemoteTestGUI, ilObjCloudGUI, ilObjPortfolioTemplateGUI
* @ilCtrl_Calls ilRepositoryGUI: ilObjTrainingProgrammeGUI
*
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
		
		$this->creation_mode = false;

		$this->ctrl->saveParameter($this, array("ref_id"));
		if (!ilUtil::isAPICall())
			$this->ctrl->setReturn($this,"");

		// determine current ref id and mode
		if (!empty($_GET["ref_id"]) || $this->ctrl->getCmd() == "showTree")
		{
			$this->cur_ref_id = $_GET["ref_id"];
		}
		else
		{
//echo "1-".$_SESSION["il_rep_ref_id"]."-";
			if (!empty($_SESSION["il_rep_ref_id"]) && !empty($_GET["getlast"]))
			{
				$this->cur_ref_id = $_SESSION["il_rep_ref_id"];
//echo "2-".$this->cur_ref_id."-";
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
				// #10033
				$_GET = array("baseClass"=>"ilRepositoryGUI");
				$_POST = array();
				$this->ctrl->setCmd("frameset");
			}
		}
//echo "<br>+".$_GET["ref_id"]."+";
		if (!$tree->isInTree($this->cur_ref_id) && $this->ctrl->getCmd() != "showTree")
		{
			$this->cur_ref_id = $this->tree->getRootId();

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

		// store current ref id
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
		
		$_GET["ref_id"] = $this->cur_ref_id;
		
	}

	
	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $rbacsystem, $ilias, $lng, $ilCtrl, $ilHelp;

		// check creation mode
		// determined by "new_type" parameter
		$new_type = ($_POST["new_type"] != "" && $ilCtrl->getCmd() == "create")
			? $_POST["new_type"]
			: $_GET["new_type"];

		if ($new_type != "" && $new_type != "sty")
		{
			$this->creation_mode = true;
			$ilHelp->setScreenIdComponent($new_type);
			$ilHelp->setDefaultScreenId(ilHelpGUI::ID_PART_SCREEN, "create");
		}

		// handle frameset command
		$cmd = $this->ctrl->getCmd();
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

		// determine next class
		if ($cmd != "frameset")
		{
			if ($this->creation_mode)
			{
				$obj_type = $new_type;
				$class_name = $this->objDefinition->getClassName($obj_type);
				if (strtolower($class_name) != "user")
				{
					$next_class = strtolower("ilObj".$class_name."GUI");
				}
				else
				{
					$next_class = $this->ctrl->getNextClass();
				}
				// Only set the fixed cmdClass if the next class is different to
				// the GUI class of the new object.
				// An example:
				// Copy Category uses this call structure:
				// RespositoryGUI -> CategoryGUI -> ilObjectCopyGUI
				// Without this fix, the cmdClass ilObjectCopyGUI would never be reached
				if($this->ctrl->getNextClass() != strtolower('ilObj'.$class_name.'GUI'))
				{
					$this->ctrl->setCmdClass($next_class);
				}
			}
			else if ((($next_class = $this->ctrl->getNextClass($this)) == "")
				|| ($next_class == "ilrepositorygui" && $this->ctrl->getCmd() == "return"))
			{
				if ($cmd != "frameset" && $cmd != "showTree")
				{
					// get GUI of current object
					$obj_type = ilObject::_lookupType($this->cur_ref_id,true);
					$class_name = $this->objDefinition->getClassName($obj_type);
					$next_class = strtolower("ilObj".$class_name."GUI");

					$this->ctrl->setCmdClass($next_class);
					if ($this->ctrl->getCmd() == "return")
					{
						$this->ctrl->setCmd("");
					}
				}
			}
		}

		// commands that are always handled by repository gui
		// to do: move to container
		//if ($cmd == "showTree" || $cmd == "linkSelector" || $cmd == "linkChilds")
		if ($cmd == "showTree")
		{
			$next_class = "";
		}

		switch ($next_class)
		{		
			default:
				// forward all other classes to gui commands
				if ($next_class != "" && $next_class != "ilrepositorygui")
				{
					$class_path = $this->ctrl->lookupClassPath($next_class);
					// get gui class instance
					require_once($class_path);
					$class_name = $this->ctrl->getClassForClasspath($class_path);
					if (!$this->creation_mode)
					{
						if(is_subclass_of($class_name, "ilObject2GUI"))
						{
							$this->gui_obj = new $class_name($this->cur_ref_id, ilObject2GUI::REPOSITORY_NODE_ID);
						}
						else
						{
							$this->gui_obj = new $class_name("", $this->cur_ref_id, true, false);
						}						
					}
					else
					{
						if(is_subclass_of($class_name, "ilObject2GUI"))
						{
							$this->gui_obj = new $class_name(null, ilObject2GUI::REPOSITORY_NODE_ID, $this->cur_ref_id);
						}
						// dirty walkaround for ilinc classrooms which need passed the ref_id of the parent iLinc course
						else if ($class_name == 'ilObjiLincClassroomGUI')
						{														
							$this->gui_obj = new $class_name("", $this->cur_ref_id, true, false);
						}
						else
						{
							$this->gui_obj = new $class_name("", 0, true, false);
						}
					}
					//$this->gui_obj = new $class_name("", $this->cur_ref_id, true, false);

	
					$tabs_out = ($new_type == "")
						? true
						: false;
					$this->gui_obj->setCreationMode($this->creation_mode);
					$this->ctrl->setReturn($this, "return");

					$this->show();
				}
				else	// 
				{
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
					
					// process tree command
					if ($cmd == "showTree")
					{
						$this->showTree();
						return;
					}
					
					$cmd = $this->ctrl->getCmd("");
					
					// check read access for category
					if ($this->cur_ref_id > 0 && !$rbacsystem->checkAccess("read", $this->cur_ref_id))
					{
						$_SESSION["il_rep_ref_id"] = "";
						$ilias->raiseError($lng->txt("permission_denied"), $ilias->error_obj->MESSAGE);
						$this->tpl->show();
					}
					else
					{
						$this->cmd = $cmd;
						$this->$cmd();
					}
				}
				break;
		}
	}

	
	function show()
	{
		// normal command processing
		$ret =& $this->ctrl->forwardCommand($this->gui_obj);
		$this->tpl->setVariable("OBJECTS", $this->gui_obj->getHTML());

		$this->tpl->show();
	}
	
	/**
	* output tree frameset
	*/
	function frameset()
	{
		global $lng, $ilCtrl, $ilAccess;
		
$ilCtrl->redirectByClass("ilrepositorygui", "");

		include_once("Services/Frameset/classes/class.ilFramesetGUI.php");
		$fs_gui = new ilFramesetGUI();

		if ($_GET["rep_frame"] == 1)
		{
			// workaround for passing anchors (e.g. used in ilNoteGUI)
			$anchor = ($_GET["anchor"] != "")
				? "#".$_GET["anchor"]
				: "";
			$fs_gui->setMainFrameSource(
				str_replace("rep_frame", "rep_frame_done", $_SERVER["REQUEST_URI"]).$anchor);
		}
		else
		{
			$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->cur_ref_id);
			$ilCtrl->setParameterByClass("ilrepositorygui", "getlast", "true");
			if ($ilAccess->checkAccess("read", "", $this->cur_ref_id))
			{
				$fs_gui->setMainFrameSource(
					$ilCtrl->getLinkTargetByClass("ilrepositorygui", ""));
			}
			else
			{
				// if no read permission is given, do not display frameset
				// see bug http://www.ilias.de/mantis/view.php?id=10305
				$ilCtrl->redirectByClass("ilrepositorygui", "");
			}
			$ilCtrl->clearParametersByClass("ilrepositorygui");
		}
		$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->cur_ref_id);
		$fs_gui->setSideFrameSource(
			$ilCtrl->getLinkTargetByClass("ilrepositorygui", "showTree"));
		$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);

		$fs_gui->setSideFrameName("tree");
		$fs_gui->setMainFrameName("rep_content");
		$fs_gui->setFramesetTitle($this->lng->txt("repository"));
		$fs_gui->show();
		exit;
	}


	/**
	* display tree view
	*/
	function showTree()
	{
		global $ilCtrl, $tree, $ilSetting, $lng;

		$ilCtrl->setParameter($this, "active_node", $_GET["active_node"]);

		$this->tpl = new ilTemplate("tpl.main.html", true, true);
		$this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
		
		$this->tpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");

		include_once ("./Services/Repository/classes/class.ilRepositoryExplorer.php");

		$active_node = ($_GET["active_node"] > 1)
			? $_GET["active_node"]
			: ($_GET["ref_id"] > 1)
				? $_GET["ref_id"]
				: 0;
		$top_node = 0;
		if ($ilSetting->get("rep_tree_limit_grp_crs") && $active_node > 0)
		{
			$path = $tree->getPathId($active_node);
			foreach ($path as $n)
			{
				if ($top_node > 0)
				{
					break;
				}
				if (in_array(ilObject::_lookupType(ilObject::_lookupObjId($n)),
					array("crs", "grp")))
				{
					$top_node = $n;
				}
			}
		}

		$exp = new ilRepositoryExplorer("ilias.php?baseClass=ilRepositoryGUI&amp;cmd=goto", $top_node);
		$exp->setUseStandardFrame(false);
		$exp->setExpandTarget($ilCtrl->getLinkTarget($this, "showTree"));
		$exp->setFrameUpdater("tree", "updater");
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

		if ($active_node > 0)
		{
			$path = $tree->getPathId($active_node);
			if ($top_node > 0)
			{
				$exp->setForceOpenPath($path);
				$exp->setExpand($expanded);
			}
			else
			{
				$exp->setForceOpenPath($path + array($top_node));
			}
			$exp->highlightNode($active_node);
		}

		// build html-output
		if ($top_node > 0)
		{
			$head_tpl = new ilTemplate("tpl.cont_tree_head.html", true, true,
				"Services/Repository");
			$path = ilObject::_getIcon(ROOT_FOLDER_ID, "tiny", "root");
			$nd = $tree->getNodeData(ROOT_FOLDER_ID);
			$title = $nd["title"];
			if ($title == "ILIAS")
			{
				$title = $lng->txt("repository");
			}
			$head_tpl->setVariable("IMG_SRC", $path);
			$head_tpl->setVariable("ALT_IMG", $lng->txt("icon")." ".$title);
			$head_tpl->setVariable("LINK_TXT", $title);
			$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", "1");
			$head_tpl->setVariable("LINK_HREF",
				$ilCtrl->getLinkTargetByClass("ilrepositorygui", "frameset"));
			$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $_GET["ref_id"]);
			$exp->setTreeLead($head_tpl->get());

			$exp->initItemCounter(1);
			$exp->setOutput($tree->getParentId($top_node), 1,
				ilObject::_lookupObjId($tree->getParentId($top_node)));
		}
		else
		{
			$exp->setOutput(0);
		}
		$output = $exp->getOutput(false);

//if ($GLOBALS["ilUser"]->getLogin() == "alex") echo "topnode:$top_node:activenode:$active_node:";
		

		// asynchronous output
		if ($ilCtrl->isAsynch())
		{
			echo $output; exit;
		}

		$this->tpl->setCurrentBlock("content");
		$this->tpl->setVariable("TXT_EXPLORER_HEADER", $this->lng->txt("overview"));
		$this->tpl->setVariable("EXP_REFRESH", $this->lng->txt("refresh"));
		$this->tpl->setVariable("EXPLORER",$output);
		$ilCtrl->setParameter($this, "repexpand", $_GET["repexpand"]);
		$this->tpl->setVariable("ACTION", $ilCtrl->getLinkTarget($this, "showTree", "", false, false));
		$this->tpl->parseCurrentBlock();
		
		include_once("./Services/jQuery/classes/class.iljQueryUtil.php");
		iljQueryUtil::initjQuery($this->tpl);
		
		$this->tpl->show(false);
		exit;
	}

} // END class.ilRepository


?>
