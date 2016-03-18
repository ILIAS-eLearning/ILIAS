<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObjectGUI.php";

/**
* Class ilObjExerciseGUI
*
* @author Stefan Meyer <smeyer@databay.de>
* @author Alex Killing <alex.killing@gmx.de>
* @author Michael Jansen <mjansen@databay.de>
* $Id$
* 
* @ilCtrl_Calls ilObjExerciseGUI: ilPermissionGUI, ilLearningProgressGUI, ilInfoScreenGUI
* @ilCtrl_Calls ilObjExerciseGUI: ilObjectCopyGUI, ilExportGUI, ilShopPurchaseGUI
* @ilCtrl_Calls ilObjExerciseGUI: ilCommonActionDispatcherGUI, ilCertificateGUI 
* @ilCtrl_Calls ilObjExerciseGUI: ilExAssignmentEditorGUI, ilExSubmissionGUI
* @ilCtrl_Calls ilObjExerciseGUI: ilExerciseManagementGUI, ilExcCriteriaCatalogueGUI
* 
* @ingroup ModulesExercise
*/
class ilObjExerciseGUI extends ilObjectGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjExerciseGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output = true)
	{
		global $lng;
		
		$this->type = "exc";
		$this->ilObjectGUI($a_data,$a_id,$a_call_by_reference,false);
		
		$lng->loadLanguageModule("exercise");
		$lng->loadLanguageModule("exc");
		$this->ctrl->saveParameter($this, "ass_id");
		
		if ($_REQUEST["ass_id"] > 0)
		{
			include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
			$this->ass = new ilExAssignment((int) $_REQUEST["ass_id"]);
		}
	}

	function executeCommand()
	{
  		global $ilUser,$ilCtrl, $ilTabs, $lng;
  
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		$this->prepareOutput();
  
//echo "-".$next_class."-".$cmd."-"; exit;
  		switch($next_class)
		{
			case "ilinfoscreengui":
				$ilTabs->activateTab("info");
				$this->infoScreen();	// forwards command
				break;

			case 'ilpermissiongui':
				$ilTabs->activateTab("permissions");
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
			break;
	
			case "illearningprogressgui":
				$ilTabs->activateTab("learning_progress");
				include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';
	
				$new_gui =& new ilLearningProgressGUI(ilLearningProgressGUI::LP_CONTEXT_REPOSITORY,
					$this->object->getRefId(),
					$_GET['user_id'] ? $_GET['user_id'] : $ilUser->getId());
				$this->ctrl->forwardCommand($new_gui);
				$this->tabs_gui->setTabActive('learning_progress');
			break;
			
			case 'ilobjectcopygui':
				$ilCtrl->saveParameter($this, 'new_type');
				$ilCtrl->setReturnByClass(get_class($this),'create');

				include_once './Services/Object/classes/class.ilObjectCopyGUI.php';
				$cp = new ilObjectCopyGUI($this);
				$cp->setType('exc');
				$this->ctrl->forwardCommand($cp);
				break;

			case "ilexportgui":
				$ilTabs->activateTab("export");
				include_once("./Services/Export/classes/class.ilExportGUI.php");
				$exp_gui = new ilExportGUI($this);
				$exp_gui->addFormat("xml");
				$ret = $this->ctrl->forwardCommand($exp_gui);
//				$this->tpl->show();
				break;
			
			case 'ilshoppurchasegui':
				include_once './Services/Payment/classes/class.ilShopPurchaseGUI.php';
				$sp = new ilShopPurchaseGUI($_GET['ref_id']);

				$this->ctrl->forwardCommand($sp);
				break;
			
			case "ilcommonactiondispatchergui":
				include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				$this->ctrl->forwardCommand($gui);
				break;
			
			case "ilcertificategui":
				$this->setSettingsSubTabs();
				$this->tabs_gui->activateTab("settings");
				$this->tabs_gui->activateSubTab("certificate");
				include_once "./Services/Certificate/classes/class.ilCertificateGUI.php";
				include_once "./Modules/Exercise/classes/class.ilExerciseCertificateAdapter.php";
				$output_gui = new ilCertificateGUI(new ilExerciseCertificateAdapter($this->object));
				$this->ctrl->forwardCommand($output_gui);
				break;
			
			case "ilexassignmenteditorgui":
				$this->checkPermission("write");
				$ilTabs->activateTab("content");
				$this->addContentSubTabs("list_assignments");
				include_once("./Modules/Exercise/classes/class.ilExAssignmentEditorGUI.php");
				$ass_gui = new ilExAssignmentEditorGUI($this->object->getId(), $this->object->isCompletionBySubmissionEnabled(), $this->ass);
				$this->ctrl->forwardCommand($ass_gui);
				break;
			
			case "ilexsubmissiongui":
				$this->checkPermission("read");
				$ilTabs->activateTab("content");
				$this->addContentSubTabs("content");
				$this->ctrl->setReturn($this, "showOverview");
				include_once("./Modules/Exercise/classes/class.ilExSubmissionGUI.php");
				$sub_gui = new ilExSubmissionGUI($this->object, $this->ass, (int)$_REQUEST["member_id"]);
				$this->ctrl->forwardCommand($sub_gui);
				break;
			
			case "ilexercisemanagementgui":
				$this->checkPermission("write");
				$ilTabs->activateTab("grades");				
				include_once("./Modules/Exercise/classes/class.ilExerciseManagementGUI.php");
				$mgmt_gui = new ilExerciseManagementGUI($this->object, $this->ass);
				$this->ctrl->forwardCommand($mgmt_gui);
				break;
			
			case "ilexccriteriacataloguegui":
				$this->checkPermission("write");
				$ilTabs->activateTab("settings");	
				$this->setSettingsSubTabs();
				$ilTabs->activateSubTab("crit");
				include_once("./Modules/Exercise/classes/class.ilExcCriteriaCatalogueGUI.php");
				$crit_gui = new ilExcCriteriaCatalogueGUI($this->object);
				$this->ctrl->forwardCommand($crit_gui);
				break;
				
			default:						
				if(!$cmd)
				{
					$cmd = "infoScreen";
				}
	
				$cmd .= "Object";
	
				$this->$cmd();
	
			break;
		}
		
		$this->addHeaderAction();
  
  		return true;
	}

	function viewObject()
	{
		$this->infoScreenObject();
	}
	
	protected function afterSave(ilObject $a_new_object)
	{
		global $ilCtrl;
		
		$a_new_object->saveData();
		
		ilUtil::sendSuccess($this->lng->txt("exc_added"), true);
		
		$ilCtrl->setParameterByClass("ilExAssignmentEditorGUI", "ref_id", $a_new_object->getRefId());
		$ilCtrl->redirectByClass("ilExAssignmentEditorGUI", "addAssignment");
	}

	protected function listAssignmentsObject()
	{
		global $ilCtrl;
		
		$this->checkPermissionBool("write");
		
		// #16587
		$ilCtrl->redirectByClass("ilExAssignmentEditorGUI", "listAssignments");
	}
	
	/**
	* Init properties form.
	*/
	protected function initEditCustomForm(ilPropertyFormGUI $a_form)
	{
		$a_form->setTitle($this->lng->txt("exc_edit_exercise"));

		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('exc_passing_exc'));
		$a_form->addItem($section);

		// pass mode
		$radg = new ilRadioGroupInputGUI($this->lng->txt("exc_pass_mode"), "pass_mode");
	
			$op1 = new ilRadioOption($this->lng->txt("exc_pass_all"), "all",
				$this->lng->txt("exc_pass_all_info"));
			$radg->addOption($op1);
			$op2 = new ilRadioOption($this->lng->txt("exc_pass_minimum_nr"), "nr",
				$this->lng->txt("exc_pass_minimum_nr_info"));
			$radg->addOption($op2);

			// minimum number of assignments to pass
			$ni = new ilNumberInputGUI($this->lng->txt("exc_min_nr"), "pass_nr");
			$ni->setSize(4);
			$ni->setMaxLength(4);
			$ni->setRequired(true);
			include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
			$mand = ilExAssignment::countMandatory($this->object->getId());
			$min = max($mand, 1);
			$ni->setMinValue($min, true);
			$ni->setInfo($this->lng->txt("exc_min_nr_info"));
			$op2->addSubItem($ni);

		$a_form->addItem($radg);

		// completion by submission
		$subcompl = new ilRadioGroupInputGUI($this->lng->txt("exc_passed_status_determination"), "completion_by_submission");
			$op1 = new ilRadioOption($this->lng->txt("exc_completion_by_tutor"), 0, "");
			$subcompl->addOption($op1);
			$op2 = new ilRadioOption($this->lng->txt("exc_completion_by_submission"), 1,$this->lng->txt("exc_completion_by_submission_info"));
			$subcompl->addOption($op2);
		$a_form->addItem($subcompl);

		/*$subcompl = new ilCheckboxInputGUI($this->lng->txt('exc_completion_by_submission'), 'completion_by_submission');
		$subcompl->setInfo($this->lng->txt('exc_completion_by_submission_info'));
		$subcompl->setValue(1);
		$a_form->addItem($subcompl);*/

		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('exc_publishing'));
		$a_form->addItem($section);

		// show submissions
		$cb = new ilCheckboxInputGUI($this->lng->txt("exc_show_submissions"), "show_submissions");
		$cb->setInfo($this->lng->txt("exc_show_submissions_info"));
		$a_form->addItem($cb);
		
		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('exc_notification'));
		$a_form->addItem($section);

		// submission notifications
		$cbox = new ilCheckboxInputGUI($this->lng->txt("exc_submission_notification"), "notification");
		$cbox->setInfo($this->lng->txt("exc_submission_notification_info"));
		$a_form->addItem($cbox);		
	}
	
	/**
	* Get values for properties form
	*/
	protected function getEditFormCustomValues(array &$a_values)
	{
		global $ilUser;

		$a_values["desc"] = $this->object->getLongDescription();
		$a_values["show_submissions"] = $this->object->getShowSubmissions();
		$a_values["pass_mode"] = $this->object->getPassMode();
		if ($a_values["pass_mode"] == "nr")
		{
			$a_values["pass_nr"] = $this->object->getPassNr();
		}
		
		include_once "./Services/Notification/classes/class.ilNotification.php";
		$a_values["notification"] = ilNotification::hasNotification(
				ilNotification::TYPE_EXERCISE_SUBMISSION, $ilUser->getId(),
				$this->object->getId());
				
		$a_values['completion_by_submission'] = (int) $this->object->isCompletionBySubmissionEnabled();
	}

	protected function updateCustom(ilPropertyFormGUI $a_form)
	{
		global $ilUser;
		$this->object->setShowSubmissions($a_form->getInput("show_submissions"));
		$this->object->setPassMode($a_form->getInput("pass_mode"));		
		if ($this->object->getPassMode() == "nr")
		{
			$this->object->setPassNr($a_form->getInput("pass_nr"));
		}
		
		$this->object->setCompletionBySubmission($a_form->getInput('completion_by_submission') == 1 ? true : false);
		
		include_once "./Services/Notification/classes/class.ilNotification.php";
		ilNotification::setNotification(ilNotification::TYPE_EXERCISE_SUBMISSION,
			$ilUser->getId(), $this->object->getId(),
			(bool)$a_form->getInput("notification"));
	}
  
	/**
	 * Add subtabs of content view
	 *
	 * @param	object		$tabs_gui		ilTabsGUI object
	 */
	function addContentSubTabs($a_activate)
	{
		global $ilTabs, $lng, $ilCtrl, $ilAccess;
		
		$ilTabs->addSubTab("content", $lng->txt("view"),
			$ilCtrl->getLinkTarget($this, "showOverview"));
		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{
			$ilTabs->addSubTab("list_assignments", $lng->txt("edit"),
				$ilCtrl->getLinkTargetByClass("ilExAssignmentEditorGUI", "listAssignments"));
		}
		$ilTabs->activateSubTab($a_activate);
	}

	/**
	* adds tabs to tab gui object
	*
	* @param	object		$tabs_gui		ilTabsGUI object
	*/
	function getTabs($tabs_gui)
	{
		global $ilAccess, $ilUser, $lng, $ilHelp;
  
		$ilHelp->setScreenIdComponent("exc");
		
		if ($ilAccess->checkAccess("read", "", $this->object->getRefId()))
		{
			$tabs_gui->addTab("content",
				$lng->txt("exc_assignments"),
				$this->ctrl->getLinkTarget($this, "showOverview"));
		}

		$next_class = strtolower($this->ctrl->getNextClass());
		if ($ilAccess->checkAccess("visible", "", $this->object->getRefId()))
		{
			$tabs_gui->addTab("info",
				$lng->txt("info_short"),
				$this->ctrl->getLinkTargetByClass("ilinfoscreengui", "showSummary"));
		}

		// edit properties
		if ($ilAccess->checkAccess("write", "", $this->ref_id))
		{
			/*$tabs_gui->addTab("assignments",
				$lng->txt("exc_edit_assignments"),
				$this->ctrl->getLinkTarget($this, 'listAssignments'));*/
			
			$tabs_gui->addTab("settings",
				$lng->txt("settings"),
				$this->ctrl->getLinkTarget($this, 'edit'));
			
			$tabs_gui->addTab("grades",
				$lng->txt("exc_submissions_and_grades"),
				$this->ctrl->getLinkTargetByClass("ilexercisemanagementgui", "members"));
		}

		// learning progress
		$save_sort_order = $_GET["sort_order"];		// hack, because exercise sort parameters
		$save_sort_by = $_GET["sort_by"];			// must not be forwarded to learning progress
		$save_offset = $_GET["offset"];
		$_GET["offset"] = $_GET["sort_by"] = $_GET["sort_order"] = "";
		
		include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
		if(ilLearningProgressAccess::checkAccess($this->object->getRefId()))
		{
			$tabs_gui->addTab('learning_progress',
				$lng->txt('learning_progress'),
				$this->ctrl->getLinkTargetByClass(array('ilobjexercisegui','illearningprogressgui'),''));
		}

		$_GET["sort_order"] = $save_sort_order;		// hack, part ii
		$_GET["sort_by"] = $save_sort_by;
		$_GET["offset"] = $save_offset;

		// export
		if ($ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			$tabs_gui->addTab("export",
				$lng->txt("export"),
				$this->ctrl->getLinkTargetByClass("ilexportgui", ""));
		}


		// permissions
		if ($ilAccess->checkAccess("edit_permission", "", $this->ref_id))
		{
			$tabs_gui->addTab('permissions',
				$lng->txt("perm_settings"),
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"));
		}
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
		global $ilAccess, $ilUser, $ilTabs, $lng;
		
		$ilTabs->activateTab("info");

		$this->checkPermission("visible");

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);
		
		$info->enablePrivateNotes();
		
		$info->enableNews();
		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]))
		{
			$info->enableNewsEditing();
			$info->setBlockProperty("news", "settings", true);
		}
		
		// standard meta data
		//$info->addMetaDataSections($this->object->getId(),0, $this->object->getType());

		// instructions
		$info->addSection($this->lng->txt("exc_overview"));
		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
		$ass = ilExAssignment::getAssignmentDataOfExercise($this->object->getId());
		$cnt = 0;
		$mcnt = 0;
		foreach ($ass as $a)
		{
			$cnt++;
			if ($a["mandatory"])
			{
				$mcnt++;
			}
		}
		$info->addProperty($lng->txt("exc_assignments"), $cnt);
		$info->addProperty($lng->txt("exc_mandatory"), $mcnt);
		if ($this->object->getPassMode() != "nr")
		{
			$info->addProperty($lng->txt("exc_pass_mode"),
				$lng->txt("exc_msg_all_mandatory_ass"));
		}
		else
		{
			$info->addProperty($lng->txt("exc_pass_mode"),
				sprintf($lng->txt("exc_msg_min_number_ass"), $this->object->getPassNr()));
		}

		// feedback from tutor
		include_once("Services/Tracking/classes/class.ilLPMarks.php");
		if ($ilAccess->checkAccess("read", "", $this->ref_id))
		{
			$lpcomment = ilLPMarks::_lookupComment($ilUser->getId(), $this->object->getId());
			$mark = ilLPMarks::_lookupMark($ilUser->getId(), $this->object->getId());
			//$status = ilExerciseMembers::_lookupStatus($this->object->getId(), $ilUser->getId());
			$st = $this->object->determinStatusOfUser($ilUser->getId());
			$status = $st["overall_status"];
			if ($lpcomment != "" || $mark != "" || $status != "notgraded")
			{
				$info->addSection($this->lng->txt("exc_feedback_from_tutor"));
				if ($lpcomment != "")
				{
					$info->addProperty($this->lng->txt("exc_comment"),
						$lpcomment);
				}
				if ($mark != "")
				{
					$info->addProperty($this->lng->txt("exc_mark"),
						$mark);
				}

				//if ($status == "") 
				//{
				//  $info->addProperty($this->lng->txt("status"),
				//		$this->lng->txt("message_no_delivered_files"));				
				//}
				//else
				if ($status != "notgraded")
				{
					$img = '<img src="'.ilUtil::getImagePath("scorm/".$status.".svg").'" '.
						' alt="'.$lng->txt("exc_".$status).'" title="'.$lng->txt("exc_".$status).
						'" />';

					$add = "";
					if ($st["failed_a_mandatory"])
					{
						$add = " (".$lng->txt("exc_msg_failed_mandatory").")";
					}
					else if ($status == "failed")
					{
						$add = " (".$lng->txt("exc_msg_missed_minimum_number").")";
					}
					$info->addProperty($this->lng->txt("status"),
						$img." ".$this->lng->txt("exc_".$status).$add);
				}
			}
		}
		
		// forward the command
		$this->ctrl->forwardCommand($info);
	}
	
	function editObject() 
	{
		$this->setSettingsSubTabs();
		$this->tabs_gui->activateSubTab("edit");
		return parent::editObject();
	}
	
	protected function setSettingsSubTabs()
	{
		$this->tabs_gui->addSubTab("edit",
			$this->lng->txt("general_settings"),
			$this->ctrl->getLinkTarget($this, "edit"));
		
		$this->tabs_gui->addSubTab("crit",
			$this->lng->txt("exc_criteria_catalogues"),
			$this->ctrl->getLinkTargetByClass("ilexccriteriacataloguegui", ""));
		
		include_once "Services/Certificate/classes/class.ilCertificate.php";
		if(ilCertificate::isActive())
		{
			$this->tabs_gui->addSubTab("certificate",
				$this->lng->txt("certificate"),
				$this->ctrl->getLinkTarget($this, "certificate"));		
		}
	}

	/**
	* redirect script
	*
	* @param	string		$a_target
	*/
	public static function _goto($a_target, $a_raw)
	{
		global $ilErr, $lng, $ilAccess;

		$ass_id = null;
		$parts = explode("_", $a_raw);
		if(sizeof($parts) == 2)
		{
			$ass_id = (int)$parts[1];
		}
		
		if ($ilAccess->checkAccess("read", "", $a_target))
		{
			if($ass_id)
			{
				$_GET["ass_id_goto"] = $ass_id;
			}
			$_GET["ref_id"] = $a_target;
			$_GET["cmd"] = "showOverview";
			$_GET["baseClass"] = "ilExerciseHandlerGUI";
			include("ilias.php");
			exit;
		}
		else if ($ilAccess->checkAccess("visible", "", $a_target))
		{
			$_GET["ref_id"] = $a_target;
			$_GET["cmd"] = "infoScreen";
			$_GET["baseClass"] = "ilExerciseHandlerGUI";
			include("ilias.php");
			exit;
		}
		else if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID))
		{
			ilUtil::sendFailure(sprintf($lng->txt("msg_no_perm_read_item"),
				ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))), true);
			ilObjectGUI::_gotoRepositoryRoot();
		}
		
		$ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
	}		

	/**
	* Add locator item
	*/
	function addLocatorItems()
	{
		global $ilLocator;
		
		if (is_object($this->object))
		{
			// #17955
			$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, "showOverview"), "", $_GET["ref_id"]);
		}
	}
	
	
	////
	//// Assignments, Learner's View
	////

	/**
	 * Show overview of assignments
	 */
	function showOverviewObject()
	{
		global $tpl, $ilTabs, $ilUser, $ilToolbar;
		
		$this->checkPermission("read");
		
		include_once("./Services/Tracking/classes/class.ilLearningProgress.php");
		ilLearningProgress::_tracProgress($ilUser->getId(),$this->object->getId(),
			$this->object->getRefId(), 'exc');
		
		$ilTabs->activateTab("content");
		$this->addContentSubTabs("content");
		
		// show certificate?
		if($this->object->hasUserCertificate($ilUser->getId()))
		{					
			include_once "./Modules/Exercise/classes/class.ilExerciseCertificateAdapter.php";
			include_once "./Services/Certificate/classes/class.ilCertificate.php";
			$adapter = new ilExerciseCertificateAdapter($this->object);
			if(ilCertificate::_isComplete($adapter))
			{
				$ilToolbar->addButton($this->lng->txt("certificate"),
					$this->ctrl->getLinkTarget($this, "outCertificate"));
			}
		}	
		
		include_once("./Modules/Exercise/classes/class.ilExAssignmentGUI.php");
		$ass_gui = new ilExAssignmentGUI($this->object);
				
		include_once("./Modules/Exercise/classes/class.ilExAssignment.php");
		include_once("./Services/Accordion/classes/class.ilAccordionGUI.php");
		$acc = new ilAccordionGUI();
		$acc->setId("exc_ow_".$this->object->getId());

		$ass_data = ilExAssignment::getInstancesByExercise($this->object->getId());
		foreach ($ass_data as $ass)
		{
			// incoming assignment deeplink
			$force_open = false;
			if(isset($_GET["ass_id_goto"]) &&
				(int)$_GET["ass_id_goto"] == $ass->getId())
			{
				$force_open = true;
			}	
			
			$acc->addItem($ass_gui->getOverviewHeader($ass),
				$ass_gui->getOverviewBody($ass),
				$force_open);										
		}
		
		if (count($ass_data) < 2)
		{
			$acc->setBehaviour("FirstOpen");
		}
		else
		{
			$acc->setUseSessionStorage(true);
		}
		
		$tpl->setContent($acc->getHTML());
	}
	
	function certificateObject()
	{
		$this->setSettingsSubTabs();
		$this->tabs_gui->activateTab("settings");
		$this->tabs_gui->activateSubTab("certificate");
		
		include_once "./Services/Certificate/classes/class.ilCertificateGUI.php";
		include_once "./Modules/Exercise/classes/class.ilExerciseCertificateAdapter.php";
		$output_gui = new ilCertificateGUI(new ilExerciseCertificateAdapter($this->object));
		$output_gui->certificateEditor();				
	}
	
	function outCertificateObject()
	{
		global $ilUser;
	
		if($this->object->hasUserCertificate($ilUser->getId()))
		{	
			ilUtil::sendFailure($this->lng->txt("msg_failed"));
			$this->showOverviewObject();			
		}
		
		include_once "./Services/Certificate/classes/class.ilCertificate.php";
		include_once "./Modules/Exercise/classes/class.ilExerciseCertificateAdapter.php";
		$certificate = new ilCertificate(new ilExerciseCertificateAdapter($this->object));
		$certificate->outCertificate(array("user_id" => $ilUser->getId()));					
	}		
	
}

?>