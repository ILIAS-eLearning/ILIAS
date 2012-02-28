<?php

/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
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
* Class ilObjFolderGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @ilCtrl_Calls ilObjFolderGUI: ilConditionHandlerInterface, ilPermissionGUI
* @ilCtrl_Calls ilObjFolderGUI: ilCourseContentGUI, ilLearningProgressGUI
* @ilCtrl_Calls ilObjFolderGUI: ilInfoScreenGUI, ilPageObjectGUI, ilColumnGUI
* @ilCtrl_Calls ilObjFolderGUI: ilCourseItemAdministrationGUI, ilObjectCopyGUI, ilObjStyleSheetGUI
* @ilCtrl_Calls ilObjFolderGUI: ilExportGUI, ilCommonActionDispatcherGUI, ilDidacticTemplateGUI
*
* @extends ilObjectGUI
*/

require_once "./Services/Container/classes/class.ilContainerGUI.php";

class ilObjFolderGUI extends ilContainerGUI
{
	var $folder_tree;		// folder tree

	/**
	* Constructor
	* @access	public
	*/
	function ilObjFolderGUI($a_data, $a_id = 0, $a_call_by_reference = true, $a_prepare_output = false)
	{
		$this->type = "fold";
		parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output, false);
	}


	/**
	* View folder
	*/
	function viewObject()
	{
		global $tree;

		if (strtolower($_GET["baseClass"]) == "iladministrationgui")
		{
			parent::viewObject();
			return true;
		}
		$this->renderObject();
		$this->tabs_gui->setTabActive('view_content');
		return true;
	}
		
	/**
	* Render folder
	*/
	function renderObject()
	{
		global $ilTabs;
		
		$ilTabs->activateTab("view_content");
		$ret =  parent::renderObject();
		return $ret;
	}

	function &executeCommand()
	{
		global $ilUser,$ilCtrl;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch ($next_class)
		{
			case "ilconditionhandlerinterface":
				$this->prepareOutput();
				include_once './Services/AccessControl/classes/class.ilConditionHandlerInterface.php';

				if($_GET['item_id'])
				{
					$this->ctrl->saveParameter($this,'item_id',$_GET['item_id']);
					$this->__setSubTabs('activation');
					$this->tabs_gui->setTabActive('view_content');

					$new_gui =& new ilConditionHandlerInterface($this,(int) $_GET['item_id']);
					$this->ctrl->forwardCommand($new_gui);
				}
				else
				{
					$new_gui =& new ilConditionHandlerInterface($this);
					$this->ctrl->forwardCommand($new_gui);
				}
				break;
				
			case 'ilpermissiongui':
				$this->prepareOutput();
				$this->tabs_gui->setTabActive('perm_settings');
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			case 'ilcoursecontentgui':
				$this->prepareOutput();
				include_once './Modules/Course/classes/class.ilCourseContentGUI.php';
				$course_content_obj = new ilCourseContentGUI($this);
				$this->ctrl->forwardCommand($course_content_obj);
				break;
				
			case 'ilcourseitemadministrationgui':
				$this->prepareOutput();
				include_once 'Modules/Course/classes/class.ilCourseItemAdministrationGUI.php';
				$this->tabs_gui->clearSubTabs();
				$this->ctrl->setReturn($this,'view');
				$item_adm_gui = new ilCourseItemAdministrationGUI($this->object,(int) $_REQUEST['item_id']);
				$this->ctrl->forwardCommand($item_adm_gui);
				break;

			case "illearningprogressgui":
				$this->prepareOutput();
				include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';
				
				$new_gui =& new ilLearningProgressGUI(LP_MODE_REPOSITORY,
													  $this->object->getRefId(),
													  $_GET['user_id'] ? $_GET['user_id'] : $ilUser->getId());
				$this->ctrl->forwardCommand($new_gui);
				$this->tabs_gui->setTabActive('learning_progress');
				break;

			// container page editing
			case "ilpageobjectgui":
				$this->prepareOutput(false);
				$this->checkPermission("write");
				$ret = $this->forwardToPageObject();
				if ($ret != "")
				{
					$this->tpl->setContent($ret);
				}
				break;

			case 'ilinfoscreengui':
				$this->prepareOutput();
				$this->infoScreen();
				break;

			case 'ilobjectcopygui':
				$this->prepareOutput();

				include_once './Services/Object/classes/class.ilObjectCopyGUI.php';
				$cp = new ilObjectCopyGUI($this);
				$cp->setType('fold');
				$this->ctrl->forwardCommand($cp);
				break;

			case "ilobjstylesheetgui":
				$this->forwardToStyleSheet();
				break;
				
			case 'ilexportgui':
				$this->prepareOutput();
					
				$this->tabs_gui->setTabActive('export');
				include_once './Services/Export/classes/class.ilExportGUI.php';
				$exp = new ilExportGUI($this);
				$exp->addFormat('xml');
				$this->ctrl->forwardCommand($exp);
				break;
			
			case "ilcommonactiondispatchergui":
				include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				$this->ctrl->forwardCommand($gui);
				break;

			case 'ildidactictemplategui':
				$this->ctrl->setReturn($this,'edit');
				include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateGUI.php';
				$did = new ilDidacticTemplateGUI($this);
				$this->ctrl->forwardCommand($did);
				break;

			default:

				$this->prepareOutput();
				// Dirty hack for course timings view
				if($this->forwardToTimingsView())
				{
					break;
				}

				if (empty($cmd))
				{
					$cmd = "view";
				}
				$cmd .= "Object";
				$this->$cmd();
				break;
		}
		
		$this->addHeaderAction();
	}

	/**
	* set tree
	*/
	function setFolderTree($a_tree)
	{
		$this->folder_tree =& $a_tree;
	}
	
	/**
	 * Import object
	 * @return 
	 */
	public function importFileObject()
	{
		global $lng;
		
		if(parent::importFileObject())
		{
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$this->ctrl->returnToParent($this);
		}
	}

	protected function initEditCustomForm(ilPropertyFormGUI $a_form) 
	{
		// Show didactic template type
		$this->initDidacticTemplate($a_form);

		// Sorting
		$sog = new ilRadioGroupInputGUI($this->lng->txt('sorting_header'),'sor');
		$sog->setRequired(true);
		
		// implicit: there is always a group or course in the path
		$sde = new ilRadioOption();
		$sde->setValue(ilContainer::SORT_INHERIT);
		$sde->setTitle($this->lng->txt('sort_inherit_prefix').' ('.
			ilContainerSortingSettings::sortModeToString(ilContainerSortingSettings::lookupSortModeFromParentContainer($this->object->getId())).') ');
		$sde->setInfo($this->lng->txt('sorting_info_inherit'));
		$sog->addOption($sde);
		
		$sma = new ilRadioOption();
		$sma->setValue(ilContainer::SORT_TITLE);
		$sma->setTitle($this->lng->txt('sorting_title_header'));
		$sma->setInfo($this->lng->txt('sorting_info_title'));
		$sog->addOption($sma);

		$sti = new ilRadioOption();
		$sti->setValue(ilContainer::SORT_MANUAL);
		$sti->setTitle($this->lng->txt('sorting_manual_header'));
		$sti->setInfo($this->lng->txt('sorting_info_manual'));
		$sog->addOption($sti);
		
		$a_form->addItem($sog);
	}

	protected function getEditFormCustomValues(array &$a_values)
	{
		// we cannot use $this->object->getOrderType()
		// if set to inherit it will be translated to parent setting
		include_once './Services/Container/classes/class.ilContainerSortingSettings.php';
		$sort = new ilContainerSortingSettings($this->object->getId());
		$a_values["sor"] = $sort->getSortMode();
	}

	protected function updateCustom(ilPropertyFormGUI $a_form)
	{
		// Save sorting
		include_once './Services/Container/classes/class.ilContainerSortingSettings.php';
		$sort = new ilContainerSortingSettings($this->object->getId());
		$sort->setSortMode($a_form->getInput('sor'));
		$sort->update();
	}
	
	// BEGIN ChangeEvent show info screen on folder object
	/**
	* this one is called from the info button in the repository
	* not very nice to set cmdClass/Cmd manually, if everything
	* works through ilCtrl in the future this may be changed
	*/
	function showSummaryObject()
	{
		$this->ctrl->setCmd("showSummary");
		$this->ctrl->setCmdClass("ilinfoscreengui");
		$this->infoScreen();
	}
	
	/**
	* this one is called from the info button in the repository
	* not very nice to set cmdClass/Cmd manually, if everything
	* works through ilCtrl in the future this may be changed
	*/
	function infoScreenObject()
	{
		$this->ctrl->setCmd("showSummary");
		$this->ctrl->setCmdClass("ilinfoscreengui");
		$this->infoScreen();
	}

	/**
	* show information screen
	*/
	function infoScreen()
	{
		global $ilAccess;

		if (!$ilAccess->checkAccess("visible", "", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);

		$info->enablePrivateNotes();
		
		if ($ilAccess->checkAccess("read", "", $_GET["ref_id"]))
		{
			$info->enableNews();
		}

		// no news editing for files, just notifications
		$info->enableNewsEditing(false);
		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]))
		{
			$news_set = new ilSetting("news");
			$enable_internal_rss = $news_set->get("enable_rss_for_internal");
			
			if ($enable_internal_rss)
			{
				$info->setBlockProperty("news", "settings", true);
				$info->setBlockProperty("news", "public_notifications_option", true);
			}
		}

		
		// standard meta data
		$info->addMetaDataSections($this->object->getId(),0, $this->object->getType());
		
		// forward the command
		$this->ctrl->forwardCommand($info);
	}
	// END ChangeEvent show info screen on folder object

	/**
	* Get tabs
	*/
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem, $ilUser, $lng, $ilCtrl,$ilAccess;

		$this->ctrl->setParameter($this,"ref_id",$this->ref_id);

		$tabs_gui->setTabActive("");
		if ($rbacsystem->checkAccess('read',$this->ref_id))
		{
			$tabs_gui->addTab("view_content", $lng->txt("content"),
				$this->ctrl->getLinkTarget($this, ""));

			//BEGIN ChangeEvent add info tab to category object
			$force_active = ($this->ctrl->getNextClass() == "ilinfoscreengui"
				|| strtolower($_GET["cmdClass"]) == "ilnotegui")
				? true
				: false;
			$tabs_gui->addTarget("info_short",
				 $this->ctrl->getLinkTargetByClass(
				 array("ilobjfoldergui", "ilinfoscreengui"), "showSummary"),
				 array("showSummary","", "infoScreen"),
				 "", "", $force_active);
			//END ChangeEvent add info tab to category object
		}
		
		if ($rbacsystem->checkAccess('write',$this->ref_id))
		{
			$tabs_gui->addTarget("settings",
				$this->ctrl->getLinkTarget($this, "edit"), "edit", "", "", ($ilCtrl->getCmd() == "edit"));
		}

		// learning progress
		include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
		if(ilLearningProgressAccess::checkAccess($this->object->getRefId()))
		{
			$tabs_gui->addTarget('learning_progress',
								 $this->ctrl->getLinkTargetByClass(array('ilobjfoldergui','illearningprogressgui'),''),
								 '',
								 array('illplistofobjectsgui','illplistofsettingsgui','illearningprogressgui','illplistofprogressgui'));
		}
		
		if($ilAccess->checkAccess('write','',$this->object->getRefId()))
		{
			$tabs_gui->addTarget(
				'export',
				$this->ctrl->getLinkTargetByClass('ilexportgui',''),
				'export',
				'ilexportgui'
			);
		}
		

		if ($rbacsystem->checkAccess('edit_permission',$this->ref_id))
		{
			$tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}

		// show clipboard in repository
		if ($this->ctrl->getTargetScript() == "repository.php" and !empty($_SESSION['il_rep_clipboard']))
		{
			$tabs_gui->addTarget("clipboard",
				 $this->ctrl->getLinkTarget($this, "clipboard"), "clipboard", get_class($this));
		}

	}

	// Methods for ConditionHandlerInterface
	function initConditionHandlerGUI($item_id)
	{
		include_once './Services/AccessControl/classes/class.ilConditionHandlerInterface.php';

		if(!is_object($this->chi_obj))
		{
			if($_GET['item_id'])
			{
				$this->chi_obj =& new ilConditionHandlerInterface($this,$item_id);
				$this->ctrl->saveParameter($this,'item_id',$_GET['item_id']);
			}
			else
			{
				$this->chi_obj =& new ilConditionHandlerInterface($this);
			}
		}
		return true;
	}

	/**
	* set sub tabs
	*/
	function __setSubTabs($a_tab)
	{
		global $rbacsystem,$ilUser;
	
		switch ($a_tab)
		{
				
			case "activation":
				
				$this->tabs_gui->addSubTabTarget("activation",
												 $this->ctrl->getLinkTargetByClass('ilCourseItemAdministrationGUI','edit'),
												 "edit", get_class($this));
				$this->ctrl->setParameterByClass('ilconditionhandlerinterface','item_id',(int) $_GET['item_id']);
				$this->tabs_gui->addSubTabTarget("preconditions",
												 $this->ctrl->getLinkTargetByClass('ilConditionHandlerInterface','listConditions'),
												 "", "ilConditionHandlerInterface");
				break;
		}
	}

	/**
	* goto target group
	*/
	function _goto($a_target)
	{
		global $ilAccess, $ilErr, $lng;

		if ($ilAccess->checkAccess("read", "", $a_target))
		{
			ilObjectGUI::_gotoRepositoryNode($a_target);
		}
		$ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
	}


	public function downloadFolderObject () {
		global $ilAccess, $ilErr, $lng;
			
		if (!$ilAccess->checkAccess("read", "", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}
		$filename = $this->object->downloadFolder();
		ilUtil::deliverFile($filename, ilUtil::getASCIIFilename($this->object->getTitle().".zip"));				
	}
	
	/**
	* Modify Item ListGUI for presentation in container
	*/
	function modifyItemGUI($a_item_list_gui, $a_item_data, $a_show_path)
	{
		global $tree;

		// if folder is in a course, modify item list gui according to course requirements
		if ($course_ref_id = $tree->checkForParentType($this->object->getRefId(),'crs'))
		{
			include_once("./Modules/Course/classes/class.ilObjCourse.php");
			include_once("./Modules/Course/classes/class.ilObjCourseGUI.php");
			$course_obj_id = ilObject::_lookupObjId($course_ref_id);
			ilObjCourseGUI::_modifyItemGUI($a_item_list_gui, 'ilcoursecontentgui', $a_item_data, $a_show_path,
				ilObjCourse::_lookupAboStatus($course_obj_id), $course_ref_id, $course_obj_id,
				$this->object->getRefId());
		}
	}
	
	protected function forwardToTimingsView()
	{
		global $tree;
		
		if(!$crs_ref = $tree->checkForParentType($this->ref_id, 'crs'))
		{
			return false;
		}
		include_once './Modules/Course/classes/class.ilObjCourse.php';
		if(!$this->ctrl->getCmd() and ilObjCourse::_lookupViewMode(ilObject::_lookupObjId($crs_ref)) == ilContainer::VIEW_TIMING)
		{
			if(!isset($_SESSION['crs_timings'])) {
				$_SESSION['crs_timings'] = true;
			}
			
			if($_SESSION['crs_timings'] == true) {
				include_once './Modules/Course/classes/class.ilCourseContentGUI.php';
				$course_content_obj = new ilCourseContentGUI($this);
				$this->ctrl->setCmdClass(get_class($course_content_obj));
				$this->ctrl->setCmd('editUserTimings');
				$this->ctrl->forwardCommand($course_content_obj);
				return true;
			}
		}
		$_SESSION['crs_timings'] = false;
		return false;
	}
	

} // END class.ilObjFolderGUI
?>
