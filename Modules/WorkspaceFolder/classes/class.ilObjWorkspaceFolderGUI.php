<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObject2GUI.php";

/**
* Class ilObjWorkspaceFolderGUI
*
* @author Alex Killing <alex.killing@gmx.de>
* $Id: class.ilObjFolderGUI.php 25134 2010-08-13 14:22:11Z smeyer $
*
* @ilCtrl_Calls ilObjWorkspaceFolderGUI: ilInfoScreenGUI, ilPermissionGUI, 
*
* @extends ilObject2GUI
*/
class ilObjWorkspaceFolderGUI extends ilObject2GUI
{
	var $folder_tree;		// folder tree

	function getType()
	{
		return "wsfold";
	}

	function &executeCommand()
	{
		global $ilUser,$ilCtrl;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		switch ($next_class)
		{
			default:

				// $this->prepareOutput();
				// Dirty hack for course timings view
				if($this->forwardToTimingsView())
				{
					break;
				}

				if (empty($cmd))
				{
					$cmd = "view";
				}
				$this->$cmd();
				break;
		}
	}

	/**
	* set tree
	*/
	function setFolderTree($a_tree)
	{
		$this->folder_tree =& $a_tree;
	}
	
	/**
	 * Init object import form
	 *
	 * @param        string        new type
	 */
	public function initImportForm($a_new_type = "")
	{
		global $lng, $ilCtrl;

		$lng->loadLanguageModule("fold");

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		$this->form->setTableWidth('600px');
		$this->form->setTarget("_top");

		// Import file
		include_once("./Services/Form/classes/class.ilFileInputGUI.php");
		$fi = new ilFileInputGUI($lng->txt("import_file"), "importfile");
		$fi->setSuffixes(array("zip"));
		$fi->setRequired(true);
		$this->form->addItem($fi);

		$this->form->addCommandButton("importFile", $lng->txt("import"));
		$this->form->addCommandButton("cancel", $lng->txt("cancel"));
		$this->form->setTitle($lng->txt($a_new_type."_import"));

		$this->form->setFormAction($ilCtrl->getFormAction($this));
	}
	
	/**
	 * Import object
	 * @return 
	 */
	public function importFile()
	{
		global $lng;
		
		if(parent::importFileObject())
		{
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$this->ctrl->returnToParent($this);
		}
	}
	
	/**
	 * Save object
	 * @return 
	 */
	public function afterSave($fold)
	{
		$this->ctrl->returnToParent($this);
	}
	
	/**
	 * Update object
	 * @return 
	 */
	/*
	public function edit()
	{
		$this->tabs_gui->setTabActive('settings');
		$this->initFormEdit();
		
		include_once './Services/Container/classes/class.ilContainerSortingSettings.php';
		$this->form->getItemByPostVar('tit')->setValue($this->object->getTitle());
		$this->form->getItemByPostVar('des')->setValue($this->object->getDescription());
		$this->form->getItemByPostVar('sor')->setValue($this->object->getOrderType());
#			ilContainerSortingSettings::_readSortMode($this->object->getId())
#		);

		$this->tpl->setContent($this->form->getHTML());
		return true;
	}
	 */
	
	/*
	public function update()
	{
		global $ilUser;

		$this->initFormEdit();
		if($this->form->checkInput())
		{
			$this->object->setTitle($this->form->getInput('tit'));
			$this->object->setDescription($this->form->getInput('des'));
			$this->object->update();
			
			// Save sorting
			include_once './Services/Container/classes/class.ilContainerSortingSettings.php';
			$sort = new ilContainerSortingSettings($this->object->getId());
			$sort->setSortMode($this->form->getInput('sor'));
			$sort->update();
			
			include_once 'Services/Tracking/classes/class.ilChangeEvent.php';
			if (ilChangeEvent::_isActive())
			{
				ilChangeEvent::_recordWriteEvent($this->object->getId(), $ilUser->getId(), 'update');
				ilChangeEvent::_catchupWriteEvents($this->object->getId(), $ilUser->getId());
			}
			
			ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"),true);
			$this->ctrl->redirect($this,'edit');
		}
		$this->form->setValuesByPost();
		$this->tabs_gui->setTabActive('settings');
		$this->tpl->setContent($this->form->getHTML());
		return true;
	}
	 */
	
	/**
	 * Init edit form
	 * @return 
	 */
	/*
	protected function initFormEdit()
	{
		global $tree;
		
		if($this->form instanceof ilPropertyFormGUI)
		{
			return true;			
		}
		include_once './Services/Form/classes/class.ilPropertyFormGUI.php';
		$this->form = new ilPropertyFormGUI();
		$this->form->setFormAction($this->ctrl->getFormAction($this,'update'));
		$this->form->setTitle($this->lng->txt($this->type.'_edit'));
		
		// Title
		$tit = new ilTextInputGUI($this->lng->txt('title'),'tit');
		$tit->setRequired(true);
		$tit->setMaxLength(128);
		$this->form->addItem($tit);
		
		// Description
		$des = new ilTextAreaInputGUI($this->lng->txt('description'),'des');
		$this->form->addItem($des);
		
		// Sorting
		$sog = new ilRadioGroupInputGUI($this->lng->txt('sorting_header'),'sor');
		$sog->setRequired(true);
		
		// implicit: there is always a group or course in the path
		$sde = new ilRadioOption();
		$sde->setValue(ilContainer::SORT_INHERIT);
		
		$title = $this->lng->txt('sort_inherit_prefix');
		$title .= ' ('.ilContainerSortingSettings::sortModeToString(ilContainerSortingSettings::lookupSortModeFromParentContainer($this->object->getId())).') ';
		$sde->setTitle($title);
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
		
		$this->form->addItem($sog);

		$this->form->addCommandButton('update', $this->lng->txt('save'));
		$this->form->addCommandButton('cancel', $this->lng->txt('cancel'));
	}
	*/

	
		/**
	* updates object entry in object_data
	*
	* @access	public
	*/
	/*
	function update($a_return_to_parent = false)
	{
		$this->object->setTitle(ilUtil::stripSlashes($_POST["Fobject"]["title"]));
		$this->object->setDescription(ilUtil::stripSlashes($_POST["Fobject"]["desc"]));
		$this->update = $this->object->update();

		// BEGIN ChangeEvent: Record write event.
		require_once('Services/Tracking/classes/class.ilChangeEvent.php');
		if (ilChangeEvent::_isActive())
		{
			global $ilUser;
			ilChangeEvent::_recordWriteEvent($this->object->getId(), $ilUser->getId(), 'update');
			ilChangeEvent::_catchupWriteEvents($this->object->getId(), $ilUser->getId());
		}
		// END ChangeEvent: Record write event.
		
		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"),true);

		if ($a_return_to_parent)
		{
			$this->ctrl->returnToParent($this);
		}
		else
		{
			$this->ctrl->redirect($this);
		}
	}
	 */

	// BEGIN ChangeEvent show info screen on folder object
	/**
	* this one is called from the info button in the repository
	* not very nice to set cmdClass/Cmd manually, if everything
	* works through ilCtrl in the future this may be changed
	*/
	function showSummary()
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
	function setTabs()
	{
		global $rbacsystem, $ilUser, $lng, $ilCtrl,$ilAccess;

		$this->ctrl->setParameter($this,"ref_id",$this->ref_id);

		$this->tabs_gui->setTabActive("");
		if ($rbacsystem->checkAccess('read',$this->ref_id))
		{
			$this->tabs_gui->addTab("view_content", $lng->txt("content"),
				$this->ctrl->getLinkTarget($this, ""));

			//BEGIN ChangeEvent add info tab to category object
			$force_active = ($this->ctrl->getNextClass() == "ilinfoscreengui"
				|| strtolower($_GET["cmdClass"]) == "ilnotegui")
				? true
				: false;
			$this->tabs_gui->addTarget("info_short",
				 $this->ctrl->getLinkTargetByClass(
				 array("ilobjworkspacefoldergui", "ilinfoscreengui"), "showSummary"),
				 array("showSummary","", "infoScreen"),
				 "", "", $force_active);
			//END ChangeEvent add info tab to category object
		}
		
		if ($rbacsystem->checkAccess('write',$this->ref_id))
		{
			$this->tabs_gui->addTarget("settings",
				$this->ctrl->getLinkTarget($this, "edit"), "edit", "", "", ($ilCtrl->getCmd() == "edit"));
		}

		// learning progress
		include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
		if(ilLearningProgressAccess::checkAccess($this->object->getRefId()))
		{
			$this->tabs_gui->addTarget('learning_progress',
								 $this->ctrl->getLinkTargetByClass(array('ilobjworkspacefoldergui','illearningprogressgui'),''),
								 '',
								 array('illplistofobjectsgui','illplistofsettingsgui','illearningprogressgui','illplistofprogressgui'));
		}
		
		if($ilAccess->checkAccess('write','',$this->object->getRefId()))
		{
			$this->tabs_gui->addTarget(
				'export',
				$this->ctrl->getLinkTargetByClass('ilexportgui',''),
				'export',
				'ilexportgui'
			);
		}
		

		if ($rbacsystem->checkAccess('edit_permission',$this->ref_id))
		{
			$this->tabs_gui->addTarget("perm_settings",
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"), array("perm","info","owner"), 'ilpermissiongui');
		}

		// show clipboard in repository
		if ($this->ctrl->getTargetScript() == "repository.php" and !empty($_SESSION['il_rep_clipboard']))
		{
			$this->tabs_gui->addTarget("clipboard",
				 $this->ctrl->getLinkTarget($this, "clipboard"), "clipboard", get_class($this));
		}

	}

	// Methods for ConditionHandlerInterface
	function initConditionHandlerGUI($item_id)
	{
		include_once './classes/class.ilConditionHandlerInterface.php';

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
			$_GET["cmd"] = "frameset";
			$_GET["ref_id"] = $a_target;
			include("repository.php");
			exit;
		}
		/*
		else
		{
			// to do: force flat view
			
			// no info screen for folders
			if ($ilAccess->checkAccess("visible", "", $a_target))
			{
				$_GET["cmd"] = "infoScreen";
				$_GET["ref_id"] = $a_target;
				include("repository.php");
				exit;
			}
			else
			{
				// This part will never be reached
				if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID))
				{
					$_GET["cmd"] = "frameset";
					$_GET["target"] = "";
					$_GET["ref_id"] = ROOT_FOLDER_ID;
					ilUtil::sendInfo(sprintf($lng->txt("msg_no_perm_read_item"),
						ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))), true);
					include("repository.php");
					exit;
				}
			}
		}
		*/
		$ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
	}


	public function downloadFolder() {
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
