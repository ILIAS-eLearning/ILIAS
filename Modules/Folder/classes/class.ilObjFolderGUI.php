<?php

/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilObjFolderGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id$
*
* @ilCtrl_Calls ilObjFolderGUI: ilPermissionGUI
* @ilCtrl_Calls ilObjFolderGUI: ilCourseContentGUI, ilLearningProgressGUI
* @ilCtrl_Calls ilObjFolderGUI: ilInfoScreenGUI, ilContainerPageGUI, ilColumnGUI
* @ilCtrl_Calls ilObjFolderGUI: ilObjectCopyGUI, ilObjStyleSheetGUI
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
		
		$this->checkPermission('read');

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
		
		$this->checkPermission('read');

		$ilTabs->activateTab("view_content");
		$ret =  parent::renderObject();
		return $ret;
	}

	function &executeCommand()
	{
		global $ilUser,$ilCtrl;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		
		// show repository tree
		$this->showRepTree(true);

		switch ($next_class)
		{			
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
			
			case "illearningprogressgui":
				$this->prepareOutput();
				include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';
				
				$new_gui =& new ilLearningProgressGUI(ilLearningProgressGUI::LP_CONTEXT_REPOSITORY,
													  $this->object->getRefId(),
													  $_GET['user_id'] ? $_GET['user_id'] : $ilUser->getId());
				$this->ctrl->forwardCommand($new_gui);
				$this->tabs_gui->setTabActive('learning_progress');
				break;

			// container page editing
			case "ilcontainerpagegui":
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
			case 'ilcolumngui':
				$this->tabs_gui->setTabActive('none');
				$this->checkPermission("read");
				$this->viewObject();
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
		
		$this->initSortingForm(
			$a_form,
			array(
				ilContainer::SORT_INHERIT,
				ilContainer::SORT_TITLE,
				ilContainer::SORT_CREATION,
				ilContainer::SORT_MANUAL
			)
		);
	}

	protected function getEditFormCustomValues(array &$a_values)
	{
		// we cannot use $this->object->getOrderType()
		// if set to inherit it will be translated to parent setting
		#include_once './Services/Container/classes/class.ilContainerSortingSettings.php';
		#$sort = new ilContainerSortingSettings($this->object->getId());
		#$a_values["sor"] = $sort->getSortMode();
	}

	protected function updateCustom(ilPropertyFormGUI $a_form)
	{
		$this->saveSortingSettings($a_form);
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
	
	protected function afterSave(ilObject $a_new_object)
	{	
		include_once './Services/Container/classes/class.ilContainerSortingSettings.php';
		$sort = new ilContainerSortingSettings($a_new_object->getId());
		$sort->setSortMode(ilContainer::SORT_INHERIT);
		$sort->update();
		
		// always send a message
		ilUtil::sendSuccess($this->lng->txt("fold_added"),true);
		$this->ctrl->setParameter($this, "ref_id", $a_new_object->getRefId());
		$this->redirectToRefId($a_new_object->getRefId(), "");
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
		global $rbacsystem, $ilUser, $lng, $ilCtrl,$ilAccess, $ilHelp;

		$this->ctrl->setParameter($this,"ref_id",$this->ref_id);
		
		$ilHelp->setScreenIdComponent("fold");

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
		if ($_GET["baseClass"] == "ilRepositoryGUI" and !empty($_SESSION['il_rep_clipboard']))
		{
			$tabs_gui->addTarget("clipboard",
				 $this->ctrl->getLinkTarget($this, "clipboard"), "clipboard", get_class($this));
		}

	}

	/**
	* goto target group
	*/
	public static function _goto($a_target)
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
	
	/**
	 * Edit
	 *
	 * @param
	 * @return
	 */
	public function editObject()
	{
		global $ilTabs, $ilErr;
		
		$this->setSubTabs("settings");
		$ilTabs->activateTab("settings");

		if (!$this->checkPermissionBool("write"))
		{
			$ilErr->raiseError($this->lng->txt("msg_no_perm_write"),$ilErr->MESSAGE);
		}

		$form = $this->initEditForm();
		$values = $this->getEditFormValues();
		if($values)
		{
			$form->setValuesByArray($values,TRUE);
		}
		$GLOBALS['tpl']->setContent($form->getHTML());
	}
	
	
	/**
	 * Set sub tabs
	 */
	function setSubTabs($a_tab)
	{
		global $ilTabs, $lng;
		
		$ilTabs->addSubTab("settings",
			$lng->txt("fold_settings"),
			$this->ctrl->getLinkTarget($this,'edit'));
		
		// custom icon
		if ($this->ilias->getSetting("custom_icons"))
		{
			$ilTabs->addSubTab("icons",
				$lng->txt("icon_settings"),
				$this->ctrl->getLinkTarget($this,'editIcons'));
		}
		
		$ilTabs->activateSubTab($a_tab);
		$ilTabs->activateTab("settings");
	}

	
	////
	//// Icons
	////
	
	/**
	 * Edit folder icons
	 */
	function editIconsObject($a_form = null)
	{
		global $tpl;

		$this->checkPermission('write');
	
		$this->tabs_gui->setTabActive('settings');
		
		if(!$a_form)
		{
			$a_form = $this->initIconsForm();
		}
		
		$tpl->setContent($a_form->getHTML());
	}

	function initIconsForm()
	{
		$this->setSubTabs("icons");
		
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));	
		
		$this->showCustomIconsEditing(1, $form);
		
		// $form->setTitle($this->lng->txt('edit_grouping'));
		$form->addCommandButton('updateIcons', $this->lng->txt('save'));					
		
		return $form;
	}
	
	/**
	* update container icons
	*/
	function updateIconsObject()
	{
		global $ilSetting;

		$this->checkPermission('write');
		
		$form = $this->initIconsForm();
		if($form->checkInput())
		{
			//save custom icons
			if ($ilSetting->get("custom_icons"))
			{
				if($_POST["cont_icon_delete"])
				{
					$this->object->removeCustomIcon();
				}
				$this->object->saveIcons($_FILES["cont_icon"]['tmp_name']);
			}
			ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"),true);
			$this->ctrl->redirect($this,"editIcons");
		}

		$form->setValuesByPost();
		$this->editIconsObject($form);	
	}

	

} // END class.ilObjFolderGUI
?>
