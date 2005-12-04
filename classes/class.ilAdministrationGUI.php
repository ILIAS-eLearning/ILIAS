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
include_once("classes/class.ilTabsGUI.php");

/**
* Class ilAdministratioGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilAdministrationGUI: ilObjGroupGUI, ilObjFolderGUI, ilObjFileGUI, ilObjCourseGUI, ilCourseObjectivesGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjSAHSLearningModuleGUI, ilObjChatGUI, ilObjForumGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjLearningModuleGUI, ilObjDlBookGUI, ilObjGlossaryGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjQuestionPoolGUI, ilObjSurveyQuestionPoolGUI, ilObjTestGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjSurveyGUI, ilObjExerciseGUI, ilObjMediaPoolGUI, ilObjFileBasedLMGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjCategoryGUI, ilObjUserGUI, ilObjRoleGUI, ilObjUserFolderGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjiLincCourseGUI, ilObjiLincClassroomGUI, ilObjLinkResourceGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjRootFolderGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjSystemFolderGUI, ilObjRoleFolderGUI, ilObjAuthSettingsGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjChatServerGUI, ilObjLanguageFolderGUI, ilObjMailGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjObjectFolderGUI, ilObjPaymentSettingsGUI, ilObjRecoveryFolderGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjSearchSettingsGUI, ilObjStyleSettingsGUI, ilObjTaxonomyFolderGUI
* @ilCtrl_Calls ilAdministrationGUI: ilObjAssessmentFolderGUI, ilObjExternalToolsSettingsGUI, ilObjUserTrackingGUI
*
* @package core
*/
class ilAdministrationGUI
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
	function ilAdministrationGUI()
	{
		global $lng, $ilias, $tpl, $tree, $rbacsystem, $objDefinition,
			$_GET, $ilCtrl, $ilLog;;

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
		if (!empty($_GET["ref_id"]) && $tree->isInTree($_GET["ref_id"]))
		{
			$this->cur_ref_id = $_GET["ref_id"];
		}
		else
		{
			//$this->cur_ref_id = $this->tree->getRootId();
			$_POST = array();
			$_GET["cmd"] = "";
		}
	}

	
	/**
	* execute command
	*/
	function &executeCommand()
	{
		global $tree, $rbacsystem, $ilias, $lng;
		
		// permission checks
		include_once './classes/class.ilMainMenuGUI.php';
		if(!ilMainMenuGUI::_checkAdministrationPermission())
		{
			$ilias->raiseError("You are not entitled to access this page!",$ilias->error_obj->WARNING);
		}

		// check creation mode
		// determined by "new_type" parameter
		$new_type = $_POST["new_type"]
			? $_POST["new_type"]
			: $_GET["new_type"];
		if ($new_type != "" && $this->ctrl->getCmd() == "create")
		{
			$this->creation_mode = true;
		}

		// determine next class
		if ($this->creation_mode)
		{
			$obj_type = $new_type;
			$class_name = $this->objDefinition->getClassName($obj_type);
			$next_class = strtolower("ilObj".$class_name."GUI");
			$this->ctrl->setCmdClass($next_class);
		}
		else
		{
			$next_class = $this->ctrl->getNextClass($this);
		}

		if (($next_class == "iladministrationgui" || $next_class == ""
			) && ($this->ctrl->getCmd() == "return"))
		{

			// get GUI of current object
			$obj_type = ilObject::_lookupType($this->cur_ref_id,true);
			$class_name = $this->objDefinition->getClassName($obj_type);
			$next_class = strtolower("ilObj".$class_name."GUI");
			$this->ctrl->setCmdClass($next_class);
			$this->ctrl->setCmd("view");
		}
		
		$cmd = $this->ctrl->getCmd("frameset");
		
//echo "<br>cmd:$cmd:nextclass:$next_class:-".$_GET["cmdClass"]."-".$_GET["cmd"]."-";
		switch ($next_class)
		{
			/*
			case "ilobjusergui":
				include_once("./classes/class.ilObjUserGUI.php");

				if(!$_GET['obj_id'])
				{
					$this->gui_obj = new ilObjUserGUI("",$_GET['ref_id'],true, false);
					$this->gui_obj->setCreationMode($this->creation_mode);

					$this->prepareOutput(false);
					$ret =& $this->ctrl->forwardCommand($this->gui_obj);
				}
				else
				{
					$this->gui_obj = new ilObjUserGUI("", $_GET['obj_id'],false, false);
					$this->gui_obj->setCreationMode($this->creation_mode);

					$this->prepareOutput(false);
					$ret =& $this->ctrl->forwardCommand($this->gui_obj);
				}
				$this->tpl->show();
				break;
			*/
				
			/*
			case "ilobjuserfoldergui":
				include_once("./classes/class.ilObjUserFolderGUI.php");

				$this->gui_obj = new ilObjUserFolderGUI("", $_GET['ref_id'],true, false);
				$this->gui_obj->setCreationMode($this->creation_mode);

				$this->prepareOutput(false);
				$ret =& $this->ctrl->forwardCommand($this->gui_obj);
				$this->tpl->show();
				break;*/

			default:
			
				// forward all other classes to gui commands
				if ($next_class != "" && $next_class != "iladministrationgui")
				{
					$class_path = $this->ctrl->lookupClassPath($next_class);

					// get gui class instance
					include_once($class_path);
					$class_name = $this->ctrl->getClassForClasspath($class_path);
					
					if (($next_class == "ilobjrolegui" || $next_class == "ilobjusergui"))
					{
						if ($_GET["obj_id"] != "")
						{
							$this->gui_obj = new $class_name("", $_GET["obj_id"], false, false);
							$this->gui_obj->setCreationMode(false);
						}
						else
						{
							$this->gui_obj = new $class_name("", $this->cur_ref_id, true, false);
							$this->gui_obj->setCreationMode(true);
						}
					}
					else
					{
						$this->gui_obj = new $class_name("", $this->cur_ref_id, true, false);
						$this->gui_obj->setCreationMode($this->creation_mode);
					}
					$tabs_out = ($new_type == "")
						? true
						: false;
					
					$this->ctrl->setReturn($this, "return");					
					$ret =& $this->ctrl->forwardCommand($this->gui_obj);
					$html = $this->gui_obj->getHTML();
					if ($html != "")
					{
						$this->tpl->setVariable("OBJECTS", $html);
					}
					$this->tpl->show();
				}
				else	// 
				{
					$cmd = $this->ctrl->getCmd("frameset");
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
		global $tree;

		$tpl = new ilTemplate("tpl.adm_frameset.html", false, false);
		$tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());
//echo "<br>-".$this->ctrl->getLinkTarget($this, "view")."-";
		$this->ctrl->setParameter($this, "ref_id", ROOT_FOLDER_ID);
		$tpl->setVariable("MAIN_CONTENT", $this->ctrl->getLinkTargetByClass("ilobjrootfoldergui", "view"));
		$this->ctrl->setParameter($this, "expand", "1");
		$tpl->setVariable("TREE_CONTENT", $this->ctrl->getLinkTarget($this, "showTree"));
//echo "<br>+".$this->ctrl->getLinkTarget($this, "showTree")."+";
		$tpl->parseCurrentBlock();
		$tpl->show();
	}


	/**
	* display tree view
	*/
	function showTree()
	{
		global $tpl, $tree, $lng;

		require_once "classes/class.ilAdministrationExplorer.php";

		$tpl->addBlockFile("CONTENT", "content", "tpl.explorer.html");
		
		$explorer = new ilAdministrationExplorer("ilias.php?baseClass=ilAdministrationGUI&cmd=view");		
		$explorer->setExpand($_GET["expand"]);
		$explorer->setExpandTarget($this->ctrl->getLinkTarget($this, "showTree"));
		
		// hide RecoveryFolder if empty
		if (!$tree->getChilds(RECOVERY_FOLDER_ID))
		{
			$explorer->addFilter("recf");
		}
		$explorer->addFilter("rolf");

		/*
		$explorer->addFilter("root");
		$explorer->addFilter("cat");
		$explorer->addFilter("grp");
		$explorer->addFilter("crs");
		$explorer->addFilter("le");
		$explorer->addFilter("frm");
		$explorer->addFilter("lo");
		$explorer->addFilter("rolf");
		$explorer->addFilter("adm");
		$explorer->addFilter("lngf");
		$explorer->addFilter("usrf");
		$explorer->addFilter("objf");
		*/
		//$explorer->setFiltered(false);
		$explorer->setOutput(0);		
		$output = $explorer->getOutput();		
		$tpl->setCurrentBlock("content");
		$tpl->setVariable("TXT_EXPLORER_HEADER", $lng->txt("all_objects"));
		$tpl->setVariable("EXP_REFRESH", $lng->txt("refresh"));
		$tpl->setVariable("EXPLORER",$output);
		$this->ctrl->setParameter($this, "expand", $_GET["expand"]);
		$tpl->setVariable("ACTION", $this->ctrl->getLinkTarget($this, "showTree"));
		$tpl->parseCurrentBlock();
		
		$tpl->show(false);
	}

} // END class.ilRepository


?>
