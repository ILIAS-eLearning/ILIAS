<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


require_once "./Services/Container/classes/class.ilContainerGUI.php";

/**
* Class ilObjCourseGUI
*
* @author Stefan Meyer <smeyer.ilias@gmx.de> 
* $Id$
*
* @ilCtrl_Calls ilObjCourseGUI: ilCourseRegistrationGUI, ilCourseObjectivesGUI
* @ilCtrl_Calls ilObjCourseGUI: ilObjCourseGroupingGUI, ilInfoScreenGUI, ilLearningProgressGUI, ilPermissionGUI
* @ilCtrl_Calls ilObjCourseGUI: ilRepositorySearchGUI, ilConditionHandlerGUI
* @ilCtrl_Calls ilObjCourseGUI: ilCourseContentGUI, ilPublicUserProfileGUI, ilMemberExportGUI
* @ilCtrl_Calls ilObjCourseGUI: ilObjectCustomUserFieldsGUI, ilMemberAgreementGUI, ilSessionOverviewGUI
* @ilCtrl_Calls ilObjCourseGUI: ilColumnGUI, ilContainerPageGUI
* @ilCtrl_Calls ilObjCourseGUI: ilLicenseOverviewGUI, ilObjectCopyGUI, ilObjStyleSheetGUI
* @ilCtrl_Calls ilObjCourseGUI: ilCourseParticipantsGroupsGUI, ilExportGUI, ilCommonActionDispatcherGUI
* @ilCtrl_Calls ilObjCourseGUI: ilDidacticTemplateGUI, ilCertificateGUI, ilObjectServiceSettingsGUI
* @ilCtrl_Calls ilObjCourseGUI: ilContainerStartObjectsGUI, ilContainerStartObjectsPageGUI
* @ilCtrl_Calls ilObjCourseGUI: ilMailMemberSearchGUI, ilBadgeManagementGUI
* @ilCtrl_Calls ilObjCourseGUI: ilLOPageGUI, ilObjectMetaDataGUI
*
* @extends ilContainerGUI
*/
class ilObjCourseGUI extends ilContainerGUI
{
	/**
	 * Constructor
	 * @access public
	 */
	public function __construct()
	{
		global $ilCtrl, $ilHelp;

		// CONTROL OPTIONS
		$this->ctrl = $ilCtrl;
		$this->ctrl->saveParameter($this,array("ref_id","cmdClass"));

		$this->type = "crs";
		parent::__construct('',(int) $_GET['ref_id'],true,false);

		$this->lng->loadLanguageModule('crs');

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
			ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
			$this->cancelMemberObject();
			return false;
		}
		
		foreach($_POST["member"] as $usr_id)
		{
			$rcps[] = ilObjUser::_lookupLogin($usr_id);
		}

		require_once 'Services/Mail/classes/class.ilMailFormCall.php';
		require_once 'Modules/Course/classes/class.ilCourseMailTemplateTutorContext.php';

		ilMailFormCall::setRecipients($rcps);
		ilUtil::redirect(
			ilMailFormCall::getRedirectTarget(
				$this, 
				'members',
				array(),
				array(
					'type'   => 'new',
					'sig' => $this->createMailSignature()
				),
				array(
					ilMailFormCall::CONTEXT_KEY => ilCourseMailTemplateTutorContext::ID,
					'ref_id' => $this->object->getRefId(),
					'ts'     => time()
				)
			)
		);		
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
		global $ilUser, $ilSetting;
		
		// #11895
		include_once './Modules/Course/classes/class.ilCourseParticipants.php';
		$part = ilCourseParticipants::_getInstanceByObjId($a_new_object->getId());
		$part->add($ilUser->getId(), ilCourseConstants::CRS_ADMIN);
		$part->updateNotification($ilUser->getId(), $ilSetting->get('mail_crs_admin_notification', true));

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
		if ($ilCtrl->getNextClass() != "ilcolumngui")
		{
			include_once 'Services/Tracking/classes/class.ilLearningProgress.php';
			ilLearningProgress::_tracProgress($ilUser->getId(),$this->object->getId(),
				$this->object->getRefId(),'crs');
		}
		
		if(!$this->checkAgreement())
		{
			include_once('Services/Membership/classes/class.ilMemberAgreementGUI.php');
			$this->tabs_gui->clearTargets();
			$this->ctrl->setReturn($this,'view_content');
			$agreement = new ilMemberAgreementGUI($this->object->getRefId());
			$this->ctrl->setCmdClass(get_class($agreement));
			$this->ctrl->forwardCommand($agreement);
			return true;
		}

		if(!$this->__checkStartObjects())
		{
			include_once "Services/Container/classes/class.ilContainerStartObjectsContentGUI.php";
			$stgui = new ilContainerStartObjectsContentGUI($this, $this->object);
			$stgui->enableDesktop($this->object->getAboStatus(), $this);
			return $stgui->getHTML();
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
			include_once './Modules/Course/classes/class.ilCourseMailTemplateMemberContext.php';
            require_once 'Services/Mail/classes/class.ilMailFormCall.php';
			
			$emails = explode(",",$this->object->getContactEmail());
			foreach ($emails as $email) {
				$email = trim($email);
				$etpl = new ilTemplate("tpl.crs_contact_email.html", true, true , 'Modules/Course');
				$etpl->setVariable(
					"EMAIL_LINK",
					ilMailFormCall::getLinkTarget(
						$info, 'showSummary', array(),
						array(
							'type'   => 'new',
							'rcp_to' => $email,
							'sig' => $this->createMailSignature()
						),
						array(
							ilMailFormCall::CONTEXT_KEY => ilCourseMailTemplateMemberContext::ID,
							'ref_id' => $this->object->getRefId(),
							'ts'     => time()
						)
					)
				);              
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


		// support contacts
		$parts = ilParticipants::getInstanceByObjId($this->object->getId());
		$conts = $parts->getContacts();
		if (count($conts) > 0)
		{
			$info->addSection($this->lng->txt("crs_mem_contacts"));
			foreach ($conts as $c)
			{
				include_once("./Services/User/classes/class.ilPublicUserProfileGUI.php");
				$pgui = new ilPublicUserProfileGUI($c);
				$pgui->setBackUrl($this->ctrl->getLinkTargetByClass("ilinfoscreengui"));
				$pgui->setEmbedded(true);
				$info->addProperty("", $pgui->getHTML());
			}
		}



		//	
		// access
		//
		
		// #10360
		$this->lng->loadLanguageModule("rep");
		$info->addSection($this->lng->txt("rep_activation_availability"));
		$info->showLDAPRoleGroupMappingInfo();
								
		// activation
		if($this->object->getActivationUnlimitedStatus())
		{
			$info->addProperty($this->lng->txt("rep_activation_access"),
				$this->lng->txt('crs_visibility_limitless'));
		}
		else
		{
			$info->addProperty($this->lng->txt('rep_activation_access'),
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
				if($this->object->getSubscriptionMinMembers())
				{				
					$info->addProperty(
						$this->lng->txt("mem_min_users"),
						$this->object->getSubscriptionMinMembers()
					);
				}		
				if($this->object->getSubscriptionMaxMembers())
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
		}
		
		if($this->object->getCancellationEnd())
		{		
			$info->addProperty($this->lng->txt('crs_cancellation_end'),
				ilDatePresentation::formatDate( $this->object->getCancellationEnd()));
		}
				
		if($this->object->getCourseStart())
		{	
			$info->addProperty($this->lng->txt('crs_period'),
				ilDatePresentation::formatPeriod(
					$this->object->getCourseStart(),
					$this->object->getCourseEnd()
			));
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
		include_once "Services/Membership/classes/class.ilMembershipNotifications.php";
		$noti = new ilMembershipNotifications($this->ref_id);
		if($noti->canCurrentUserEdit())
		{
			if((bool)$_REQUEST["crs_ntf"])
			{
				$noti->activateUser();
			}
			else
			{
				$noti->deactivateUser();
			}
		}
		ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
		$this->ctrl->redirect($this, "");
	}
	
	/**
	 * Edit info page informations
	 *
	 * @access public
	 * 
	 */
	public function editInfoObject(ilPropertyFormGUI $a_form = null)
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
	 	
		if(!$a_form)
		{
			$a_form = $this->initInfoEditor();
		}
		$this->tpl->addBlockFile('ADM_CONTENT','adm_content','tpl.edit_info.html','Modules/Course');
		$this->tpl->setVariable('INFO_TABLE',$a_form->getHTML());
		
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
		$this->record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_EDITOR,'crs',$this->object->getId());
		$this->record_gui->setPropertyForm($form);
		$this->record_gui->parse();

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
		
		
		// validate
		
		$error = false;		
		$ilErr->setMessage('');
		
		$file_obj->validate();
		$this->object->validateInfoSettings();
		if(strlen($ilErr->getMessage()))
		{
			$error = $ilErr->getMessage();
		}
			
		// needed for proper advanced MD validation	 		
		$form = $this->initInfoEditor();
		$form->checkInput();			
		if(!$this->record_gui->importEditFormPostValues())
		{	
			$error = true;
		}	
		
		if($error)
		{								
			if($error !== true)
			{
				ilUtil::sendFailure($ilErr->getMessage());
			}
			$this->editInfoObject($form);
			return false;
		}
		
		$this->object->update();
		$file_obj->create();
		$this->record_gui->writeEditForm();
		
		
		// Update ecs content
		include_once 'Modules/Course/classes/class.ilECSCourseSettings.php';
		$ecs = new ilECSCourseSettings($this->object);
		$ecs->handleContentUpdate();
	
		ilUtil::sendSuccess($this->lng->txt("crs_settings_saved"));
		$this->editInfoObject();
		return true;
	}

	function updateObject()
	{
		$form = $this->initEditForm();
		$form->checkInput();
		
		$this->object->setTitle(ilUtil::stripSlashes($_POST['title']));
		$this->object->setDescription(ilUtil::stripSlashes($_POST['desc']));		
					
		/*
		$archive_start = $this->loadDate('archive_start');
		$archive_end = $this->loadDate('archive_end');				 
		*/
		$period = $form->getItemByPostVar("access_period");										
		$sub_period = $form->getItemByPostVar("subscription_period");	
		
		// $act_type->setChecked($this->object->getActivationType() == IL_CRS_ACTIVATION_LIMITED);
		
		if($period->getStart() || $period->getEnd())
		{
			// if start or end is missing validation will fail, setting values for reload
			$this->object->setActivationType(IL_CRS_ACTIVATION_LIMITED);
			$this->object->setActivationStart($period->getStart() ? $period->getStart()->get(IL_CAL_UNIX) : null);
			$this->object->setActivationEnd($period->getEnd() ? $period->getEnd()->get(IL_CAL_UNIX) : null);			
			$this->object->setActivationVisibility((int)$_POST['activation_visibility']);		
		}
		else
		{
			$this->object->setActivationType(IL_CRS_ACTIVATION_UNLIMITED);
			$this->object->setActivationStart(null);
			$this->object->setActivationEnd(null);			
			// $this->object->setActivationVisibility(false);		
		}		
		
		$this->object->setOfflineStatus(!(bool)$_POST['activation_online']);		
				
		$this->object->setSubscriptionPassword(ilUtil::stripSlashes($_POST['subscription_password']));		
		$this->object->setSubscriptionStart(null);
		$this->object->setSubscriptionEnd(null);		
		
		$sub_type = (int)$_POST['subscription_type'];
		if($sub_type != IL_CRS_SUBSCRIPTION_DEACTIVATED)
		{		
			$this->object->setSubscriptionType($sub_type);
						
			if($sub_period->getStart() &&
				$sub_period->getEnd())
			{
				$this->object->setSubscriptionLimitationType(IL_CRS_SUBSCRIPTION_LIMITED);
				$this->object->setSubscriptionStart($sub_period->getStart()->get(IL_CAL_UNIX));
				$this->object->setSubscriptionEnd($sub_period->getEnd()->get(IL_CAL_UNIX));		
			}
			else
			{
				$this->object->setSubscriptionLimitationType(IL_CRS_SUBSCRIPTION_UNLIMITED);
			}
		}
		else
		{
			$this->object->setSubscriptionType(IL_CRS_SUBSCRIPTION_DIRECT);  // see ilObjCourse::__createDefaultSettings()
			$this->object->setSubscriptionLimitationType(IL_CRS_SUBSCRIPTION_DEACTIVATED);
		}		
		
		$this->object->enableRegistrationAccessCode((int) $_POST['reg_code_enabled']);
		$this->object->setRegistrationAccessCode(ilUtil::stripSlashes($_POST['reg_code']));
		
		$this->object->setCancellationEnd($form->getItemByPostVar("cancel_end")->getDate());		
				
		$this->object->enableSubscriptionMembershipLimitation((int) $_POST['subscription_membership_limitation']);		
		$this->object->setSubscriptionMaxMembers((int) $_POST['subscription_max']);		
		$this->object->setSubscriptionMinMembers((int)$_POST['subscription_min']);
		
		$old_autofill = $this->object->hasWaitingListAutoFill();
		
		switch((int) $_POST['waiting_list'])
		{
			case 2:
				$this->object->enableWaitingList(true);
				$this->object->setWaitingListAutoFill(true);
				break;
			
			case 1:
				$this->object->enableWaitingList(true);
				$this->object->setWaitingListAutoFill(false);
				break;
			
			default:
				$this->object->enableWaitingList(false);
				$this->object->setWaitingListAutoFill(false);
				break;
		}
				
		#$this->object->setSubscriptionNotify((int) $_POST['subscription_notification']);
					
		$crs_period = $form->getItemByPostVar("period");				
		$this->object->setCourseStart($crs_period->getStart());
		$this->object->setCourseEnd($crs_period->getEnd());		
		
		$this->object->setViewMode((int) $_POST['view_mode']);

		if($this->object->getViewMode() == IL_CRS_VIEW_TIMING)
		{
			$this->object->setOrderType(ilContainer::SORT_ACTIVATION);
		}
		else
		{
			$this->object->setOrderType($form->getInput('sorting'));
		}
		$this->saveSortingSettings($form);
		
		/*
		$this->object->setArchiveStart($archive_start->get(IL_CAL_UNIX));
		$this->object->setArchiveEnd($archive_end->get(IL_CAL_UNIX));		
		$this->object->setArchiveType($_POST['archive_type']);
		 */
		$this->object->setAboStatus((int) $_POST['abo']);
		$this->object->setShowMembers((int) $_POST['show_members']);
		$this->object->setMailToMembersType((int) $_POST['mail_type']);
		
		$this->object->enableSessionLimit((int) $_POST['sl']);
		$this->object->setNumberOfPreviousSessions(is_numeric($_POST['sp']) ? (int) $_POST['sp'] : -1 );
		$this->object->setNumberOfnextSessions(is_numeric($_POST['sn']) ? (int) $_POST['sn'] : -1 );

		$this->object->setAutoNotification($_POST['auto_notification'] == 1 ? true : false);
		
		
		$show_lp_sync_confirmation = false;
		
		// could be hidden in form
		if(isset($_POST['status_dt']))
		{
			if($this->object->getStatusDetermination() != ilObjCourse::STATUS_DETERMINATION_LP &&
				(int)$_POST['status_dt'] == ilObjCourse::STATUS_DETERMINATION_LP)
			{
				$show_lp_sync_confirmation = true;
			}
			else
			{
				$this->object->setStatusDetermination((int)$_POST['status_dt']);		
			}
		}	

		if($this->object->validate())
		{
			$this->object->update();
			
			// if autofill has been activated trigger process
			if(!$old_autofill &&
				$this->object->hasWaitingListAutoFill())
			{
				$this->object->handleAutoFill();
			}
			
			// BEGIN ChangeEvent: Record write event
			require_once('Services/Tracking/classes/class.ilChangeEvent.php');
			global $ilUser;
			ilChangeEvent::_recordWriteEvent($this->object->getId(), $ilUser->getId(), 'update');
			ilChangeEvent::_catchupWriteEvents($this->object->getId(), $ilUser->getId());			
			// END ChangeEvent: Record write event
			
			
			include_once './Services/Object/classes/class.ilObjectServiceSettingsGUI.php';
			ilObjectServiceSettingsGUI::updateServiceSettingsForm(
				$this->object->getId(),
				$form,
				array(
					ilObjectServiceSettingsGUI::CALENDAR_VISIBILITY,
					ilObjectServiceSettingsGUI::NEWS_VISIBILITY,
					ilObjectServiceSettingsGUI::AUTO_RATING_NEW_OBJECTS,				
					ilObjectServiceSettingsGUI::TAG_CLOUD,
					ilObjectServiceSettingsGUI::CUSTOM_METADATA,
					ilObjectServiceSettingsGUI::BADGES
				)
			);
			
			// Update ecs export settings
			include_once 'Modules/Course/classes/class.ilECSCourseSettings.php';	
			$ecs = new ilECSCourseSettings($this->object);			
			if(!$ecs->handleSettingsUpdate())
			{
				$this->editObject();
				return false;
			}			
			
			if($show_lp_sync_confirmation)
			{
				return $this->confirmLPSync();
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
	
	protected function confirmLPSync()
	{
		global $tpl;
		
		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$cgui = new ilConfirmationGUI();
		$cgui->setFormAction($this->ctrl->getFormAction($this, "setLPSync"));
		$cgui->setHeaderText($this->lng->txt("crs_status_determination_sync"));
		$cgui->setCancel($this->lng->txt("cancel"), "edit");
		$cgui->setConfirm($this->lng->txt("confirm"), "setLPSync");
		
		$tpl->setContent($cgui->getHTML());
	}
	
	protected function setLPSyncObject()
	{
		$this->object->setStatusDetermination(ilObjCourse::STATUS_DETERMINATION_LP);
		$this->object->update();

		$this->object->syncMembersStatusWithLP();
		
		ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
		$this->ctrl->redirect($this, "edit");
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
	
		$form->addCommandButton('update',$this->lng->txt('save'));
		$form->addCommandButton('cancel',$this->lng->txt('cancel'));
		
		$form->setFormAction($this->ctrl->getFormAction($this,'update'));
		
		// title
		$title = new ilTextInputGUI($this->lng->txt('title'),'title');
		$title->setSubmitFormOnEnter(true);
		$title->setValue($this->object->getTitle());
		$title->setSize(min(40, ilObject::TITLE_LENGTH));
		$title->setMaxLength(ilObject::TITLE_LENGTH);
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
		
		// period		
		include_once "Services/Form/classes/class.ilDateDurationInputGUI.php";
		$cdur = new ilDateDurationInputGUI($this->lng->txt('crs_period'), 'period');			
		$cdur->setInfo($this->lng->txt('crs_period_info'));			
		if($this->object->getCourseStart())
		{
			$cdur->setStart($this->object->getCourseStart());
		}		
		if($this->object->getCourseStart())
		{
			$cdur->setEnd($this->object->getCourseEnd());
		}	
		$form->addItem($cdur);			
		
			
		// activation/availability
		
		$this->lng->loadLanguageModule('rep');
		
		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('rep_activation_availability'));
		$form->addItem($section);
		
		$online = new ilCheckboxInputGUI($this->lng->txt('rep_activation_online'),'activation_online');
		$online->setChecked(!$this->object->getOfflineStatus());
		$online->setInfo($this->lng->txt('crs_activation_online_info'));
		$form->addItem($online);				
		
		// $act_type = new ilCheckboxInputGUI($this->lng->txt('crs_visibility_until'), 'activation_type');
		// $act_type->setInfo($this->lng->txt('crs_visibility_until_info'));
		// $act_type->setChecked($this->object->getActivationType() == IL_CRS_ACTIVATION_LIMITED);
		// $form->addItem($act_type);
		
		include_once "Services/Form/classes/class.ilDateDurationInputGUI.php";
		$dur = new ilDateDurationInputGUI($this->lng->txt('rep_time_period'), "access_period");
		$dur->setShowTime(true);																	
		$dur->setStart(new ilDateTime($this->object->getActivationStart(),IL_CAL_UNIX));				
		$dur->setEnd(new ilDateTime($this->object->getActivationEnd(),IL_CAL_UNIX));			
		$form->addItem($dur);

			$visible = new ilCheckboxInputGUI($this->lng->txt('rep_activation_limited_visibility'), 'activation_visibility');
			$visible->setInfo($this->lng->txt('crs_activation_limited_visibility_info'));
			$visible->setChecked($this->object->getActivationVisibility());
			$dur->addSubItem($visible);
				
		
		$section = new ilFormSectionHeaderGUI();
		$section->setTitle($this->lng->txt('crs_reg'));
		$form->addItem($section);
		
		$reg_proc = new ilRadioGroupInputGUI($this->lng->txt('crs_registration_type'),'subscription_type');
		$reg_proc->setValue(
			($this->object->getSubscriptionLimitationType() != IL_CRS_SUBSCRIPTION_DEACTIVATED)
				? $this->object->getSubscriptionType()
				: IL_CRS_SUBSCRIPTION_DEACTIVATED);
		// $reg_proc->setInfo($this->lng->txt('crs_reg_type_info'));

			$opt = new ilRadioOption($this->lng->txt('crs_subscription_options_direct'),IL_CRS_SUBSCRIPTION_DIRECT);
			$reg_proc->addOption($opt);
		
			$opt = new ilRadioOption($this->lng->txt('crs_subscription_options_password'),IL_CRS_SUBSCRIPTION_PASSWORD);
			
				$pass = new ilTextInputGUI($this->lng->txt("password"),'subscription_password');
				$pass->setInfo($this->lng->txt('crs_reg_password_info'));
				$pass->setSubmitFormOnEnter(true);
				$pass->setSize(12);
				$pass->setMaxLength(12);
				$pass->setValue($this->object->getSubscriptionPassword());
			
			$opt->addSubItem($pass);
			$reg_proc->addOption($opt);
		
			$opt = new ilRadioOption($this->lng->txt('crs_subscription_options_confirmation'),IL_CRS_SUBSCRIPTION_CONFIRMATION);
			$opt->setInfo($this->lng->txt('crs_registration_confirmation_info'));
			$reg_proc->addOption($opt);			
			
			$opt = new ilRadioOption($this->lng->txt('crs_reg_no_selfreg'),IL_CRS_SUBSCRIPTION_DEACTIVATED);
			$opt->setInfo($this->lng->txt('crs_registration_deactivated'));
			$reg_proc->addOption($opt);			

		$form->addItem($reg_proc);
		
		
		// Registration codes
		$reg_code = new ilCheckboxInputGUI($this->lng->txt('crs_reg_code'),'reg_code_enabled');
		$reg_code->setChecked($this->object->isRegistrationAccessCodeEnabled());
		$reg_code->setValue(1);
		$reg_code->setInfo($this->lng->txt('crs_reg_code_enabled_info'));
		
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
		
		// time limit		
		include_once "Services/Form/classes/class.ilDateDurationInputGUI.php";
		$sdur = new ilDateDurationInputGUI($this->lng->txt('crs_registration_limited'), "subscription_period");
		$sdur->setShowTime(true);		
		if($this->object->getSubscriptionStart())
		{
			$sdur->setStart(new ilDateTime($this->object->getSubscriptionStart(),IL_CAL_UNIX));			
		}
		if($this->object->getSubscriptionEnd())
		{
			$sdur->setEnd(new ilDateTime($this->object->getSubscriptionEnd(),IL_CAL_UNIX));			
		}
		$form->addItem($sdur);
		
		// cancellation limit		
		$cancel = new ilDateTimeInputGUI($this->lng->txt('crs_cancellation_end'), 'cancel_end');
		$cancel->setInfo($this->lng->txt('crs_cancellation_end_info'));
		$cancel_end = $this->object->getCancellationEnd();	
		if($cancel_end)
		{
			$cancel->setDate($cancel_end);
		}
		$form->addItem($cancel);
		
		// Max members
		$lim = new ilCheckboxInputGUI($this->lng->txt('crs_subscription_max_members_short'),'subscription_membership_limitation');
		$lim->setInfo($this->lng->txt('crs_subscription_max_members_short_info'));
		$lim->setValue(1);
		$lim->setChecked($this->object->isSubscriptionMembershipLimited());
		
			$min = new ilTextInputGUI('','subscription_min');
			$min->setSubmitFormOnEnter(true);
			$min->setSize(4);
			$min->setMaxLength(4);
			$min->setValue($this->object->getSubscriptionMinMembers() ? $this->object->getSubscriptionMinMembers() : '');
			$min->setTitle($this->lng->txt('crs_subscription_min_members'));
			$min->setInfo($this->lng->txt('crs_subscription_min_members_info'));			
			$lim->addSubItem($min);
		
			$max = new ilTextInputGUI('','subscription_max');
			$max->setSubmitFormOnEnter(true);
			$max->setSize(4);
			$max->setMaxLength(4);
			$max->setValue($this->object->getSubscriptionMaxMembers() ? $this->object->getSubscriptionMaxMembers() : '');
			$max->setTitle($this->lng->txt('crs_subscription_max_members'));
			$max->setInfo($this->lng->txt('crs_reg_max_info'));
		
		$lim->addSubItem($max);
		
			/*
			$wait = new ilCheckboxInputGUI($this->lng->txt('crs_waiting_list'),'waiting_list');
			$wait->setChecked($this->object->enabledWaitingList());
			$wait->setInfo($this->lng->txt('crs_wait_info'));
			$lim->addSubItem($wait);
			
			$wait = new ilCheckboxInputGUI($this->lng->txt('crs_waiting_list'),'waiting_list');
			$wait->setChecked($this->object->enabledWaitingList());
			$wait->setInfo($this->lng->txt('crs_wait_info'));
			$lim->addSubItem($wait);
			
			$auto = new ilCheckboxInputGUI($this->lng->txt('crs_waiting_list_autofill'), 'auto_wait');
			$auto->setChecked($this->object->hasWaitingListAutoFill());
			$auto->setInfo($this->lng->txt('crs_waiting_list_autofill_info'));
			$wait->addSubItem($auto);
			*/
		
			$wait = new ilRadioGroupInputGUI($this->lng->txt('crs_waiting_list'), 'waiting_list');
			
			$option = new ilRadioOption($this->lng->txt('none'), 0);
			$wait->addOption($option);
			
			$option = new ilRadioOption($this->lng->txt('crs_waiting_list_no_autofill'), 1);
			$option->setInfo($this->lng->txt('crs_wait_info'));
			$wait->addOption($option);
			
			$option = new ilRadioOption($this->lng->txt('crs_waiting_list_autofill'), 2);
			$option->setInfo($this->lng->txt('crs_waiting_list_autofill_info'));
			$wait->addOption($option);
			
			if($this->object->hasWaitingListAutoFill())
			{
				$wait->setValue(2);
			}
			else if($this->object->enabledWaitingList())
			{
				$wait->setValue(1);
			}
			
		$lim->addSubItem($wait);
		
		$form->addItem($lim);
	

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

		$form->addItem($view_type);
		
		$this->initSortingForm(
			$form, 
			array(
				ilContainer::SORT_TITLE,
				ilContainer::SORT_MANUAL,
				ilContainer::SORT_CREATION,
				ilContainer::SORT_ACTIVATION
			)
		);
		


		// lp vs. course status
		include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
		if(ilObjUserTracking::_enabledLearningProgress())
		{
			include_once './Services/Object/classes/class.ilObjectLP.php';
			$olp = ilObjectLP::getInstance($this->object->getId());
			if($olp->getCurrentMode())
			{
				$lp_status = new ilFormSectionHeaderGUI();
				$lp_status->setTitle($this->lng->txt('crs_course_status_of_users'));
				$form->addItem($lp_status);

				$lp_status_options = new ilRadioGroupInputGUI($this->lng->txt('crs_status_determination'), "status_dt");
//				$lp_status_options->setRequired(true);
				$lp_status_options->setValue($this->object->getStatusDetermination());

				$lp_option = new ilRadioOption($this->lng->txt('crs_status_determination_lp'),
					ilObjCourse::STATUS_DETERMINATION_LP, $this->lng->txt('crs_status_determination_lp_info'));
				$lp_status_options->addOption($lp_option);
				$lp_status_options->addOption(new ilRadioOption($this->lng->txt('crs_status_determination_manual'),
					ilObjCourse::STATUS_DETERMINATION_MANUAL));

				$form->addItem($lp_status_options);
			}
		}

		// additional features
		$feat = new ilFormSectionHeaderGUI();
		$feat->setTitle($this->lng->txt('obj_features'));
		$form->addItem($feat);

		include_once './Services/Object/classes/class.ilObjectServiceSettingsGUI.php';
		ilObjectServiceSettingsGUI::initServiceSettingsForm(
				$this->object->getId(),
				$form,
				array(
					ilObjectServiceSettingsGUI::CALENDAR_VISIBILITY,
					ilObjectServiceSettingsGUI::NEWS_VISIBILITY,
					ilObjectServiceSettingsGUI::AUTO_RATING_NEW_OBJECTS,
					ilObjectServiceSettingsGUI::TAG_CLOUD,
					ilObjectServiceSettingsGUI::CUSTOM_METADATA,
					ilObjectServiceSettingsGUI::BADGES
				)
			);

		$mem = new ilCheckboxInputGUI($this->lng->txt('crs_show_members'),'show_members');
		$mem->setChecked($this->object->getShowMembers());
		$mem->setInfo($this->lng->txt('crs_show_members_info'));
		$form->addItem($mem);

		// Show members type
		$mail_type = new ilRadioGroupInputGUI($this->lng->txt('crs_mail_type'), 'mail_type');
		$mail_type->setValue($this->object->getMailToMembersType());

		$mail_tutors = new ilRadioOption($this->lng->txt('crs_mail_tutors_only'), ilCourseConstants::MAIL_ALLOWED_TUTORS,
			$this->lng->txt('crs_mail_tutors_only_info'));
		$mail_type->addOption($mail_tutors);

		$mail_all = new ilRadioOption($this->lng->txt('crs_mail_all'),  ilCourseConstants::MAIL_ALLOWED_ALL,
			$this->lng->txt('crs_mail_all_info'));
		$mail_type->addOption($mail_all);
		$form->addItem($mail_type);

		// Notification Settings
		/*$notification = new ilFormSectionHeaderGUI();
		$notification->setTitle($this->lng->txt('crs_notification'));
		$form->addItem($notification);*/
		
		// Self notification
		$not = new ilCheckboxInputGUI($this->lng->txt('crs_auto_notification'), 'auto_notification');
		$not->setValue(1);
		$not->setInfo($this->lng->txt('crs_auto_notification_info'));
		$not->setChecked( $this->object->getAutoNotification() );
		$form->addItem($not);
		

		// Further information
		//$further = new ilFormSectionHeaderGUI();
		//$further->setTitle($this->lng->txt('crs_further_settings'));
		//$form->addItem($further);
		
		$desk = new ilCheckboxInputGUI($this->lng->txt('crs_add_remove_from_desktop'),'abo');
		$desk->setChecked($this->object->getAboStatus());
		$desk->setInfo($this->lng->txt('crs_add_remove_from_desktop_info'));
		$form->addItem($desk);
		

		// Edit ecs export settings
		include_once 'Modules/Course/classes/class.ilECSCourseSettings.php';
		$ecs = new ilECSCourseSettings($this->object);		
		$ecs->addSettingsToForm($form, 'crs');

		return $form;
	}

	protected function  getEditFormValues()
	{
		// values are done in initEditForm()
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
		global $ilSetting;

		$this->checkPermission('write');
		
		$form = $this->initCourseIconsForm();
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
		global $rbacsystem,$ilUser,$ilAccess,$tree;
		
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
												 $this->ctrl->getLinkTargetByClass('ilConditionHandlerGUI','listConditions'),
												 "", "ilConditionHandlerGUI");

				$this->tabs_gui->addSubTabTarget("crs_start_objects",
												 $this->ctrl->getLinkTargetByClass('ilContainerStartObjectsGUI','listStructure'),
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
				include_once("./Services/Maps/classes/class.ilMapUtil.php");
				if (ilMapUtil::isActivated())
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
						$this->ctrl->getLinkTargetByClass("ilcertificategui", "certificateeditor"),
						"", "ilcertificategui");					
				}
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

					$this->tabs_gui->addSubTabTarget(
						'crs_members_gallery',
						$this->ctrl->getLinkTargetByClass('ilUsersGalleryGUI', 'view'),
						'',
						'ilUsersGalleryGUI'
					);
				}
				elseif(
					$this->object->getShowMembers() == $this->object->SHOW_MEMBERS_ENABLED
				)
				{
					$this->tabs_gui->addSubTabTarget(
						'crs_members_gallery',
						$this->ctrl->getLinkTargetByClass('ilUsersGalleryGUI', 'view'),
						'',
						'ilUsersGalleryGUI'
					);
				}
				
				// members map
				include_once("./Services/Maps/classes/class.ilMapUtil.php");
				if (ilMapUtil::isActivated() && $this->object->getEnableCourseMap())
				{
					$this->tabs_gui->addSubTabTarget("crs_members_map",
						$this->ctrl->getLinkTarget($this,'membersMap'),
						"membersMap", get_class($this));
				}
				
				$childs = (array) $tree->getChildsByType($this->object->getRefId(),'sess');
				if(count($childs) && $ilAccess->checkAccess('write','',$this->object->getRefId()))
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
	 * show possible sub objects selection list
	 */
	function showPossibleSubObjects()
	{
		if ($this->object->getViewMode() == ilContainer::VIEW_OBJECTIVE
			&& !$this->isActiveAdministrationPanel())
		{
			return false;
		}
		parent::showPossibleSubObjects();
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
		global $rbacadmin, $ilUser, $ilSetting;
		
		$a_new_object->getMemberObject()->add($ilUser->getId(),IL_CRS_ADMIN);
		$a_new_object->getMemberObject()->updateNotification($ilUser->getId(),$ilSetting->get('mail_crs_admin_notification', true));
		// cognos-blu-patch: begin
		$a_new_object->getMemberObject()->updateContact($ilUser->getId(),1);
		// cognos-blu-patch: end
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
	
	public function readMemberData($ids,$role = 'admin',$selected_columns = null)
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

		$do_prtf = (is_array($selected_columns) && 
			in_array('prtf', $selected_columns) &&
			is_array($ids));
		if($do_prtf)
		{
			include_once "Modules/Portfolio/classes/class.ilObjPortfolio.php";
			$all_prtf = ilObjPortfolio::getAvailablePortfolioLinksForUserIds($ids,
				$this->ctrl->getLinkTarget($this, "members"));
		}

		foreach((array) $ids as $usr_id)
		{
			$name = ilObjUser::_lookupName($usr_id);
			$tmp_data['firstname'] = $name['firstname'];
			$tmp_data['lastname'] = $name['lastname'];
			$tmp_data['login'] = ilObjUser::_lookupLogin($usr_id);
			$tmp_data['passed'] = $this->object->getMembersObject()->hasPassed($usr_id) ? 1 : 0;
			if($this->object->getStatusDetermination() == ilObjCourse::STATUS_DETERMINATION_LP)
			{
				$tmp_data['passed_info'] = $this->object->getMembersObject()->getPassedInfo($usr_id);
			}
			$tmp_data['notification'] = $this->object->getMembersObject()->isNotificationEnabled($usr_id) ? 1 : 0;
			$tmp_data['blocked'] = $this->object->getMembersObject()->isBlocked($usr_id) ? 1 : 0;
			// cognos-blu-patch: begin
			$tmp_data['contact'] = $this->object->getMembersObject()->isContact($usr_id) ? 1 : 0;
			// cognos-blu-patch: end
			
			$tmp_data['usr_id'] = $usr_id;
		
			if($this->show_tracking)
			{
				if(in_array($usr_id,$completed))
				{
					$tmp_data['progress'] = ilLPStatus::LP_STATUS_COMPLETED;
				}
				elseif(in_array($usr_id,$in_progress))
				{
					$tmp_data['progress'] = ilLPStatus::LP_STATUS_IN_PROGRESS;
				}
				elseif(in_array($usr_id,$failed))
				{
					$tmp_data['progress'] = ilLPStatus::LP_STATUS_FAILED;
				}
				else
				{
					$tmp_data['progress'] = ilLPStatus::LP_STATUS_NOT_ATTEMPTED;
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
							
			if($do_prtf)
			{
				$tmp_data['prtf'] = $all_prtf[$usr_id];
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
		global $ilUser, $ilAccess, $ilToolbar, $lng, $ilCtrl, $tpl, $rbacreview;
		
		include_once('./Modules/Course/classes/class.ilCourseParticipants.php');
		include_once('./Modules/Course/classes/class.ilCourseParticipantsTableGUI.php');
		
		
		if(isset($_GET['member_table_nav']))
		{
			list($_SESSION['crs_print_sort'],$_SESSION['crs_print_order'],$tmp) = explode(':',$_GET['member_table_nav']);
		}

		$this->checkPermission('write');
		
		include_once './Services/Tracking/classes/class.ilObjUserTracking.php';
		$this->show_tracking = (ilObjUserTracking::_enabledLearningProgress() and 
			ilObjUserTracking::_enabledUserRelatedData());
		if($this->show_tracking)
		{			
			include_once('./Services/Object/classes/class.ilObjectLP.php');
			$olp = ilObjectLP::getInstance($this->object->getId());
			$this->show_tracking = $olp->isActive();
		}
			
		include_once('./Services/Object/classes/class.ilObjectActivation.php');
		$this->timings_enabled = (ilObjectActivation::hasTimings($this->object->getRefId()) and 
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
				'user_type'				=> $this->getLocalRoles(),
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
		$this->addMailToMemberButton($ilToolbar, "members", true);

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
					$this->lng->txt('show'));
				$this->ctrl->clearParameters($this);
			}
			else
			{
				$table_gui = new ilWaitingListTableGUI($this,$waiting_list,true);
				$this->ctrl->setParameter($this,'wait_hide',1);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('hide'));
				$this->ctrl->clearParameters($this);
			}
			$table_gui->setUsers($wait);
			$table_gui->setTitle($this->lng->txt('crs_waiting_list'),'icon_usr.svg',$this->lng->txt('crs_waiting_list'));
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
					$this->lng->txt('show'));
				$this->ctrl->clearParameters($this);
			}
			else
			{
				$table_gui = new ilSubscriberTableGUI($this,true);
				$this->ctrl->setParameter($this,'subscriber_hide',1);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('hide'));
				$this->ctrl->clearParameters($this);
			}
			$table_gui->readSubscriberData();
			$table_gui->setTitle($this->lng->txt('group_new_registrations'),'icon_usr.svg',$this->lng->txt('group_new_registrations'));
			$this->tpl->setVariable('TABLE_SUB',$table_gui->getHTML());
		}
				
		
		
		if($rbacreview->getNumberOfAssignedUsers(array($this->object->getDefaultAdminRole())))
		{
			// Security: display the list of course administrators read-only, 
			// if the user doesn't have the 'edit_permission' permission. 
 			$showEditLink = 
				(
					$ilAccess->checkAccess("edit_permission", '', $this->object->getRefId()) or 
					ilCourseParticipants::_getInstanceByObjId($this->object->getId())->isAdmin($ilUser->getId())
				);
			if($ilUser->getPref('crs_admin_hide'))
			{
				$table_gui = new ilCourseParticipantsTableGUI(
					$this,
					'admin',
					false,
					false,
					$this->timings_enabled,
					$showEditLink,
					$this->object->getDefaultAdminRole(),
					$this->object->getStatusDetermination() == ilObjCourse::STATUS_DETERMINATION_LP
				);
				$this->ctrl->setParameter($this,'admin_hide',0);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('show'));
				$this->ctrl->clearParameters($this);
			}
			else
			{
				$table_gui = new ilCourseParticipantsTableGUI(
					$this,
					'admin',
					true,
					false,
					$this->timings_enabled,
					$showEditLink,
					$this->object->getDefaultAdminRole(),					
					$this->object->getStatusDetermination() == ilObjCourse::STATUS_DETERMINATION_LP
				);
				$this->ctrl->setParameter($this,'admin_hide',1);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('hide'));
				$this->ctrl->clearParameters($this);
			}
			$table_gui->setTitle($this->lng->txt('crs_administrators'),'icon_usr.svg',$this->lng->txt('crs_administrators'));
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
					false,
					$this->timings_enabled,
					true,
					$this->object->getDefaultTutorRole(),
					$this->object->getStatusDetermination() == ilObjCourse::STATUS_DETERMINATION_LP
				);
				$this->ctrl->setParameter($this,'tutor_hide',0);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('show'));
				$this->ctrl->clearParameters($this);
			}
			else
			{
				$table_gui = new ilCourseParticipantsTableGUI(
					$this,
					'tutor',
					true,
					false,
					$this->timings_enabled,
					true,
					$this->object->getDefaultTutorRole(),
					$this->object->getStatusDetermination() == ilObjCourse::STATUS_DETERMINATION_LP
				);
				$this->ctrl->setParameter($this,'tutor_hide',1);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('hide'));
				$this->ctrl->clearParameters($this);
			}
			$table_gui->setTitle($this->lng->txt('crs_tutors'),'icon_usr.svg',$this->lng->txt('crs_tutors'));
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
					$this->object->getDefaultMemberRole(),
					$this->object->getStatusDetermination() == ilObjCourse::STATUS_DETERMINATION_LP
				);

				$this->ctrl->setParameter($this,'member_hide',0);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('show'));
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
					$this->object->getDefaultMemberRole(),
					$this->object->getStatusDetermination() == ilObjCourse::STATUS_DETERMINATION_LP
				);
				$this->ctrl->setParameter($this,'member_hide',1);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('hide'));
				$this->ctrl->clearParameters($this);
			}
			$table_gui->setTitle($this->lng->txt('crs_members'),'icon_usr.svg',$this->lng->txt('crs_members'));
			$table_gui->parse();
			$this->tpl->setCurrentBlock('member_block');
			$this->tpl->setVariable('MEMBERS',$table_gui->getHTML());
			$this->tpl->parseCurrentBlock();
			
		}
		foreach(ilCourseParticipants::getMemberRoles($this->object->getRefId()) as $role_id)
		{
			// Do not show table if no user is assigned
			if(!($GLOBALS['rbacreview']->getNumberOfAssignedUsers(array($role_id))))
			{
				continue;
			}
			if($ilUser->getPref('crs_role_hide_'.$role_id))
			{
				$table_gui = new ilCourseParticipantsTableGUI(
					$this,
					'role',
					false,
					$this->show_tracking,
					$this->timings_enabled,
					true,
					$role_id,
					$this->object->getStatusDetermination() == ilObjCourse::STATUS_DETERMINATION_LP
				);
				$this->ctrl->setParameter($this,'role_hide_'.$role_id,0);
				$table_gui->addHeaderCommand(
					$this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('show')
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
					$role_id,
					$this->object->getStatusDetermination() == ilObjCourse::STATUS_DETERMINATION_LP
				);
				$this->ctrl->setParameter($this,'role_hide_'.$role_id,1);
				$table_gui->addHeaderCommand(
					$this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('hide')
				);
				$this->ctrl->clearParameters($this);
			}

			$table_gui->setTitle(ilObject::_lookupTitle($role_id),'icon_usr.svg',$this->lng->txt('crs_members'));
			$table_gui->parse();
			$this->tpl->setCurrentBlock('member_block');
			$this->tpl->setVariable('MEMBERS',$table_gui->getHTML());
			$this->tpl->parseCurrentBlock();
		}
		
		
		$this->tpl->setVariable('TXT_SELECTED_USER',$this->lng->txt('crs_selected_users'));
		$this->tpl->setVariable('BTN_FOOTER_EDIT',$this->lng->txt('edit'));
		$this->tpl->setVariable('BTN_FOOTER_VAL',$this->lng->txt('remove'));
		$this->tpl->setVariable('BTN_FOOTER_MAIL',$this->lng->txt('crs_mem_send_mail'));
		$this->tpl->setVariable('ARROW_DOWN',ilUtil::getImagePath('arrow_downright.svg'));
		
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
		// cognos-blu-patch: begin
		$contact = is_array($_POST['contact']) ? $_POST['contact'] : array();
		
		$this->updateParticipantsStatus('admins',$visible_members,$passed,$notification,array(),$contact);
		// cognos-blu-patch: end
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
		// cognos-blu-patch: begin
		$contact = is_array($_POST['contact']) ? $_POST['contact'] : array();

		$this->updateParticipantsStatus('admins',$visible_members,$passed,$notification,array(),$contact);
		// cognos-blu-patch: end
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
		// cognos-blu-patch: begin
		$contact = is_array($_POST['contact']) ? $_POST['contact'] : array();
		
		$this->updateParticipantsStatus('members',$visible_members,$passed,array(),$blocked, $contact);
		// cognos-blu-patch: end
	
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
		// cognos-blu-patch: begin
		$contact = is_array($_POST['contact']) ? $_POST['contact'] : array();

		$this->updateParticipantsStatus('members',$users,$passed,array(),$blocked,$contact);
		// cognos-blu-patch: end
	}
	
	/**
	 * sync course status and lp status 
	 *  
	 * @param int $a_member_id
	 * @param bool $a_has_passed
	 */
	protected function updateLPFromStatus($a_member_id, $a_has_passed)
	{					
		global $ilUser;
		
		include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
		if(ilObjUserTracking::_enabledLearningProgress() &&
			$this->object->getStatusDetermination() == ilObjCourse::STATUS_DETERMINATION_LP)
		{	
			include_once './Services/Object/classes/class.ilObjectLP.php';
			$olp = ilObjectLP::getInstance($this->object->getId());
			if($olp->getCurrentMode() == ilLPObjSettings::LP_MODE_MANUAL_BY_TUTOR)
			{
				include_once 'Services/Tracking/classes/class.ilLPMarks.php';
				$marks = new ilLPMarks($this->object->getId(), $a_member_id);
				
				// only if status has changed
				if($marks->getCompleted() != $a_has_passed)
				{
					$marks->setCompleted($a_has_passed);
					$marks->update();
					
					// as course is origin of LP status change, block syncing
					include_once("./Modules/Course/classes/class.ilCourseAppEventListener.php");
					ilCourseAppEventListener::setBlockedForLP(true);

					include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
					ilLPStatusWrapper::_updateStatus($this->object->getId(), $a_member_id);					
				}				
			}
		}
	}

	// cognos-blu-patch: begin
	function updateParticipantsStatus($type,$visible_members,$passed,$notification,$blocked,$contact)
	// cognos-blu-patch: end
	{
		global $ilAccess,$ilErr,$ilUser,$rbacadmin;
		foreach($visible_members as $member_id)
		{
			$this->object->getMembersObject()->updatePassed($member_id,in_array($member_id,$passed),true);
			
			$this->updateLPFromStatus($member_id, in_array($member_id, $passed));
			
			switch($type)
			{
				case 'admins';
					$this->object->getMembersObject()->updateNotification($member_id,in_array($member_id,$notification));
					// cognos-blu-patch: begin
					$this->object->getMembersObject()->updateContact($member_id,in_array($member_id,$contact) ? TRUE : FALSE);
					// cognos-blu-patch: end
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
					
					// cognos-blu-patch: begin
					
					// check if member is admin or tutor: otherwise reset contact flag
					if(!$this->object->getMembersObject()->isAdmin($member_id) and !$this->object->getMembersObject()->isTutor($member_id))
					{
						$this->object->getMembersObject()->updateContact($member_id,FALSE);
					}
					// cognos-blu-patch: end
					$this->object->getMembersObject()->updateBlocked($member_id,in_array($member_id,$blocked));
					
					break;
			}
		}
		
		ilUtil::sendSuccess($this->lng->txt('settings_saved'));
		$this->membersObject();
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
		
		$post_participants = array_unique(array_merge((array) $_POST['admins'],(array) $_POST['tutors'],(array) $_POST['members'], (array) $_POST['roles']));
		$real_participants = ilCourseParticipants::_getInstanceByObjId($this->object->getId())->getParticipants();
		$participants = array_intersect((array) $post_participants, (array) $real_participants);
		
		
		
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
		$table_gui->setTitle($this->lng->txt('crs_header_edit_members'),'icon_usr.svg',$this->lng->txt('crs_header_edit_members'));
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
		global $rbacsystem, $rbacreview, $ilUser, $ilAccess;
                
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
		// cognos-blu-patch: begin
		$contact = $_POST['contact'] ? $_POST['contact'] : array();
		// cognos-blu-patch: end
		
		// Determine whether the user has the 'edit_permission' permission
		$hasEditPermissionAccess = 
			(
				$ilAccess->checkAccess('edit_permission','',$this->object->getRefId()) or
				ilCourseParticipants::_getInstanceByObjId($this->object->getId())->isAdmin($ilUser->getId())
			);

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
			$this->object->getMembersObject()->updatePassed($usr_id,in_array($usr_id,$passed),true);
			$this->object->getMembersObject()->sendNotification(
				$this->object->getMembersObject()->NOTIFY_STATUS_CHANGED,
				$usr_id);
			
			// cognos-blu-patch: begin
			if(
				($GLOBALS['rbacreview']->isAssigned($usr_id, $this->object->getDefaultAdminRole()) or $GLOBALS['rbacreview']->isAssigned($usr_id, $this->object->getDefaultTutorRole())) and
				in_array($usr_id,$contact)
			)
			{
				$this->object->getMembersObject()->updateContact($usr_id,TRUE);
			}
			else
			{
				$this->object->getMembersObject()->updateContact($usr_id,FALSE);
			}
			// cognos-blu-patch: end
			
			$this->updateLPFromStatus($usr_id,in_array($usr_id,$passed));	
		}
		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"),true);
		$this->ctrl->redirect($this, "members");
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
		$this->object->getMembersObject()->updatePassed((int) $_GET['member_id'],(int) $_POST['passed'],true);
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
	 * @param int $a_type role_id
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
				case $this->object->getDefaultMemberRole():
					$this->object->getMembersObject()->add($user_id,IL_CRS_MEMBER);
					break;
				case $this->object->getDefaultTutorRole():
					$this->object->getMembersObject()->add($user_id,IL_CRS_TUTOR);
					break;
				case $this->object->getDefaultAdminRole():
					$this->object->getMembersObject()->add($user_id,IL_CRS_ADMIN);
					break;
				default:
					if(in_array($a_type,$this->object->getLocalCourseRoles(true)))
					{
						$this->object->getMembersObject()->add($user_id,IL_CRS_MEMBER);
						$this->object->getMembersObject()->updateRoleAssignments($user_id,(array)$a_type);
					}
					else
					{
						$GLOBALS['ilLog']->write(__METHOD__.': Can\'t find role with role id "' . $a_type . '" to assign users to.');
						ilUtil::sendFailure($this->lng->txt("crs_cannot_find_role"),true);
						return false;
					}
					break;
			}
			$this->object->getMembersObject()->sendNotification($this->object->getMembersObject()->NOTIFY_ACCEPT_USER,$user_id);

			$this->object->checkLPStatusSync($user_id);

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

			$this->object->checkLPStatusSync($user_id);
			
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

				$this->object->checkLPStatusSync($usr_id);
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
		
		include_once "Services/Utilities/classes/class.ilConfirmationGUI.php";
		$cgui = new ilConfirmationGUI();		
		$cgui->setHeaderText($this->lng->txt('crs_unsubscribe_sure'));
		$cgui->setFormAction($this->ctrl->getFormAction($this));
		$cgui->setCancel($this->lng->txt("cancel"), "cancel");
		$cgui->setConfirm($this->lng->txt("crs_unsubscribe"), "performUnsubscribe");		
		$this->tpl->setContent($cgui->getHTML());							
	}
	
	/**
	 * DEPRECATED? 
	 */
	function unsubscribeObject()
	{
		$this->leaveObject();
	}

	function performUnsubscribeObject()
	{
		global $ilUser, $ilCtrl;

		// CHECK ACCESS
		$this->checkPermission('leave');
		$this->object->getMembersObject()->delete($this->ilias->account->getId());
		$this->object->getMembersObject()->sendUnsubscribeNotificationToAdmins($this->ilias->account->getId());
		$this->object->getMembersObject()->sendNotification($this->object->getMembersObject()->NOTIFY_UNSUBSCRIBE,$ilUser->getId());
		
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
		global $ilAccess, $ilUser;
		
		$this->checkPermission('write');
		
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
		if(
			!$ilAccess->checkAccess('edit_permission', '',$this->object->getRefId()) and
			!ilCourseParticipants::_getInstanceByObjId($this->object->getId())->isAdmin($ilUser->getId())
		)
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

		$this->setSubTabs('members');
		$this->tabs_gui->setTabActive('members');
		$this->tabs_gui->setSubTabActive('crs_member_administration');
		
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
				ilUtil::getImagePath('icon_usr.svg'));
		}
		
		$this->tpl->setContent($confirm->getHTML());
		
	}

	/**
	 * Remove members
	 * @global ilRbacReview $rbacreview
	 * @global ilRbacSystem $rbacsystem
	 * @return boolean
	 */
	protected function removeMembersObject()
	{
		global $rbacreview, $rbacsystem, $ilAccess, $ilUser;
                
		$this->checkPermission('write');
		
		if(!is_array($_POST["participants"]) or !count($_POST["participants"]))
		{
			ilUtil::sendFailure($this->lng->txt("crs_no_member_selected"));
			$this->membersObject();

			return false;
		}
		
		// If the user doesn't have the edit_permission and is not administrator, he may not remove
		// members who have the course administrator role
		if (
			!$ilAccess->checkAccess('edit_permission', '', $this->object->getRefId()) and 
			!ilCourseParticipants::_getInstanceByObjId($this->object->getId())->isAdmin($ilUser->getId())
		)
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
			// SEND NOTIFICATION
			foreach($_POST["participants"] as $usr_id)
			{
				$this->object->getMembersObject()->sendNotification($this->object->getMembersObject()->NOTIFY_DISMISS_MEMBER,$usr_id);
			}
		}
		ilUtil::sendSuccess($this->lng->txt("crs_members_deleted"), true);
		$this->ctrl->redirect($this, "members");

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
	 * Get tabs for member agreement
	 */
	protected function getAgreementTabs()
	{
		
		if ($ilAccess->checkAccess('visible','',$this->ref_id))
		{
			$GLOBALS['ilTabs']->addTarget("info_short",
								 $this->ctrl->getLinkTargetByClass(
								 array("ilobjcoursegui", "ilinfoscreengui"), "showSummary"),
								 "infoScreen"
			);
		}
		if($ilAccess->checkAccess('leave','',$this->object->getRefId()) and $this->object->getMemberObject()->isMember())
		{
			$GLOBALS['ilTabs']->addTarget("crs_unsubscribe",
					$this->ctrl->getLinkTarget($this, "unsubscribe"), 
					'leave',
					 "");
		}
		
	}

	/**
	* Get tabs
	*/
	function getTabs()
	{
		global $rbacsystem,$ilAccess,$ilUser, $lng, $ilHelp;

		$ilHelp->setScreenIdComponent("crs");
		
		$this->ctrl->setParameter($this,"ref_id",$this->ref_id);

		if($ilAccess->checkAccess('read','',$this->ref_id))
		{
			$this->tabs_gui->addTab('view_content', $lng->txt("content"),
								 $this->ctrl->getLinkTarget($this,''));
		}
		
		// learning objectives
		if($ilAccess->checkAccess('write','',$this->ref_id))
		{
			include_once('./Modules/Course/classes/class.ilCourseObjective.php');
			if($this->object->getViewMode() == IL_CRS_VIEW_OBJECTIVE or ilCourseObjective::_getCountObjectives($this->object->getId()))
			{
				$this->tabs_gui->addTarget(
						'crs_objectives',
						$this->ctrl->getLinkTargetByClass('illoeditorgui',''),
						'illoeditorgui'
				);
						
			}
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
			$this->tabs_gui->addTarget("info_short",
								 $this->ctrl->getLinkTargetByClass(
								 array("ilobjcoursegui", "ilinfoscreengui"), "showSummary"),
								 "infoScreen",
								 "", "", $force_active);
		}
		if ($ilAccess->checkAccess('write','',$this->ref_id))
		{
			$force_active = (strtolower($_GET["cmdClass"]) == "ilconditionhandlergui"
				&& $_GET["item_id"] == "")
				? true
				: false;
			$this->tabs_gui->addTarget("settings",
				$this->ctrl->getLinkTarget($this, "edit"),
				array("edit", "editMapSettings", "editCourseIcons", "listStructure"), "", "", $force_active);
		}
		
		
		$is_participant = ilCourseParticipants::_isParticipant($this->ref_id, $ilUser->getId());
		include_once './Services/Mail/classes/class.ilMail.php';
		$mail = new ilMail($GLOBALS['ilUser']->getId());
		
		// member list
		if($ilAccess->checkAccess('write','',$this->ref_id))
		{
			$this->tabs_gui->addTarget("members",
								 $this->ctrl->getLinkTarget($this, "members"), 
								 "members",
								 get_class($this));
		}			
		elseif(
			$this->object->getShowMembers() == $this->object->SHOW_MEMBERS_ENABLED and
			$is_participant
		)
		{
			$this->tabs_gui->addTarget(
				'members',
				$this->ctrl->getLinkTargetByClass('ilUsersGalleryGUI', 'view'),
				'',
				'ilUsersGalleryGUI'
			);
		}
		elseif(
			$this->object->getMailToMembersType() == ilCourseConstants::MAIL_ALLOWED_ALL and
			$GLOBALS['rbacsystem']->checkAccess('internal_mail',$mail->getMailObjectReferenceId ()) and
			$is_participant
		)
		{
			$this->tabs_gui->addTarget("members",
				$this->ctrl->getLinkTarget($this, "mailMembersBtn"),
				"members",
				get_class($this));
			
		}
		
		// badges
		if($ilAccess->checkAccess('write','',$this->ref_id))
		{
			include_once 'Services/Badge/classes/class.ilBadgeHandler.php';
			if(ilBadgeHandler::getInstance()->isObjectActive($this->object->getId()))
			{
				$this->tabs_gui->addTarget("obj_tool_setting_badges",
					 $this->ctrl->getLinkTargetByClass("ilbadgemanagementgui", ""), 
					 "",
					 "ilbadgemanagementgui");
			}
		}		

		// learning progress
		include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
		if(ilLearningProgressAccess::checkAccess($this->object->getRefId(), $is_participant))
		{
			$this->tabs_gui->addTarget('learning_progress',
								 $this->ctrl->getLinkTargetByClass(array('ilobjcoursegui','illearningprogressgui'),''),
								 '',
								 array('illplistofobjectsgui','illplistofsettingsgui','illearningprogressgui','illplistofprogressgui'));
		}
		
		

		// license overview
		include_once("Services/License/classes/class.ilLicenseAccess.php");
		if ($ilAccess->checkAccess('edit_permission', '', $this->ref_id)
		and ilLicenseAccess::_isEnabled())
		{
			$this->tabs_gui->addTarget("licenses",
				$this->ctrl->getLinkTargetByClass('illicenseoverviewgui', ''),
			"", "illicenseoverviewgui");
		}

		// meta data
		if ($ilAccess->checkAccess('write','',$this->ref_id))
		{
			include_once "Services/Object/classes/class.ilObjectMetaDataGUI.php";
			$mdgui = new ilObjectMetaDataGUI($this->object);					
			$mdtab = $mdgui->getTab();
			if($mdtab)
			{
				$this->tabs_gui->addTarget("meta_data",
									 $mdtab,
									 "",
									 "ilobjectmetadatagui");
			}
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

		if ($ilAccess->checkAccess('edit_permission','',$this->ref_id))
		{
			$this->tabs_gui->addTarget("perm_settings",
								 $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"),
								 array("perm","info","owner"), 'ilpermissiongui');
		}

		if ($this->ctrl->getTargetScript() == "adm_object.php")
		{
			$this->tabs_gui->addTarget("show_owner",
								 $this->ctrl->getLinkTarget($this, "owner"), "owner", get_class($this));
			
			if ($this->tree->getSavedNodeData($this->ref_id))
			{
				$this->tabs_gui->addTarget("trash",
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
				$this->tabs_gui->addTab(
					'leave',
					$this->lng->txt('membership_leave'),
					$this->ctrl->getLinkTargetByClass('ilcourseregistrationgui','show','')
				);
					
			}
			else
			{			
				
				$this->tabs_gui->addTarget("join",
									 $this->ctrl->getLinkTargetByClass('ilcourseregistrationgui', "show"), 
									 'show',
									 "");
			}
		}
		if($ilAccess->checkAccess('leave','',$this->object->getRefId())
			and $this->object->getMemberObject()->isMember())
		{
			$this->tabs_gui->addTarget("crs_unsubscribe",
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
						$print_member[$member_id]['progress'] = $this->lng->txt(ilLPStatus::LP_STATUS_COMPLETED);
					}
					elseif(in_array($member_id,$in_progress))
					{
						$print_member[$member_id]['progress'] = $this->lng->txt(ilLPStatus::LP_STATUS_IN_PROGRESS);
					}
					elseif(in_array($member_id,$failed))
					{
						$print_member[$member_id]['progress'] = $this->lng->txt(ilLPStatus::LP_STATUS_FAILED);
					}
					else
					{
						$print_member[$member_id]['progress'] = $this->lng->txt(ilLPStatus::LP_STATUS_NOT_ATTEMPTED);
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
		
		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($this->lng->txt('back'),
			$this->ctrl->getLinkTarget($this, 'members'));
		
		$list = $this->initAttendanceList();
		$form = $list->initForm('printMembersOutput');
		$this->tpl->setContent($form->getHTML());	
	}
	
	protected function initAttendanceList()
	{
		include_once('./Modules/Course/classes/class.ilCourseParticipants.php');
		$members_obj = ilCourseParticipants::_getInstanceByObjId($this->object->getId());
		
		include_once('./Modules/Course/classes/class.ilCourseWaitingList.php');
		$waiting_list = new ilCourseWaitingList($this->object->getId());
		
		include_once 'Services/Membership/classes/class.ilAttendanceList.php';
		$list = new ilAttendanceList($this, $members_obj, $waiting_list);		
		$list->setId('crsmemlst');
	
		$list->setTitle($this->lng->txt('crs_members_print_title'),
			$this->lng->txt('obj_crs').': '.$this->object->getTitle());
				
		include_once './Services/Tracking/classes/class.ilObjUserTracking.php';
		$this->show_tracking = (ilObjUserTracking::_enabledLearningProgress() and 
			ilObjUserTracking::_enabledUserRelatedData());
		if($this->show_tracking)
		{
			include_once('./Services/Object/classes/class.ilObjectLP.php');
			$olp = ilObjectLP::getInstance($this->object->getId());
			$this->show_tracking = $olp->isActive();
		}
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
		$list->getNonMemberUserData($this->members_data);
		
		$list->getFullscreenHTML();
		exit();
	
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

	function executeCommand()
	{
		global $rbacsystem,$ilUser,$ilAccess,$ilErr,$ilTabs,$ilNavigationHistory,$ilCtrl, $ilToolbar;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
	
		$this->prepareOutput();
		
		// show repository tree
		$this->showRepTree();
		
		// add entry to navigation history
		if(!$this->getCreationMode() &&
			$ilAccess->checkAccess('read', '', $_GET['ref_id']))
		{
			include_once("./Services/Link/classes/class.ilLink.php");
			$ilNavigationHistory->addItem($_GET["ref_id"],
				ilLink::_getLink($_GET["ref_id"], "crs"), "crs");
		}

		switch($next_class)
		{
			case "ilinfoscreengui":
				$this->infoScreen();	// forwards command
				break;
			
			case 'ilobjectmetadatagui';
				if(!$ilAccess->checkAccess('write','',$this->object->getRefId()))
				{
					$ilErr->raiseError($this->lng->txt('permission_denied'),$ilErr->WARNING);
				}
				$this->tabs_gui->setTabActive('meta_data');
				include_once 'Services/Object/classes/class.ilObjectMetaDataGUI.php';
				$md_gui = new ilObjectMetaDataGUI($this->object);	
				$this->ctrl->forwardCommand($md_gui);
				break;
				
			case 'ilcourseregistrationgui':
				$this->ctrl->setReturn($this,'');
				$this->tabs_gui->setTabActive('join');
				include_once('./Modules/Course/classes/class.ilCourseRegistrationGUI.php');
				$registration = new ilCourseRegistrationGUI($this->object, $this);
				$this->ctrl->forwardCommand($registration);
				break;
				
			case 'ilobjectcustomuserfieldsgui':
				include_once './Services/Membership/classes/class.ilObjectCustomUserFieldsGUI.php';
				
				if(isset($_REQUEST['member_id']))
				{
					$this->ctrl->setReturn($this,'members');
				}
				
				$cdf_gui = new ilObjectCustomUserFieldsGUI($this->object->getId());
				$this->setSubTabs('properties');
				$this->tabs_gui->setTabActive('settings');
				$this->ctrl->forwardCommand($cdf_gui);
				break;

			case "ilcourseobjectivesgui":
				include_once './Modules/Course/classes/class.ilCourseObjectivesGUI.php';

				$this->ctrl->setReturn($this,"");
				$reg_gui = new ilCourseObjectivesGUI($this->object->getRefId());
				$ret =& $this->ctrl->forwardCommand($reg_gui);
				break;

			case 'ilobjcoursegroupinggui':
				include_once './Modules/Course/classes/class.ilObjCourseGroupingGUI.php';

				$this->ctrl->setReturn($this,'edit');
				$this->setSubTabs('properties');
				$crs_grp_gui = new ilObjCourseGroupingGUI($this->object,(int) $_GET['obj_id']);
				$this->ctrl->forwardCommand($crs_grp_gui);
				$this->tabs_gui->setTabActive('settings');
				$this->tabs_gui->setSubTabActive('groupings');
				break;

			case "ilcolumngui":
				$this->tabs_gui->setTabActive('none');
				$this->checkPermission("read");
				//$this->prepareOutput();
				//include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
				//$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
				//	ilObjStyleSheet::getContentStylePath(0));
				//$this->renderObject();
				$this->viewObject();
				break;

			case "ilconditionhandlergui":
				include_once './Services/AccessControl/classes/class.ilConditionHandlerGUI.php';				
				// preconditions for whole course				
				$this->setSubTabs("properties");
				$this->tabs_gui->setTabActive('settings');
				$new_gui = new ilConditionHandlerGUI($this);
				$this->ctrl->forwardCommand($new_gui);				
				break;

			case "illearningprogressgui":
				include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';

				$new_gui = new ilLearningProgressGUI(ilLearningProgressGUI::LP_CONTEXT_REPOSITORY,
													  $this->object->getRefId(),
													  $_GET['user_id'] ? $_GET['user_id'] : $ilUser->getId());
				$this->ctrl->forwardCommand($new_gui);
				$this->tabs_gui->setTabActive('learning_progress');
				break;

			case 'ilusersgallerygui':
				$is_admin       = (bool)$ilAccess->checkAccess('write', '', $this->object->ref_id);
				$is_participant = (bool)ilCourseParticipants::_isParticipant($this->ref_id, $ilUser->getId());
				if(
					!$is_admin &&
					(
						$this->object->getShowMembers() == $this->object->SHOW_MEMBERS_DISABLED ||
						!$is_participant
					)
				)
				{
					$ilErr->raiseError($this->lng->txt('msg_no_perm_read'), $ilErr->MESSAGE);
				}

				$this->addMailToMemberButton($ilToolbar, 'jump2UsersGallery');

				require_once 'Services/User/classes/class.ilUsersGalleryGUI.php';
				require_once 'Services/User/classes/class.ilUsersGalleryParticipants.php';
				$this->setSubTabs('members');
				$this->tabs_gui->setTabActive('members');
				$this->tabs_gui->setSubTabActive('crs_members_gallery');

				$provider    = new ilUsersGalleryParticipants($this->object->getMembersObject());
				$gallery_gui = new ilUsersGalleryGUI($provider);
				$this->ctrl->forwardCommand($gallery_gui);
				break;

			case 'illicenseoverviewgui':
				include_once("./Services/License/classes/class.ilLicenseOverviewGUI.php");
				$license_gui = new ilLicenseOverviewGUI($this, ilLicenseOverviewGUI::LIC_MODE_REPOSITORY);
				$ret =& $this->ctrl->forwardCommand($license_gui);
				$this->tabs_gui->setTabActive('licenses');
				break;

			case 'ilpermissiongui':
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$this->tabs_gui->setTabActive('perm_settings');
				$perm_gui = new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			case 'ilrepositorysearchgui':
				
				if(!$this->checkPermissionBool('write'))
				{
					$GLOBALS['ilErr']->raiseError($GLOBALS['lng']->txt('permission_denied'), $GLOBALS['ilErr']->WARNING);
				}
				include_once('./Services/Search/classes/class.ilRepositorySearchGUI.php');
				$rep_search = new ilRepositorySearchGUI();
				if(ilCourseParticipant::_getInstanceByObjId($this->object->getId(), $GLOBALS['ilUser']->getId())->isAdmin() or $this->checkPermissionBool('edit_permission'))
				{
					$rep_search->setCallback($this,
						'assignMembersObject',
						$this->getLocalRoles()
						);
				}
				else
				{
					//#18445 excludes admin role
					$rep_search->setCallback($this,
						'assignMembersObject',
					    $this->getLocalRoles(array($this->object->getDefaultAdminRole()))
						);
					
				}
				

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
				$this->tpl->enableDragDropFileUpload(null);				
				require_once './Services/User/classes/class.ilPublicUserProfileGUI.php';
				$this->setSubTabs('members');
				$this->tabs_gui->setTabActive('members');
				$profile_gui = new ilPublicUserProfileGUI($_GET["user"]);
				$profile_gui->setBackUrl($this->ctrl->getLinkTargetByClass("ilUsersGalleryGUI",'view'));
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
				$this->tabs_gui->clearTargets();
				
				$this->ctrl->setReturn($this,'');
				$agreement = new ilMemberAgreementGUI($this->object->getRefId());
				$this->ctrl->forwardCommand($agreement);
				break;
				
			case 'ilsessionoverviewgui':								
				$this->setSubTabs('members');
				$this->tabs_gui->setTabActive('members');
				$this->tabs_gui->setSubTabActive('events');
				
				include_once './Modules/Course/classes/class.ilCourseParticipants.php';
				$prt = ilCourseParticipants::_getInstanceByObjId($this->object->getId());
			
				include_once('./Modules/Session/classes/class.ilSessionOverviewGUI.php');
				$overview = new ilSessionOverviewGUI($this->object->getRefId(), $prt);
				$this->ctrl->forwardCommand($overview);				
				break;
			
			// container page editing
			case "ilcontainerpagegui":
				$ret = $this->forwardToPageObject();
				if ($ret != "")
				{
					$this->tpl->setContent($ret);
				}
				break;
				
			case "ilcontainerstartobjectspagegui":
				// file downloads, etc. (currently not active)
				include_once "Services/Container/classes/class.ilContainerStartObjectsPageGUI.php";
				$pgui = new ilContainerStartObjectsPageGUI($this->object->getId());							
				$ret = $this->ctrl->forwardCommand($pgui);
				if($ret)
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

				if($cmd == "show" || $cmd = "")
				{
					$this->addMailToMemberButton($ilToolbar, "members");
				}
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
			
			case 'ilobjectservicesettingsgui':
				$this->ctrl->setReturn($this,'edit');
				$this->setSubTabs("properties");
				$this->tabs_gui->activateTab('settings');
				$this->tabs_gui->acltivateSubTab('tool_settings');
				
				include_once './Services/Object/classes/class.ilObjectServiceSettingsGUI.php';
				$service = new ilObjectServiceSettingsGUI(
						$this,
						$this->object->getId(),
						array(
							ilObjectServiceSettingsGUI::CALENDAR_VISIBILITY
						));
				$this->ctrl->forwardCommand($service);
				break;

			case 'illoeditorgui':
				#$this->tabs_gui->clearTargets();
				#$this->tabs_gui->setBackTarget($this->lng->txt('back'),$this->ctrl->getLinkTarget($this,''));
				$this->tabs_gui->activateTab('crs_objectives');
				
				include_once './Modules/Course/classes/Objectives/class.ilLOEditorGUI.php';
				$editor = new ilLOEditorGUI($this->object);
				$this->ctrl->forwardCommand($editor);
				break;
			
			case 'ilcontainerstartobjectsgui':
				$this->ctrl->setReturn($this,'edit');
				$this->tabs_gui->clearTargets();
				$this->tabs_gui->setBackTarget($this->lng->txt("back_to_crs_content"),
					$this->ctrl->getLinkTarget($this, "edit"));
				$this->tabs_gui->addTab("start",
					$this->lng->txt("crs_start_objects"),
					$this->ctrl->getLinkTargetByClass("ilcontainerstartobjectsgui", "listStructure"));
				global $ilHelp;
				$ilHelp->setScreenIdComponent("crs");
				
				include_once './Services/Container/classes/class.ilContainerStartObjectsGUI.php';
				$stgui = new ilContainerStartObjectsGUI($this->object);
				$this->ctrl->forwardCommand($stgui);
				break;		
			
			case 'illomembertestresultgui':
				include_once './Modules/Course/classes/Objectives/class.ilLOMemberTestResultGUI.php';
				$GLOBALS['ilCtrl']->setReturn($this, 'members');
				$GLOBALS['ilTabs']->clearTargets();
				$GLOBALS['ilTabs']->setBackTarget(
					$GLOBALS['lng']->txt('back'),
					$GLOBALS['ilCtrl']->getLinkTarget($this,'members')
				);
				
				$result_view = new ilLOMemberTestResultGUI($this, $this->object, (int) $_REQUEST['uid']);
				$this->ctrl->forwardCommand($result_view);
				break;

			case 'ilmailmembersearchgui':
				include_once 'Services/Mail/classes/class.ilMail.php';
				$mail = new ilMail($ilUser->getId());

				if(!($this->object->getMailToMembersType() == ilCourseConstants::MAIL_ALLOWED_ALL ||
					$ilAccess->checkAccess('write',"",$this->object->getRefId())) &&
					$rbacsystem->checkAccess('internal_mail',$mail->getMailObjectReferenceId()))
				{
					$ilErr->raiseError($this->lng->txt("msg_no_perm_read"),$ilErr->MESSAGE);
				}
				
				$this->tabs_gui->setTabActive('members');

				include_once './Services/Contact/classes/class.ilMailMemberSearchGUI.php';
				include_once './Services/Contact/classes/class.ilMailMemberCourseRoles.php';
				
				$mail_search = new ilMailMemberSearchGUI($this, $this->object->getRefId(), new ilMailMemberCourseRoles());
				$mail_search->setObjParticipants(ilCourseParticipants::_getInstanceByObjId($this->object->getId()));
				$this->ctrl->forwardCommand($mail_search);
				break;
				
			case 'ilbadgemanagementgui':
				$this->tabs_gui->setTabActive('obj_tool_setting_badges');
				include_once 'Services/Badge/classes/class.ilBadgeManagementGUI.php';
				$bgui = new ilBadgeManagementGUI($this->object->getRefId(), $this->object->getId(), 'crs');
				$this->ctrl->forwardCommand($bgui);
				break;
				
            default:
/*                if(!$this->creation_mode)
                {
                    $this->checkPermission('visible');
                }*/
                /*
                if(!$this->creation_mode and !$ilAccess->checkAccess('visible','',$this->object->getRefId(),'crs'))
                {
                    $ilErr->raiseError($this->lng->txt("msg_no_perm_read"),$ilErr->MESSAGE);
                }
                */

                // #9401 - see also ilStartupGUI::_checkGoto()
                if($cmd == 'infoScreenGoto')
                {
                    if(ilObjCourse::_isActivated($this->object->getId()) &&
                        ilObjCourse::_registrationEnabled($this->object->getId()))
                    {
                        $cmd = 'join';
                    }
                    else
                    {
                        $cmd = 'infoScreen';
                    }
                }
                
            	if(!$this->creation_mode)
				{
					if ($cmd == "infoScreen")
					{
						$this->checkPermission("visible");
					}
					else
					{
//						$this->checkPermission("read");
					}
				}


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
                    $obj_gui = new ilCourseObjectivesGUI($this->object->getRefId());
                    $ret =& $this->ctrl->forwardCommand($obj_gui);
                    break;
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
			$GLOBALS['ilLog']->write(__METHOD__.': Missing course confirmation.');
			return false;
		}
		// Check required fields
		include_once('Modules/Course/classes/Export/class.ilCourseUserData.php');
		if(!ilCourseUserData::_checkRequired($ilUser->getId(),$this->object->getId()))
		{
			$GLOBALS['ilLog']->write(__METHOD__.': Missing required fields');
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
				$license = new ilLicense($item['obj_id']);
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
	public static function _forwards()
	{
		return array("ilCourseRegisterGUI",'ilConditionHandlerGUI');
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
	public static function _goto($a_target, $a_add = "")
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
				ilObjectGUI::_gotoRepositoryNode($a_target, "infoScreenGoto");
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


	/**
	* Edit Map Settings
	*/
	function editMapSettingsObject()
	{
		global $ilUser, $ilCtrl, $ilUser, $ilAccess;

		$this->setSubTabs("properties");
		$this->tabs_gui->setTabActive('settings');
		
		if (!ilMapUtil::isActivated() ||
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
			$def = ilMapUtil::getDefaultSettings();
			$latitude = $def["latitude"];
			$longitude = $def["longitude"];
			$zoom =  $def["zoom"];
		}

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
		
		include_once("./Services/Maps/classes/class.ilMapUtil.php");
		if (!ilMapUtil::isActivated() || !$this->object->getEnableCourseMap())
		{
			return;
		}
		
		$map = ilMapUtil::getMapGUI();
		$map->setMapId("course_map")
			->setWidth("700px")
			->setHeight("500px")
			->setLatitude($this->object->getLatitude())
			->setLongitude($this->object->getLongitude())
			->setZoom($this->object->getLocationZoom())
			->setEnableTypeControl(true)
			->setEnableNavigationControl(true)
			->setEnableCentralMarker(true);

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
	 * @param type $a_item_list_gui
	 * @param type $a_item_data
	 * @param type $a_show_path
	 * @return type
	 */
	public function modifyItemGUI($a_item_list_gui, $a_item_data, $a_show_path)
	{
		return ilObjCourseGUI::_modifyItemGUI($a_item_list_gui, 'ilcoursecontentgui', $a_item_data, $a_show_path,
			$this->object->getAboStatus(), $this->object->getRefId(), $this->object->getId());
	}
	
	/**
	* We need a static version of this, e.g. in folders of the course
	*/
	public static function _modifyItemGUI($a_item_list_gui, $a_cmd_class, $a_item_data, $a_show_path,
		$a_abo_status, $a_course_ref_id, $a_course_obj_id, $a_parent_ref_id = 0)
	{
		global $lng, $ilAccess;
		
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
				
		$a_item_list_gui->enableSubscribe($a_abo_status);
	
		$is_tutor = ($ilAccess->checkAccess('write','',
			$a_course_ref_id,'crs', $a_course_obj_id));
		
		if($a_show_path and $is_tutor)
		{
			$a_item_list_gui->addCustomProperty($lng->txt('path'),				
				ilContainer::_buildPath($a_item_data['ref_id'], $a_course_ref_id),
				false,
				true);
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
		if($this->object->getViewMode() == IL_CRS_VIEW_TIMING)
		{
			$this->tabs_gui->addSubTabTarget('timings_timings',
				$this->ctrl->getLinkTargetByClass('ilcoursecontentgui','editUserTimings'));
		}
		
		$this->addStandardContainerSubTabs(false);
		

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
		
		// #10206 / #10217
		if(is_array($_POST[$a_field]['date']))
		{		
			$dt['year'] = (int) $_POST[$a_field]['date']['y'];
			$dt['mon'] = (int) $_POST[$a_field]['date']['m'];
			$dt['mday'] = (int) $_POST[$a_field]['date']['d'];
			$dt['hours'] = (int) $_POST[$a_field]['time']['h'];
			$dt['minutes'] = (int) $_POST[$a_field]['time']['m'];
			$dt['seconds'] = (int) $_POST[$a_field]['time']['s'];
		}
		else
		{
			$date = date_parse($_POST[$a_field]['date']." ".$_POST[$a_field]['time']);
			$dt['year'] = (int) $date['year'];
			$dt['mon'] = (int) $date['month'];
			$dt['mday'] = (int) $date['day'];
			$dt['hours'] = (int) $date['hour'];
			$dt['minutes'] = (int) $date['minute'];
			$dt['seconds'] = (int) $date['second'];
		}
		
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
		ilUtil::sendQuestion($this->lng->txt('crs_objectives_reset_sure'));
		
		include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
		$confirm = new ilConfirmationGUI();
		$confirm->setFormAction($this->ctrl->getFormAction($this));
		$confirm->setConfirm($this->lng->txt('reset'), 'reset');
		$confirm->setCancel($this->lng->txt('cancel'), 'cancel');
		
		$GLOBALS['tpl']->setContent($confirm->getHTML());
		return true;		
	}
	
	function resetObject()
	{
		global $ilUser;
		
		include_once './Modules/Course/classes/Objectives/class.ilLOUserResults.php';
		$usr_results = new ilLOUserResults($this->object->getId(), $GLOBALS['ilUser']->getId());
		$usr_results->delete();

		
		include_once './Modules/Course/classes/Objectives/class.ilLOTestRun.php';
		include_once './Modules/Course/classes/Objectives/class.ilLOSettings.php';
		ilLOTestRun::deleteRuns(
			$this->object->getId(), 
				$GLOBALS['ilUser']->getId()
		);
		
		include_once './Modules/Course/classes/class.ilCourseObjectiveResult.php';
		
		$tmp_obj_res = new ilCourseObjectiveResult($ilUser->getId());
		$tmp_obj_res->reset($this->object->getId());
		
		$ilUser->deletePref('crs_objectives_force_details_'.$this->object->getId());
		
		ilUtil::sendSuccess($this->lng->txt('crs_objectives_reseted'));
		$this->viewObject();
	}
	
	function __checkStartObjects()
	{		
		global $ilAccess,$ilUser;

		if($ilAccess->checkAccess('write','',$this->object->getRefId()))
		{
			return true;
		}
		
		include_once './Services/Container/classes/class.ilContainerStartObjects.php';
		$this->start_obj = new ilContainerStartObjects($this->object->getRefId(),
			$this->object->getId());
		if(count($this->start_obj->getStartObjects()) && 
			!$this->start_obj->allFullfilled($ilUser->getId()))
		{
			return false;
		}
		
		return true;
	}
	
	/**
	 * Handle member view
	 * @return 
	 */
	public function prepareOutput($a_show_subobjects = true)
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
		parent::prepareOutput($a_show_subobjects);
	}
	
	/**
	 * Create a course mail signature
	 * @return string 
	 */
	public function createMailSignature()
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
				
		if($lg && $this->ref_id && ilCourseParticipants::_isParticipant($this->ref_id, $ilUser->getId()))
		{							
			// certificate
			include_once "Services/Certificate/classes/class.ilCertificate.php";
			if (ilCertificate::isActive() &&
				ilCertificate::isObjectActive($this->object->getId()) && 
				ilCourseParticipants::getDateTimeOfPassed($this->object->getId(), $ilUser->getId()))
			{			    
				$cert_url = $this->ctrl->getLinkTarget($this, "deliverCertificate");
				
				$this->lng->loadLanguageModule("certificate");
				$lg->addCustomCommand($cert_url, "download_certificate");
				
				$lg->addHeaderIcon("cert_icon",
						ilUtil::getImagePath("icon_cert.svg"),
						$this->lng->txt("download_certificate"),
						null,
						null,
						$cert_url);
			}
			
			// notification
			include_once "Services/Membership/classes/class.ilMembershipNotifications.php";			
			if(ilMembershipNotifications::isActive())
			{
				$noti = new ilMembershipNotifications($this->ref_id);				
				if(!$noti->isCurrentUserActive())
				{
					$lg->addHeaderIcon("not_icon",
						ilUtil::getImagePath("notification_off.svg"),
						$this->lng->txt("crs_notification_deactivated"));

					$this->ctrl->setParameter($this, "crs_ntf", 1);
					$caption = "crs_activate_notification";
				}
				else
				{				
					$lg->addHeaderIcon("not_icon",
						ilUtil::getImagePath("notification_on.svg"),
						$this->lng->txt("crs_notification_activated"));

					$this->ctrl->setParameter($this, "crs_ntf", 0);
					$caption = "crs_deactivate_notification";
				}

				if($noti->canCurrentUserEdit())
				{
					$lg->addCustomCommand($this->ctrl->getLinkTarget($this, "saveNotification"),
						$caption);
				}

				$this->ctrl->setParameter($this, "crs_ntf", "");
			}
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
			!ilCertificate::isObjectActive($this->object->getId()) ||
			!ilCourseParticipants::getDateTimeOfPassed($this->object->getId(), $user_id))
		{
			ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
			$this->ctrl->redirect($this);
		}
		
		include_once "./Modules/Course/classes/class.ilCourseCertificateAdapter.php";
		$certificate = new ilCertificate(new ilCourseCertificateAdapter($this->object));
		$certificate->outCertificate(array("user_id" => $user_id), true);				
	}
	
	
	protected function afterSaveCallback()
	{
		$this->ctrl->redirectByClass(array('ilrepositorygui','ilobjcoursegui','illoeditorgui'),'materials');
	}
		
	public function saveSortingObject()
	{			
		if(isset($_POST['position']["lobj"]))
		{
			$lobj = $_POST['position']["lobj"];
			unset($_POST['position']["lobj"]);
			
			$objective_order = array();
			foreach($lobj as $objective_id => $materials)
			{
				$objective_order[$objective_id] = $materials[0];
				unset($lobj[$objective_id][0]);
			}
			
			// objective order
			include_once "Modules/Course/classes/class.ilCourseObjective.php";
			asort($objective_order);
			$pos = 0;
			foreach(array_keys($objective_order) as $objective_id)
			{
				$obj = new ilCourseObjective($this->object, $objective_id);
				$obj->writePosition(++$pos);
			}
			
			// material order
			include_once "Modules/Course/classes/class.ilCourseObjectiveMaterials.php";
			foreach($lobj as $objective_id => $materials)
			{
				$objmat = new ilCourseObjectiveMaterials($objective_id);
				
				asort($materials);
				$pos = 0;
				foreach(array_keys($materials) as $ass_id)
				{
					$objmat->writePosition($ass_id, ++$pos);
				}
			}
		}
		
		return parent::saveSortingObject();
	}
	
	/**
	 * 
	 * @return booleanRedirect ot test after confirmation of resetting completed objectives
	 */
	protected function redirectLocToTestConfirmedObject()
	{
		include_once './Services/Link/classes/class.ilLink.php';
		ilUtil::redirect(ilLink::_getLink((int) $_REQUEST['tid']));
		return TRUE;
		
	}
	
	/**
	 * Test redirection will be moved lo adapter
	 */
	protected function redirectLocToTestObject($a_force_new_run = NULL)
	{
		$objective_id = (int) $_REQUEST['objective_id'];
		$test_id = (int) $_REQUEST['tid'];
		
		include_once './Modules/Course/classes/Objectives/class.ilLOUserResults.php';
		include_once './Modules/Course/classes/Objectives/class.ilLOSettings.php';
		include_once './Modules/Course/classes/Objectives/class.ilLOTestAssignments.php';
		
		
		$res = new ilLOUserResults(
				$this->object->getId(),
				$GLOBALS['ilUser']->getId());
		$passed = $res->getCompletedObjectiveIds();

		$has_completed = FALSE;
		if($objective_id)
		{
			$objective_ids = array($objective_id);
			if(in_array($objective_id, $passed))
			{
				$has_completed = TRUE;
				$passed = array();
			}
		}
		else
		{
			include_once './Modules/Course/classes/class.ilCourseObjective.php';
			$objective_ids = ilCourseObjective::_getObjectiveIds($this->object->getId(),true);
			
			// do not disable objective question if all are passed
			if(count($objective_ids) == count($passed))
			{
				$has_completed = TRUE;
				$passed = array();
			}
		}
		
		if($has_completed)
		{
			// show confirmation
			$this->redirectLocToTestConfirmation($objective_id,$test_id);
			return TRUE;
		}
		
		include_once './Services/Link/classes/class.ilLink.php';
		ilUtil::redirect(ilLink::_getLink($test_id));
		return TRUE;
		
	}
	
	/**
	 * Show confirmation whether user wants to start a new run or resume a previous run
	 * @param type $a_objective_id
	 * @param type $a_test_id
	 */
	protected function redirectLocToTestConfirmation($a_objective_id, $a_test_id)
	{
		include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
		$confirm = new ilConfirmationGUI();
		$confirm->setFormAction($GLOBALS['ilCtrl']->getFormAction($this));
		
		if($a_objective_id)
		{
			$question = $this->lng->txt('crs_loc_objective_passed_confirmation');
		}
		else
		{
			$question = $this->lng->txt('crs_loc_objectives_passed_confirmation');
		}
		
		$confirm->addHiddenItem('objective_id', $a_objective_id);
		$confirm->addHiddenItem('tid', $a_test_id);
		$confirm->setConfirm($this->lng->txt('crs_loc_tst_start'), 'redirectLocToTestConfirmed');
		$confirm->setCancel($this->lng->txt('cancel'), 'view');
		
		ilUtil::sendQuestion($question);

		$GLOBALS['tpl']->setContent($confirm->getHTML());
		return true;
	}
	// end-patch lok

	/**
	 *
	 * @var int[] $a_exclude a list of role ids which will not added to the results (optional)
	 * returns all local roles [role_id] => title
	 * @return array localroles
	 */
	protected function getLocalRoles($a_exclude = array())
	{
		$crs_admin = $this->object->getDefaultAdminRole();
		$crs_member = $this->object->getDefaultMemberRole();
		$local_roles = $this->object->getLocalCourseRoles(false);
		$crs_roles = array();

		//put the course member role to the top of the crs_roles array
		if(in_array($crs_member, $local_roles))
		{
			$crs_roles[$crs_member] = ilObjRole::_getTranslation(array_search ($crs_member, $local_roles));
			unset($local_roles[$crs_roles[$crs_member]]);
		}

		foreach($local_roles as $title => $role_id)
		{
			if($role_id == $crs_admin && !$this->hasAdminPermission())
			{
				continue;
			}

			$crs_roles[$role_id] = ilObjRole::_getTranslation($title);
		}

		if(count($a_exclude) > 0)
		{
			foreach($a_exclude as $excluded_role)
			{
				if(isset($crs_roles[$excluded_role]))
				{
					unset($crs_roles[$excluded_role]);
				}
			}
		}
		return $crs_roles;
	}

	/**
	 * user has admin permission or "edit permission" permission on this course
	 * @return bool
	 */
	protected function hasAdminPermission()
	{
		global $ilUser;
		return ilCourseParticipant::_getInstanceByObjId($this->object->getId(), $ilUser->getId())->isAdmin()
		or $this->checkPermissionBool('edit_permission');
	}

	protected function mailMembersBtnObject()
	{
		global $ilToolbar;
		$this->checkPermission('read');

		$this->tabs_gui->setTabActive('members');

		$this->addMailToMemberButton($ilToolbar, "mailMembersBtn");
	}

	/**
	 * add Mail to Member button to toolbar
	 *
	 * @param ilToolbarGUI $ilToolbar
	 * @param string $back_cmd
	 * @param bool $a_separator
	 */
	protected function addMailToMemberButton($ilToolbar, $back_cmd = null, $a_separator = false)
	{
		global $ilUser, $rbacsystem, $ilAccess;
		include_once 'Services/Mail/classes/class.ilMail.php';
		$mail = new ilMail($ilUser->getId());

		if(
			($this->object->getMailToMembersType() == ilCourseConstants::MAIL_ALLOWED_ALL or
				$ilAccess->checkAccess('write',"",$this->object->getRefId())) and
			$rbacsystem->checkAccess('internal_mail',$mail->getMailObjectReferenceId()))
		{

			if($a_separator)
			{
				$ilToolbar->addSeparator();
			}

			if($back_cmd)
			{
				$this->ctrl->setParameter($this, "back_cmd", $back_cmd);
			}

			$ilToolbar->addButton($this->lng->txt("mail_members"),
				$this->ctrl->getLinkTargetByClass('ilMailMemberSearchGUI','')
			);
		}
	}

	/**
	 * 
	 */
	protected function jump2UsersGalleryObject()
	{
		$this->ctrl->redirectByClass('ilUsersGalleryGUI');
	}

	public function confirmRefuseSubscribersObject()
	{
		if(!is_array($_POST["subscribers"]))
		{
			ilUtil::sendFailure($this->lng->txt("crs_no_subscribers_selected"));
			$this->membersObject();

			return false;
		}

		$this->lng->loadLanguageModule('mmbr');

		$this->checkPermission('write');
		$this->setSubTabs('members');
		$this->tabs_gui->setTabActive('members');
		$this->tabs_gui->setSubTabActive('crs_member_administration');

		include_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
		$c_gui = new ilConfirmationGUI();

		// set confirm/cancel commands
		$c_gui->setFormAction($this->ctrl->getFormAction($this, "refuseSubscribers"));
		$c_gui->setHeaderText($this->lng->txt("info_refuse_sure"));
		$c_gui->setCancel($this->lng->txt("cancel"), "members");
		$c_gui->setConfirm($this->lng->txt("confirm"), "refuseSubscribers");

		foreach($_POST["subscribers"] as $subscribers)
		{
			$name = ilObjUser::_lookupName($subscribers);

			$c_gui->addItem('subscribers[]',
							$name['user_id'],
							$name['lastname'].', '.$name['firstname'].' ['.$name['login'].']',
							ilUtil::getImagePath('icon_usr.svg'));
		}

		$this->tpl->setContent($c_gui->getHTML());
		return true;
	}

	public function confirmAssignSubscribersObject()
	{
		if(!is_array($_POST["subscribers"]))
		{
			ilUtil::sendFailure($this->lng->txt("crs_no_subscribers_selected"));
			$this->membersObject();

			return false;
		}
		$this->checkPermission('write');
		$this->setSubTabs('members');
		$this->tabs_gui->setTabActive('members');
		$this->tabs_gui->setSubTabActive('crs_member_administration');

		include_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
		$c_gui = new ilConfirmationGUI();

		// set confirm/cancel commands
		$c_gui->setFormAction($this->ctrl->getFormAction($this, "assignSubscribers"));
		$c_gui->setHeaderText($this->lng->txt("info_assign_sure"));
		$c_gui->setCancel($this->lng->txt("cancel"), "members");
		$c_gui->setConfirm($this->lng->txt("confirm"), "assignSubscribers");

		foreach($_POST["subscribers"] as $subscribers)
		{
			$name = ilObjUser::_lookupName($subscribers);

			$c_gui->addItem('subscribers[]',
							$name['user_id'],
							$name['lastname'].', '.$name['firstname'].' ['.$name['login'].']',
							ilUtil::getImagePath('icon_usr.svg'));
		}

		$this->tpl->setContent($c_gui->getHTML());
		return true;
	}

	public function confirmRefuseFromListObject()
	{
		if(!is_array($_POST["waiting"]))
		{
			ilUtil::sendFailure($this->lng->txt("no_checkbox"));
			$this->membersObject();

			return false;
		}

		$this->lng->loadLanguageModule('mmbr');

		$this->checkPermission('write');
		$this->setSubTabs('members');
		$this->tabs_gui->setTabActive('members');
		$this->tabs_gui->setSubTabActive('crs_member_administration');

		include_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
		$c_gui = new ilConfirmationGUI();

		// set confirm/cancel commands
		$c_gui->setFormAction($this->ctrl->getFormAction($this, "refuseFromList"));
		$c_gui->setHeaderText($this->lng->txt("info_refuse_sure"));
		$c_gui->setCancel($this->lng->txt("cancel"), "members");
		$c_gui->setConfirm($this->lng->txt("confirm"), "refuseFromList");

		foreach($_POST["waiting"] as $waiting)
		{
			$name = ilObjUser::_lookupName($waiting);

			$c_gui->addItem('waiting[]',
							$name['user_id'],
							$name['lastname'].', '.$name['firstname'].' ['.$name['login'].']',
							ilUtil::getImagePath('icon_usr.svg'));
		}

		$this->tpl->setContent($c_gui->getHTML());
		return true;
	}

	public function confirmAssignFromWaitingListObject()
	{
		if(!is_array($_POST["waiting"]))
		{
			ilUtil::sendFailure($this->lng->txt("crs_no_users_selected"));
			$this->membersObject();

			return false;
		}
		$this->checkPermission('write');
		$this->setSubTabs('members');
		$this->tabs_gui->setTabActive('members');
		$this->tabs_gui->setSubTabActive('crs_member_administration');

		include_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
		$c_gui = new ilConfirmationGUI();

		// set confirm/cancel commands
		$c_gui->setFormAction($this->ctrl->getFormAction($this, "assignFromWaitingList"));
		$c_gui->setHeaderText($this->lng->txt("info_assign_sure"));
		$c_gui->setCancel($this->lng->txt("cancel"), "members");
		$c_gui->setConfirm($this->lng->txt("confirm"), "assignFromWaitingList");

		foreach($_POST["waiting"] as $waiting)
		{
			$name = ilObjUser::_lookupName($waiting);

			$c_gui->addItem('waiting[]',
							$name['user_id'],
							$name['lastname'].', '.$name['firstname'].' ['.$name['login'].']',
							ilUtil::getImagePath('icon_usr.svg'));
		}

		$this->tpl->setContent($c_gui->getHTML());
		return true;
	}
} // END class.ilObjCourseGUI
?>
