<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


require_once "./Services/Container/classes/class.ilContainerGUI.php";

/**
* Class ilObjCourseGUI
*
* @author Stefan Meyer <smeyer.ilias@gmx.de> 
* $Id$
*
* @ilCtrl_Calls ilObjCourseGUI: ilCourseRegistrationGUI, ilShopPurchaseGUI, ilCourseObjectivesGUI
* @ilCtrl_Calls ilObjCourseGUI: ilObjCourseGroupingGUI, ilMDEditorGUI, ilInfoScreenGUI, ilLearningProgressGUI, ilPermissionGUI
* @ilCtrl_Calls ilObjCourseGUI: ilRepositorySearchGUI, ilConditionHandlerInterface
* @ilCtrl_Calls ilObjCourseGUI: ilCourseContentGUI, ilPublicUserProfileGUI, ilMemberExportGUI
* @ilCtrl_Calls ilObjCourseGUI: ilObjectCustomUserFieldsGUI, ilMemberAgreementGUI, ilSessionOverviewGUI
* @ilCtrl_Calls ilObjCourseGUI: ilColumnGUI, ilPageObjectGUI, ilCourseItemAdministrationGUI
* @ilCtrl_Calls ilObjCourseGUI: ilLicenseOverviewGUI, ilObjectCopyGUI, ilObjStyleSheetGUI
* @ilCtrl_Calls ilObjCourseGUI: ilCourseParticipantsGroupsGUI, ilExportGUI, ilCommonActionDispatcherGUI
* @ilCtrl_Calls ilObjCourseGUI: ilDidacticTemplateGUI, ilCertificateGUI
*
* 
* @extends ilContainerGUI
*/
class ilObjCourseGUI extends ilContainerGUI
{
	/**
	* Constructor
	* @access public
	*/
	function ilObjCourseGUI()
	{
		global $ilCtrl, $ilHelp;

		// CONTROL OPTIONS
		$this->ctrl =& $ilCtrl;
		$this->ctrl->saveParameter($this,array("ref_id","cmdClass"));

		$this->type = "crs";
		$this->ilContainerGUI('',(int) $_GET['ref_id'],true,false);

		$this->lng->loadLanguageModule('crs');
		$ilHelp->addHelpSection('crs');

		$this->SEARCH_USER = 1;
		$this->SEARCH_GROUP = 2;
		$this->SEARCH_COURSE = 3;
	}

	function gatewayObject()
	{
		switch($_POST["action"])
		{

			case "deleteSubscribers":
				$this->deleteSubscribers();
				break;

			case "addSubscribers":
				$this->addSubscribers();
				break;

			case "addFromWaitingList":
				$this->addFromWaitingList();
				break;

			case "removeFromWaitingList":
				$this->removeFromWaitingList();
				break;

			default:
				$this->viewObject();
				break;
		}
		return true;
	}
	
	/**
	 * Gateway for member administration commands
	 *
	 * @access public
	 * 
	 */
	public function memberGatewayObject()
	{
		if(isset($_POST['btn_pressed']['deleteMembers']))
		{
			return $this->deleteMembersObject();
		}
		elseif($_POST['btn_pressed']['sendMailToSelectedUsers'])
		{
			return $this->sendMailToSelectedUsersObject();
		}		
		else
		{
			return $this->updateMembersObject();
		}
	}

	function sendMailToSelectedUsersObject()
	{
		if(isset($_GET['member_id']))
		{
			$_POST['member'] = array($_GET['member_id']);
		}
		else
		{
			$_POST['member'] = array_unique(array_merge((array) $_POST['members'],
				(array) $_POST['tutors'],
				(array) $_POST['admins'],
				(array) $_POST['waiting'],
				(array) $_POST['subscribers'],
				(array) $_POST['roles']
			));
		}
		

		if (!count($_POST["member"]))
		{
			ilUtil::sendFailure($this->lng->txt("no_checkbox"));
			$this->membersObject();
			return false;
		}
		foreach($_POST["member"] as $usr_id)
		{
			$rcps[] = ilObjUser::_lookupLogin($usr_id);
		}
        require_once 'Services/Mail/classes/class.ilMailFormCall.php';
		ilUtil::redirect(ilMailFormCall::_getRedirectTarget($this, 'members', 
			array(), 
			array(
				'type' => 'new', 
				'rcp_to' => implode(',',$rcps),
				'sig'	=> $this->createMailSignature()
		)));
	}
	
	/**
	* canceledObject is called when operation is canceled, method links back
	* @access	public
	*/
	function cancelMemberObject()
	{
		$this->__unsetSessionVariables();

		$return_location = "members";

		#ilUtil::sendSuccess($this->lng->txt("action_aborted"),true);
		ilUtil::redirect($this->ctrl->getLinkTarget($this,$return_location,"",false,false));
	}

	/**
	 * add course admin after import file
	 * @return 
	 */
	protected function afterImport(ilObject $a_new_object)
	{
		global $ilUser;
		
		$part = new ilCourseParticipants($a_new_object->getId());
		$part->add($ilUser->getId(), ilCourseConstants::CRS_ADMIN);
		$part->updateNotification($ilUser->getId(), 1);

		parent::afterImport($a_new_object);
	}

	function renderObject()
	{
		$this->ctrl->setCmd("view");
		$this->viewObject();
	}
	
	/**
	 * 
	 * @param
	 * @return
	 */
	protected function forwardToTimingsView()
	{
		if(!$this->ctrl->getCmd() and $this->object->getViewMode() == ilContainer::VIEW_TIMING)
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
	
	
	function viewObject()
	{
		global $rbacsystem, $ilUser, $ilCtrl;

		// CHECK ACCESS
		$this->checkPermission('read','view');
		/*
		if(!$rbacsystem->checkAccess("read",$this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}
		*/
		if (strtolower($_GET["baseClass"]) == "iladministrationgui")
		{
			parent::viewObject();
			return true;
		}
		
		// Fill meta header tags
		include_once('Services/MetaData/classes/class.ilMDUtils.php');
		ilMDUtils::_fillHTMLMetaTags($this->object->getId(),$this->object->getId(),'crs');
		
		// Trac access
		include_once 'Services/Tracking/classes/class.ilLearningProgress.php';
		ilLearningProgress::_tracProgress($ilUser->getId(),$this->object->getId(),
			$this->object->getRefId(),'crs');
		
		if(!$this->checkAgreement())
		{
			include_once('Services/Membership/classes/class.ilMemberAgreementGUI.php');
			$this->ctrl->setReturn($this,'view_content');
			$agreement = new ilMemberAgreementGUI($this->object->getRefId());
			$this->ctrl->setCmdClass(get_class($agreement));
			$this->ctrl->forwardCommand($agreement);
			return true;
		}

		if(!$this->__checkStartObjects())
		{
			$this->showStartObjects();
		}

		// views handled by general container logic
		if ($this->object->getViewMode() == ilContainer::VIEW_SIMPLE ||
			$this->object->getViewMode() == ilContainer::VIEW_BY_TYPE ||
			$this->object->getViewMode() == ilContainer::VIEW_SESSIONS ||
			$this->object->getViewMode() == ilContainer::VIEW_TIMING ||
			$this->object->getViewMode() == ilContainer::VIEW_OBJECTIVE
			)
		{
			$ret = parent::renderObject();
			return $ret;
		}
		else
		{
			include_once './Modules/Course/classes/class.ilCourseContentGUI.php';
			$course_content_obj = new ilCourseContentGUI($this);
	
			$this->ctrl->setCmdClass(get_class($course_content_obj));
			$this->ctrl->forwardCommand($course_content_obj);
		}

		return true;
	}

	function renderContainer()
	{
		return parent::renderObject();
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
		global $ilErr,$ilAccess, $ilUser, $ilSetting;

		$this->checkPermission('visible');
		
		// Fill meta header tags
		include_once('Services/MetaData/classes/class.ilMDUtils.php');
		ilMDUtils::_fillHTMLMetaTags($this->object->getId(),$this->object->getId(),'crs');

		$this->tabs_gui->setTabActive('info_short');

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		include_once 'Modules/Course/classes/class.ilCourseFile.php';

		$files =& ilCourseFile::_readFilesByCourse($this->object->getId());

		$info = new ilInfoScreenGUI($this);
		$info->enablePrivateNotes();
		$info->enableFeedback();
		$info->enableNews();
		if ($ilAccess->checkAccess("write", "", $_GET["ref_id"]))
		{
			$info->enableNewsEditing();
		}

		if(strlen($this->object->getImportantInformation()) or
		   strlen($this->object->getSyllabus()) or
		   count($files))
		{
			$info->addSection($this->lng->txt('crs_general_informations'));
		}

		if(strlen($this->object->getImportantInformation()))
		{
			$info->addProperty($this->lng->txt('crs_important_info'),
							   "<strong>".nl2br(
							   ilUtil::makeClickable($this->object->getImportantInformation(), true)."</strong>"));
		}
		if(strlen($this->object->getSyllabus()))
		{
			$info->addProperty($this->lng->txt('crs_syllabus'), nl2br(
								ilUtil::makeClickable ($this->object->getSyllabus(), true)));
		}
		// files
		if(count($files))
		{
			$tpl = new ilTemplate('tpl.event_info_file.html',true,true,'Modules/Course');
			
			foreach($files as $file)
			{
				$tpl->setCurrentBlock("files");
				$this->ctrl->setParameter($this,'file_id',$file->getFileId());
				$tpl->setVariable("DOWN_LINK",$this->ctrl->getLinkTarget($this,'sendfile'));
				$tpl->setVariable("DOWN_NAME",$file->getFileName());
				$tpl->setVariable("DOWN_INFO_TXT",$this->lng->txt('crs_file_size_info'));
				$tpl->setVariable("DOWN_SIZE",$file->getFileSize());
				$tpl->setVariable("TXT_BYTES",$this->lng->txt('bytes'));
				$tpl->parseCurrentBlock();
			}
			$info->addProperty($this->lng->txt('crs_file_download'),
							   $tpl->get());
		}

		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordGUI.php');
		$record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_INFO,'crs',$this->object->getId());
		$record_gui->setInfoObject($info);
		$record_gui->parse();
		
		// meta data
		$info->addMetaDataSections($this->object->getId(),0, $this->object->getType());
			 
		// contact
		if($this->object->hasContactData())
		{
			$info->addSection($this->lng->txt("crs_contact"));
		}
		if(strlen($this->object->getContactName()))
		{
			$info->addProperty($this->lng->txt("crs_contact_name"),
							   $this->object->getContactName());
		}
		if(strlen($this->object->getContactResponsibility()))
		{
			$info->addProperty($this->lng->txt("crs_contact_responsibility"),
							   $this->object->getContactResponsibility());
		}
		if(strlen($this->object->getContactPhone()))
		{
			$info->addProperty($this->lng->txt("crs_contact_phone"),
							   $this->object->getContactPhone());
		}
		if($this->object->getContactEmail())
		{
            require_once 'Services/Mail/classes/class.ilMailFormCall.php';
			$emails = split(",",$this->object->getContactEmail());
			foreach ($emails as $email) {
				$email = trim($email);
				$etpl = new ilTemplate("tpl.crs_contact_email.html", true, true , 'Modules/Course');
                $etpl->setVariable("EMAIL_LINK", ilMailFormCall::_getLinkTarget($info, 'showSummary', array(), 
					array('type' => 'new', 'rcp_to' => $email,'sig' => $this->createMailSignature())));
				$etpl->setVariable("CONTACT_EMAIL", $email);				
				$mailString .= $etpl->get()."<br />";
			}
			$info->addProperty($this->lng->txt("crs_contact_email"), $mailString);
		}
		if(strlen($this->object->getContactConsultation()))
		{
			$info->addProperty($this->lng->txt("crs_contact_consultation"),
							   nl2br($this->object->getContactConsultation()));
		}		
		//	
		// access
		//
		$info->addSection($this->lng->txt("access"));
		$info->showLDAPRoleGroupMappingInfo();
		
		// activation
		if($this->object->getOfflineStatus())
		{
			$info->addProperty($this->lng->txt('crs_visibility'),
							   $this->lng->txt('crs_visibility_unvisible'));
		}
		elseif($this->object->getActivationUnlimitedStatus())
		{
			$info->addProperty($this->lng->txt("crs_visibility"),
				$this->lng->txt('crs_unlimited'));
		}
		else
		{
			$info->addProperty($this->lng->txt('crs_visibility'),
				ilDatePresentation::formatPeriod(
					new ilDateTime($this->object->getActivationStart(),IL_CAL_UNIX),
					new ilDateTime($this->object->getActivationEnd(),IL_CAL_UNIX)));
					
		}
		switch($this->object->getSubscriptionLimitationType())
		{
			case IL_CRS_SUBSCRIPTION_DEACTIVATED:
				$txt = $this->lng->txt("crs_info_reg_deactivated");
				break;

			default:
				switch($this->object->getSubscriptionType())
				{
					case IL_CRS_SUBSCRIPTION_CONFIRMATION:
						$txt = $this->lng->txt("crs_info_reg_confirmation");
						break;
					case IL_CRS_SUBSCRIPTION_DIRECT:
						$txt = $this->lng->txt("crs_info_reg_direct");
						break;
					case IL_CRS_SUBSCRIPTION_PASSWORD:
						$txt = $this->lng->txt("crs_info_reg_password");
						break;
				}
		}
		
		// subscription
		$info->addProperty($this->lng->txt("crs_info_reg"),$txt);


		if($this->object->getSubscriptionLimitationType() != IL_CRS_SUBSCRIPTION_DEACTIVATED)
		{
			if($this->object->getSubscriptionUnlimitedStatus())
			{
				$info->addProperty($this->lng->txt("crs_reg_until"),
								   $this->lng->txt('crs_unlimited'));
			}
			elseif($this->object->getSubscriptionStart() < time())
			{
				$info->addProperty($this->lng->txt("crs_reg_until"),
								   $this->lng->txt('crs_to').' '.
								   ilDatePresentation::formatDate(new ilDateTime($this->object->getSubscriptionEnd(),IL_CAL_UNIX)));
			}
			elseif($this->object->getSubscriptionStart() > time())
			{
				$info->addProperty($this->lng->txt("crs_reg_until"),
								   $this->lng->txt('crs_from').' '.
								   ilDatePresentation::formatDate(new ilDateTime($this->object->getSubscriptionStart(),IL_CAL_UNIX)));
			}
			if ($this->object->isSubscriptionMembershipLimited()) 
			{
				include_once './Services/Membership/classes/class.ilParticipants.php';
				$info->addProperty(
					$this->lng->txt("mem_free_places"),
					max(
						0,
						$this->object->getSubscriptionMaxMembers() - ilParticipants::lookupNumberOfMembers($this->object->getRefId()))
				);
			}
		}
		
		// archive
		if($this->object->getViewMode() == IL_CRS_VIEW_ARCHIVE)
		{		
			if($this->object->getArchiveType() != IL_CRS_ARCHIVE_NONE)
			{
				$info->addProperty($this->lng->txt("crs_archive"),
					ilDatePresentation::formatPeriod(
						new ilDateTime($this->object->getArchiveStart(),IL_CAL_UNIX),
						new ilDateTime($this->object->getArchiveStart(),IL_CAL_UNIX)));
			}
		}
		// Confirmation
		include_once('Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
		$privacy = ilPrivacySettings::_getInstance();
		
		include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');
		if($privacy->courseConfirmationRequired() or ilCourseDefinedFieldDefinition::_getFields($this->object->getId()) or $privacy->enabledCourseExport())
		{
			include_once('Services/PrivacySecurity/classes/class.ilExportFieldsInfo.php');
			
			$field_info = ilExportFieldsInfo::_getInstanceByType($this->object->getType());
		
			$this->lng->loadLanguageModule('ps');
			$info->addSection($this->lng->txt('crs_user_agreement_info'));
			$info->addProperty($this->lng->txt('ps_export_data'),$field_info->exportableFieldsToInfoString());
			
			if($fields = ilCourseDefinedFieldDefinition::_fieldsToInfoString($this->object->getId()))
			{
				$info->addProperty($this->lng->txt('ps_crs_user_fields'),$fields);
			}
		}
		
		$info->enableLearningProgress(true);

		// forward the command
		$this->ctrl->forwardCommand($info);
	}

	/**
	 * :TEMP: Save notification setting (from infoscreen)
	 */
	function saveNotificationObject()
	{
		global $ilUser;

		$ilUser->setPref("grpcrs_ntf_".$this->ref_id, (bool)$_REQUEST["crs_ntf"]);
		$ilUser->writePrefs();

		ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
		$this->ctrl->redirect($this, "");
	}

	/**
	 * List start objects
	 * @global ilRbacSystem $rbacsystem
	 * @return void
	 */
	protected function listStructureObject()
	{		
		global $tpl, $ilToolbar;
		
		$this->checkPermission('write');
				
		$this->setSubTabs("properties");
		$this->tabs_gui->setTabActive('settings');
		$this->tabs_gui->setSubTabActive('crs_start_objects');

		$ilToolbar->addButton($this->lng->txt('crs_add_starter'),
				$this->ctrl->getLinkTarget($this, 'selectStarter'));
		
		include_once './Modules/Course/classes/class.ilCourseStartObjectsTableGUI.php';
		$table = new ilCourseStartObjectsTableGUI($this, 'listStructure', $this->object);		
		$tpl->setContent($table->getHTML());				
	}
	
	function askDeleteStarterObject()
	{
		global $tpl;
		
		if(!count($_POST['starter']))
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			$this->listStructureObject();
			return true;
		}

		// display confirmation message
		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$cgui = new ilConfirmationGUI();
		$cgui->setFormAction($this->ctrl->getFormAction($this, "listStructure"));
		$cgui->setHeaderText($this->lng->txt("crs_starter_delete_sure"));
		$cgui->setCancel($this->lng->txt("cancel"), "listStructure");
		$cgui->setConfirm($this->lng->txt("delete"), "deleteStarter");

		// list objects that should be deleted
		include_once './Modules/Course/classes/class.ilCourseStart.php';
		$crs_start = new ilCourseStart($this->object->getRefId(),$this->object->getId());	
		$all = $crs_start->getStartObjects();		
		foreach($_POST['starter'] as $starter_id)
		{			
			$obj_id = ilObject::_lookupObjId($all[$starter_id]["item_ref_id"]);
			$title = ilObject::_lookupTitle($obj_id);
			$icon = ilObject::_getIcon($obj_id, "tiny");
			$alt = $this->lng->txt('obj_'.ilObject::_lookupType($obj_id));
			$cgui->addItem("starter[]", $starter_id, $title, $icon, $alt);
		}

		$tpl->setContent($cgui->getHTML());
	}

	function deleteStarterObject()
	{		
		$this->checkPermission('write');
		
		if(!count($_POST['starter']))
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));			
		}
		else
		{
			include_once './Modules/Course/classes/class.ilCourseStart.php';
			$crs_start = new ilCourseStart($this->object->getRefId(),$this->object->getId());		
			foreach($_POST['starter'] as $starter_id)
			{		
				$crs_start->delete((int)$starter_id);
			}

			ilUtil::sendSuccess($this->lng->txt('crs_starter_deleted'));
		}
		
		$this->listStructureObject();		
		return true;
	}
		

	function selectStarterObject()
	{		
		global $tpl;

		$this->checkPermission('write');

		$this->tabs_gui->clearTargets();
		$this->tabs_gui->setBackTarget($this->lng->txt('back'),
			$this->ctrl->getLinkTarget($this, 'listStructure'));
		
		include_once './Modules/Course/classes/class.ilCourseStartObjectsTableGUI.php';
		$table = new ilCourseStartObjectsTableGUI($this, 'selectStarter', $this->object);
		
		$tpl->setContent($table->getHTML());				
	}

	function addStarterObject()
	{		
		global $rbacsystem;

		$this->checkPermission('write');

		if(!count($_POST['starter']))
		{
			ilUtil::sendFailure($this->lng->txt('crs_select_one_object'));
			$this->selectStarterObject();

			return false;			
		}
		
		include_once './Modules/Course/classes/class.ilCourseStart.php';
		$crs_start =& new ilCourseStart($this->object->getRefId(),$this->object->getId());
		$added = 0;
		foreach($_POST['starter'] as $item_ref_id)
		{
			if(!$crs_start->exists($item_ref_id))
			{
				++$added;
				$crs_start->add($item_ref_id);
			}
		}
		if($added)
		{
			ilUtil::sendSuccess($this->lng->txt('crs_added_starters'));
			$this->listStructureObject();

			return true;
		}
		else
		{
			ilUtil::sendFailure($this->lng->txt('crs_starters_already_assigned'));
			$this->selectStarterObject();

			return false;
		}
	}
	
	/**
	 * Edit info page informations
	 *
	 * @access public
	 * 
	 */
	public function editInfoObject()
	{
		include_once 'Modules/Course/classes/class.ilCourseFile.php';

		global $ilErr,$ilAccess;

		$this->checkPermission('write');
		/*
		if(!$ilAccess->checkAccess('write','',$this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('msg_no_perm_read'),$ilErr->MESSAGE);
		}
		*/
		$this->setSubTabs('properties');
		$this->tabs_gui->setTabActive('settings');
		$this->tabs_gui->setSubTabActive('crs_info_settings');
	 	
	 	$form = $this->initInfoEditor();
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.edit_info.html','Modules/Course');
		$this->tpl->setVariable('INFO_TABLE',$form->getHTML());
		
		if(!count($files = ilCourseFile::_readFilesByCourse($this->object->getId())))
		{
			return true;
		}
		$rows = array();
		foreach($files as $file)
		{
			$table_data['id'] = $file->getFileId();
			$table_data['filename'] = $file->getFileName();
			$table_data['filetype'] = $file->getFileType();
			$table_data['filesize'] = $file->getFileSize();
			
			$rows[] = $table_data; 
		}
		
		include_once("./Modules/Course/classes/class.ilCourseInfoFileTableGUI.php");
		$table_gui = new ilCourseInfoFileTableGUI($this, "edit");
		$table_gui->setTitle($this->lng->txt("crs_info_download"));
		$table_gui->setData($rows);
		$table_gui->addCommandButton("cancel", $this->lng->txt("cancel"));
		$table_gui->addMultiCommand("confirmDeleteInfoFiles", $this->lng->txt("delete"));
		$table_gui->setSelectAllCheckbox("file_id");
		$this->tpl->setVariable('INFO_FILE_TABLE',$table_gui->getHTML());

		return true;
		
	}
	
	/**
	 * show info file donfimation table
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function confirmDeleteInfoFilesObject()
	{
		if(!count($_POST['file_id']))
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			$this->editInfoObject();
			return false;
		}

		$this->setSubTabs('properties');
		$this->tabs_gui->setTabActive('settings');
		$this->tabs_gui->setSubTabActive('crs_info_settings');
		
		include_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
		$c_gui = new ilConfirmationGUI();
		
		// set confirm/cancel commands
		$c_gui->setFormAction($this->ctrl->getFormAction($this, "deleteInfoFiles"));
		$c_gui->setHeaderText($this->lng->txt("info_delete_sure"));
		$c_gui->setCancel($this->lng->txt("cancel"), "editInfo");
		$c_gui->setConfirm($this->lng->txt("confirm"), "deleteInfoFiles");

		// add items to delete
		include_once('Modules/Course/classes/class.ilCourseFile.php');
		foreach($_POST["file_id"] as $file_id)
		{
			$file = new ilCourseFile($file_id);
			$c_gui->addItem("file_id[]", $file_id, $file->getFileName());
		}
		
		$this->tpl->setContent($c_gui->getHTML());
	}
	
	/**
	 * Delete info files
	 *
	 * @access public
	 * 
	 */
	public function deleteInfoFilesObject()
	{
		if(!count($_POST['file_id']))
		{
			ilUtil::sendFailure($this->lng->txt('select_one'));
			$this->editInfoObject();
			return false;
		}
		include_once('Modules/Course/classes/class.ilCourseFile.php');
		
		foreach($_POST['file_id'] as $file_id)
		{
			$file = new ilCourseFile($file_id);
			if($this->object->getId() == $file->getCourseId())
			{
				$file->delete();
			}
		}
		ilUtil::sendSuccess($this->lng->txt('settings_saved'));
		$this->editInfoObject();
		return true;	
	}
	 	
	/**
	 * init info editor
	 *
	 * @access public
	 * @param
	 * 
	 */
	public function initInfoEditor()
	{
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this,'updateInfo'));
		$form->setMultipart(true);
		$form->setTitle($this->lng->txt('crs_general_info'));
		$form->addCommandButton('updateInfo',$this->lng->txt('save'));
		$form->addCommandButton('cancel',$this->lng->txt('cancel'));
		
		$area = new ilTextAreaInputGUI($this->lng->txt('crs_important_info'),'important');
		$area->setValue($this->object->getImportantInformation());
		$area->setRows(6);
		$area->setCols(80);
		$form->addItem($area);
		
		$area = new ilTextAreaInputGUI($this->lng->txt('crs_syllabus'),'syllabus');
		$area->setValue($this->object->getSyllabus());
		$area->setRows(6);
		$area->setCols(80);
		$form->addItem($area);
		
		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('crs_info_download'));
		$form->addItem($section);
		
		$file = new ilFileInputGUI($this->lng->txt('crs_file'),'file');
		$file->enableFileNameSelection('file_name');
		$form->addItem($file);
		
		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('crs_contact'));
		$form->addItem($section);
		
		$text = new ilTextInputGUI($this->lng->txt('crs_contact_name'),'contact_name');
		$text->setValue($this->object->getContactName());
		$text->setSize(40);
		$text->setMaxLength(70);
		$form->addItem($text);
		
		$text = new ilTextInputGUI($this->lng->txt('crs_contact_responsibility'),'contact_responsibility');
		$text->setValue($this->object->getContactResponsibility());
		$text->setSize(40);
		$text->setMaxLength(70);
		$form->addItem($text);

		$text = new ilTextInputGUI($this->lng->txt('crs_contact_phone'),'contact_phone');
		$text->setValue($this->object->getContactPhone());
		$text->setSize(40);
		$text->setMaxLength(40);
		$form->addItem($text);

		$text = new ilTextInputGUI($this->lng->txt('crs_contact_email'),'contact_email');
		$text->setValue($this->object->getContactEmail());
		$text->setInfo($this->lng->txt('crs_contact_email_info'));
		$text->setSize(40);
		$text->setMaxLength(255);
		$form->addItem($text);

		$area = new ilTextAreaInputGUI($this->lng->txt('crs_contact_consultation'),'contact_consultation');
		$area->setValue($this->object->getContactConsultation());
		$area->setRows(6);
		$area->setCols(80);
		$form->addItem($area);
		
		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordGUI.php');
		$record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_EDITOR,'crs',$this->object->getId());
		$record_gui->setPropertyForm($form);
		$record_gui->parse();

		return $form;
	}
	
	function updateInfoObject()
	{
		global $ilErr,$ilAccess;

		$this->checkPermission('write');

		include_once 'Modules/Course/classes/class.ilCourseFile.php';
		$file_obj = new ilCourseFile();
		$file_obj->setCourseId($this->object->getId());
		$file_obj->setFileName(strlen($_POST['file_name']) ?
							   ilUtil::stripSlashes($_POST['file_name']) :
							   $_FILES['file']['name']);
		$file_obj->setFileSize($_FILES['file']['size']);
		$file_obj->setFileType($_FILES['file']['type']);
		$file_obj->setTemporaryName($_FILES['file']['tmp_name']);
		$file_obj->setErrorCode($_FILES['file']['error']);

		$this->object->setImportantInformation(ilUtil::stripSlashes($_POST['important']));
		$this->object->setSyllabus(ilUtil::stripSlashes($_POST['syllabus']));
		$this->object->setContactName(ilUtil::stripSlashes($_POST['contact_name']));
		$this->object->setContactResponsibility(ilUtil::stripSlashes($_POST['contact_responsibility']));
		$this->object->setContactPhone(ilUtil::stripSlashes($_POST['contact_phone']));
		$this->object->setContactEmail(ilUtil::stripSlashes($_POST['contact_email']));
		$this->object->setContactConsultation(ilUtil::stripSlashes($_POST['contact_consultation']));
		
		include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordGUI.php');
		$record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_EDITOR,
			'crs',$this->object->getId());
		$record_gui->loadFromPost();

		// Validate
		$ilErr->setMessage('');
		$file_obj->validate();
		$this->object->validateInfoSettings();

		if(strlen($ilErr->getMessage()))
		{
			ilUtil::sendFailure($ilErr->getMessage());
			$this->editInfoObject();
			return false;
		}
		$this->object->update();
		$file_obj->create();
		$record_gui->saveValues();

		$this->object->updateECSContent();

		ilUtil::sendSuccess($this->lng->txt("crs_settings_saved"));
		$this->editInfoObject();
		return true;
	}

	function updateObject()
	{
		$this->object->setTitle(ilUtil::stripSlashes($_POST['title']));
		$this->object->setDescription(ilUtil::stripSlashes($_POST['desc']));		
		
		$activation_start = $this->loadDate('activation_start');
		$activation_end = $this->loadDate('activation_end');
		$subscription_start = $this->loadDate('subscription_start');
		$subscription_end = $this->loadDate('subscription_end');
		$archive_start = $this->loadDate('archive_start');
		$archive_end = $this->loadDate('archive_end');

		$this->object->setActivationType((int) $_POST['activation_type']);
		$this->object->setActivationStart($activation_start->get(IL_CAL_UNIX));
		$this->object->setActivationEnd($activation_end->get(IL_CAL_UNIX));
		$this->object->setSubscriptionLimitationType((int) $_POST['subscription_limitation_type']);
		$this->object->setSubscriptionType((int) $_POST['subscription_type']);
		$this->object->setSubscriptionPassword(ilUtil::stripSlashes($_POST['subscription_password']));
		$this->object->setSubscriptionStart($subscription_start->get(IL_CAL_UNIX));
		$this->object->setSubscriptionEnd($subscription_end->get(IL_CAL_UNIX));
		$this->object->enableSubscriptionMembershipLimitation((int) $_POST['subscription_membership_limitation']);
		$this->object->setSubscriptionMaxMembers((int) $_POST['subscription_max']);
		$this->object->enableRegistrationAccessCode((int) $_POST['reg_code_enabled']);
		$this->object->setRegistrationAccessCode(ilUtil::stripSlashes($_POST['reg_code']));
		$this->object->enableWaitingList((int) $_POST['waiting_list']);
		#$this->object->setSubscriptionNotify((int) $_POST['subscription_notification']);
		$this->object->setViewMode((int) $_POST['view_mode']);

		if($this->object->getViewMode() == IL_CRS_VIEW_TIMING)
		{
			$this->object->setOrderType(ilContainer::SORT_ACTIVATION);
		}
		else
		{
			$this->object->setOrderType((int) $_POST['order_type']);
		}
		$this->object->setArchiveStart($archive_start->get(IL_CAL_UNIX));
		$this->object->setArchiveEnd($archive_end->get(IL_CAL_UNIX));
		$this->object->setArchiveType($_POST['archive_type']);
		$this->object->setAboStatus((int) $_POST['abo']);
		$this->object->setShowMembers((int) $_POST['show_members']);
		
		$this->object->enableSessionLimit((int) $_POST['sl']);
		$this->object->setNumberOfPreviousSessions(is_numeric($_POST['sp']) ? (int) $_POST['sp'] : -1 );
		$this->object->setNumberOfnextSessions(is_numeric($_POST['sn']) ? (int) $_POST['sn'] : -1 );

		$this->object->setAutoNotiDisabled($_POST['deact_auto_noti'] == 1 ? true : false);
		
		// could be hidden in form
		if(isset($_POST['status_dt']))
		{
			$this->object->setStatusDetermination((int) $_POST['status_dt']);				
		}	

		if($this->object->validate())
		{
			$this->object->update();
			
			if((bool)$_POST['status_sync'])
			{
				$this->object->syncMembersStatusWithLP();				
			}

			// BEGIN ChangeEvent: Record write event
			require_once('Services/Tracking/classes/class.ilChangeEvent.php');
			global $ilUser;
			ilChangeEvent::_recordWriteEvent($this->object->getId(), $ilUser->getId(), 'update');
			ilChangeEvent::_catchupWriteEvents($this->object->getId(), $ilUser->getId());			
			// END ChangeEvent: Record write event

			// Update ecs export settings
			if(!$this->updateECSExportSettings())
			{
				$this->editObject();
				return false;
			}
			return $this->afterUpdate();
		}
		else
		{
			ilUtil::sendFailure($this->object->getMessage());
			$this->editObject();
			return false;
		}
	}

	/**
	 * Update ECS Export Settings
	 *
	 * @access protected
	 */
	protected function updateECSExportSettings()
	{
		global $rbacadmin;

		// ECS enabled
		include_once('./Services/WebServices/ECS/classes/class.ilECSServerSettings.php');
		if(!ilECSServerSettings::getInstance()->activeServerExists())
		{
			return true;
		}

		// Parse post data
		$mids = array();
		foreach((array) $_POST['ecs_sid'] as $sid_mid)
		{
			$tmp = explode('_',$sid_mid);
			$mids[$tmp[0]][] = $tmp[1];
		}

		try
		{
			include_once './Services/WebServices/ECS/classes/class.ilECSCommunitiesCache.php';
			include_once './Services/WebServices/ECS/classes/class.ilECSParticipantSettings.php';

			// Update for each server
			foreach(ilECSParticipantSettings::getExportServers() as $server_id)
			{
				// Export
				$export = true;
				if(!$_POST['ecs_export'])
				{
					$export = false;
				}
				if(!count($mids[$server_id]))
				{
					$export = false;
				}
				$this->object->handleECSSettings(
					$server_id,
					$export,
					ilECSCommunitiesCache::getInstance()->lookupOwnId($server_id,$mids[$server_id][0]),
					$mids[$server_id]
				);
			}
			return true;
		}
		catch(ilECSConnectorException $exc)
		{
			ilUtil::sendFailure('Error connecting to ECS server: '.$exc->getMessage());
			return false;
		}
		catch(ilECSContentWriterException $exc)
		{
			ilUtil::sendFailure('Course export failed with message: '.$exc->getMessage());
			return false;
		}
		return true;

		if($_POST['ecs_export'] and !$_POST['ecs_owner'])
		{
			ilUtil::sendFailure($this->lng->txt('ecs_no_owner'));
			return false;
		}
		try
		{
			$this->object->handleECSSettings((bool) $_POST['ecs_export'],(int) $_POST['ecs_owner'],(array) $_POST['ecs_mids']);
			
			// update performed now grant/revoke ecs user permissions
			include_once('./Services/WebServices/ECS/classes/class.ilECSExport.php');
			$export = new ilECSExport($this->object->getId());
			if($export->isExported())
			{
				// Grant permission
				$rbacadmin->grantPermission($ecs_settings->getGlobalRole(),
					ilRbacReview::_getOperationIdsByName(array('join','visible')),
					$this->object->getRefId());
				
			}
			else
			{
				$rbacadmin->revokePermission($this->object->getRefId(),
					$ecs_settings->getGlobalRole());
			}
		}
		catch(ilECSConnectorException $exc)
		{
			ilUtil::sendFailure('Error connecting to ECS server: '.$exc->getMessage());
			return false;
		}
		catch(ilECSContentWriterException $exc)
		{
			ilUtil::sendFailure('Course export failed with message: '.$exc->getMessage());
			return false;
		}
		return true;
	}
	
	/**
	 * edit object
	 *
	 * @access public
	 * @return
	 */
	public function editObject()
	{
		parent::editObject();
		
		$this->setSubTabs('properties');
		$this->tabs_gui->setSubTabActive('crs_settings');
	}
	
	/**
	 * init form
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function initEditForm()
	{
		include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
		include_once('./Services/Calendar/classes/class.ilDateTime.php');
		
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->lng->txt('crs_edit'));
		$form->setTitleIcon(ilUtil::getImagePath('icon_crs_s.gif'));
	
		$form->addCommandButton('update',$this->lng->txt('save'));
		$form->addCommandButton('cancel',$this->lng->txt('cancel'));
		
		$form->setFormAction($this->ctrl->getFormAction($this,'update'));
		
		// title
		$title = new ilTextInputGUI($this->lng->txt('title'),'title');
		$title->setSubmitFormOnEnter(true);
		$title->setValue($this->object->getTitle());
		$title->setSize(40);
		$title->setMaxLength(128);
		$title->setRequired(true);
		$form->addItem($title);
		
		// desc
		$desc = new ilTextAreaInputGUI($this->lng->txt('description'),'desc');
		$desc->setValue($this->object->getLongDescription());
		$desc->setRows(2);
		$desc->setCols(40);
		$form->addItem($desc);

		// Show didactic template type
		$this->initDidacticTemplate($form);
		
		// reg type
		$act_type = new ilRadioGroupInputGUI($this->lng->txt('crs_visibility'),'activation_type');
		$act_type->setValue($this->object->getActivationType());
		
			$opt = new ilRadioOption($this->lng->txt('crs_visibility_unvisible'),IL_CRS_ACTIVATION_OFFLINE);
			$opt->setInfo($this->lng->txt('crs_availability_unvisible_info'));
			$act_type->addOption($opt);
			
			$opt = new ilRadioOption($this->lng->txt('crs_visibility_limitless'),IL_CRS_ACTIVATION_UNLIMITED);
			$opt->setInfo($this->lng->txt('crs_availability_limitless_info'));
			$act_type->addOption($opt);
			
			$opt = new ilRadioOption($this->lng->txt('crs_visibility_until'),IL_CRS_ACTIVATION_LIMITED);
			$opt->setInfo($this->lng->txt('crs_availability_until_info'));

				$start = new ilDateTimeInputGUI($this->lng->txt('crs_start'),'activation_start');
				#$start->setMode(ilDateTimeInputGUI::MODE_INPUT);
				$start->setShowTime(true);
				$start_date = new ilDateTime($this->object->getActivationStart(),IL_CAL_UNIX);
				$start->setDate($start_date);
				$opt->addSubItem($start);

				$end = new ilDateTimeInputGUI($this->lng->txt('crs_end'),'activation_end');
				#$end->setMode(ilDateTimeInputGUI::MODE_INPUT);
				$end->setShowTime(true);
				$end_date = new ilDateTime($this->object->getActivationEnd(),IL_CAL_UNIX);
				$end->setDate($end_date);
				$opt->addSubItem($end);
				
			$act_type->addOption($opt);
		
		$form->addItem($act_type);
		
		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('crs_reg'));
		$form->addItem($section);
		
		$reg_type = new ilRadioGroupInputGUI($this->lng->txt('crs_reg_period'),'subscription_limitation_type');
		$reg_type->setValue($this->object->getSubscriptionLimitationType());		
		
			$opt = new ilRadioOption($this->lng->txt('crs_reg_deactivated'),IL_CRS_SUBSCRIPTION_DEACTIVATED);
			$opt->setInfo($this->lng->txt('crs_registration_deactivated'));
			$reg_type->addOption($opt);
			
			$opt = new ilRadioOption($this->lng->txt('crs_registration_unlimited'),IL_CRS_SUBSCRIPTION_UNLIMITED);
			$opt->setInfo($this->lng->txt('crs_reg_unlim_info'));
			$reg_type->addOption($opt);

			$opt = new ilRadioOption($this->lng->txt('crs_registration_limited'),IL_CRS_SUBSCRIPTION_LIMITED);
			$opt->setInfo($this->lng->txt('crs_reg_lim_info'));

				$start = new ilDateTimeInputGUI($this->lng->txt('crs_start'),'subscription_start');
				$start->setShowTime(true);
				$start_date = new ilDateTime($this->object->getSubscriptionStart(),IL_CAL_UNIX);
				$start->setDate($start_date);
				$opt->addSubItem($start);

				$end = new ilDateTimeInputGUI($this->lng->txt('crs_end'),'subscription_end');
				$end->setShowTime(true);
				$end_date = new ilDateTime($this->object->getSubscriptionEnd(),IL_CAL_UNIX);
				$end->setDate($end_date);
				$opt->addSubItem($end);
				
			$reg_type->addOption($opt);

		$form->addItem($reg_type);
		
		
		
		$reg_proc = new ilRadioGroupInputGUI($this->lng->txt('crs_registration_type'),'subscription_type');
		$reg_proc->setValue($this->object->getSubscriptionType());
		$reg_proc->setInfo($this->lng->txt('crs_reg_type_info'));

			$opt = new ilRadioOption($this->lng->txt('crs_subscription_options_confirmation'),IL_CRS_SUBSCRIPTION_CONFIRMATION);
			$reg_proc->addOption($opt);
			
			$opt = new ilRadioOption($this->lng->txt('crs_subscription_options_direct'),IL_CRS_SUBSCRIPTION_DIRECT);
			$reg_proc->addOption($opt);

			$opt = new ilRadioOption($this->lng->txt('crs_subscription_options_password'),IL_CRS_SUBSCRIPTION_PASSWORD);
			
				$pass = new ilTextInputGUI('','subscription_password');
				$pass->setSubmitFormOnEnter(true);
				$pass->setSize(12);
				$pass->setMaxLength(12);
				$pass->setValue($this->object->getSubscriptionPassword());
			
			$opt->addSubItem($pass);
			$reg_proc->addOption($opt);

		$form->addItem($reg_proc);
		
		// Registration codes
		$reg_code = new ilCheckboxInputGUI('','reg_code_enabled');
		$reg_code->setChecked($this->object->isRegistrationAccessCodeEnabled());
		$reg_code->setValue(1);
		$reg_code->setInfo($this->lng->txt('crs_reg_code_enabled_info'));
		$reg_code->setOptionTitle($this->lng->txt('crs_reg_code'));
		
		/*
		$code = new ilNonEditableValueGUI($this->lng->txt('crs_reg_code_value'));
		$code->setValue($this->object->getRegistrationAccessCode());
		$reg_code->addSubItem($code);
		*/
		
		#$link = new ilNonEditableValueGUI($this->lng->txt('crs_reg_code_link'));
		// Create default access code
		if(!$this->object->getRegistrationAccessCode())
		{
			include_once './Services/Membership/classes/class.ilMembershipRegistrationCodeUtils.php';
			$this->object->setRegistrationAccessCode(ilMembershipRegistrationCodeUtils::generateCode());
		}
		$reg_link = new ilHiddenInputGUI('reg_code');
		$reg_link->setValue($this->object->getRegistrationAccessCode());
		$form->addItem($reg_link);
		
		
		$link = new ilCustomInputGUI($this->lng->txt('crs_reg_code_link'));
		include_once './Services/Link/classes/class.ilLink.php';
		$val = ilLink::_getLink($this->object->getRefId(),$this->object->getType(),array(),'_rcode'.$this->object->getRegistrationAccessCode()); 
		$link->setHTML('<font class="small">'.$val.'</font>');
		$reg_code->addSubItem($link);
		
		$form->addItem($reg_code);
		
		
		// Max members
		$lim = new ilCheckboxInputGUI($this->lng->txt('crs_subscription_max_members_short'),'subscription_membership_limitation');
		$lim->setValue(1);
		$lim->setOptionTitle($this->lng->txt('crs_subscription_max_members'));
		$lim->setChecked($this->object->isSubscriptionMembershipLimited());
		
			$max = new ilTextInputGUI('','subscription_max');
			$max->setSubmitFormOnEnter(true);
			$max->setSize(4);
			$max->setMaxLength(4);
			$max->setValue($this->object->getSubscriptionMaxMembers() ? $this->object->getSubscriptionMaxMembers() : '');
			$max->setTitle($this->lng->txt('members').':');
			$max->setInfo($this->lng->txt('crs_reg_max_info'));
		
		$lim->addSubItem($max);
		
			$wait = new ilCheckboxInputGUI('','waiting_list');
			$wait->setOptionTitle($this->lng->txt('crs_waiting_list'));
			$wait->setChecked($this->object->enabledWaitingList());
			$wait->setInfo($this->lng->txt('crs_wait_info'));
			$lim->addSubItem($wait);
		
		$form->addItem($lim);
	
		$not = new ilCheckboxInputGUI($this->lng->txt('crs_deact_auto_noti'), 'deact_auto_noti');
		$not->setValue(1);
		$not->setInfo($this->lng->txt('crs_deact_auto_noti_info'));
		$not->setChecked( $this->object->getAutoNotiDisabled() );
		$form->addItem($not);

		$pres = new ilFormSectionHeaderGUI();
		$pres->setTitle($this->lng->txt('crs_view_mode'));
		
		$form->addItem($pres);
		
		// presentation type
		$view_type = new ilRadioGroupInputGUI($this->lng->txt('crs_presentation_type'),'view_mode');
		$view_type->setValue($this->object->getViewMode());
		
			$opts = new ilRadioOption($this->lng->txt('cntr_view_sessions'),IL_CRS_VIEW_SESSIONS);
			$opts->setInfo($this->lng->txt('cntr_view_info_sessions'));
			$view_type->addOption($opts);

				// Limited sessions
				$sess = new ilCheckboxInputGUI($this->lng->txt('sess_limit'),'sl');
				$sess->setValue(1);
				$sess->setChecked($this->object->isSessionLimitEnabled());
				$sess->setInfo($this->lng->txt('sess_limit_info'));

					$prev = new ilNumberInputGUI($this->lng->txt('sess_num_prev'),'sp');
					#$prev->setSubmitFormOnEnter(true);
					$prev->setMinValue(0);
					$prev->setValue($this->object->getNumberOfPreviousSessions() == -1 ?
						'' :
						$this->object->getNumberOfPreviousSessions()
					);
					$prev->setSize(2);
					$prev->setMaxLength(3);
					$sess->addSubItem($prev);

					$next = new ilNumberInputGUI($this->lng->txt('sess_num_next'),'sn');
					#$next->setSubmitFormOnEnter(true);
					$next->setMinValue(0);
					$next->setValue($this->object->getNumberOfNextSessions() == -1 ?
						'' :
						$this->object->getNumberOfnextSessions()
					);
					$next->setSize(2);
					$next->setMaxLength(3);
					$sess->addSubItem($next);

				$opts->addSubItem($sess);



			
			$optsi = new ilRadioOption($this->lng->txt('cntr_view_simple'),IL_CRS_VIEW_SIMPLE);
			$optsi->setInfo($this->lng->txt('cntr_view_info_simple'));
			$view_type->addOption($optsi);

			$optbt = new ilRadioOption($this->lng->txt('cntr_view_by_type'),IL_CRS_VIEW_BY_TYPE);
			$optbt->setInfo($this->lng->txt('cntr_view_info_by_type'));
			$view_type->addOption($optbt);
			
			$opto = new ilRadioOption($this->lng->txt('crs_view_objective'),IL_CRS_VIEW_OBJECTIVE);
			$opto->setInfo($this->lng->txt('crs_view_info_objective'));
			$view_type->addOption($opto);

			$optt = new ilRadioOption($this->lng->txt('crs_view_timing'),IL_CRS_VIEW_TIMING);
			$optt->setInfo($this->lng->txt('crs_view_info_timing'));
			$view_type->addOption($optt);

			/*
			$opt = new ilRadioOption($this->lng->txt('crs_view_archive'),IL_CRS_VIEW_ARCHIVE);
			$opt->setInfo($this->lng->txt('crs_archive_info'));
			
				$down = new ilCheckboxInputGUI('','archive_type');
				$down->setOptionTitle($this->lng->txt('crs_archive_download'));
				$down->setChecked($this->object->getArchiveType() == IL_CRS_ARCHIVE_DOWNLOAD);
				$opt->addSubItem($down);
				
				$start = new ilDateTimeInputGUI($this->lng->txt('crs_start'),'archive_start');
				$start->setShowTime(true);
				$start_date = new ilDateTime($this->object->getArchiveStart(),IL_CAL_UNIX);
				$start->setDate($start_date);
				$opt->addSubItem($start);

				$end = new ilDateTimeInputGUI($this->lng->txt('crs_end'),'archive_end');
				$end->setShowTime(true);
				$end_date = new ilDateTime($this->object->getArchiveEnd(),IL_CAL_UNIX);
				$end->setDate($end_date);
				$opt->addSubItem($end);
				
			$view_type->addOption($opt);
		*/
		$form->addItem($view_type);
		
		// sorting type
		$sort = new ilRadioGroupInputGUI($this->lng->txt('crs_sortorder_abo'),'order_type');
		$sort->setValue($this->object->getOrderType());
		
			$opt = new ilRadioOption($this->lng->txt('crs_sort_title'),ilContainer::SORT_TITLE);
			$opt->setInfo($this->lng->txt('crs_sort_title_info'));
			$sort->addOption($opt);
			
			$opt = new ilRadioOption($this->lng->txt('crs_sort_manual'),ilContainer::SORT_MANUAL);
			$opt->setInfo($this->lng->txt('crs_sort_manual_info'));
			$sort->addOption($opt);

			$opt = new ilRadioOption($this->lng->txt('crs_sort_activation'),ilContainer::SORT_ACTIVATION);
			$opt->setInfo($this->lng->txt('crs_sort_timing_info'));
			$sort->addOption($opt);
			

		$form->addItem($sort);
		
		// lp vs. course status
		include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
		if(ilObjUserTracking::_enabledLearningProgress())
		{					
			include_once './Services/Tracking/classes/class.ilLPObjSettings.php';
			$lp_settings = new ilLPObjSettings($this->object->getId());
			if($lp_settings->getMode())
			{							
				$lp_status = new ilFormSectionHeaderGUI();
				$lp_status->setTitle($this->lng->txt('crs_course_status_of_users'));
				$form->addItem($lp_status);

				$lp_status_options = new ilRadioGroupInputGUI($this->lng->txt('crs_status_determination'), "status_dt");
				$lp_status_options->setRequired(true);
				$lp_status_options->setValue($this->object->getStatusDetermination());
								
				$lp_option = new ilRadioOption($this->lng->txt('crs_status_determination_lp'), 
					ilObjCourse::STATUS_DETERMINATION_LP);
				$lp_status_options->addOption($lp_option);
				$lp_status_options->addOption(new ilRadioOption($this->lng->txt('crs_status_determination_manual'),
					ilObjCourse::STATUS_DETERMINATION_MANUAL));			
				
				if($this->object->getStatusDetermination() != ilObjCourse::STATUS_DETERMINATION_LP)
				{
					$lp_sync = new ilCheckboxInputGUI($this->lng->txt('crs_status_determination_sync'), "status_sync");			
					$lp_option->addSubItem($lp_sync);
				}

				$form->addItem($lp_status_options);		
			}
		}
		
		$further = new ilFormSectionHeaderGUI();
		$further->setTitle($this->lng->txt('crs_further_settings'));
		$form->addItem($further);
		
		$desk = new ilCheckboxInputGUI($this->lng->txt('crs_add_remove_from_desktop'),'abo');
		$desk->setChecked($this->object->getAboStatus());
		$desk->setInfo($this->lng->txt('crs_add_remove_from_desktop_info'));
		$form->addItem($desk);
		
		$mem = new ilCheckboxInputGUI($this->lng->txt('crs_show_members'),'show_members');
		$mem->setChecked($this->object->getShowMembers());
		$mem->setInfo($this->lng->txt('crs_show_members_info'));
		$form->addItem($mem);
		
		$this->fillECSExportSettings($form);

		return $form;
	}

	protected function  getEditFormValues()
	{
		// values are done in initEditForm()
	}


	/**
	 * Fill ECS export settings "multiple servers"
	 * @param ilPropertyFormGUI $a_formm
	 */
	protected function fillECSExportSettings(ilPropertyFormGUI $a_form)
	{
		global $ilLog;

		include_once('./Services/WebServices/ECS/classes/class.ilECSServerSettings.php');
		if(!ilECSServerSettings::getInstance()->activeServerExists())
		{
			return true;
		}

		// Return if no participant is enabled for export and the current object is not released
		include_once './Services/WebServices/ECS/classes/class.ilECSExport.php';
		include_once './Services/WebServices/ECS/classes/class.ilECSParticipantSettings.php';

		$exportablePart = ilECSParticipantSettings::getExportableParticipants();
		if(!$exportablePart and !ilECSExport::_isExported($this->object->getId()))
		{
			return true;
		}

		$GLOBALS['lng']->loadLanguageModule('ecs');

		// show ecs property form section
		$ecs = new ilFormSectionHeaderGUI();
		$ecs->setTitle($this->lng->txt('ecs_export'));
		$a_form->addItem($ecs);


		// release or not
		$exp = new ilRadioGroupInputGUI($this->lng->txt('ecs_export_obj_settings'),'ecs_export');
		$exp->setRequired(true);
		$exp->setValue(ilECSExport::_isExported($this->object->getId()) ? 1 : 0);
		$off = new ilRadioOption($this->lng->txt('ecs_export_disabled'),0);
		$exp->addOption($off);
		$on = new ilRadioOption($this->lng->txt('ecs_export_enabled'),1);
		$exp->addOption($on);
		$a_form->addItem($exp);

		// Show all exportable participants
		$publish_for = new ilCheckboxGroupInputGUI($this->lng->txt('ecs_publish_for'),'ecs_sid');

		// @TODO: Active checkboxes for recipients
		//$publish_for->setValue((array) $members);

		// Read receivers
		$receivers = array();
		foreach(ilECSExport::getExportServerIds($this->object->getId()) as $sid)
		{
			$exp = new ilECSExport($sid, $this->object->getId());
			$eid = $exp->getEContentId();
			try
			{
				include_once './Services/WebServices/ECS/classes/class.ilECSEContentReader.php';
				$econtent_reader = new ilECSEContentReader($sid,$eid);
				$econtent_reader->read(true);
				$details = $econtent_reader->getEContentDetails();
				if($details instanceof ilECSEContentDetails)
				{
					foreach($details->getReceivers() as $mid)
					{
						$receivers[] = $sid.'_'.$mid;
					}
					#$owner = $details->getFirstSender();
				}
			}
			catch(ilECSConnectorException $exc)
			{
				$ilLog->write(__METHOD__.': Error connecting to ECS server. '.$exc->getMessage());
			}
			catch(ilECSReaderException $exc)
			{
				$ilLog->write(__METHOD__.': Error parsing ECS query: '.$exc->getMessage());
			}
		}

		$publish_for->setValue($receivers);

		foreach($exportablePart as $pInfo)
		{
			include_once './Services/WebServices/ECS/classes/class.ilECSParticipantSetting.php';
			$partSetting = new ilECSParticipantSetting($pInfo['sid'], $pInfo['mid']);

			$com = new ilCheckboxInputGUI(
				$partSetting->getCommunityName().': '.$partSetting->getTitle(),
				'sid_mid'
			);
			$com->setValue($pInfo['sid'].'_'.$pInfo['mid']);
			$publish_for->addOption($com);
		}
		$on->addSubItem($publish_for);
		return true;
	}


	/**
	* edit container icons
	*/
	function editCourseIconsObject($a_form = null)
	{
		global $tpl;

		$this->checkPermission('write');
	
		$this->setSubTabs("properties");
		$this->tabs_gui->setTabActive('settings');
		
		if(!$a_form)
		{
			$a_form = $this->initCourseIconsForm();
		}
		
		$tpl->setContent($a_form->getHTML());
	}

	function initCourseIconsForm()
	{
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));	
		
		$this->showCustomIconsEditing(1, $form);
		
		// $form->setTitle($this->lng->txt('edit_grouping'));
		$form->addCommandButton('updateCourseIcons', $this->lng->txt('save'));					
		
		return $form;
	}

	function sendFileObject()
	{
		include_once 'Modules/Course/classes/class.ilCourseFile.php';
		$file = new ilCourseFile((int) $_GET['file_id']);
		ilUtil::deliverFile($file->getAbsolutePath(),$file->getFileName(),$file->getFileType());
		return true;
	}
	
	/**
	* update container icons
	*/
	function updateCourseIconsObject()
	{
		$this->checkPermission('write');
		
		$form = $this->initCourseIconsForm();
		if($form->checkInput())
		{
			//save custom icons
			if ($this->ilias->getSetting("custom_icons"))
			{
				if($_POST["cont_big_icon_delete"])
				{
					$this->object->removeBigIcon();
				}
				if($_POST["cont_small_icon_delete"])
				{
					$this->object->removeSmallIcon();
				}
				if($_POST["cont_tiny_icon_delete"])
				{
					$this->object->removeTinyIcon();
				}				
				$this->object->saveIcons($_FILES["cont_big_icon"]['tmp_name'],
					$_FILES["cont_small_icon"]['tmp_name'], $_FILES["cont_tiny_icon"]['tmp_name']);
			}
			
			ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"),true);
			$this->ctrl->redirect($this,"editCourseIcons");
		}

		$form->setValuesByPost();
		$this->editCourseIconsObject($form);	
	}


	/**
	* set sub tabs
	*/
	function setSubTabs($a_tab)
	{
		global $rbacsystem,$ilUser,$ilAccess;
		
		switch ($a_tab)
		{
			case "properties":
				$this->tabs_gui->addSubTabTarget("crs_settings",
												 $this->ctrl->getLinkTarget($this,'edit'),
												 "edit", get_class($this));

				$this->tabs_gui->addSubTabTarget("crs_info_settings",
												 $this->ctrl->getLinkTarget($this,'editInfo'),
												 "editInfo", get_class($this));

				$this->tabs_gui->addSubTabTarget("preconditions",
												 $this->ctrl->getLinkTargetByClass('ilConditionHandlerInterface','listConditions'),
												 "", "ilConditionHandlerInterface");
				$this->tabs_gui->addSubTabTarget("crs_start_objects",
												 $this->ctrl->getLinkTarget($this,'listStructure'),
												 "listStructure", get_class($this));
				$this->tabs_gui->addSubTabTarget('groupings',
												 $this->ctrl->getLinkTargetByClass('ilobjcoursegroupinggui','listGroupings'),
												 'listGroupings',
												 get_class($this));

				// custom icon
				if ($this->ilias->getSetting("custom_icons"))
				{
					$this->tabs_gui->addSubTabTarget("icon_settings",
													 $this->ctrl->getLinkTarget($this,'editCourseIcons'),
													 "editCourseIcons", get_class($this));
				}
				
				// map settings
				include_once("./Services/GoogleMaps/classes/class.ilGoogleMapUtil.php");
				if (ilGoogleMapUtil::isActivated())
				{
					$this->tabs_gui->addSubTabTarget("crs_map_settings",
						 $this->ctrl->getLinkTarget($this,'editMapSettings'),
						 "editMapSettings", get_class($this));
				}

				
				include_once('Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
				include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');
				// only show if export permission is granted
				if(ilPrivacySettings::_getInstance()->checkExportAccess($this->object->getRefId()) or ilCourseDefinedFieldDefinition::_hasFields($this->object->getId()))
				{
					$this->tabs_gui->addSubTabTarget('crs_custom_user_fields',
													$this->ctrl->getLinkTargetByClass('ilobjectcustomuserfieldsgui'),
													'',
													'ilobjectcustomuserfieldsgui');
				}
				
				// certificates
				include_once "Services/Certificate/classes/class.ilCertificate.php";
				if(ilCertificate::isActive())
				{					
					$this->tabs_gui->addSubTabTarget(
						"certificate",
						$this->ctrl->getLinkTargetByClass("ilcertificategui", "certificateeditor"));					
				}
				break;
				
			case "item_activation":
				$this->tabs_gui->addSubTabTarget("activation",
												 $this->ctrl->getLinkTargetByClass('ilCourseItemAdministrationGUI','edit'),
												 "edit", get_class($this));
				$this->ctrl->setParameterByClass('ilconditionhandlerinterface','item_id',(int) $_GET['item_id']);
				$this->tabs_gui->addSubTabTarget("preconditions",
												 $this->ctrl->getLinkTargetByClass('ilConditionHandlerInterface','listConditions'),
												 "", "ilConditionHandlerInterface");
				break;
				
			case 'members':
				if($ilAccess->checkAccess('write','',$this->object->getRefId()))
				{
					$this->tabs_gui->addSubTabTarget("crs_member_administration",
													 $this->ctrl->getLinkTarget($this,'members'),
													 "members", get_class($this));

					$this->tabs_gui->addSubTabTarget("crs_members_groups",
													 $this->ctrl->getLinkTargetByClass("ilCourseParticipantsGroupsGUI", "show"),
													 "", "ilCourseParticipantsGroupsGUI");
				}
				$this->tabs_gui->addSubTabTarget("crs_members_gallery",
												 $this->ctrl->getLinkTarget($this,'membersGallery'),
												 "membersGallery", get_class($this));
				
				// members map
				include_once("./Services/GoogleMaps/classes/class.ilGoogleMapUtil.php");
				if (ilGoogleMapUtil::isActivated() && $this->object->getEnableCourseMap())
				{
					$this->tabs_gui->addSubTabTarget("crs_members_map",
						$this->ctrl->getLinkTarget($this,'membersMap'),
						"membersMap", get_class($this));
				}

				
				include_once 'Services/Mail/classes/class.ilMail.php';
				$mail =& new ilMail($ilUser->getId());
				if($rbacsystem->checkAccess('mail_visible',$mail->getMailObjectReferenceId()))
				{
					$this->tabs_gui->addSubTabTarget("mail_members",
													 $this->ctrl->getLinkTarget($this,'mailMembers'),
													 "mailMembers", get_class($this));
				}
				
				if($ilAccess->checkAccess('write','',$this->object->getRefId()))
				{
					$this->tabs_gui->addSubTabTarget("events",
													 $this->ctrl->getLinkTargetByClass('ilsessionoverviewgui','listSessions'),
													 "", 'ilsessionoverviewgui');
				}

				include_once 'Services/PrivacySecurity/classes/class.ilPrivacySettings.php';
				if(ilPrivacySettings::_getInstance()->checkExportAccess($this->object->getRefId()))
				{
					$this->tabs_gui->addSubTabTarget('export_members',
													$this->ctrl->getLinkTargetByClass('ilmemberexportgui','show'),
													"", 'ilmemberexportgui');
				}
				
				break;

				
		}
	}

	/**
	* remove small icon
	*
	* @access	public
	*/
	function removeSmallIconObject()
	{
		$this->object->removeSmallIcon();
		$this->ctrl->redirect($this,'editCourseIcons');		
	}

	/**
	* remove big icon
	*
	* @access	public
	*/
	function removeBigIconObject()
	{
		$this->object->removeBigIcon();
		$this->ctrl->redirect($this,'editCourseIcons');		
	}


	/**
	* remove small icon
	*
	* @access	public
	*/
	function removeTinyIconObject()
	{
		$this->object->removeTinyIcon();
		$this->ctrl->redirect($this,'editCourseIcons');		
	}

	/**
	* save object
	* @access	public
	*/
	protected function afterSave(ilObject $a_new_object)
	{
		global $rbacadmin, $ilUser;
		
		$a_new_object->getMemberObject()->add($ilUser->getId(),IL_CRS_ADMIN);
		$a_new_object->getMemberObject()->updateNotification($ilUser->getId(),1);
		$a_new_object->update();
		
		// BEGIN ChangeEvent: Record write event.
		require_once('Services/Tracking/classes/class.ilChangeEvent.php');
		global $ilUser;
		ilChangeEvent::_recordWriteEvent($a_new_object->getId(), $ilUser->getId(), 'create');		
		// END ChangeEvent: Record write event.

		// always send a message
		ilUtil::sendSuccess($this->lng->txt("crs_added"),true);
		
		$this->ctrl->setParameter($this, "ref_id", $a_new_object->getRefId());
		ilUtil::redirect($this->getReturnLocation("save",
			$this->ctrl->getLinkTarget($this, "edit", "", false, false)));
	}
	
	function downloadArchivesObject()
	{
		global $rbacsystem;

		$_POST["archives"] = $_POST["archives"] ? $_POST["archives"] : array();

		// MINIMUM ACCESS LEVEL = 'write'
		$this->checkPermission('read');
		/*
		if(!$rbacsystem->checkAccess("read", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}
		*/
		if(!count($_POST['archives']))
		{
			ilUtil::sendFailure($this->lng->txt('crs_no_archive_selected'));
			$this->archiveObject();

			return false;
		}
		if(count($_POST['archives']) > 1)
		{
			ilUtil::sendFailure($this->lng->txt('crs_select_one_archive'));
			$this->archiveObject();

			return false;
		}

		$this->object->initCourseArchiveObject();
		
		$abs_path = $this->object->archives_obj->getArchiveFile((int) $_POST['archives'][0]);
		$basename = basename($abs_path);

		ilUtil::deliverFile($abs_path,$basename);
	}
	
	/**
	 * set preferences (show/hide tabel content)
	 *
	 * @access public
	 * @return
	 */
	public function setShowHidePrefs()
	{
		global $ilUser;
		
		if(isset($_GET['admin_hide']))
		{
			$ilUser->writePref('crs_admin_hide',(int) $_GET['admin_hide']);
		}
		if(isset($_GET['tutor_hide']))
		{
			$ilUser->writePref('crs_tutor_hide',(int) $_GET['tutor_hide']);
		}
		if(isset($_GET['member_hide']))
		{
			$ilUser->writePref('crs_member_hide',(int) $_GET['member_hide']);
		}
		if(isset($_GET['subscriber_hide']))
		{
			$ilUser->writePref('crs_subscriber_hide',(int) $_GET['subscriber_hide']);
		}
		if(isset($_GET['wait_hide']))
		{
			$ilUser->writePref('crs_wait_hide',(int) $_GET['wait_hide']);
		}
		include_once './Modules/Course/classes/class.ilCourseParticipants.php';
		foreach(ilCourseParticipants::getMemberRoles($this->object->getRefId()) as $role_id)
		{
			if(isset($_GET['role_hide_'.$role_id]))
			{
				$ilUser->writePref('crs_role_hide_'.$role_id,(int) $_GET['role_hide_'.$role_id]);
			}
		}
	}
	
	public function readMemberData($ids,$role = 'admin')
	{
		if($this->show_tracking)
		{
			include_once 'Services/Tracking/classes/class.ilLPStatusWrapper.php';
			$completed = ilLPStatusWrapper::_lookupCompletedForObject($this->object->getId());
			$in_progress = ilLPStatusWrapper::_lookupInProgressForObject($this->object->getId());
			$failed = ilLPStatusWrapper::_lookupFailedForObject($this->object->getId());
		}
		include_once('./Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
		$privacy = ilPrivacySettings::_getInstance();

		if($privacy->enabledCourseAccessTimes())
		{
			include_once('./Services/Tracking/classes/class.ilLearningProgress.php');
			$progress = ilLearningProgress::_lookupProgressByObjId($this->object->getId());
		}

		foreach((array) $ids as $usr_id)
		{
			$name = ilObjUser::_lookupName($usr_id);
			$tmp_data['firstname'] = $name['firstname'];
			$tmp_data['lastname'] = $name['lastname'];
			$tmp_data['login'] = ilObjUser::_lookupLogin($usr_id);
			$tmp_data['passed'] = $this->object->getMembersObject()->hasPassed($usr_id) ? 1 : 0;
			$tmp_data['passed_info'] = $this->object->getMembersObject()->getPassedInfo($usr_id);
			$tmp_data['notification'] = $this->object->getMembersObject()->isNotificationEnabled($usr_id) ? 1 : 0;
			$tmp_data['blocked'] = $this->object->getMembersObject()->isBlocked($usr_id) ? 1 : 0;
			$tmp_data['usr_id'] = $usr_id;

			if($this->show_tracking)
			{
				if(in_array($usr_id,$completed))
				{
					$tmp_data['progress'] = LP_STATUS_COMPLETED;
				}
				elseif(in_array($usr_id,$in_progress))
				{
					$tmp_data['progress'] = LP_STATUS_IN_PROGRESS;
				}
				elseif(in_array($usr_id,$failed))
				{
					$tmp_data['progress'] = LP_STATUS_FAILED;
				}
				else
				{
					$tmp_data['progress'] = LP_STATUS_NOT_ATTEMPTED;
				}
			}

			if($privacy->enabledCourseAccessTimes())
			{
				if(isset($progress[$usr_id]['ts']) and $progress[$usr_id]['ts'])
				{
					$tmp_data['access_ut'] = $progress[$usr_id]['ts'];
					$tmp_data['access_time'] = ilDatePresentation::formatDate(new ilDateTime($progress[$usr_id]['ts'],IL_CAL_UNIX));
				}
				else
				{
					$tmp_data['access_ut'] = 0;
					$tmp_data['access_time'] = $this->lng->txt('no_date');
				}
			}
			$members[$usr_id] = $tmp_data;
		}
		return $members ? $members : array();
	}
	
	
	
	/**
	 * Member administration
	 *
	 * @global ilRbacReview $rbacreview
	 * @access protected
	 * @return
	 */
	protected function membersObject()
	{
		global $ilUser, $rbacsystem, $ilToolbar, $lng, $ilCtrl, $tpl, $rbacreview;
		
		include_once('./Modules/Course/classes/class.ilCourseParticipants.php');
		include_once('./Modules/Course/classes/class.ilCourseParticipantsTableGUI.php');
		include_once './Services/Tracking/classes/class.ilObjUserTracking.php';
		include_once('./Services/Tracking/classes/class.ilLPObjSettings.php');
		
		
		if(isset($_GET['member_table_nav']))
		{
			list($_SESSION['crs_print_sort'],$_SESSION['crs_print_order'],$tmp) = explode(':',$_GET['member_table_nav']);
		}

		$this->checkPermission('write');
		$this->show_tracking = (ilObjUserTracking::_enabledLearningProgress() and 
			ilObjUserTracking::_enabledUserRelatedData() and
			ilLPObjSettings::_lookupMode($this->object->getId()) != LP_MODE_DEACTIVATED);

		include_once('./Modules/Course/classes/class.ilCourseItems.php');
		$this->timings_enabled = (ilCourseItems::_hasTimings($this->object->getRefId()) and 
			($this->object->getViewMode() == IL_CRS_VIEW_TIMING));
			
		$this->setSubTabs('members');
		$this->tabs_gui->setTabActive('members');
		$this->tabs_gui->setSubTabActive('crs_member_administration');
		
		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.crs_edit_members.html','Modules/Course');
		$this->tpl->setVariable('FORMACTION',$this->ctrl->getFormAction($this));
		
		// add members
		include_once './Services/Search/classes/class.ilRepositorySearchGUI.php';
		ilRepositorySearchGUI::fillAutoCompleteToolbar(
			$this,
			$ilToolbar,
			array(
				'auto_complete_name'	=> $lng->txt('user'),
				'user_type'				=> array(
					ilCourseConstants::CRS_MEMBER => $lng->txt("crs_member"),
					ilCourseConstants::CRS_TUTOR => $lng->txt("crs_tutor"),
					ilCourseConstants::CRS_ADMIN => $lng->txt("crs_admin")
				),
				'submit_name'			=> $lng->txt('add')
			)
		);
		
		// spacer
		$ilToolbar->addSeparator();

		// search button
		$ilToolbar->addButton($this->lng->txt("crs_search_users"),
			$this->ctrl->getLinkTargetByClass('ilRepositorySearchGUI','start'));
			
		// separator
		$ilToolbar->addSeparator();
			
		// print button
		$ilToolbar->addButton($this->lng->txt("crs_print_list"),
			$this->ctrl->getLinkTarget($this, 'printMembers'));
		
		/* attendance list button
		$ilToolbar->addButton($this->lng->txt("sess_gen_attendance_list"),
			$this->ctrl->getLinkTarget($this, 'attendanceList'));
		*/

		$this->setShowHidePrefs();
		
		// Waiting list table
		include_once('./Modules/Course/classes/class.ilCourseWaitingList.php');
		$waiting_list = new ilCourseWaitingList($this->object->getId());
		if(count($wait = $waiting_list->getAllUsers()))
		{
			include_once('./Services/Membership/classes/class.ilWaitingListTableGUI.php');
			if($ilUser->getPref('crs_wait_hide'))
			{
				$table_gui = new ilWaitingListTableGUI($this,$waiting_list,false);
				$this->ctrl->setParameter($this,'wait_hide',0);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('show'),
					'',
					ilUtil::getImagePath('edit_add.png'));
				$this->ctrl->clearParameters($this);
			}
			else
			{
				$table_gui = new ilWaitingListTableGUI($this,$waiting_list,true);
				$this->ctrl->setParameter($this,'wait_hide',1);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('hide'),
					'',
					ilUtil::getImagePath('edit_remove.png'));
				$this->ctrl->clearParameters($this);
			}
			$table_gui->setUsers($wait);
			$table_gui->setTitle($this->lng->txt('crs_waiting_list'),'icon_usr.gif',$this->lng->txt('crs_waiting_list'));
			$this->tpl->setVariable('TABLE_WAIT',$table_gui->getHTML());
		}

		// Subscriber table
		if($subscribers = ilCourseParticipants::lookupSubscribers($this->object->getId()))
		{
			include_once('./Services/Membership/classes/class.ilSubscriberTableGUI.php');
			if($ilUser->getPref('crs_subscriber_hide'))
			{
				$table_gui = new ilSubscriberTableGUI($this,false);
				$this->ctrl->setParameter($this,'subscriber_hide',0);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('show'),
					'',
					ilUtil::getImagePath('edit_add.png'));
				$this->ctrl->clearParameters($this);
			}
			else
			{
				$table_gui = new ilSubscriberTableGUI($this,true);
				$this->ctrl->setParameter($this,'subscriber_hide',1);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('hide'),
					'',
					ilUtil::getImagePath('edit_remove.png'));
				$this->ctrl->clearParameters($this);
			}
			$table_gui->readSubscriberData();
			$table_gui->setTitle($this->lng->txt('group_new_registrations'),'icon_usr.gif',$this->lng->txt('group_new_registrations'));
			$this->tpl->setVariable('TABLE_SUB',$table_gui->getHTML());
		}
				
		
		
		if($rbacreview->getNumberOfAssignedUsers(array($this->object->getDefaultAdminRole())))
		{
			// Security: display the list of course administrators read-only, 
			// if the user doesn't have the 'edit_permission' permission. 
 			$showEditLink = $rbacsystem->checkAccess("edit_permission", $this->object->getRefId());
			if($ilUser->getPref('crs_admin_hide'))
			{
				$table_gui = new ilCourseParticipantsTableGUI(
					$this,
					'admin',
					false,
					$this->show_tracking,
					$this->timings_enabled,
					$showEditLink,
					$this->object->getDefaultAdminRole()
				);
				$this->ctrl->setParameter($this,'admin_hide',0);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('show'),
					'',
					ilUtil::getImagePath('edit_add.png'));
				$this->ctrl->clearParameters($this);
			}
			else
			{
				$table_gui = new ilCourseParticipantsTableGUI(
					$this,
					'admin',
					true,
					$this->show_tracking,
					$this->timings_enabled,
					$showEditLink,
					$this->object->getDefaultAdminRole()
				);
				$this->ctrl->setParameter($this,'admin_hide',1);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('hide'),
					'',
					ilUtil::getImagePath('edit_remove.png'));
				$this->ctrl->clearParameters($this);
			}
			$table_gui->setTitle($this->lng->txt('crs_administrators'),'icon_usr.gif',$this->lng->txt('crs_administrators'));
			$table_gui->parse();
			$this->tpl->setVariable('ADMINS',$table_gui->getHTML());	
		}
		if($rbacreview->getNumberOfAssignedUsers(array($this->object->getDefaultTutorRole())))
		{
			if($ilUser->getPref('crs_tutor_hide'))
			{
				$table_gui = new ilCourseParticipantsTableGUI(
					$this,
					'tutor',
					false,
					$this->show_tracking,
					$this->timings_enabled,
					true,
					$this->object->getDefaultTutorRole()
				);
				$this->ctrl->setParameter($this,'tutor_hide',0);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('show'),
					'',
					ilUtil::getImagePath('edit_add.png'));
				$this->ctrl->clearParameters($this);
			}
			else
			{
				$table_gui = new ilCourseParticipantsTableGUI(
					$this,
					'tutor',
					true,
					$this->show_tracking,
					$this->timings_enabled,
					true,
					$this->object->getDefaultTutorRole()
				);
				$this->ctrl->setParameter($this,'tutor_hide',1);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('hide'),
					'',
					ilUtil::getImagePath('edit_remove.png'));
				$this->ctrl->clearParameters($this);
			}
			$table_gui->setTitle($this->lng->txt('crs_tutors'),'icon_usr.gif',$this->lng->txt('crs_tutors'));
			$table_gui->parse();
			$this->tpl->setVariable('TUTORS',$table_gui->getHTML());	
		}
		if($rbacreview->getNumberOfAssignedUsers(array($this->object->getDefaultMemberRole())))
		{
			if($ilUser->getPref('crs_member_hide'))
			{
				$table_gui = new ilCourseParticipantsTableGUI(
					$this,
					'member',
					false,
					$this->show_tracking,
					$this->timings_enabled,
					true,
					$this->object->getDefaultMemberRole()
				);

				$this->ctrl->setParameter($this,'member_hide',0);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('show'),
					'',
					ilUtil::getImagePath('edit_add.png'));
				$this->ctrl->clearParameters($this);
			}
			else
			{
				$table_gui = new ilCourseParticipantsTableGUI(
					$this,
					'member',
					true,
					$this->show_tracking,
					$this->timings_enabled,
					true,
					$this->object->getDefaultMemberRole()
				);
				$this->ctrl->setParameter($this,'member_hide',1);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('hide'),
					'',
					ilUtil::getImagePath('edit_remove.png'));
				$this->ctrl->clearParameters($this);
			}
			$table_gui->setTitle($this->lng->txt('crs_members'),'icon_usr.gif',$this->lng->txt('crs_members'));
			$table_gui->parse();
			$this->tpl->setCurrentBlock('member_block');
			$this->tpl->setVariable('MEMBERS',$table_gui->getHTML());
			$this->tpl->parseCurrentBlock();
			
		}

		foreach(ilCourseParticipants::getMemberRoles($this->object->getRefId()) as $role_id)
		{
			if($ilUser->getPref('crs_role_hide_'.$role_id))
			{
				$table_gui = new ilCourseParticipantsTableGUI(
					$this,
					'role',
					false,
					$this->show_tracking,
					$this->timings_enabled,
					true,
					$role_id
				);
				$this->ctrl->setParameter($this,'role_hide_'.$role_id,0);
				$table_gui->addHeaderCommand(
					$this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('show'),
					'',
					ilUtil::getImagePath('edit_add.png')
				);
				$this->ctrl->clearParameters($this);
			}
			else
			{
				$table_gui = new ilCourseParticipantsTableGUI(
					$this,
					'role',
					true,
					$this->show_tracking,
					$this->timings_enabled,
					true,
					$role_id
				);
				$this->ctrl->setParameter($this,'role_hide_'.$role_id,1);
				$table_gui->addHeaderCommand(
					$this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('hide'),
					'',
					ilUtil::getImagePath('edit_remove.png')
				);
				$this->ctrl->clearParameters($this);
			}

			$table_gui->setTitle(ilObject::_lookupTitle($role_id),'icon_usr.gif',$this->lng->txt('crs_members'));
			$table_gui->parse();
			$this->tpl->setCurrentBlock('member_block');
			$this->tpl->setVariable('MEMBERS',$table_gui->getHTML());
			$this->tpl->parseCurrentBlock();
		}
		
		
		$this->tpl->setVariable('TXT_SELECTED_USER',$this->lng->txt('crs_selected_users'));
		$this->tpl->setVariable('BTN_FOOTER_EDIT',$this->lng->txt('edit'));
		$this->tpl->setVariable('BTN_FOOTER_VAL',$this->lng->txt('remove'));
		$this->tpl->setVariable('BTN_FOOTER_MAIL',$this->lng->txt('crs_mem_send_mail'));
		$this->tpl->setVariable('ARROW_DOWN',ilUtil::getImagePath('arrow_downright.gif'));
		
	}
	


	/**
	 * update admin status
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function updateAdminStatusObject()
	{
		$this->checkPermission('write');
		
		$visible_members = array_intersect(array_unique((array) $_POST['visible_member_ids']),$this->object->getMembersObject()->getAdmins());
		$passed = is_array($_POST['passed']) ? $_POST['passed'] : array();
		$notification = is_array($_POST['notification']) ? $_POST['notification'] : array();
		
		$this->updateParticipantsStatus('admins',$visible_members,$passed,$notification,array());
	}
	
	/**
	 * update tuto status
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function updateTutorStatusObject()
	{
		$this->checkPermission('write');
		
		$visible_members = array_intersect(array_unique((array) $_POST['visible_member_ids']),$this->object->getMembersObject()->getTutors());
		$passed = is_array($_POST['passed']) ? $_POST['passed'] : array();
		$notification = is_array($_POST['notification']) ? $_POST['notification'] : array();

		$this->updateParticipantsStatus('admins',$visible_members,$passed,$notification,array());
	}
	
	/**
	 * updateMemberStatus
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function updateMemberStatusObject()
	{
		$this->checkPermission('write');

		$visible_members = array_intersect(array_unique((array) $_POST['visible_member_ids']),$this->object->getMembersObject()->getMembers());
		$passed = is_array($_POST['passed']) ? $_POST['passed'] : array();
		$blocked = is_array($_POST['blocked']) ? $_POST['blocked'] : array();
		
		$this->updateParticipantsStatus('members',$visible_members,$passed,array(),$blocked);
	
	}

	/**
	 * Update status of additional course roles
	 */
	public function updateRoleStatusObject()
	{
		global $rbacreview;

		$this->checkPermission('write');

		include_once './Modules/Course/classes/class.ilCourseParticipants.php';

		$users = array();
		foreach(ilCourseParticipants::getMemberRoles($this->object->getRefId()) as $role_id)
		{
			$users = array_merge($users,$rbacreview->assignedUsers($role_id));
		}

		$passed = is_array($_POST['passed']) ? $_POST['passed'] : array();
		$blocked = is_array($_POST['blocked']) ? $_POST['blocked'] : array();

		$this->updateParticipantsStatus('members',$users,$passed,array(),$blocked);
	}
	
	/**
	 * sync course status and lp status 
	 *  
	 * @param int $a_member_id
	 * @param bool $a_has_passed
	 */
	protected function updateLPFromStatus($a_member_id, $a_has_passed)
	{					
		include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
		if(ilObjUserTracking::_enabledLearningProgress() &&
			$this->object->getStatusDetermination() == ilObjCourse::STATUS_DETERMINATION_LP)
		{	
			include_once './Services/Tracking/classes/class.ilLPObjSettings.php';
			$lp_settings = new ilLPObjSettings($this->object->getId());
			if($lp_settings->getMode() == LP_MODE_MANUAL_BY_TUTOR)
			{
				include_once 'Services/Tracking/classes/class.ilLPMarks.php';
				$marks = new ilLPMarks($this->object->getId(), $a_member_id);
				$marks->setCompleted($a_has_passed);
				$marks->update();

				include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
				ilLPStatusWrapper::_updateStatus($this->object->getId(), $a_member_id, null, false, true);
			}
		}
	}

	function updateParticipantsStatus($type,$visible_members,$passed,$notification,$blocked)
	{
		global $ilAccess,$ilErr,$ilUser,$rbacadmin;

		foreach($visible_members as $member_id)
		{
			$this->object->getMembersObject()->updatePassed($member_id,in_array($member_id,$passed),$ilUser->getId());
			
			$this->updateLPFromStatus($member_id, in_array($member_id, $passed));
			
			switch($type)
			{
				case 'admins';
					$this->object->getMembersObject()->updateNotification($member_id,in_array($member_id,$notification));
					$this->object->getMembersObject()->updateBlocked($member_id,false);
					break;
					
				case 'members':
					if($this->object->getMembersObject()->isBlocked($member_id) and !in_array($member_id,$blocked))
					{
						$this->object->getMembersObject()->sendNotification($this->object->getMembersObject()->NOTIFY_UNBLOCK_MEMBER,$member_id);
					}
					if(!$this->object->getMembersObject()->isBlocked($member_id) and in_array($member_id,$blocked))
					{
						$this->object->getMembersObject()->sendNotification($this->object->getMembersObject()->NOTIFY_BLOCK_MEMBER,$member_id);
					}					
					$this->object->getMembersObject()->updateNotification($member_id,false);
					$this->object->getMembersObject()->updateBlocked($member_id,in_array($member_id,$blocked));
					
					
					break;
			}
		}
		
		ilUtil::sendSuccess($this->lng->txt('settings_saved'));
		$this->membersObject();
	}


	function __showWaitingList()
	{
		include_once './Modules/Course/classes/class.ilObjCourseGrouping.php';

		$this->object->initWaitingList();
		if($this->object->waiting_list_obj->getCountUsers())
		{
			$counter = 0;
			$f_result = array();
			foreach($this->object->waiting_list_obj->getAllUsers() as $waiting_data)
			{
				// GET USER OBJ
				if($tmp_obj = ilObjectFactory::getInstanceByObjId($waiting_data['usr_id'],false))
				{
					$waiting_list_ids[] = $waiting_data['usr_id'];
					$message = '';
					// Check if user is member in course grouping
					foreach(ilObjCourseGrouping::_getGroupingCourseIds($this->object->getRefId(),
						$this->object->getId()) as $course_data)
					{
						$tmp_members = ilCourseParticipants::_getInstanceByObjId($course_data['id']);
						if($course_data['id'] != $this->object->getId() and
							$tmp_members->isGroupingMember($tmp_obj->getId(),$course_data['unique']))
						{
							$message .= ('<br /><font class="alert">'.$this->lng->txt('crs_member_of').' ');
							$message .= (ilObject::_lookupTitle($course_data['id'])."</font>");
						}
					}
					
					$f_result[$counter][]	= ilUtil::formCheckbox(0,"waiting_list[]",$waiting_data['usr_id']);
					$f_result[$counter][]	= $tmp_obj->getLastname().', '.$tmp_obj->getFirstname().$message;
					$f_result[$counter][]   = $tmp_obj->getLogin();
					$f_result[$counter][] = ilDatePresentation::formatDate(new ilDateTime($waiting_data['time'],IL_CAL_UNIX));
					unset($tmp_obj);
					++$counter;
				}
			}
			$this->__showWaitingListTable($f_result,$waiting_list_ids);

		} // END waiting list
	}

	
	/**
	 * edit member 
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function editMemberObject()
	{
		$_POST['members'] = array((int) $_GET['member_id']);
		$this->editMembersObject();
		return true;
	}
	
	
	/**
	 * edit members
	 *
	 * @access public
	 * @return
	 */
	public function editMembersObject()
	{
		$this->checkPermission('write');
		
		$participants = array_unique(array_merge((array) $_POST['admins'],(array) $_POST['tutors'],(array) $_POST['members'], (array) $_POST['roles']));
		
		if(!count($participants))
		{
			ilUtil::sendFailure($this->lng->txt('no_checkbox'));
			$this->membersObject();
			return false;
		}
		
		$this->setSubTabs('members');
		$this->tabs_gui->setTabActive('members');
		$this->tabs_gui->setSubTabActive('crs_member_administration');
		
		include_once('./Modules/Course/classes/class.ilCourseEditParticipantsTableGUI.php');
		$table_gui = new ilCourseEditParticipantsTableGUI($this);
		$table_gui->setTitle($this->lng->txt('crs_header_edit_members'),'icon_usr.gif',$this->lng->txt('crs_header_edit_members'));
		$table_gui->setData($this->readMemberData($participants));

		$this->tpl->setContent($table_gui->getHTML());
		return true;
	}
	
	/**
	 * update members
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function updateMembersObject()
	{
		global $rbacsystem, $rbacreview, $ilUser;
                
		$this->checkPermission('write');
		
		if(!count($_POST['participants']))
		{
			ilUtil::sendFailure($this->lng->txt('no_checkbox'));
			$this->membersObject();
			return false;
		}
		
		$notifications = $_POST['notification'] ? $_POST['notification'] : array();
		$passed = $_POST['passed'] ? $_POST['passed'] : array();
		$blocked = $_POST['blocked'] ? $_POST['blocked'] : array();
		
		// Determine whether the user has the 'edit_permission' permission
		$hasEditPermissionAccess = $rbacsystem->checkAccess('edit_permission', $this->object->getRefId());

		// Get all assignable local roles of the course object, and
		// determine the role id of the course administrator role.
		$assignableLocalCourseRoles = array();
        $courseAdminRoleId = $this->object->getDefaultAdminRole();
		foreach ($this->object->getLocalCourseRoles(false) as $title => $role_id)
		{
			$assignableLocalCourseRoles[$role_id] = $title;
		}
                
		// Validate the user ids and role ids in the post data
		foreach($_POST['participants'] as $usr_id)
		{
			$memberIsCourseAdmin = $rbacreview->isAssigned($usr_id, $courseAdminRoleId);
                        
			// If the current user doesn't have the 'edit_permission' 
			// permission, make sure he doesn't remove the course
			// administrator role of members who are course administrator.
			if (! $hasEditPermissionAccess && $memberIsCourseAdmin &&
				! in_array($courseAdminRoleId, $_POST['roles'][$usr_id])
			)
			{
				$_POST['roles'][$usr_id][] = $courseAdminRoleId;
			}
                        
			// Validate the role ids in the post data
			foreach ((array) $_POST['roles'][$usr_id] as $role_id)
			{
				if(!array_key_exists($role_id, $assignableLocalCourseRoles))
				{
					ilUtil::sendFailure($this->lng->txt('msg_no_perm_perm'));
					$this->membersObject();
					return false;
		        }
		        if(!$hasEditPermissionAccess && 
					$role_id == $courseAdminRoleId &&
					!$memberIsCourseAdmin)
				{
					ilUtil::sendFailure($this->lng->txt('msg_no_perm_perm'));
					$this->membersObject();
					return false;
				}
			}
		}
		
		$has_admin = false;
		foreach(ilCourseParticipants::_getInstanceByObjId($this->object->getId())->getAdmins() as $admin_id)
		{
			if(!isset($_POST['roles'][$admin_id]))
			{
				$has_admin = true;
				break;
			}
			if(in_array($courseAdminRoleId,$_POST['roles'][$admin_id]))
			{
				$has_admin = true;
				break;
			}
		}
		
		if(!$has_admin)
		{
			ilUtil::sendFailure($this->lng->txt('crs_min_one_admin'));
			$_POST['members'] = $_POST['participants'];
			$this->editMembersObject();
			return false;
		}

		foreach($_POST['participants'] as $usr_id)
		{
			$this->object->getMembersObject()->updateRoleAssignments($usr_id,(array) $_POST['roles'][$usr_id]);
			
			// Disable notification for all of them
			$this->object->getMembersObject()->updateNotification($usr_id,0);
			if(($this->object->getMembersObject()->isTutor($usr_id) or $this->object->getMembersObject()->isAdmin($usr_id)) and in_array($usr_id,$notifications))
			{
				$this->object->getMembersObject()->updateNotification($usr_id,1);
			}
			
			$this->object->getMembersObject()->updateBlocked($usr_id,0);
			if((!$this->object->getMembersObject()->isAdmin($usr_id) and !$this->object->getMembersObject()->isTutor($usr_id)) and in_array($usr_id,$blocked))
			{
				$this->object->getMembersObject()->updateBlocked($usr_id,1);
			}
			$this->object->getMembersObject()->updatePassed($usr_id,in_array($usr_id,$passed),$ilUser->getId());
			$this->object->getMembersObject()->sendNotification(
				$this->object->getMembersObject()->NOTIFY_STATUS_CHANGED,
				$usr_id);
			
			$this->updateLPFromStatus($usr_id,in_array($usr_id,$passed));	
		}
		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"));
		$this->membersObject();
		return true;		
	
	}
	
	


	function updateMemberObject()
	{
		global $rbacsystem, $ilUser;

		$this->checkPermission('write');

		// CHECK MEMBER_ID
		if(!isset($_GET["member_id"]) or !$this->object->getMembersObject()->isAssigned((int) $_GET["member_id"]))
		{
			$this->ilias->raiseError($this->lng->txt("crs_no_valid_member_id_given"),$this->ilias->error_obj->MESSAGE);
		}

		
		// Remember settings for notification
		$passed = $this->object->getMembersObject()->hasPassed((int) $_GET['member_id']);
		$notify = $this->object->getMembersObject()->isNotificationEnabled((int) $_GET['member_id']);
		$blocked = $this->object->getMembersObject()->isBlocked((int) $_GET['member_id']);
		
		$this->object->getMembersObject()->updateRoleAssignments((int) $_GET['member_id'],$_POST['roles']);
		$this->object->getMembersObject()->updatePassed((int) $_GET['member_id'],(int) $_POST['passed'],$ilUser->getId());
		$this->object->getMembersObject()->updateNotification((int) $_GET['member_id'],(int) $_POST['notification']);
		$this->object->getMembersObject()->updateBlocked((int) $_GET['member_id'],(int) $_POST['blocked']);
		
		if($passed != $this->object->getMembersObject()->hasPassed((int) $_GET['member_id']) or
			$notify != $this->object->getMembersObject()->isNotificationEnabled((int) $_GET['member_id']) or
			$blocked != $this->object->getMembersObject()->isBlocked((int) $_GET['member_id']))
		{
			$this->object->getMembersObject()->sendNotification($this->object->getMembersObject()->NOTIFY_STATUS_CHANGED,(int) $_GET['member_id']);
		}

		$this->updateLPFromStatus((int) $_GET['member_id'], (bool) $_POST['passed']);	
		
		ilUtil::sendSuccess($this->lng->txt("crs_member_updated"));
		$this->membersObject();
		return true;		

	}

	
	/**
	 * callback from repository search gui
	 * @global ilRbacSystem $rbacsystem
	 * @param int $a_type
	 * @param array $a_usr_ids
	 * @return bool
	 */
	public function assignMembersObject(array $a_usr_ids,$a_type)
	{
		global $rbacsystem;

		$this->checkPermission('write');
		if(!count($a_usr_ids))
		{
			ilUtil::sendFailure($this->lng->txt("crs_no_users_selected"),true);
			return false;
		}

		$added_users = 0;
		foreach($a_usr_ids as $user_id)
		{
			if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user_id,false))
			{
				continue;
			}
			if($this->object->getMembersObject()->isAssigned($user_id))
			{
				continue;
			}
			switch($a_type)
			{
				case ilCourseConstants::CRS_MEMBER:
					$this->object->getMembersObject()->add($user_id,IL_CRS_MEMBER);
					break;
				case ilCourseConstants::CRS_TUTOR:
					$this->object->getMembersObject()->add($user_id,IL_CRS_TUTOR);
					break;
				case ilCourseConstants::CRS_ADMIN:
					$this->object->getMembersObject()->add($user_id,IL_CRS_ADMIN);
					break;
				
			}
			$this->object->getMembersObject()->sendNotification($this->object->getMembersObject()->NOTIFY_ACCEPT_USER,$user_id);

			include_once './Modules/Forum/classes/class.ilForumNotification.php';
			ilForumNotification::checkForumsExistsInsert($this->object->getRefId(), $user_id);

			++$added_users;
		}
		if($added_users)
		{
			ilUtil::sendSuccess($this->lng->txt("crs_users_added"),true);
			unset($_SESSION["crs_search_str"]);
			unset($_SESSION["crs_search_for"]);
			unset($_SESSION['crs_usr_search_result']);

			$this->checkLicenses(true);
			$this->ctrl->redirect($this,'members');
		}
		ilUtil::sendFailure($this->lng->txt("crs_users_already_assigned"),true);
		return false;
	}

	public function assignFromWaitingListObject()
	{
		global $rbacsystem;

		$this->checkPermission('write');

		if(!count($_POST["waiting"]))
		{
			ilUtil::sendFailure($this->lng->txt("crs_no_users_selected"));
			$this->membersObject();

			return false;
		}
		include_once('./Modules/Course/classes/class.ilCourseWaitingList.php');
		$waiting_list = new ilCourseWaitingList($this->object->getId());

		$added_users = 0;
		foreach($_POST["waiting"] as $user_id)
		{
			if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user_id,false))
			{
				continue;
			}
			if($this->object->getMembersObject()->isAssigned($user_id))
			{
				continue;
			}
			$this->object->getMembersObject()->add($user_id,IL_CRS_MEMBER);
			$this->object->getMembersObject()->sendNotification($this->object->getMembersObject()->NOTIFY_ACCEPT_USER,$user_id);
			$waiting_list->removeFromList($user_id);

			include_once('./Modules/Forum/classes/class.ilForumNotification.php');
			ilForumNotification::checkForumsExistsInsert($this->object->getRefId(), $user_id);
			++$added_users;
		}

		if($added_users)
		{
			ilUtil::sendSuccess($this->lng->txt("crs_users_added"));
			$this->membersObject();
			return true;
		}
		else
		{
			ilUtil::sendFailure($this->lng->txt("crs_users_already_assigned"));
			$this->membersObject();
			return false;
		}
	}
	
	/**
	 * refuse from waiting list
	 *
	 * @access public
	 * @return
	 */
	public function refuseFromListObject()
	{
		global $ilUser;
		
		$this->checkPermission('write');
		
		if(!count($_POST['waiting']))
		{
			ilUtil::sendFailure($this->lng->txt('no_checkbox'));
			$this->membersObject();
			return false;
		}
		
		include_once('./Modules/Course/classes/class.ilCourseWaitingList.php');
		$waiting_list = new ilCourseWaitingList($this->object->getId());

		foreach($_POST["waiting"] as $user_id)
		{
			$waiting_list->removeFromList($user_id);
			$this->object->getMembersObject()->sendNotification($this->object->getMembersObject()->NOTIFY_DISMISS_SUBSCRIBER,$user_id);
		}
		
		ilUtil::sendSuccess($this->lng->txt('crs_users_removed_from_list'));
		$this->membersObject();
		return true;
	}
	

	function performRemoveFromWaitingListObject()
	{
		global $rbacsystem;

		// MINIMUM ACCESS LEVEL = 'administrate'
		$this->checkPermission('write');
		/*
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		*/
		if(!is_array($_SESSION["crs_delete_waiting_list_ids"]))
		{
			ilUtil::sendFailure($this->lng->txt("crs_no_users_selected"));
			$this->membersObject();

			return false;
		}

		$this->object->initWaitingList();
		foreach($_SESSION['crs_delete_waiting_list_ids'] as $usr_id)
		{
			$this->object->waiting_list_obj->removeFromList($usr_id);
		}
		ilUtil::sendSuccess($this->lng->txt('crs_users_removed_from_list'));
		$this->membersObject();

		return true;
	}

		
	public function assignSubscribersObject()
	{
		global $rbacsystem,$ilErr;


		$this->checkPermission('write');

		if(!is_array($_POST["subscribers"]))
		{
			ilUtil::sendFailure($this->lng->txt("crs_no_subscribers_selected"));
			$this->membersObject();

			return false;
		}
		
		if(!$this->object->getMembersObject()->assignSubscribers($_POST["subscribers"]))
		{
			ilUtil::sendFailure($ilErr->getMessage());
			$this->membersObject();
			return false;
		}
		else
		{
			foreach($_POST["subscribers"] as $usr_id)
			{
				$this->object->getMembersObject()->sendNotification($this->object->getMembersObject()->NOTIFY_ACCEPT_SUBSCRIBER,$usr_id);

				include_once('./Modules/Forum/classes/class.ilForumNotification.php');
				ilForumNotification::checkForumsExistsInsert($this->object->getRefId(), $usr_id);
			}
		}
		ilUtil::sendSuccess($this->lng->txt("crs_subscribers_assigned"));
		$this->membersObject();
		
		return true;
	}

	function autoFillObject()
	{
		global $rbacsystem;

		$this->checkPermission('write');

		if($this->object->isSubscriptionMembershipLimited() and $this->object->getSubscriptionMaxMembers() and 
		   $this->object->getSubscriptionMaxMembers() <= $this->object->getMembersObject()->getCountMembers())
		{
			ilUtil::sendFailure($this->lng->txt("crs_max_members_reached"));
			$this->membersObject();

			return false;
		}
		if($number = $this->object->getMembersObject()->autoFillSubscribers())
		{
			ilUtil::sendSuccess($this->lng->txt("crs_number_users_added")." ".$number);
		}
		else
		{
			ilUtil::sendFailure($this->lng->txt("crs_no_users_added"));
		}
		$this->membersObject();

		return true;
	}


	function deleteSubscribers()
	{
		global $rbacsystem;

		$this->tabs_gui->setTabActive('members');

		// MINIMUM ACCESS LEVEL = 'administrate'
		$this->checkPermission('write');
		/*
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		*/
		if(!is_array($_POST["subscriber"]) or !count($_POST["subscriber"]))
		{
			ilUtil::sendFailure($this->lng->txt("crs_no_subscribers_selected"));
			$this->membersObject();

			return false;
		}
		ilUtil::sendQuestion($this->lng->txt("crs_delete_subscribers_sure"));

		// SHOW DELETE SCREEN
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_editMembers.html",'Modules/Course');

		// SAVE IDS IN SESSION
		$_SESSION["crs_delete_subscriber_ids"] = $_POST["subscriber"];

		$counter = 0;
		$f_result = array();

		foreach($_POST["subscriber"] as $member_id)
		{
			$member_data = $this->object->getMembersObject()->getSubscriberData($member_id);

			// GET USER OBJ
			if($tmp_obj = ilObjectFactory::getInstanceByObjId($member_id,false))
			{
				$f_result[$counter][]	= $tmp_obj->getLogin();
				$f_result[$counter][]	= $tmp_obj->getFirstname();
				$f_result[$counter][]	= $tmp_obj->getLastname();
				$f_result[$counter][]   = ilDatePresentation::formatDate(new ilDateTime($member_data['time']),IL_CAL_UNIX);

				unset($tmp_obj);
				++$counter;
			}
		}
		return $this->__showDeleteSubscriberTable($f_result);
	}
		
	function removeFromWaitingList()
	{
		global $rbacsystem;

		$this->tabs_gui->setTabActive('members');

		// MINIMUM ACCESS LEVEL = 'administrate'
		$this->checkPermission('write');
		/*
		if(!$rbacsystem->checkAccess("write", $this->object->getRefId()))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		*/
		if(!is_array($_POST["waiting_list"]) or !count($_POST["waiting_list"]))
		{
			ilUtil::sendFailure($this->lng->txt("crs_no_users_selected"));
			$this->membersObject();

			return false;
		}
		ilUtil::sendSuccess($this->lng->txt("crs_delete_from_list_sure"));

		// SHOW DELETE SCREEN
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_editMembers.html",'Modules/Course');
		$this->object->initWaitingList();

		// SAVE IDS IN SESSION
		$_SESSION["crs_delete_waiting_list_ids"] = $_POST["waiting_list"];

		$counter = 0;
		$f_result = array();

		foreach($_POST["waiting_list"] as $wait_id)
		{
			$user_data =& $this->object->waiting_list_obj->getUser($wait_id);
			// GET USER OBJ
			if($tmp_obj = ilObjectFactory::getInstanceByObjId($wait_id,false))
			{
				$f_result[$counter][]	= $tmp_obj->getLogin();
				$f_result[$counter][]	= $tmp_obj->getFirstname();
				$f_result[$counter][]	= $tmp_obj->getLastname();
				#$f_result[$counter][]   = strftime("%Y-%m-%d %R",$user_data["time"]);
				$f_result[$counter][]   = ilDatePresentation::formatDate(new ilDateTime($user_data['time'],IL_CAL_UNIX));

				unset($tmp_obj);
				++$counter;
			}
		}
		return $this->__showRemoveFromWaitingListTable($f_result);
	}
	
	public function leaveObject()
	{
		global $ilUser;
		
		$this->checkPermission('leave');
		
		if($this->object->getMembersObject()->isLastAdmin($ilUser->getId()))
		{
			ilUtil::sendFailure($this->lng->txt('crs_min_one_admin'));
			$this->viewObject();
			return false;
		}
		
		
		$this->tabs_gui->setTabActive('crs_unsubscribe');
		
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_unsubscribe_sure.html",'Modules/Course');
		ilUtil::sendQuestion($this->lng->txt('crs_unsubscribe_sure'));
		
		$this->tpl->setVariable("UNSUB_FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt("cancel"));
		$this->tpl->setVariable("CMD_SUBMIT",'performUnsubscribe');
		$this->tpl->setVariable("TXT_SUBMIT",$this->lng->txt("crs_unsubscribe"));
		return true;
				
	}
	
	
	function unsubscribeObject()
	{
		global $rbacsystem,$ilAccess;

		// CHECK ACCESS
		$this->checkPermission('leave');
		/*
		if(!$ilAccess->checkAccess("leave",'', $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_write"),$this->ilias->error_obj->MESSAGE);
		}
		*/

		$this->tabs_gui->setTabActive('crs_unsubscribe');
		#$this->setSubTabs('members');


		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_unsubscribe_sure.html",'Modules/Course');
		ilUtil::sendQuestion($this->lng->txt('crs_unsubscribe_sure'));
		
		$this->tpl->setVariable("UNSUB_FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt("cancel"));
		$this->tpl->setVariable("CMD_SUBMIT",'performUnsubscribe');
		$this->tpl->setVariable("TXT_SUBMIT",$this->lng->txt("crs_unsubscribe"));
		
		return true;
	}

	function performUnsubscribeObject()
	{
		global $rbacsystem, $ilUser, $ilCtrl;

		// CHECK ACCESS
		$this->checkPermission('leave');
		$this->object->getMembersObject()->delete($this->ilias->account->getId());
		$this->object->getMembersObject()->sendUnsubscribeNotificationToAdmins($this->ilias->account->getId());
		$this->object->getMembersObject()->sendNotification($this->object->getMembersObject()->NOTIFY_UNSUBSCRIBE,$ilUser->getId());
		
		include_once './Modules/Forum/classes/class.ilForumNotification.php';
		ilForumNotification::checkForumsExistsDelete($this->ref_id, $ilUser->getId());

		ilUtil::sendSuccess($this->lng->txt('crs_unsubscribed_from_crs'),true);

		$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->tree->getParentId($this->ref_id));
		$ilCtrl->redirectByClass("ilrepositorygui", "");
	}

	/**
	 * Delete members
	 * @global ilAccessHandler $ilAccess
	 * @return
	 */
	function deleteMembersObject()
	{
		global $ilAccess;
		
		$this->checkPermission('write');
		
		$this->setSubTabs('members');
		$this->tabs_gui->setTabActive('members');
		$this->tabs_gui->setSubTabActive('crs_member_administration');
		
		$participants = array_merge((array) $_POST['admins'],(array) $_POST['tutors'], (array) $_POST['members'], (array) $_POST['roles']);
		
		if(!$participants)
		{
			ilUtil::sendFailure($this->lng->txt('no_checkbox'));
			$this->membersObject();
			return true;
		}

		// Check last admin
		if(!$this->object->getMemberObject()->checkLastAdmin($participants))
		{
			ilUtil::sendFailure($this->lng->txt('crs_at_least_one_admin'));
			$this->membersObject();

			return false;
		}
		
		// Access check for admin deletion
		if(!$ilAccess->checkAccess('edit_permission', '',$this->object->getRefId()))
		{
			foreach ($participants as $usr_id)
			{
				$part = ilCourseParticipant::_getInstanceByObjId($this->object->getId(),$usr_id);
				if($part->isAdmin())
				{
					ilUtil::sendFailure($this->lng->txt("msg_no_perm_perm"));
					$this->membersObject();
					return false;
				}
			}
		}
		
		
		include_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
		$confirm = new ilConfirmationGUI();
		$confirm->setFormAction($this->ctrl->getFormAction($this,'deleteMembers'));
		$confirm->setHeaderText($this->lng->txt('crs_header_delete_members'));
		$confirm->setConfirm($this->lng->txt('confirm'),'removeMembers');
		$confirm->setCancel($this->lng->txt('cancel'),'members');
		
		foreach($participants as $usr_id)
		{
			$name = ilObjUser::_lookupName($usr_id);

			$confirm->addItem('participants[]',
				$name['user_id'],
				$name['lastname'].', '.$name['firstname'].' ['.$name['login'].']',
				ilUtil::getImagePath('icon_usr.gif'));
		}
		
		$this->tpl->setContent($confirm->getHTML());
		
	}

	function removeMembersObject()
	{
		global $rbacreview, $rbacsystem;
                
		$this->checkPermission('write');
		
		if(!is_array($_POST["participants"]) or !count($_POST["participants"]))
		{
			ilUtil::sendFailure($this->lng->txt("crs_no_member_selected"));
			$this->membersObject();

			return false;
		}
		
		// If the user doesn't have the edit_permission, he may not remove
		// members who have the course administrator role
		if (! $rbacsystem->checkAccess('edit_permission', $this->object->getRefId()))
		{
			// Determine the role id of the course administrator role.
			$courseAdminRoleId = null;
			foreach ($this->object->getLocalCourseRoles(false) as $title => $role_id)
			{
				if (substr($title, 0, 12) == 'il_crs_admin')
				{
					$courseAdminRoleId = $role_id;
				}
			}
                
			foreach ($_POST['participants'] as $usr_id)
			{
				if ($rbacreview->isAssigned($usr_id, $courseAdminRoleId))
				{
					ilUtil::sendFailure($this->lng->txt("msg_no_perm_perm"));
					$this->membersObject();
					return false;
				}
			}
		}
        
		if(!$this->object->getMembersObject()->deleteParticipants($_POST["participants"]))
		{
			ilUtil::sendFailure($this->object->getMessage());
			$this->membersObject();

			return false;
		}
		else
		{
			include_once './Modules/Forum/classes/class.ilForumNotification.php';

			// SEND NOTIFICATION
			foreach($_POST["participants"] as $usr_id)
			{
				$this->object->getMembersObject()->sendNotification($this->object->getMembersObject()->NOTIFY_DISMISS_MEMBER,$usr_id);
				ilForumNotification::checkForumsExistsDelete($this->object->getRefId(), $usr_id);
			}
		}
		ilUtil::sendSuccess($this->lng->txt("crs_members_deleted"));
		$this->membersObject();

		return true;
	}

	function refuseSubscribersObject()
	{
		global $rbacsystem;

		$this->checkPermission('write');
		
		if(!$_POST['subscribers'])
		{
			ilUtil::sendFailure($this->lng->txt("crs_no_subscribers_selected"));
			$this->membersObject();
			return false;
		}
	
		if(!$this->object->getMembersObject()->deleteSubscribers($_POST["subscribers"]))
		{
			ilUtil::sendFailure($this->object->getMessage());
			$this->membersObject();
			return false;
		}
		else
		{
			foreach($_POST['subscribers'] as $usr_id)
			{
				$this->object->getMembersObject()->sendNotification($this->object->getMembersObject()->NOTIFY_DISMISS_SUBSCRIBER,$usr_id);
			}
		}

		ilUtil::sendSuccess($this->lng->txt("crs_subscribers_deleted"));
		$this->membersObject();
		return true;
	}

	/**
	* Get tabs
	*/
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem,$ilAccess,$ilUser, $lng, $ilHelp;

		$ilHelp->setScreenIdComponent("crs");
		
		$this->ctrl->setParameter($this,"ref_id",$this->ref_id);

		if($ilAccess->checkAccess('read','',$this->ref_id))
		{
			$tabs_gui->addTab('view_content', $lng->txt("content"),
								 $this->ctrl->getLinkTarget($this,''));
		}
		
		if ($ilAccess->checkAccess('visible','',$this->ref_id))
		{
			//$next_class = $this->ctrl->getNextClass($this);
			
			// this is not nice. tabs should be displayed in ilcoursegui
			// not via ilrepositorygui, then next_class == ilinfoscreengui
			// could be checked
			$force_active = (strtolower($_GET["cmdClass"]) == "ilinfoscreengui"
				|| strtolower($_GET["cmdClass"]) == "ilnotegui")
				? true
				: false;
			$tabs_gui->addTarget("info_short",
								 $this->ctrl->getLinkTargetByClass(
								 array("ilobjcoursegui", "ilinfoscreengui"), "showSummary"),
								 "infoScreen",
								 "", "", $force_active);
		}
		if ($ilAccess->checkAccess('write','',$this->ref_id))
		{
			$force_active = (strtolower($_GET["cmdClass"]) == "ilconditionhandlerinterface"
				&& $_GET["item_id"] == "")
				? true
				: false;
			$tabs_gui->addTarget("settings",
				$this->ctrl->getLinkTarget($this, "edit"),
				array("edit", "editMapSettings", "editCourseIcons", "listStructure"), "", "", $force_active);
		}
		
		
		$is_participant = ilCourseParticipants::_isParticipant($this->ref_id, $ilUser->getId());
		
		// member list
		if($ilAccess->checkAccess('write','',$this->ref_id))
		{
			$tabs_gui->addTarget("members",
								 $this->ctrl->getLinkTarget($this, "members"), 
								 "members",
								 get_class($this));
		}			
		elseif ($this->object->getShowMembers() == $this->object->SHOW_MEMBERS_ENABLED &&
			$is_participant)
		{
			$tabs_gui->addTarget("members",
								 $this->ctrl->getLinkTarget($this, "membersGallery"), 
								 "members",
								 get_class($this));
		}

		// learning progress
		include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
		if(ilLearningProgressAccess::checkAccess($this->object->getRefId(), $is_participant))
		{
			$tabs_gui->addTarget('learning_progress',
								 $this->ctrl->getLinkTargetByClass(array('ilobjcoursegui','illearningprogressgui'),''),
								 '',
								 array('illplistofobjectsgui','illplistofsettingsgui','illearningprogressgui','illplistofprogressgui'));
		}
		
		
		// learning objectives
		if($ilAccess->checkAccess('write','',$this->ref_id))
		{
			include_once('./Modules/Course/classes/class.ilCourseObjective.php');
			if($this->object->getViewMode() == IL_CRS_VIEW_OBJECTIVE or ilCourseObjective::_getCountObjectives($this->object->getId()))
			{
				$force_active = (strtolower($_GET["cmdClass"]) == "ilcourseobjectivesgui")
					? true
					: false;
				$tabs_gui->addTarget("crs_objectives",
									 $this->ctrl->getLinkTarget($this,"listObjectives"), 
									 "listObjectives",
									 get_class($this), "", $force_active);
			}
		}

		// license overview
		include_once("Services/License/classes/class.ilLicenseAccess.php");
		if ($ilAccess->checkAccess('edit_permission', '', $this->ref_id)
		and ilLicenseAccess::_isEnabled())
		{
			$tabs_gui->addTarget("licenses",
				$this->ctrl->getLinkTargetByClass('illicenseoverviewgui', ''),
			"", "illicenseoverviewgui");
		}

		// lom meta data
		if ($ilAccess->checkAccess('write','',$this->ref_id))
		{
			$tabs_gui->addTarget("meta_data",
								 $this->ctrl->getLinkTargetByClass(array('ilobjcoursegui','ilmdeditorgui'),'listSection'),
								 "",
								 "ilmdeditorgui");
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

		if ($ilAccess->checkAccess('edit_permission','',$this->ref_id))
		{
			$tabs_gui->addTarget("perm_settings",
								 $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"),
								 array("perm","info","owner"), 'ilpermissiongui');
		}

		if ($this->ctrl->getTargetScript() == "adm_object.php")
		{
			$tabs_gui->addTarget("show_owner",
								 $this->ctrl->getLinkTarget($this, "owner"), "owner", get_class($this));
			
			if ($this->tree->getSavedNodeData($this->ref_id))
			{
				$tabs_gui->addTarget("trash",
									 $this->ctrl->getLinkTarget($this, "trash"), "trash", get_class($this));
			}
		}
		// Join/Leave
		if($ilAccess->checkAccess('join','',$this->ref_id)
			and !$this->object->getMemberObject()->isAssigned())
		{
			include_once './Modules/Course/classes/class.ilCourseWaitingList.php';
			if(ilCourseWaitingList::_isOnList($ilUser->getId(), $this->object->getId()))
			{
				$tabs_gui->addTab(
					'leave',
					$this->lng->txt('membership_leave'),
					$this->ctrl->getLinkTargetByClass('ilcourseregistrationgui','show','')
				);
					
			}
			else
			{			
				
				$tabs_gui->addTarget("join",
									 $this->ctrl->getLinkTargetByClass('ilcourseregistrationgui', "show"), 
									 'show',
									 "");
			}
		}
		if($ilAccess->checkAccess('leave','',$this->object->getRefId())
			and $this->object->getMemberObject()->isMember())
		{
			$tabs_gui->addTarget("crs_unsubscribe",
								 $this->ctrl->getLinkTarget($this, "unsubscribe"), 
								 'leave',
								 "");
			
		}
	}
	
	function fetchPrintSubscriberData($a_members)
	{
		foreach($a_members as $member_id)
		{
			
			$member_data = $this->object->getMembersObject()->getSubscriberData($member_id);

			if($tmp_obj = ilObjectFactory::getInstanceByObjId($member_id,false))
			{
				$print_member[$member_id]['login'] = $tmp_obj->getLogin();
				$print_member[$member_id]['name'] = $tmp_obj->getLastname().', '.$tmp_obj->getFirstname();
				$print_member[$member_id]['time'] = ilDatePresentation::formatDate(new ilDateTime($member_data['time'],IL_CAL_UNIX));
			}
		}
		switch($_SESSION['crs_print_sort'])
		{
			case 'lastname':
				return ilUtil::sortArray($print_member,'name',$_SESSION['crs_print_order'], false, true);
				
			case 'login':
				return ilUtil::sortArray($print_member,'login',$_SESSION['crs_print_order'], false, true);
			
			case 'sub_time':
				return ilUtil::sortArray($print_member,'time',$_SESSION['crs_print_order'], false, true);
			
			default:
				return ilUtil::sortArray($print_member,'name',$_SESSION['crs_print_order'], false, true);
		}
	}
	
	function fetchPrintMemberData($a_members)
	{
		global $ilAccess,$lng;

		$lng->loadLanguageModule('trac');

		#$is_admin = (bool) $ilAccess->checkAccess("write",'',$this->object->getRefId());
		$is_admin = true;
		
		include_once('./Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
		$privacy = ilPrivacySettings::_getInstance();

		if($privacy->enabledCourseAccessTimes())
		{
			include_once('./Services/Tracking/classes/class.ilLearningProgress.php');
			$progress = ilLearningProgress::_lookupProgressByObjId($this->object->getId());
		}
		
		if($this->show_tracking)
		{
			include_once 'Services/Tracking/classes/class.ilLPStatusWrapper.php';
			$completed = ilLPStatusWrapper::_lookupCompletedForObject($this->object->getId());
			$in_progress = ilLPStatusWrapper::_lookupInProgressForObject($this->object->getId());
			$failed = ilLPStatusWrapper::_lookupFailedForObject($this->object->getId());
		}
		
		foreach($a_members as $member_id)
		{
			// GET USER OBJ
			if($tmp_obj = ilObjectFactory::getInstanceByObjId($member_id,false))
			{
				$print_member[$member_id]['login'] = $tmp_obj->getLogin();
				$print_member[$member_id]['name'] = $tmp_obj->getLastname().', '.$tmp_obj->getFirstname();

				if($this->object->getMembersObject()->isAdmin($member_id))
				{
					$print_member[$member_id]['role'] = $this->lng->txt("il_crs_admin");
				}
				elseif($this->object->getMembersObject()->isTutor($member_id))
				{
					$print_member[$member_id]['role'] = $this->lng->txt("il_crs_tutor");
				}
				elseif($this->object->getMembersObject()->isMember($member_id))
				{
					$print_member[$member_id]['role'] = $this->lng->txt("il_crs_member");
				}
				if($this->object->getMembersObject()->isAdmin($member_id) or $this->object->getMembersObject()->isTutor($member_id))
				{
					if($this->object->getMembersObject()->isNotificationEnabled($member_id))
					{
						$print_member[$member_id]['status'] = $this->lng->txt("crs_notify");
					}
					else
					{
						$print_member[$member_id]['status'] = $this->lng->txt("crs_no_notify");
					}
				}
				else
				{
					if($this->object->getMembersObject()->isBlocked($member_id))
					{
						$print_member[$member_id]['status'] = $this->lng->txt("crs_blocked");
					}
					else
					{
						$print_member[$member_id]['status'] = $this->lng->txt("crs_unblocked");
					}
				}
	
				if($is_admin)
				{
					$print_member[$member_id]['passed'] = $this->object->getMembersObject()->hasPassed($member_id) ?
									  $this->lng->txt('crs_member_passed') :
									  $this->lng->txt('crs_member_not_passed');
					
				}
				if($privacy->enabledCourseAccessTimes())
				{
					if(isset($progress[$member_id]['ts']) and $progress[$member_id]['ts'])
					{
						ilDatePresentation::setUseRelativeDates(false);
						$print_member[$member_id]['access'] = ilDatePresentation::formatDate(new ilDateTime($progress[$member_id]['ts'],IL_CAL_UNIX));
						ilDatePresentation::setUseRelativeDates(true);
					}
					else
					{
						$print_member[$member_id]['access'] = $this->lng->txt('no_date');
					}
				}
				if($this->show_tracking)
				{
					if(in_array($member_id,$completed))
					{
						$print_member[$member_id]['progress'] = $this->lng->txt(LP_STATUS_COMPLETED);
					}
					elseif(in_array($member_id,$in_progress))
					{
						$print_member[$member_id]['progress'] = $this->lng->txt(LP_STATUS_IN_PROGRESS);
					}
					elseif(in_array($member_id,$failed))
					{
						$print_member[$member_id]['progress'] = $this->lng->txt(LP_STATUS_FAILED);
					}
					else
					{
						$print_member[$member_id]['progress'] = $this->lng->txt(LP_STATUS_NOT_ATTEMPTED);
					}
				}
				
			}
		}
		
		switch($_SESSION['crs_print_sort'])
		{
			case 'progress':
				return ilUtil::sortArray($print_member,'progress',$_SESSION['crs_print_order'], false, true);
			
			case 'access_time':
				return ilUtil::sortArray($print_member,'access',$_SESSION['crs_print_order'], false, true);
			
			case 'lastname':
				return ilUtil::sortArray($print_member,'name',$_SESSION['crs_print_order'], false, true);
				
			case 'login':
				return ilUtil::sortArray($print_member,'login',$_SESSION['crs_print_order'], false, true);
			
			case 'passed':
				return ilUtil::sortArray($print_member,'passed',$_SESSION['crs_print_order'], false, true);
			
			case 'blocked':
			case 'notification':
				return ilUtil::sortArray($print_member,'status',$_SESSION['crs_print_order'], false, true);
			
			default:
				return ilUtil::sortArray($print_member,'name',$_SESSION['crs_print_order'], false, true);
		}
	}
		
	function printMembersObject()
	{		
		global $ilTabs;
		
		$this->checkPermission('write');
		$ilTabs->activateTab('members');
		
		$list = $this->initAttendanceList();
		$form = $list->initForm('printMembersOutput');
		$this->tpl->setContent($form->getHTML());	
	}
	
	protected function initAttendanceList()
	{
		include_once('./Modules/Course/classes/class.ilCourseParticipants.php');
		$members_obj = ilCourseParticipants::_getInstanceByObjId($this->object->getId());
		
		include_once 'Services/Membership/classes/class.ilAttendanceList.php';
		$list = new ilAttendanceList($this, $members_obj);		
		
		include_once './Services/Tracking/classes/class.ilObjUserTracking.php';
		include_once('./Services/Tracking/classes/class.ilLPObjSettings.php');
		$this->show_tracking = (ilObjUserTracking::_enabledLearningProgress() and 
			ilObjUserTracking::_enabledUserRelatedData() and
			ilLPObjSettings::_lookupMode($this->object->getId()) != LP_MODE_DEACTIVATED);
		if($this->show_tracking)
		{
			$list->addPreset('progress', $this->lng->txt('learning_progress'), true);
		}
		
		include_once('./Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
		$privacy = ilPrivacySettings::_getInstance();
		if($privacy->enabledCourseAccessTimes())
		{
			$list->addPreset('access', $this->lng->txt('last_access'), true);
		}
		
		$list->addPreset('status', $this->lng->txt('crs_status'), true);
		$list->addPreset('passed', $this->lng->txt('crs_passed'), true);
		
		return $list;
	}
	
	public function getAttendanceListUserData($a_user_id)
	{		
		return $this->members_data[$a_user_id];
	}
	
	function printMembersOutputObject()
	{		
		$list = $this->initAttendanceList();		
		$list->initFromForm();
		$list->setCallback(array($this, 'getAttendanceListUserData'));	
		
		$this->members_data = $this->fetchPrintMemberData($this->object->getMembersObject()->getParticipants());
		
		$list->setTitle($this->lng->txt('crs_members_print_title'),
			$this->lng->txt('obj_crs').': '.$this->object->getTitle().
			' -> '.$this->lng->txt('crs_header_members').' ('.ilFormat::formatUnixTime(time(),true).')');
		
		echo $list->getFullscreenHTML();
		exit();
	
		/* currently deactivated
		
		// SUBSCRIBERS		
		if(count($members = $this->object->getMembersObject()->getSubscribers()))
		{
			$tpl = new ilTemplate('tpl.crs_members_print.html',true,true,'Modules/Course');
		  
			$members = $this->fetchPrintSubscriberData($members);
			foreach($members as $member_data)
			{
				$tpl->setCurrentBlock("subscribers_row");
				$tpl->setVariable("SLOGIN",$member_data['login']);
				$tpl->setVariable("SNAME",$member_data['name']);
				$tpl->setVariable("STIME",$member_data["time"]);
				$tpl->parseCurrentBlock();
			}
			
			$tpl->setVariable("SUBSCRIBERS_IMG_SOURCE",ilUtil::getImagePath('icon_usr.gif'));
			$tpl->setVariable("SUBSCRIBERS_IMG_ALT",$this->lng->txt('crs_subscribers'));
			$tpl->setVariable("SUBSCRIBERS_TABLE_HEADER",$this->lng->txt('crs_subscribers'));
			$tpl->setVariable("TXT_SLOGIN",$this->lng->txt('username'));
			$tpl->setVariable("TXT_SNAME",$this->lng->txt('name'));
			$tpl->setVariable("TXT_STIME",$this->lng->txt('crs_time'));
		  
		    $tpl->show();
		}		 
		*/
	}

	/*
	 * @author Arturo Gonzalez <arturogf@gmail.com>
	 * @access       public
	 */
	function membersGalleryObject()
	{

		global $rbacsystem, $ilErr, $ilAccess, $ilUser;

		$is_admin = (bool) $ilAccess->checkAccess("write", "", $this->object->getRefId());

		if (!$is_admin &&
			$this->object->getShowMembers() == $this->object->SHOW_MEMBERS_DISABLED)
		{
			$ilErr->raiseError($this->lng->txt("msg_no_perm_read"),$ilErr->MESSAGE);
		}


		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.crs_members_gallery.html','Modules/Course');
		
		$this->setSubTabs('members');
		$this->tabs_gui->setTabActive('members');
		$this->tabs_gui->setSubTabActive('crs_members_gallery');
		
		// MEMBERS 
		if(count($members = $this->object->getMembersObject()->getParticipants()))
		{
			$ordered_members = array();

			foreach($members as $member_id)
			{
				if(!($usr_obj = ilObjectFactory::getInstanceByObjId($member_id,false)))
				{
					continue;
				}
				
				// please do not use strtoupper on first/last name for output
				// this messes up with some unicode characters, i guess
				// depending on php verion, alex
				array_push($ordered_members,array("id" => $member_id, 
								  "login" => $usr_obj->getLogin(),
								  "lastname" => $usr_obj->getLastName(),
								  "firstname" => $usr_obj->getFirstName(),
								  "sortlastname" => strtoupper($usr_obj->getLastName()).strtoupper($usr_obj->getFirstName()),
								  "usr_obj" => $usr_obj));
			}

			$ordered_members=ilUtil::sortArray($ordered_members,"sortlastname","asc");

			foreach($ordered_members as $member)
			{
			  $usr_obj = $member["usr_obj"];

			  if(!$usr_obj->getActive())
			  {
				  continue;
			  }

			  $public_profile = in_array($usr_obj->getPref("public_profile"), array("y", "g")) ? "y" : "";
				
				// SET LINK TARGET FOR USER PROFILE
				$this->ctrl->setParameterByClass("ilpublicuserprofilegui", "user", $member["id"]);
				$profile_target = $this->ctrl->getLinkTargetByClass("ilpublicuserprofilegui","getHTML");
			  
				// GET USER IMAGE
				$file = $usr_obj->getPersonalPicturePath("xsmall");
				
				if($this->object->getMembersObject()->isAdmin($member["id"]) or $this->object->getMembersObject()->isTutor($member["id"]))
				{
					if ($public_profile == "y")
					{
						$this->tpl->setCurrentBlock("tutor_linked");
						$this->tpl->setVariable("LINK_PROFILE", $profile_target);
						$this->tpl->setVariable("SRC_USR_IMAGE", $file);
						$this->tpl->parseCurrentBlock();
					}
					else
					{
						$this->tpl->setCurrentBlock("tutor_not_linked");
						$this->tpl->setVariable("SRC_USR_IMAGE", $file);
						$this->tpl->parseCurrentBlock();
					}
					$this->tpl->setCurrentBlock("tutor");
				}
				else
				{
					if ($public_profile == "y")
					{
						$this->tpl->setCurrentBlock("member_linked");
						$this->tpl->setVariable("LINK_PROFILE", $profile_target);
						$this->tpl->setVariable("SRC_USR_IMAGE", $file);
						$this->tpl->parseCurrentBlock();
					}
					else
					{
						$this->tpl->setCurrentBlock("member_not_linked");
						$this->tpl->setVariable("SRC_USR_IMAGE", $file);
						$this->tpl->parseCurrentBlock();
					}
					$this->tpl->setCurrentBlock("member");
				}
				
				// do not show name, if public profile is not activated
				if ($public_profile == "y")
				{
					$this->tpl->setVariable("FIRSTNAME", $member["firstname"]);
					$this->tpl->setVariable("LASTNAME", $member["lastname"]);
				}
				$this->tpl->setVariable("LOGIN", $member["login"]);
				$this->tpl->parseCurrentBlock();

			}
			$this->tpl->setCurrentBlock("members");	
			$this->tpl->setVariable("MEMBERS_TABLE_HEADER",$this->lng->txt('crs_members_title'));
			$this->tpl->parseCurrentBlock();
			
		}
		
		$this->tpl->setVariable("TITLE",$this->lng->txt('crs_members_print_title'));
		$this->tpl->setVariable("CSS_PATH",ilUtil::getStyleSheetLocation());
		
		$headline = $this->object->getTitle()."<br/>".$this->object->getDescription();
		
		$this->tpl->setVariable("HEADLINE",$headline);
		
		$this->tpl->show();
		exit;
	}
	

	function __initTableGUI()
	{
		include_once "./Services/Table/classes/class.ilTableGUI.php";

		return new ilTableGUI(0,false);
	}


	function __setTableGUIBasicData(&$tbl,&$result_set,$from = "")
	{
        switch($from)
		{
			case "members":
				$offset = $_GET["update_members"] ? $_GET["offset"] : 0;
				$order = $_GET["update_members"] ? $_GET["sort_by"] : 'login';
				$direction = $_GET["update_members"] ? $_GET["sort_order"] : '';
				break;

			case "subscribers":
				$offset = $_GET["update_subscribers"] ? $_GET["offset"] : 0;
				$order = $_GET["update_subscribers"] ? $_GET["sort_by"] : 'sub_time';
				$direction = $_GET["update_subscribers"] ? $_GET["sort_order"] : '';
				break;
				
			case "group":
				$offset = $_GET["offset"];
	           	$order = $_GET["sort_by"] ? $_GET["sort_by"] : "title";
				$direction = $_GET["sort_order"];
				break;
				
   			case "role":
				$offset = $_GET["offset"];
	           	$order = $_GET["sort_by"] ? $_GET["sort_by"] : "title";
				$direction = $_GET["sort_order"];
				break;

			default:
				$offset = $_GET["offset"];
				// init sort_by (unfortunatly sort_by is preset with 'title'
	           	if ($_GET["sort_by"] == "title" or empty($_GET["sort_by"]))
                {
                    $_GET["sort_by"] = "login";
                }
                $order = $_GET["sort_by"];
				$direction = $_GET["sort_order"];
				break;
		}

		$tbl->setOrderColumn($order);
		$tbl->setOrderDirection($direction);
		$tbl->setOffset($offset);
		$tbl->setLimit($_GET["limit"]);
		$tbl->setMaxCount(count($result_set));
		$tbl->setFooter("tblfooter",$this->lng->txt("previous"),$this->lng->txt("next"));
		$tbl->setData($result_set);
	}
		

	function __showRemoveFromWaitingListTable($a_result_set)
	{
		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();
		
		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","cancelMember");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("cancel"));
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","performRemoveFromWaitingList");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("delete"));
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",4);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("crs_header_remove_from_waiting_list"),"icon_usr_b.gif",
					   $this->lng->txt("crs_header_remove_from_waiting_list"));
		$tbl->setHeaderNames(array($this->lng->txt("username"),
								   $this->lng->txt("firstname"),
								   $this->lng->txt("lastname"),
								   $this->lng->txt("crs_time")));
		$tbl->setHeaderVars(array("login",
								  "firstname",
								  "lastname",
								  "sub_time"),
							array("ref_id" => $this->object->getRefId(),
								  "cmd" => "members",
								  "cmdClass" => "ilobjcoursegui",
								  "cmdNode" => $_GET["cmdNode"]));

		$tbl->setColumnWidth(array("25%","25%","25%","25%"));

		$this->__setTableGUIBasicData($tbl,$a_result_set);
		$tbl->render();

		$this->tpl->setVariable("EDIT_MEMBER_TABLE",$tbl->tpl->get());

		return true;
	}

	function __showDeleteSubscriberTable($a_result_set)
	{
		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();
		
		$tpl->setCurrentBlock("tbl_form_header");
		$tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","removeSubscribers");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("delete"));
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock("tbl_action_btn");
		$tpl->setVariable("BTN_NAME","cancelMember");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("cancel"));
		$tpl->parseCurrentBlock();
		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("COLUMN_COUNTS",4);
		$tpl->setVariable("IMG_ARROW",ilUtil::getImagePath("arrow_downright.gif"));
		$tpl->parseCurrentBlock();

		$tbl->setTitle($this->lng->txt("crs_header_delete_subscribers"),"icon_usr_b.gif",$this->lng->txt("crs_header_delete_members"));
		$tbl->setHeaderNames(array($this->lng->txt("username"),
								   $this->lng->txt("firstname"),
								   $this->lng->txt("lastname"),
								   $this->lng->txt("crs_time")));
		$tbl->setHeaderVars(array("login",
								  "firstname",
								  "lastname",
								  "sub_time"),
							array("ref_id" => $this->object->getRefId(),
								  "cmd" => "members",
								  "cmdClass" => "ilobjcoursegui",
								  "cmdNode" => $_GET["cmdNode"]));

		$tbl->setColumnWidth(array("25%","25%","25%","25%"));

		$this->__setTableGUIBasicData($tbl,$a_result_set);
		$tbl->render();

		$this->tpl->setVariable("EDIT_MEMBER_TABLE",$tbl->tpl->get());

		return true;
	}



	function __showWaitingListTable($a_result_set,$a_waiting_list_ids = NULL)
	{
		$actions = array("addFromWaitingList"		=> $this->lng->txt("crs_add_subscribers"),
						 "removeFromWaitingList"	=> $this->lng->txt("crs_delete_from_waiting_list"));

		$tbl =& $this->__initTableGUI();
		$tpl =& $tbl->getTemplateObject();

		$tbl->enable('select_all');
		$tbl->setFormName("wait_form");
		$tbl->setSelectAllCheckbox("waiting_list");


		// SET FOOTER BUTTONS
		$tpl->setCurrentBlock("tbl_action_row");

		$tpl->setVariable("COLUMN_COUNTS",5);
		
		$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));

		$tpl->setCurrentBlock("tbl_action_select");
		$tpl->setVariable("SELECT_ACTION",ilUtil::formSelect(1,"action",$actions,false,true));
		$tpl->setVariable("BTN_NAME","gateway");
		$tpl->setVariable("BTN_VALUE",$this->lng->txt("execute"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("tbl_action_row");
		$tpl->setVariable("TPLPATH",$this->tpl->tplPath);
		$tpl->parseCurrentBlock();


		$tbl->setTitle($this->lng->txt("crs_waiting_list"),"icon_usr.gif",$this->lng->txt("crs_waiting_list"));
		$tbl->setHeaderNames(array('',
								   $this->lng->txt("name"),
								   $this->lng->txt("login"),
								   $this->lng->txt("crs_time")));
		$tbl->setHeaderVars(array("",
								  "name",
								  "login",
								  "sub_time"),
							array("ref_id" => $this->object->getRefId(),
								  "cmd" => "members",
								  "update_subscribers" => 1,
								  "cmdClass" => "ilobjcoursegui",
								  "cmdNode" => $_GET["cmdNode"]));
		$tbl->setColumnWidth(array('1%'));

		$this->__setTableGUIBasicData($tbl,$a_result_set,"subscribers");
		$tbl->render();

		$this->tpl->setCurrentBlock('sub_wait_table');
		$this->tpl->setVariable('SUB_WAIT_NAME','wait_form');
		$this->tpl->setVariable('SUB_WAIT_FORMACTION',$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("SUB_WAIT_TABLE_CONTENT",$tbl->tpl->get());
		$this->tpl->parseCurrentBlock();

		return true;
	}



	function __getDateSelect($a_type,$a_varname,$a_selected)
	{
		switch($a_type)
		{
			case "minute":
				for($i=0;$i<=60;$i++)
				{
					$days[$i] = $i < 10 ? "0".$i : $i;
				}
				return ilUtil::formSelect($a_selected,$a_varname,$days,false,true);

			case "hour":
				for($i=0;$i<24;$i++)
				{
					$days[$i] = $i < 10 ? "0".$i : $i;
				}
				return ilUtil::formSelect($a_selected,$a_varname,$days,false,true);

			case "day":
				for($i=1;$i<32;$i++)
				{
					$days[$i] = $i < 10 ? "0".$i : $i;
				}
				return ilUtil::formSelect($a_selected,$a_varname,$days,false,true);
			
			case "month":
				for($i=1;$i<13;$i++)
				{
					$month[$i] = $i < 10 ? "0".$i : $i;
				}
				return ilUtil::formSelect($a_selected,$a_varname,$month,false,true);

			case "year":
				for($i = date("Y",time());$i < date("Y",time()) + 3;++$i)
				{
					$year[$i] = $i;
				}
				return ilUtil::formSelect($a_selected,$a_varname,$year,false,true);
		}
	}

	function __toUnix($a_time_arr)
	{
		return mktime($a_time_arr["hour"],
					  $a_time_arr["minute"],
					  $a_time_arr["second"],
					  $a_time_arr["month"],
					  $a_time_arr["day"],
					  $a_time_arr["year"]);
	}
	function __unsetSessionVariables()
	{
		unset($_SESSION["crs_delete_member_ids"]);
		unset($_SESSION["crs_delete_subscriber_ids"]);
		unset($_SESSION["crs_search_str"]);
		unset($_SESSION["crs_search_for"]);
		unset($_SESSION["crs_group"]);
		unset($_SESSION["crs_role"]);
		unset($_SESSION["crs_archives"]);
	}

	function mailMembersObject()
	{
		global $rbacreview, $ilErr, $ilAccess, $ilObjDataCache, $ilias;
		include_once('./Services/AccessControl/classes/class.ilObjRole.php');

		$this->lng->loadLanguageModule('mail');
		ilUtil::sendInfo($this->lng->txt('mail_select_recipients'));

		$is_admin = (bool) $ilAccess->checkAccess("write", "", $this->object->getRefId());

		if (!$is_admin &&
			$this->object->getShowMembers() == $this->object->SHOW_MEMBERS_DISABLED)
		{
			$ilErr->raiseError($this->lng->txt("msg_no_perm_read"),$ilErr->MESSAGE);
		}

		$this->setSubTabs('members');
		$this->tabs_gui->setTabActive('members');
		$this->tabs_gui->setSubTabActive('mail_members');
		
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.mail_members.html','Services/Contact');


        require_once 'Services/Mail/classes/class.ilMailFormCall.php';
		$this->tpl->setVariable("MAILACTION", ilMailFormCall::_getLinkTarget($this, 'mailMembers', array(), array('type' => 'role', 'sig' => $this->createMailSignature())));
		$this->tpl->setVariable("SELECT_ACTION",'ilias.php?baseClass=ilmailgui&view=my_courses&search_crs='.$this->object->getId());
		$this->tpl->setVariable("MAIL_SELECTED",$this->lng->txt('send_mail_selected'));
		$this->tpl->setVariable("MAIL_MEMBERS",$this->lng->txt('send_mail_members'));
		$this->tpl->setVariable("MAIL_TUTOR",$this->lng->txt('send_mail_tutors'));
		$this->tpl->setVariable("MAIL_ADMIN",$this->lng->txt('send_mail_admins'));
		$this->tpl->setVariable("IMG_ARROW",ilUtil::getImagePath('arrow_downright.gif'));
		$this->tpl->setVariable("OK",$this->lng->txt('next'));

		// Display roles with user friendly mailbox addresses
		$role_folder = $rbacreview->getRoleFolderOfObject($this->object->getRefId());
		$role_ids = $rbacreview->getRolesOfRoleFolder($role_folder['ref_id'], false);
		
		// Sort by relevance
		$sorted_role_ids = array();
		$counter = 3;
		foreach($role_ids as $role_id)
		{
			switch(substr(ilObject::_lookupTitle($role_id),0,8))
			{
				case 'il_crs_a':
					$sorted_role_ids[2] = $role_id;
					break;
					
				case 'il_crs_t':
					$sorted_role_ids[1] = $role_id;
					break;

				case 'il_crs_m':
					$sorted_role_ids[0] = $role_id;
					break;
					
				default:
					$sorted_role_ids[$counter++] = $role_id;
					break;
			}
		}
		
		ksort($sorted_role_ids,SORT_NUMERIC);
		foreach ((array) $sorted_role_ids as $role_id)
		{
			$this->tpl->setCurrentBlock("mailbox_row");
			$role_addr = $rbacreview->getRoleMailboxAddress($role_id);

			// check if role title is unique. if not force use pear mail for roles
			$ids_for_role_title = ilObject::_getIdsForTitle(ilObject::_lookupTitle($role_id), 'role');
			if(count($ids_for_role_title) >= 2)
			{
				$ilias->setSetting('pear_mail_enable',true);
			}
			
			$this->tpl->setVariable("CHECK_MAILBOX",ilUtil::formCheckbox(1,'roles[]',
					htmlspecialchars($role_addr)
			));
			if (ilMail::_usePearMail())
			{
				// if pear mail is enabled, mailbox addresses are already localized in the language of the user
				$this->tpl->setVariable("MAILBOX",$role_addr);
			}
			else
			{
				// if pear mail is not enabled, we need to localize mailbox addresses in the language of the user
				$this->tpl->setVariable("MAILBOX",ilObjRole::_getTranslation($ilObjDataCache->lookupTitle($role_id)). " (" . $role_addr . ")");
			}

			$this->tpl->parseCurrentBlock();
		}
	}
	
	function executeCommand()
	{
		global $rbacsystem,$ilUser,$ilAccess,$ilErr,$ilTabs,$ilNavigationHistory,$ilCtrl;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
	
		$this->prepareOutput();
		
		// add entry to navigation history
		if(!$this->getCreationMode() &&
			$ilAccess->checkAccess('read', '', $_GET['ref_id']))
		{
			$link = $ilCtrl->getLinkTargetByClass("ilrepositorygui", "frameset");				

			$ilNavigationHistory->addItem($_GET['ref_id'],
				$link, 'crs');
		}
	
		if(!$this->getCreationMode())
		{	
			if(IS_PAYMENT_ENABLED)
			{
				include_once 'Services/Payment/classes/class.ilPaymentObject.php';
				if(ilPaymentObject::_requiresPurchaseToAccess($this->object->getRefId(), $type = (isset($_GET['purchasetype']) ? $_GET['purchasetype'] : NULL) ))
				{
					$ilTabs->setTabActive('info_short');

					include_once 'Services/Payment/classes/class.ilShopPurchaseGUI.php';
					$this->ctrl->setReturn($this, '');
					$pp_gui = new ilShopPurchaseGUI($this->object->getRefId());
					$this->ctrl->forwardCommand($pp_gui);
					return true;
				}
			}
		}

		switch($next_class)
		{
			case "ilinfoscreengui":
				$this->infoScreen();	// forwards command
				break;

			case 'ilmdeditorgui':
				include_once 'Services/MetaData/classes/class.ilMDEditorGUI.php';

				$md_gui =& new ilMDEditorGUI($this->object->getId(), 0, $this->object->getType());
				$md_gui->addObserver($this->object,'MDUpdateListener','General');

				$this->ctrl->forwardCommand($md_gui);
				$this->tabs_gui->setTabActive('meta_data');
				break;

			case 'ilcourseregistrationgui':
				$this->ctrl->setReturn($this,'');
				$this->tabs_gui->setTabActive('join');
				include_once('./Modules/Course/classes/class.ilCourseRegistrationGUI.php');
				$registration = new ilCourseRegistrationGUI($this->object);
				$this->ctrl->forwardCommand($registration);
				break;
				
			case 'ilobjectcustomuserfieldsgui':
				include_once './Services/Membership/classes/class.ilObjectCustomUserFieldsGUI.php';
				
				$cdf_gui = new ilObjectCustomUserFieldsGUI($this->object->getId());
				$this->setSubTabs('properties');
				$this->tabs_gui->setTabActive('settings');
				$this->ctrl->forwardCommand($cdf_gui);
				break;

			case "ilcourseobjectivesgui":
				include_once './Modules/Course/classes/class.ilCourseObjectivesGUI.php';

				$this->ctrl->setReturn($this,"");
				$reg_gui =& new ilCourseObjectivesGUI($this->object->getRefId());
				$ret =& $this->ctrl->forwardCommand($reg_gui);
				break;

			case 'ilobjcoursegroupinggui':
				include_once './Modules/Course/classes/class.ilObjCourseGroupingGUI.php';

				$this->ctrl->setReturn($this,'edit');
				$this->setSubTabs('properties');
				$crs_grp_gui =& new ilObjCourseGroupingGUI($this->object,(int) $_GET['obj_id']);
				$this->ctrl->forwardCommand($crs_grp_gui);
				$this->tabs_gui->setTabActive('settings');
				$this->tabs_gui->setSubTabActive('groupings');
				break;

			case "ilcolumngui":
				$this->tabs_gui->setTabActive('none');
				$this->checkPermission("read");
				//$this->prepareOutput();
				//include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
				//$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
				//	ilObjStyleSheet::getContentStylePath(0));
				//$this->renderObject();
				$this->viewObject();
				break;

			case "ilconditionhandlerinterface":
				include_once './Services/AccessControl/classes/class.ilConditionHandlerInterface.php';
				
				// preconditions for single course items
				if($_GET['item_id'])
				{
					$this->ctrl->saveParameter($this,'item_id',$_GET['item_id']);
					$this->tabs_gui->setTabActive('content');
					$this->setSubTabs("item_activation");

					$new_gui =& new ilConditionHandlerInterface($this,(int) $_GET['item_id']);
					$this->ctrl->forwardCommand($new_gui);
				}
				else	// preconditions for whole course
				{
					$this->setSubTabs("properties");
					$this->tabs_gui->setTabActive('settings');
					$new_gui =& new ilConditionHandlerInterface($this);

					$this->ctrl->forwardCommand($new_gui);
				}
				break;

			case "illearningprogressgui":
				include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';

				$new_gui =& new ilLearningProgressGUI(LP_MODE_REPOSITORY,
													  $this->object->getRefId(),
													  $_GET['user_id'] ? $_GET['user_id'] : $ilUser->getId());
				$this->ctrl->forwardCommand($new_gui);
				$this->tabs_gui->setTabActive('learning_progress');
				break;
				
			case 'illicenseoverviewgui':
				include_once("./Services/License/classes/class.ilLicenseOverviewGUI.php");
				$license_gui =& new ilLicenseOverviewGUI($this, LIC_MODE_REPOSITORY);
				$ret =& $this->ctrl->forwardCommand($license_gui);
				$this->tabs_gui->setTabActive('licenses');
				break;

			case 'ilpermissiongui':
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$this->tabs_gui->setTabActive('perm_settings');
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			case 'ilrepositorysearchgui':
				include_once('./Services/Search/classes/class.ilRepositorySearchGUI.php');
				$rep_search =& new ilRepositorySearchGUI();
				$rep_search->setCallback($this,
					'assignMembersObject',
					array(
						ilCourseConstants::CRS_MEMBER => $this->lng->txt('crs_member'),
						ilCourseConstants::CRS_TUTOR	=> $this->lng->txt('crs_tutor'),
						ilCourseConstants::CRS_ADMIN => $this->lng->txt('crs_admin')
						)
					);

				$this->checkLicenses();
						
				// Set tabs
				$this->ctrl->setReturn($this,'members');
				$ret =& $this->ctrl->forwardCommand($rep_search);
				$this->setSubTabs('members');
				$this->tabs_gui->setTabActive('members');
				$this->tabs_gui->setSubTabActive('crs_member_administration');
				break;

			case 'ilcoursecontentinterface':

				$this->initCourseContentInterface();
				$this->cci_obj->cci_setContainer($this);

				$this->ctrl->forwardCommand($this->cci_obj);
				$this->setSubTabs('content');
				$this->tabs_gui->setTabActive('content');
				break;

			case 'ilcoursecontentgui':
				$this->ctrl->setReturn($this,'members');
				include_once './Modules/Course/classes/class.ilCourseContentGUI.php';
				$course_content_obj = new ilCourseContentGUI($this);
				$this->ctrl->forwardCommand($course_content_obj);
				break;

			case 'ilpublicuserprofilegui':
				require_once './Services/User/classes/class.ilPublicUserProfileGUI.php';
				$profile_gui = new ilPublicUserProfileGUI($_GET["user"]);
				$this->setSubTabs('members');
				$this->tabs_gui->setTabActive('members');
				$profile_gui->setBackUrl($ilCtrl->getLinkTarget($this, "membersGallery"));
				$this->tabs_gui->setSubTabActive('crs_members_gallery');
				$html = $this->ctrl->forwardCommand($profile_gui);
				$this->tpl->setVariable("ADM_CONTENT", $html);
				break;

			case 'ilmemberexportgui':
				include_once('./Services/Membership/classes/Export/class.ilMemberExportGUI.php');
				
				$this->setSubTabs('members');
				$this->tabs_gui->setTabActive('members');
				$this->tabs_gui->setSubTabActive('export_members');
				$export = new ilMemberExportGUI($this->object->getRefId());
				$this->ctrl->forwardCommand($export);
				break;
				
			case 'ilmemberagreementgui':
				include_once('Services/Membership/classes/class.ilMemberAgreementGUI.php');
				$this->ctrl->setReturn($this,'');
				$agreement = new ilMemberAgreementGUI($this->object->getRefId());
				$this->ctrl->forwardCommand($agreement);
				break;
				
			case 'ilsessionoverviewgui':
				include_once('./Modules/Session/classes/class.ilSessionOverviewGUI.php');
				
				$this->setSubTabs('members');
				$this->tabs_gui->setTabActive('members');
				$this->tabs_gui->setSubTabActive('events');
				$overview = new ilSessionOverviewGUI($this->object->getRefId());
				$this->ctrl->forwardCommand($overview);				
				break;
				
			case 'ilcourseitemadministrationgui':
				include_once 'Modules/Course/classes/class.ilCourseItemAdministrationGUI.php';
				$this->tabs_gui->clearSubTabs();
				$this->ctrl->setReturn($this,'view');
				$item_adm_gui = new ilCourseItemAdministrationGUI($this->object,(int) $_REQUEST['item_id']);
				$this->ctrl->forwardCommand($item_adm_gui);
				break;
				
			// container page editing
			case "ilpageobjectgui":
				$ret = $this->forwardToPageObject();
				if ($ret != "")
				{
					$this->tpl->setContent($ret);
				}
				break;
				
			case 'ilobjectcopygui':
				include_once './Services/Object/classes/class.ilObjectCopyGUI.php';
				$cp = new ilObjectCopyGUI($this);
				$cp->setType('crs');
				$this->ctrl->forwardCommand($cp);
				break;
				
			case "ilobjstylesheetgui":
				$this->forwardToStyleSheet();
				break;

			case 'ilcourseparticipantsgroupsgui':
				include_once './Modules/Course/classes/class.ilCourseParticipantsGroupsGUI.php';

				$cmg_gui = new ilCourseParticipantsGroupsGUI($this->object->getRefId());
				$this->setSubTabs('members');
				$this->tabs_gui->setTabActive('members');
				$this->ctrl->forwardCommand($cmg_gui);
				break;
				
			case 'ilexportgui':
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
			
			case "ilcertificategui":
				$this->tabs_gui->activateTab("settings");
				$this->setSubTabs("properties");
				
				include_once "./Services/Certificate/classes/class.ilCertificateGUI.php";
				include_once "./Modules/Course/classes/class.ilCourseCertificateAdapter.php";
				$output_gui = new ilCertificateGUI(new ilCourseCertificateAdapter($this->object));
				$this->ctrl->forwardCommand($output_gui);
				break;

			default:
				if(!$this->creation_mode)
				{
					$this->checkPermission('visible');
				}
				/*
				if(!$this->creation_mode and !$ilAccess->checkAccess('visible','',$this->object->getRefId(),'crs'))
				{
					$ilErr->raiseError($this->lng->txt("msg_no_perm_read"),$ilErr->MESSAGE);
				}
				*/
				if( !$this->creation_mode
					&& $cmd != 'infoScreen'
					&& $cmd != 'sendfile'
					&& $cmd != 'unsubscribe'
					&& $cmd != 'performUnsubscribe'
					&& !$ilAccess->checkAccess("read",'',$this->object->getRefId())
					|| $cmd == 'join'
					|| $cmd == 'subscribe')
				{
					include_once './Modules/Course/classes/class.ilCourseParticipants.php';
					if($rbacsystem->checkAccess('join',$this->object->getRefId()) &&
						!ilCourseParticipants::_isParticipant($this->object->getRefId(),$ilUser->getId()))
					{
						include_once('./Modules/Course/classes/class.ilCourseRegistrationGUI.php');
						$this->ctrl->redirectByClass("ilCourseRegistrationGUI");
					}
					else
					{
						$this->infoScreenObject();
						break;
					}
				}
				
				if($cmd == 'listObjectives')
				{
					include_once './Modules/Course/classes/class.ilCourseObjectivesGUI.php';

					$this->ctrl->setReturn($this,"");
					$obj_gui =& new ilCourseObjectivesGUI($this->object->getRefId());
					$ret =& $this->ctrl->forwardCommand($obj_gui);
					break;
				}
				
				if((!$this->creation_mode)&&(!$rbacsystem->checkAccess("write",$this->object->getRefId())))
				{
					$this->ctrl->setReturn($this,'view');
					include_once('Services/Feedback/classes/class.ilFeedbackGUI.php');
					$feedbackGUI = new ilFeedbackGUI();
					$feedbackGUI->handleRequiredFeedback($this->object->getRefId());
				}
				
				// Dirty hack for course timings view
				if($this->forwardToTimingsView())
				{
					break;
				}

				if(!$cmd)
				{
					$cmd = 'view';
				}
				$cmd .= 'Object';
				$this->$cmd();
					
				break;
		}
		
		$this->addHeaderAction();

		return true;
	}
	
	/**
	 * Check agreement and redirect if it is not accepted
	 *
	 * @access private
	 * 
	 */
	private function checkAgreement()
	{
		global $ilUser,$ilAccess;
		
		if($ilAccess->checkAccess('write','',$this->object->getRefId()))
		{
			return true;
		}
		
		// Disable aggrement if is not member of course
		if(!$this->object->getMemberObject()->isAssigned())
		{
			return true;
		}
		
		include_once './Services/Container/classes/class.ilMemberViewSettings.php';
		if(ilMemberViewSettings::getInstance()->isActive())
		{
			return true;
		}		
		
		include_once('Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
		include_once('Services/Membership/classes/class.ilMemberAgreement.php');
		$privacy = ilPrivacySettings::_getInstance();
		
		// Check agreement
		if(($privacy->courseConfirmationRequired() or ilCourseDefinedFieldDefinition::_hasFields($this->object->getId())) 
			and !ilMemberAgreement::_hasAccepted($ilUser->getId(),$this->object->getId()))
		{
			return false;
		}
		// Check required fields
		include_once('Modules/Course/classes/Export/class.ilCourseUserData.php');
		if(!ilCourseUserData::_checkRequired($ilUser->getId(),$this->object->getId()))
		{
			return false;
		}
		return true;
	}
	
	
	/**
	 * Check the remaining licenses of course objects and generate a message if raare
	 *
	 * @access private
	 *
	 */
	private function checkLicenses($a_keep = false)
	{
		global $lng;


		include_once("Services/License/classes/class.ilLicenseAccess.php");
		if (ilLicenseAccess::_isEnabled())
		{
			$lic_set = new ilSetting("license");
			$buffer = $lic_set->get("license_warning");

			include_once("./Services/License/classes/class.ilLicense.php");
			$licensed_items = ilLicense::_getLicensedChildObjects($this->object->getRefId());
			foreach ($licensed_items as $item)
			{
				$license =& new ilLicense($item['obj_id']);
				$remaining = $license->getRemainingLicenses();
				if ($remaining <= $buffer)
				{
					$lng->loadlanguageModule("license");
					ilUtil::sendInfo(sprintf($this->lng->txt("course_licenses_rare"), $remaining), $a_keep);
					break;
				}
			}
		}
	}
	
	// STATIC
	function _forwards()
	{
		return array("ilCourseRegisterGUI",'ilConditionHandlerInterface');
	}


	function cciObjectivesObject()
	{
		$this->initCourseContentInterface();
		$this->cci_obj->cci_setContainer($this);
		$this->cci_obj->cci_objectives();

		return true;;
	}
	function cciObjectivesEditObject()
	{
		$this->tabs_gui->setTabActive('edit_content');

		$this->initCourseContentInterface();
		$this->cci_obj->cci_setContainer($this);
		$this->cci_obj->cci_view();

		return true;
	}
	function cciObjectivesAskResetObject()
	{
		$this->initCourseContentInterface();
		$this->cci_obj->cci_setContainer($this);
		$this->cci_obj->cci_objectives_ask_reset();

		return true;;
	}
	function cciResetObject()
	{
		global $ilUser;

		include_once './Modules/Course/classes/class.ilCourseObjectiveResult.php';

		$tmp_obj_res =& new ilCourseObjectiveResult($ilUser->getId());
		$tmp_obj_res->reset($this->object->getId());

		ilUtil::sendSuccess($this->lng->txt('crs_objectives_reseted'));

		$this->initCourseContentInterface();
		$this->cci_obj->cci_setContainer($this);
		$this->cci_obj->cci_objectives();
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


	function addLocatorItems()
	{
		global $ilLocator;
		switch ($this->ctrl->getCmd())
		{
			default:
				#$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, ""));
				break;
		}
	}

	/**
	* goto target course
	*/
	function _goto($a_target, $a_add = "")
	{
		global $ilAccess, $ilErr, $lng,$ilUser;
		
		include_once './Services/Membership/classes/class.ilMembershipRegistrationCodeUtils.php';
		if(substr($a_add,0,5) == 'rcode')
		{
			if($ilUser->getId() == ANONYMOUS_USER_ID)
			{
				// Redirect to login for anonymous
				ilUtil::redirect(
					"login.php?target=".$_GET["target"]."&cmd=force_login&lang=".
					$ilUser->getCurrentLanguage()
				);
			}
			
			// Redirects to target location after assigning user to course
			ilMembershipRegistrationCodeUtils::handleCode(
				$a_target,
				ilObject::_lookupType(ilObject::_lookupObjId($a_target)),
				substr($a_add,5)
			);
		}
		
		if ($a_add == "mem" && $ilAccess->checkAccess("write", "", $a_target))
		{
			ilObjectGUI::_gotoRepositoryNode($a_target, "members");
		}

		if ($ilAccess->checkAccess("read", "", $a_target))
		{
			ilObjectGUI::_gotoRepositoryNode($a_target);
		}
		else
		{
			// to do: force flat view
			if ($ilAccess->checkAccess("visible", "", $a_target))
			{
				ilObjectGUI::_gotoRepositoryNode($a_target, "infoScreen");
			}
			else
			{
				if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID))
				{
					ilUtil::sendFailure(sprintf($lng->txt("msg_no_perm_read_item"),
						ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))), true);
					ilObjectGUI::_gotoRepositoryRoot();
				}
			}
		}
		$ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
	}


	function toUnix($date,$time = array())
	{
		return mktime($time['h'],$time['m'],0,$date['m'],$date['d'],$date['y']);
	}

	function __prepareDateSelect($a_unix_time)
	{
		return array('y' => date('Y',$a_unix_time),
					 'm' => date('n',$a_unix_time),
					 'd' => date('d',$a_unix_time));
	}

	function __prepareTimeSelect($a_unix_time)
	{
		return array('h' => date('G',$a_unix_time),
					 'm' => date('i',$a_unix_time),
					 's' => date('s',$a_unix_time));
	}

	// Copy wizard

	/**
	* Edit Map Settings
	*/
	function editMapSettingsObject()
	{
		global $ilUser, $ilCtrl, $ilUser, $ilAccess;

		$this->setSubTabs("properties");
		$this->tabs_gui->setTabActive('settings');
		
		if (!ilGoogleMapUtil::isActivated() ||
			!$ilAccess->checkAccess("write", "", $this->object->getRefId()))
		{
			return;
		}

		$latitude = $this->object->getLatitude();
		$longitude = $this->object->getLongitude();
		$zoom = $this->object->getLocationZoom();
		
		// Get Default settings, when nothing is set
		if ($latitude == 0 && $longitude == 0 && $zoom == 0)
		{
			$def = ilGoogleMapUtil::getDefaultSettings();
			$latitude = $def["latitude"];
			$longitude = $def["longitude"];
			$zoom =  $def["zoom"];
		}

		//$this->tpl->setTitleIcon(ilUtil::getImagePath("icon_pd_b.gif"), $this->lng->txt("personal_desktop"));
		//$this->tpl->setVariable("HEADER", $this->lng->txt("personal_desktop"));

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		
		$form->setTitle($this->lng->txt("crs_map_settings"));
			
		// enable map
		$public = new ilCheckboxInputGUI($this->lng->txt("crs_enable_map"),
			"enable_map");
		$public->setValue("1");
		$public->setChecked($this->object->getEnableCourseMap());
		$form->addItem($public);

		// map location
		$loc_prop = new ilLocationInputGUI($this->lng->txt("crs_map_location"),
			"location");
		$loc_prop->setLatitude($latitude);
		$loc_prop->setLongitude($longitude);
		$loc_prop->setZoom($zoom);
		$form->addItem($loc_prop);
		
		$form->addCommandButton("saveMapSettings", $this->lng->txt("save"));
		
		$this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
		//$this->tpl->show();
	}

	function saveMapSettingsObject()
	{
		global $ilCtrl, $ilUser;

		$this->object->setLatitude(ilUtil::stripSlashes($_POST["location"]["latitude"]));
		$this->object->setLongitude(ilUtil::stripSlashes($_POST["location"]["longitude"]));
		$this->object->setLocationZoom(ilUtil::stripSlashes($_POST["location"]["zoom"]));
		$this->object->setEnableCourseMap(ilUtil::stripSlashes($_POST["enable_map"]));
		$this->object->update();
		
		$ilCtrl->redirect($this, "editMapSettings");
	}

	/**
	* Members map
	*/
	function membersMapObject()
	{
		global $tpl;

		$this->tabs_gui->setTabActive("members");
		$this->setSubTabs('members');
		$this->tabs_gui->setSubTabActive("crs_members_map");
		
		include_once("./Services/GoogleMaps/classes/class.ilGoogleMapUtil.php");
		if (!ilGoogleMapUtil::isActivated() || !$this->object->getEnableCourseMap())
		{
			return;
		}
		
		include_once("./Services/GoogleMaps/classes/class.ilGoogleMapGUI.php");
		$map = new ilGoogleMapGUI();
		$map->setMapId("course_map");
		$map->setWidth("700px");
		$map->setHeight("500px");
		$map->setLatitude($this->object->getLatitude());
		$map->setLongitude($this->object->getLongitude());
		$map->setZoom($this->object->getLocationZoom());
		$map->setEnableTypeControl(true);
		$map->setEnableNavigationControl(true);
		$map->setEnableCentralMarker(true);

		include_once './Modules/Course/classes/class.ilCourseParticipants.php';
		$members = ilCourseParticipants::_getInstanceByObjId($this->object->getId())->getParticipants();
		if(count($members))
		{
			foreach($members as $user_id)
			{
				$map->addUserMarker($user_id);
			}
		}

		$tpl->setContent($map->getHTML());
		$tpl->setLeftContent($map->getUserListHTML());
	}

	/**
	* Modify Item ListGUI for presentation in container
	*/
	function modifyItemGUI($a_item_list_gui, $a_item_data, $a_show_path)
	{
		return ilObjCourseGUI::_modifyItemGUI($a_item_list_gui, 'ilcoursecontentgui', $a_item_data, $a_show_path,
			$this->object->getAboStatus(), $this->object->getRefId(), $this->object->getId());
	}
	
	/**
	* We need a static version of this, e.g. in folders of the course
	*/
	static function _modifyItemGUI($a_item_list_gui, $a_cmd_class, $a_item_data, $a_show_path,
		$a_abo_status, $a_course_ref_id, $a_course_obj_id, $a_parent_ref_id = 0)
	{
		global $lng, $ilCtrl, $ilAccess;
		
		// this is set for folders within the course
		if ($a_parent_ref_id == 0)
		{
			$a_parent_ref_id = $a_course_ref_id;
		}

		// Special handling for tests in courses with learning objectives
		if($a_item_data['type'] == 'tst' and
			ilObjCourse::_lookupViewMode($a_course_obj_id) == ilContainer::VIEW_OBJECTIVE)
		{
			$a_item_list_gui->addCommandLinkParameter(array('crs_show_result' => $a_course_ref_id));				
		}
		
		// ACTIVATION
		$activation = '';
		if(isset($a_item_data['timing_type']))
		{
			switch($a_item_data['timing_type'])
			{
				case IL_CRS_TIMINGS_ACTIVATION:
						$activation = ilDatePresentation::formatPeriod(
						new ilDateTime($a_item_data['start'],IL_CAL_UNIX),
						new ilDateTime($a_item_data['end'],IL_CAL_UNIX));
						break;
						
				case IL_CRS_TIMINGS_PRESETTING:
						$activation = ilDatePresentation::formatPeriod(
						new ilDate($a_item_data['start'],IL_CAL_UNIX),
						new ilDate($a_item_data['end'],IL_CAL_UNIX));
						break;
						
				default:
					$activation = '';
					break;
			}
		}

		$a_item_list_gui->enableSubscribe($a_abo_status);
		
		// add activation custom property
		if ($activation != "")
		{
			$a_item_list_gui->addCustomProperty($lng->txt($a_item_data['activation_info']), $activation,
				false, true);
		}

		$is_tutor = ($ilAccess->checkAccess('write','',
			$a_course_ref_id,'crs', $a_course_obj_id));
		
		if($a_show_path and $is_tutor)
		{
			$a_item_list_gui->addCustomProperty($lng->txt('path'),
				
				ilContainer::_buildPath($a_item_data['ref_id'], $a_course_ref_id),
				false,
				true);
		}

		if($is_tutor)
		{
			$ilCtrl->setParameterByClass('ilcourseitemadministrationgui',"ref_id",
				$a_parent_ref_id);
			$ilCtrl->setParameterByClass('ilcourseitemadministrationgui',"item_id",
				$a_item_data['child']);
			$a_item_list_gui->addCustomCommand($ilCtrl->getLinkTargetByClass(
				array(strtolower($a_cmd_class), 'ilCourseItemAdministrationGUI'),
				'edit'),
				'activation');
		}
	}
	
	/**
	* Set content sub tabs
	*/
	function setContentSubTabs()
	{
		global $ilAccess, $lng, $ilCtrl;

		if ($this->object->getType() != 'crs')
		{
			return true;
		}
		if (!$ilAccess->checkAccess('write','',
			$this->object->getRefId(),'crs',$this->object->getId()))
		{
			$is_tutor = false;
			// No further tabs if objective view or archives
			if($this->object->enabledObjectiveView())
			{
				return false;
			}
		}
		else
		{
			$is_tutor = true;
		}

		// These subtabs should also work, if the command is called directly in
		// ilObjCourseGUI, so please use ...ByClass methods.
		// (see ilObjCourseGUI->executeCommand: case "ilcolumngui")
		
		if(!$_SESSION['crs_timings_panel'][$this->object->getId()] or 1)
		{
			if (!$this->isActiveAdministrationPanel())
			{
				$this->tabs_gui->addSubTab("view_content", $lng->txt("view"), $ilCtrl->getLinkTargetByClass("ilobjcoursegui", "view"));
			}
			else
			{
				$this->tabs_gui->addSubTab("view_content", $lng->txt("view"), $ilCtrl->getLinkTargetByClass("ilobjcoursegui", "disableAdministrationPanel"));
			}
		}
		include_once 'Modules/Course/classes/class.ilCourseItems.php';
		if($this->object->getViewMode() == IL_CRS_VIEW_TIMING)
		{
			$this->tabs_gui->addSubTabTarget('timings_timings',
				$this->ctrl->getLinkTargetByClass('ilcoursecontentgui','editUserTimings'));
		}
		
		$this->addStandardContainerSubTabs(false);

		/*
		if($is_tutor)
		{
			$this->tabs_gui->addSubTabTarget('crs_archives',
				$this->ctrl->getLinkTargetByClass(
					array('ilcoursecontentgui', 'ilcoursearchivesgui'),'view'));
		}
		*/
		return true;
	}
	
	/**
	 * load date
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function loadDate($a_field)
	{
		global $ilUser;

		include_once('./Services/Calendar/classes/class.ilDateTime.php');
		
		$dt['year'] = (int) $_POST[$a_field]['date']['y'];
		$dt['mon'] = (int) $_POST[$a_field]['date']['m'];
		$dt['mday'] = (int) $_POST[$a_field]['date']['d'];
		$dt['hours'] = (int) $_POST[$a_field]['time']['h'];
		$dt['minutes'] = (int) $_POST[$a_field]['time']['m'];
		$dt['seconds'] = (int) $_POST[$a_field]['time']['s'];
		
		$date = new ilDateTime($dt,IL_CAL_FKT_GETDATE,$ilUser->getTimeZone());
		return $date;		
	}
	
	/**
	 * ask reset test results
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function askResetObject()
	{
		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_objectives_ask_reset.html",'Modules/Course');
		
		$this->tpl->setVariable("FORMACTION",$this->ctrl->getFormAction($this));
		$this->tpl->setVariable("INFO_STRING",$this->lng->txt('crs_objectives_reset_sure'));
		$this->tpl->setVariable("TXT_CANCEL",$this->lng->txt('cancel'));
		$this->tpl->setVariable("TXT_RESET",$this->lng->txt('reset'));
		
		return true;
	}
	
	function resetObject()
	{
		global $ilUser;

		include_once './Modules/Course/classes/class.ilCourseObjectiveResult.php';
		
		$tmp_obj_res = new ilCourseObjectiveResult($ilUser->getId());
		$tmp_obj_res->reset($this->object->getId());
		
		$ilUser->deletePref('crs_objectives_force_details_'.$this->object->getId());
		
		ilUtil::sendSuccess($this->lng->txt('crs_objectives_reseted'));
		$this->viewObject();
	}
	
	function __checkStartObjects()
	{
		include_once './Modules/Course/classes/class.ilCourseStart.php';

		global $ilAccess,$ilUser;

		if($ilAccess->checkAccess('write','',$this->object->getRefId()))
		{
			return true;
		}
		$this->start_obj = new ilCourseStart($this->object->getRefId(),$this->object->getId());
		if(count($this->start_obj->getStartObjects()) and !$this->start_obj->allFullfilled($ilUser->getId()))
		{
			return false;
		}
		return true;
	}

	function showStartObjects()
	{
		include_once './Modules/Course/classes/class.ilCourseLMHistory.php';
		include_once './Services/Repository/classes/class.ilRepositoryExplorer.php';
		include_once './Services/Link/classes/class.ilLink.php';

		global $rbacsystem,$ilias,$ilUser,$ilAccess,$ilObjDataCache;

		$this->tabs_gui->setSubTabActive('view');

		$this->tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.crs_start_view.html",'Modules/Course');
		$this->tpl->setVariable("INFO_STRING",$this->lng->txt('crs_info_start'));
		$this->tpl->setVariable("TBL_TITLE_START",$this->lng->txt('crs_table_start_objects'));
		$this->tpl->setVariable("HEADER_NR",$this->lng->txt('crs_nr'));
		$this->tpl->setVariable("HEADER_DESC",$this->lng->txt('description'));
		$this->tpl->setVariable("HEADER_EDITED",$this->lng->txt('crs_objective_accomplished'));


		$lm_continue =& new ilCourseLMHistory($this->object->getRefId(),$ilUser->getId());
		$continue_data = $lm_continue->getLMHistory();

		$counter = 0;
		foreach($this->start_obj->getStartObjects() as $start)
		{
			$obj_id = $ilObjDataCache->lookupObjId($start['item_ref_id']);
			$ref_id = $start['item_ref_id'];
			$type = $ilObjDataCache->lookupType($obj_id);

			$conditions_ok = ilConditionHandler::_checkAllConditionsOfTarget($ref_id,$obj_id);

			$obj_link = ilLink::_getLink($ref_id,$type);
			$obj_frame = ilRepositoryExplorer::buildFrameTarget($type,$ref_id,$obj_id);
			$obj_frame = $obj_frame ? $obj_frame : '';

			// Tmp fix for tests
			$obj_frame = $type == 'tst' ? '' : $obj_frame;

			$contentObj = false;

			if($ilAccess->checkAccess('read','',$ref_id))
			{
				$this->tpl->setCurrentBlock("start_read");
				$this->tpl->setVariable("READ_TITLE_START",$ilObjDataCache->lookupTitle($obj_id));
				$this->tpl->setVariable("READ_TARGET_START",$obj_frame);
				$this->tpl->setVariable("READ_LINK_START", $obj_link.'&crs_show_result='.$this->object->getRefId());
				$this->tpl->parseCurrentBlock();
			}
			else
			{
				$this->tpl->setCurrentBlock("start_visible");
				$this->tpl->setVariable("VISIBLE_LINK_START",$ilObjDataCache->lookupTitle($obj_id));
				$this->tpl->parseCurrentBlock();
			}

			// CONTINUE LINK
			if(isset($continue_data[$ref_id]))
			{
				$this->tpl->setCurrentBlock("link");
				$this->tpl->setVariable("LINK_HREF",ilLink::_getLink($ref_id,'',array('obj_id',
																					  $continue_data[$ref_id]['lm_page_id'])));
				#$this->tpl->setVariable("CONTINUE_LINK_TARGET",$target);
				$this->tpl->setVariable("LINK_NAME",$this->lng->txt('continue_work'));
				$this->tpl->parseCurrentBlock();
			}

			// add to desktop link
			if(!$ilUser->isDesktopItem($ref_id,$type) and
			   $this->object->getAboStatus())
			{
				if ($ilAccess->checkAccess('read','',$ref_id))
				{
					$this->tpl->setCurrentBlock("link");
					$this->ctrl->setParameterByClass(get_class($this),'item_ref_id',$ref_id);
					$this->ctrl->setParameterByClass(get_class($this),'item_id',$ref_id);
					$this->ctrl->setParameterByClass(get_class($this),'type',$type);

					$this->tpl->setVariable("LINK_HREF",$this->ctrl->getLinkTarget($this,'addToDesk'));
					$this->tpl->setVariable("LINK_NAME", $this->lng->txt("to_desktop"));
					$this->tpl->parseCurrentBlock();
				}
			}
			elseif($this->object->getAboStatus())
			{
					$this->tpl->setCurrentBlock("link");
					$this->ctrl->setParameterByClass(get_class($this),'item_ref_id',$ref_id);
					$this->ctrl->setParameterByClass(get_class($this),'item_id',$ref_id);
					$this->ctrl->setParameterByClass(get_class($this),'type',$type);

					$this->tpl->setVariable("LINK_HREF",$this->ctrl->getLinkTarget($this,'removeFromDesk'));
					$this->tpl->setVariable("LINK_NAME", $this->lng->txt("unsubscribe"));
					$this->tpl->parseCurrentBlock();
			}


			// Description
			if(strlen($ilObjDataCache->lookupDescription($obj_id)))
			{
				$this->tpl->setCurrentBlock("start_description");
				$this->tpl->setVariable("DESCRIPTION_START",$ilObjDataCache->lookupDescription($obj_id));
				$this->tpl->parseCurrentBlock();
			}


			if($this->start_obj->isFullfilled($ilUser->getId(),$ref_id))
			{
				$accomplished = 'accomplished';
			}
			else
			{
				$accomplished = 'not_accomplished';
			}
			$this->tpl->setCurrentBlock("start_row");
			$this->tpl->setVariable("EDITED_IMG",ilUtil::getImagePath('crs_'.$accomplished.'.gif'));
			$this->tpl->setVariable("EDITED_ALT",$this->lng->txt('crs_objective_'.$accomplished));
			$this->tpl->setVariable("ROW_CLASS",'option_value');
			$this->tpl->setVariable("ROW_CLASS_CENTER",'option_value_center');
			$this->tpl->setVariable("OBJ_NR_START",++$counter.'.');
			$this->tpl->parseCurrentBlock();
		}
		return true;
	}
	
	/**
	 * Handle member view
	 * @return 
	 */
	public function prepareOutput()
	{
		global $rbacsystem;
		if(!$this->getCreationMode())
		{
			include_once './Services/Container/classes/class.ilMemberViewSettings.php';
			$settings = ilMemberViewSettings::getInstance();
			if($settings->isActive() and $settings->getContainer() != $this->object->getRefId())
			{
				$settings->setContainer($this->object->getRefId());
				$rbacsystem->initMemberView();				
			}
		}
		parent::prepareOutput();
	}
	
	/**
	 * Create a course mail signature
	 * @return 
	 */
	protected function createMailSignature()
	{
		$link = chr(13).chr(10).chr(13).chr(10);
		$link .= $this->lng->txt('crs_mail_permanent_link');
		$link .= chr(13).chr(10).chr(13).chr(10);
		include_once './Services/Link/classes/class.ilLink.php';
		$link .= ilLink::_getLink($this->object->getRefId());
		return rawurlencode(base64_encode($link));
	}
	
	protected function initHeaderAction($a_sub_type = null, $a_sub_id = null) 
	{
		global $ilSetting, $ilUser;
		
		$lg = parent::initHeaderAction($a_sub_type, $a_sub_id);
				
		if($lg && $ilSetting->get("crsgrp_ntf") &&
			ilCourseParticipants::_isParticipant($this->ref_id, $ilUser->getId()))
		{						
			if(!$ilUser->getPref("grpcrs_ntf_".$this->ref_id))
			{
				$lg->addHeaderIcon("not_icon",
					ilUtil::getImagePath("notification_off.png"),
					$this->lng->txt("crs_notification_deactivated"));
				
				$this->ctrl->setParameter($this, "crs_ntf", 1);
				$caption = "crs_activate_notification";
			}
			else
			{				
				$lg->addHeaderIcon("not_icon",
					ilUtil::getImagePath("notification_on.png"),
					$this->lng->txt("crs_notification_activated"));
				
				$this->ctrl->setParameter($this, "crs_ntf", 0);
				$caption = "crs_deactivate_notification";
			}
			
			$lg->addCustomCommand($this->ctrl->getLinkTarget($this, "saveNotification"),
				$caption);
			
			
			$this->ctrl->setParameter($this, "crs_ntf", "");
		}		
		
		return $lg;
	}	
	
	function deliverCertificateObject()
	{
		global $ilUser, $ilAccess;
	
		$user_id = null;
		if ($ilAccess->checkAccess('write','',$this->ref_id))
		{		
			$user_id = $_REQUEST["member_id"];
		}
		if(!$user_id)
		{
			$user_id = $ilUser->getId();
		}
		
		include_once "Services/Certificate/classes/class.ilCertificate.php";
		if(!ilCertificate::isActive() ||
			!ilCourseParticipants::getDateTimeOfPassed($this->object->getId(), $user_id))
		{
			ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
			$this->ctrl->redirect($this);
		}
		
		include_once "./Modules/Course/classes/class.ilCourseCertificateAdapter.php";
		$certificate = new ilCertificate(new ilCourseCertificateAdapter($this->object));
		$certificate->outCertificate(array("user_id" => $user_id), true);				
	}
	
} // END class.ilObjCourseGUI
?>
