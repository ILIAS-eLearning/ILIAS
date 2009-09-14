<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Table/classes/class.ilTableGUI.php");
include_once("classes/class.ilTabsGUI.php");
include_once("payment/classes/class.ilPaymentObject.php");


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
* @ilCtrl_Calls ilRepositoryGUI: ilObjCategoryGUI, ilObjRoleGUI
* @ilCtrl_Calls ilRepositoryGUI: ilObjiLincCourseGUI, ilObjiLincClassroomGUI, ilObjLinkResourceGUI
* @ilCtrl_Calls ilRepositoryGUI: ilObjRootFolderGUI, ilObjMediaCastGUI, ilObjRemoteCourseGUI, ilObjSessionGUI
* @ilCtrl_Calls ilRepositoryGUI: ilObjCourseReferenceGUI, ilObjCategoryReferenceGUI
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
				$_GET = array();
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
		global $tree, $rbacsystem, $ilias, $lng, $objDefinition,$ilUser, $ilCtrl;
		
		// Check for incomplete profile
		if($ilUser->getProfileIncomplete())
		{
			ilUtil::redirect('ilias.php?baseClass=ilPersonalDesktopGUI');
		}

		// check whether password of user have to be changed due to first login
		if( $ilUser->isPasswordChangeDemanded() )
		{
			ilUtil::sendInfo( $this->lng->txt('password_change_on_first_login_demand'), true );

			ilUtil::redirect('ilias.php?baseClass=ilPersonalDesktopGUI');
		}

		// check whether password of user is expired
		if( $ilUser->isPasswordExpired() )
		{
			$msg = $this->lng->txt('password_expired');
			$password_age = $ilUser->getPasswordAge();

			ilUtil::sendInfo( sprintf($msg,$password_age), true );

			ilUtil::redirect('ilias.php?baseClass=ilPersonalDesktopGUI');
		}


		// check creation mode
		// determined by "new_type" parameter
		$new_type = ($_POST["new_type"] != "" && $ilCtrl->getCmd() == "create")
			? $_POST["new_type"]
			: $_GET["new_type"];

		if ($new_type != "")
		{
			$this->creation_mode = true;
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
					include_once($class_path);
					$class_name = $this->ctrl->getClassForClasspath($class_path);
					if (!$this->creation_mode)
					{
						$this->gui_obj = new $class_name("", $this->cur_ref_id, true, false);
					}
					else
					{	
						// dirty walkaround for ilinc classrooms which need passed the ref_id of the parent iLinc course
						if ($class_name == 'ilObjiLincClassroomGUI')
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
		global $lng;
		
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
			$fs_gui->setMainFrameSource(
				"repository.php?getlast=true&ref_id=".$this->cur_ref_id);
		}
		$fs_gui->setSideFrameSource(
			"repository.php?cmd=showTree&ref_id=".$this->cur_ref_id);

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
		include_once ("./Services/Repository/classes/class.ilRepositoryExplorer.php");
		$exp = new ilRepositoryExplorer("repository.php?cmd=goto");
		$exp->setUseStandardFrame(true);
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

		echo $output;
	}

} // END class.ilRepository


?>
