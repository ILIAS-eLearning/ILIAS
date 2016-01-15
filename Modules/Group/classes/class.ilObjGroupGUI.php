<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Container/classes/class.ilContainerGUI.php";
include_once('./Modules/Group/classes/class.ilObjGroup.php');

/**
* Class ilObjGroupGUI
*
* @author	Stefan Meyer <smeyer.ilias@gmx.de>
* @author	Sascha Hofmann <saschahofmann@gmx.de>
*
* @version	$Id$
*
* @ilCtrl_Calls ilObjGroupGUI: ilGroupRegistrationGUI, ilPermissionGUI, ilInfoScreenGUI,, ilLearningProgressGUI
* @ilCtrl_Calls ilObjGroupGUI: ilRepositorySearchGUI, ilPublicUserProfileGUI, ilObjCourseGroupingGUI, ilObjStyleSheetGUI
* @ilCtrl_Calls ilObjGroupGUI: ilCourseContentGUI, ilColumnGUI, ilContainerPageGUI, ilObjectCopyGUI
* @ilCtrl_Calls ilObjGroupGUI: ilObjectCustomUserFieldsGUI, ilMemberAgreementGUI, ilExportGUI, ilMemberExportGUI
* @ilCtrl_Calls ilObjGroupGUI: ilCommonActionDispatcherGUI, ilObjectServiceSettingsGUI, ilSessionOverviewGUI
* @ilCtrl_Calls ilObjGroupGUI: ilMailMemberSearchGUI
* 
*
* @extends ilObjectGUI
*/
class ilObjGroupGUI extends ilContainerGUI
{
	/**
	* Constructor
	* @access	public
	*/
	public function __construct($a_data,$a_id,$a_call_by_reference,$a_prepare_output = false)
	{
		$this->type = "grp";
		$this->ilContainerGUI($a_data,$a_id,$a_call_by_reference,$a_prepare_output);

		$this->lng->loadLanguageModule('grp');
	}

	function &executeCommand()
	{
		global $ilUser,$rbacsystem,$ilAccess, $ilNavigationHistory,$ilErr, $ilCtrl, $ilToolbar;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();
		$this->prepareOutput();
		
		// show repository tree
		$this->showRepTree();

		// add entry to navigation history
		if (!$this->getCreationMode() &&
			$ilAccess->checkAccess("read", "", $_GET["ref_id"]))
		{
			include_once("./Services/Link/classes/class.ilLink.php");
			$ilNavigationHistory->addItem($_GET["ref_id"],
				ilLink::_getLink($_GET["ref_id"], "grp"), "grp");
		}

		switch($next_class)
		{			
			case 'ilgroupregistrationgui':
				$this->ctrl->setReturn($this,'');
				$this->tabs_gui->setTabActive('join');
				include_once('./Modules/Group/classes/class.ilGroupRegistrationGUI.php');
				$registration = new ilGroupRegistrationGUI($this->object);
				$this->ctrl->forwardCommand($registration);
				break;

			case 'ilusersgallerygui':
				$is_participant = (bool)ilGroupParticipants::_isParticipant($this->ref_id, $ilUser->getId());
				if(!$ilAccess->checkAccess('write', '', $this->ref_id) && !$is_participant)
				{
					$ilErr->raiseError($this->lng->txt('msg_no_perm_read'), $ilErr->MESSAGE);
				}

				$this->addMailToMemberButton($ilToolbar, 'jump2UsersGallery');

				require_once 'Services/User/classes/class.ilUsersGalleryParticipants.php';
				require_once 'Services/User/classes/class.ilUsersGalleryGUI.php';
				$this->setSubTabs('members');
				$this->tabs_gui->setTabActive('members');
				$this->tabs_gui->setSubTabActive('grp_members_gallery');

				$provider = new ilUsersGalleryParticipants($this->object->members_obj);
				$gallery_gui = new ilUsersGalleryGUI($provider);
				$this->ctrl->forwardCommand($gallery_gui);
				break;

			case 'ilpermissiongui':
				$this->tabs_gui->setTabActive('perm_settings');
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret =& $this->ctrl->forwardCommand($perm_gui);
				break;

			case 'ilrepositorysearchgui':
				include_once('./Services/Search/classes/class.ilRepositorySearchGUI.php');
				$rep_search =& new ilRepositorySearchGUI();
				$rep_search->setCallback($this,
					'addUserObject',
					$this->getLocalRoles()
					);

				// Set tabs
				$this->tabs_gui->setTabActive('members');
				$this->ctrl->setReturn($this,'members');
				$ret =& $this->ctrl->forwardCommand($rep_search);
				$this->setSubTabs('members');
				$this->tabs_gui->setSubTabActive('members');
				break;

			case "ilinfoscreengui":
				$ret =& $this->infoScreen();
				break;

			case "illearningprogressgui":
				include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';

				$new_gui =& new ilLearningProgressGUI(ilLearningProgressGUI::LP_CONTEXT_REPOSITORY,
													  $this->object->getRefId(),
													  $_GET['user_id'] ? $_GET['user_id'] : $ilUser->getId());
				$this->ctrl->forwardCommand($new_gui);
				$this->tabs_gui->setTabActive('learning_progress');
				break;

			case 'ilobjcoursegroupinggui':
				$this->setSubTabs('settings');
				
				include_once './Modules/Course/classes/class.ilObjCourseGroupingGUI.php';
				$this->ctrl->setReturn($this,'edit');
				$crs_grp_gui =& new ilObjCourseGroupingGUI($this->object,(int) $_GET['obj_id']);
				$this->ctrl->forwardCommand($crs_grp_gui);
				
				$this->tabs_gui->setTabActive('settings');
				$this->tabs_gui->setSubTabActive('groupings');				
				break;

			case 'ilcoursecontentgui':

				include_once './Modules/Course/classes/class.ilCourseContentGUI.php';
				$course_content_obj = new ilCourseContentGUI($this);
				$this->ctrl->forwardCommand($course_content_obj);
				break;

			case 'ilpublicuserprofilegui':
				require_once './Services/User/classes/class.ilPublicUserProfileGUI.php';
				$this->setSubTabs('members');
				$this->tabs_gui->setTabActive('group_members');
				$this->tabs_gui->setSubTabActive('grp_members_gallery');
				$profile_gui = new ilPublicUserProfileGUI($_GET["user"]);
				$profile_gui->setBackUrl($this->ctrl->getLinkTargetByClass("ilUsersGalleryGUI",'view'));
				$html = $this->ctrl->forwardCommand($profile_gui);
				$this->tpl->setVariable("ADM_CONTENT", $html);
				break;

			case "ilcolumngui":
				$this->tabs_gui->setTabActive('none');
				$this->checkPermission("read");
				include_once("./Services/Style/classes/class.ilObjStyleSheet.php");
				$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
					ilObjStyleSheet::getContentStylePath($this->object->getStyleSheetId()));
				$this->renderObject();
				break;

			// container page editing
			case "ilcontainerpagegui":
				$ret = $this->forwardToPageObject();
				if ($ret != "")
				{
					$this->tpl->setContent($ret);
				}
				break;

			case 'ilobjectcopygui':
				include_once './Services/Object/classes/class.ilObjectCopyGUI.php';
				$cp = new ilObjectCopyGUI($this);
				$cp->setType('grp');
				$this->ctrl->forwardCommand($cp);
				break;

			case "ilobjstylesheetgui":
				$this->forwardToStyleSheet();
				break;
				
			case 'ilobjectcustomuserfieldsgui':
				if(isset($_REQUEST['member_id']))
				{
					$this->ctrl->setReturn($this,'members');
				}
				include_once './Services/Membership/classes/class.ilObjectCustomUserFieldsGUI.php';
				$cdf_gui = new ilObjectCustomUserFieldsGUI($this->object->getId());
				$this->setSubTabs('settings');
				$this->tabs_gui->setTabActive('settings');
				$this->ctrl->forwardCommand($cdf_gui);
				break;
				
			case 'ilmemberagreementgui':
				include_once('Services/Membership/classes/class.ilMemberAgreementGUI.php');
				$this->ctrl->setReturn($this,'');
				$this->tabs_gui->setTabActive('view_content');
				$agreement = new ilMemberAgreementGUI($this->object->getRefId());
				$this->ctrl->forwardCommand($agreement);
				break;

			case 'ilexportgui':
				$this->tabs_gui->setTabActive('export');
				include_once './Services/Export/classes/class.ilExportGUI.php';
				$exp = new ilExportGUI($this);
				$exp->addFormat('xml');
				$this->ctrl->forwardCommand($exp);
				break;
				
			case 'ilmemberexportgui':
				include_once('./Services/Membership/classes/Export/class.ilMemberExportGUI.php');
				
				$this->setSubTabs('members');
				$this->tabs_gui->setTabActive('members');
				$this->tabs_gui->setSubTabActive('export_members');
				$export = new ilMemberExportGUI($this->object->getRefId());
				$this->ctrl->forwardCommand($export);
				break;
				
			case "ilcommonactiondispatchergui":
				include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
				$gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
				$this->ctrl->forwardCommand($gui);
				break;
			
			case 'ilobjectservicesettingsgui':
				$this->ctrl->setReturn($this,'edit');
				$this->setSubTabs("settings");
				$this->tabs_gui->activateTab('settings');
				$this->tabs_gui->activateSubTab('tool_settings');
				
				include_once './Services/Object/classes/class.ilObjectServiceSettingsGUI.php';
				$service = new ilObjectServiceSettingsGUI(
						$this,
						$this->object->getId(),
						array(
							ilObjectServiceSettingsGUI::CALENDAR_VISIBILITY
						));
				$this->ctrl->forwardCommand($service);
				break;
			
			case 'ilsessionoverviewgui':								
				$this->setSubTabs('members');
				$this->tabs_gui->setTabActive('members');
				$this->tabs_gui->setSubTabActive('events');
				
				include_once './Modules/Group/classes/class.ilGroupParticipants.php';
				$prt = ilGroupParticipants::_getInstanceByObjId($this->object->getId());
				
				include_once('./Modules/Session/classes/class.ilSessionOverviewGUI.php');
				$overview = new ilSessionOverviewGUI($this->object->getRefId(), $prt);
				$this->ctrl->forwardCommand($overview);				
				break;
			case 'ilmailmembersearchgui':
				include_once 'Services/Mail/classes/class.ilMail.php';
				$mail = new ilMail($ilUser->getId());

				if(!($ilAccess->checkAccess('write','',$this->object->getRefId()) ||
					$this->object->getMailToMembersType() == ilObjGroup::MAIL_ALLOWED_ALL) &&
					$rbacsystem->checkAccess('internal_mail',$mail->getMailObjectReferenceId()))
				{
					$ilErr->raiseError($this->lng->txt("msg_no_perm_read"),$ilErr->MESSAGE);
				}

				$this->tabs_gui->setTabActive('members');
				
				include_once './Services/Contact/classes/class.ilMailMemberSearchGUI.php';
				include_once './Services/Contact/classes/class.ilMailMemberGroupRoles.php';

				$mail_search = new ilMailMemberSearchGUI($this->object->getRefId(), new ilMailMemberGroupRoles());
				$mail_search->setObjParticipants(ilCourseParticipants::_getInstanceByObjId($this->object->getId()));
				$this->ctrl->forwardCommand($mail_search);
				break;
			default:
			
				// check visible permission
				if (!$this->getCreationMode() and
						!$ilAccess->checkAccess('visible','',$this->object->getRefId(),'grp') and
						!$ilAccess->checkAccess('read','',$this->object->getRefId(),'grp') )
				{
					$ilErr->raiseError($this->lng->txt("msg_no_perm_read"),$ilErr->MESSAGE);
				}
				
				// #9401 - see also ilStartupGUI::_checkGoto()
				if($cmd == 'infoScreenGoto')
				{										
					if($this->object->isRegistrationEnabled())
					{
						$cmd = 'join';						
					}
					else
					{
						$cmd = 'infoScreen';
					}
				}

				// check read permission
				if ((!$this->getCreationMode()
					&& !$rbacsystem->checkAccess('read',$this->object->getRefId()) && $cmd != 'infoScreen')
					|| $cmd == 'join')
				{
					// no join permission -> redirect to info screen
					if (!$rbacsystem->checkAccess('join',$this->object->getRefId()))
					{
						$this->ctrl->redirect($this, "infoScreen");
					}
					else	// no read -> show registration
					{
						include_once('./Modules/Group/classes/class.ilGroupRegistrationGUI.php');
						$this->ctrl->redirectByClass("ilGroupRegistrationGUI", "show");
					}
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
	}
	
	function viewObject()
	{
		global $tree,$rbacsystem,$ilUser;

		include_once 'Services/Tracking/classes/class.ilLearningProgress.php';
		ilLearningProgress::_tracProgress($ilUser->getId(),$this->object->getId(),
			$this->object->getRefId(),'grp');

		if (strtolower($_GET["baseClass"]) == "iladministrationgui")
		{
			parent::viewObject();
			return true;
		}
		
		if(!$this->checkAgreement())
		{
			include_once('Services/Membership/classes/class.ilMemberAgreementGUI.php');
			$this->tabs_gui->setTabActive('view_content');
			$this->ctrl->setReturn($this,'view');
			$agreement = new ilMemberAgreementGUI($this->object->getRefId());
			$this->ctrl->setCmdClass(get_class($agreement));
			$this->ctrl->forwardCommand($agreement);
			return true;
		}
		
		$this->tabs_gui->setTabActive('view_content');
		$this->renderObject();
	}
	
	/**
	* Render group
	*/
	function renderObject()
	{
		global $ilTabs;
		
		$ilTabs->activateTab("view_content");
		$ret =  parent::renderObject();
		return $ret;

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
			ilObjCourseGUI::_modifyItemGUI($a_item_list_gui,'ilcoursecontentgui',$a_item_data, $a_show_path,
				ilObjCourse::_lookupAboStatus($course_obj_id), $course_ref_id, $course_obj_id,
				$this->object->getRefId());
		}
	}
	
	protected function initCreateForm($a_new_type)
	{
		if(!is_object($this->object))
		{
			$this->object = new ilObjGroup();
		}
		
		return $this->initForm('create');
	}
	
	/**
	 * save object
	 *
	 * @global ilTree
	 * @access public
	 * @return
	 */
	public function saveObject()
	{
		global $ilErr,$ilUser,$tree,$ilSetting;
		
		$this->object = new ilObjGroup();

		// we reduced the form, only 3 values left
		// $this->load();
		
		$grp_type = ilUtil::stripSlashes($_POST['grp_type']);
		switch($grp_type)
		{
			case GRP_TYPE_PUBLIC:
				$this->object->setRegistrationType(GRP_REGISTRATION_DIRECT);
				break;
			
			default:
				$this->object->setRegistrationType(GRP_REGISTRATION_DEACTIVATED);
				break;
		}
		$this->object->setTitle(ilUtil::stripSlashes($_POST['title']));
		$this->object->setDescription(ilUtil::stripSlashes($_POST['desc']));
		$this->object->setGroupType(ilUtil::stripSlashes($_POST['grp_type']));
		$this->object->setViewMode(ilContainer::VIEW_INHERIT);

		$ilErr->setMessage('');
		
		if(!$this->object->validate())
		{
			$err = $this->lng->txt('err_check_input');
			ilUtil::sendFailure($err);
			$err = $ilErr->getMessage();
			ilUtil::sendInfo($err);
			$this->createObject();
			return true;
		}

		$this->object->create();
		$this->putObjectInTree($this->object, $_GET["ref_id"]);
		$this->object->initGroupStatus($this->object->getGroupType());
		
		// check for parent group or course => SORT_INHERIT
		$sort_mode = ilContainer::SORT_TITLE;
		if(
				$GLOBALS['tree']->checkForParentType($this->object->getRefId(),'crs') ||
				$GLOBALS['tree']->checkForParentType($this->object->getRefId(),'grp')
		)
		{
			$sort_mode = ilContainer::SORT_INHERIT;
		}
		// Save sorting
		include_once './Services/Container/classes/class.ilContainerSortingSettings.php';
		$sort = new ilContainerSortingSettings($this->object->getId());
		$sort->setSortMode($sort_mode);
		$sort->update();
		
		
		// Add user as admin and enable notification
		include_once('./Modules/Group/classes/class.ilGroupParticipants.php');
		$members_obj = ilGroupParticipants::_getInstanceByObjId($this->object->getId());
		$members_obj->add($ilUser->getId(),IL_GRP_ADMIN);
		$members_obj->updateNotification($ilUser->getId(),$ilSetting->get('mail_grp_admin_notification', true));
		

		ilUtil::sendSuccess($this->lng->txt("grp_added"),true);		
		$this->ctrl->setParameter($this,'ref_id',$this->object->getRefId());
		$this->ctrl->redirect($this, "edit");
	}
	
	/**
	 * Edit object
	 *
	 * @access public
	 * @param ilPropertyFormGUI 
	 * @return
	 */
	public function editObject(ilPropertyFormGUI $a_form = null)
	{
		$this->checkPermission("write");
		
		$this->setSubTabs('settings');
		$this->tabs_gui->setTabActive('settings');
		$this->tabs_gui->setSubTabActive('grp_settings');

		if(!$a_form)
		{
			$a_form = $this->initForm('edit');
		}

		$this->tpl->setVariable('ADM_CONTENT', $a_form->getHTML());
	}
	
	/**
	 * change group type
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function updateGroupTypeObject()
	{
		$type = $this->object->getGroupType() ? 
			$this->object->getGroupType() :
			$this->object->readGroupStatus();
			
		if($type == GRP_TYPE_PUBLIC)
		{
			$this->object->setGroupType(GRP_TYPE_CLOSED);
		}
		else
		{
			$this->object->setGroupType(GRP_TYPE_PUBLIC);
		}
		$this->object->updateGroupType();
		$this->object->update();
		ilUtil::sendSuccess($this->lng->txt('settings_saved'),true);
		$this->ctrl->redirect($this,'edit');
	}
	
	
	/**
	* update GroupObject
	* @param bool update group type
	* @access public
	*/
	public function updateObject()
	{
		global $ilErr;

		$this->checkPermission('write');
		
		
		$form = $this->initForm();
		$form->checkInput();
		
		$old_type = $this->object->getGroupType();
		$old_autofill = $this->object->hasWaitingListAutoFill();
		
		$this->load($form);
		$ilErr->setMessage('');
		
		if(!$this->object->validate())
		{
			/*
			$err = $this->lng->txt('err_check_input');
			ilUtil::sendFailure($err);
			$err = $ilErr->getMessage();
			ilUtil::sendInfo($err);			
			*/
			ilUtil::sendFailure($ilErr->getMessage()); // #16975
			
			// #17144
			$form->setValuesByPost();
			$this->editObject($form); 
			return true;
		}

		$modified = false;		
		if($this->object->isGroupTypeModified($old_type))
		{
			$modified = true;
			$this->object->setGroupType($old_type);
		}
		
		$this->object->update();
		
		
		include_once './Services/Object/classes/class.ilObjectServiceSettingsGUI.php';
		ilObjectServiceSettingsGUI::updateServiceSettingsForm(
			$this->object->getId(),
			$form,
			array(
				ilObjectServiceSettingsGUI::CALENDAR_VISIBILITY,
				ilObjectServiceSettingsGUI::NEWS_VISIBILITY,
				ilObjectServiceSettingsGUI::AUTO_RATING_NEW_OBJECTS,
				ilObjectServiceSettingsGUI::TAG_CLOUD
			)
		);
			
		// Save sorting
		$this->saveSortingSettings($form);
		
		// if autofill has been activated trigger process
		if(!$old_autofill &&
			$this->object->hasWaitingListAutoFill())
		{
			$this->object->handleAutoFill();
		}

		// BEGIN ChangeEvents: Record update Object.
		require_once('Services/Tracking/classes/class.ilChangeEvent.php');
		global $ilUser;
		ilChangeEvent::_recordWriteEvent($this->object->getId(), $ilUser->getId(), 'update');
		ilChangeEvent::_catchupWriteEvents($this->object->getId(), $ilUser->getId());		
		// END PATCH ChangeEvents: Record update Object.
		
		// Update ecs export settings
		include_once 'Modules/Group/classes/class.ilECSGroupSettings.php';	
		$ecs = new ilECSGroupSettings($this->object);			
		$ecs->handleSettingsUpdate();

		if($modified)
		{
			include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
			ilUtil::sendQuestion($this->lng->txt('grp_warn_grp_type_changed'));
			$confirm = new ilConfirmationGUI();
			$confirm->setFormAction($this->ctrl->getFormAction($this));
			$confirm->addItem(
				'grp_type',
				$this->object->getGroupType(),
				$this->lng->txt('grp_info_new_grp_type').': '.($this->object->getGroupType() == GRP_TYPE_CLOSED ? $this->lng->txt('il_grp_status_open') : $this->lng->txt('il_grp_status_closed'))
			);
			$confirm->addButton($this->lng->txt('grp_change_type'), 'updateGroupType');
			$confirm->setCancel($this->lng->txt('cancel'), 'edit');
			
			$this->tpl->setContent($confirm->getHTML());
			return true;
		}
		else
		{
			ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"),true);
			$this->ctrl->redirect($this,'edit');
			return true;
		}
	}
	
	/**
	* edit container icons
	*/
	public function editGroupIconsObject($a_form = null)
	{
		global $tpl;

		$this->checkPermission('write');
		
		$this->setSubTabs("settings");
		$this->tabs_gui->setTabActive('settings');
		$this->tabs_gui->setSubTabActive('grp_icon_settings');

		if(!$a_form)
		{
			$a_form = $this->initGroupIconsForm();
		}
		
		$tpl->setContent($a_form->getHTML());
	}
	
	function initGroupIconsForm()
	{
		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this));	
		
		$this->showCustomIconsEditing(1, $form);
		
		// $form->setTitle($this->lng->txt('edit_grouping'));
		$form->addCommandButton('updateGroupIcons', $this->lng->txt('save'));					
		
		return $form;
	}
	
	/**
	 * update group icons
	 *
	 * @access public
	 * @return
	 */
	public function updateGroupIconsObject()
	{
		global $ilSetting;

		$this->checkPermission('write');
		
		$form = $this->initGroupIconsForm();
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
			$this->ctrl->redirect($this,"editGroupIcons");
		}

		$form->setValuesByPost();
		$this->editGroupIconsObject($form);	
	}
	
	/**
	* Edit Map Settings
	*/
	public function editMapSettingsObject()
	{
		global $ilUser, $ilCtrl, $ilUser, $ilAccess;

		$this->setSubTabs("settings");
		$this->tabs_gui->setTabActive('settings');
		$this->tabs_gui->setSubTabActive('grp_map_settings');
		
		include_once('./Services/Maps/classes/class.ilMapUtil.php');
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
		
		$form->setTitle($this->lng->txt("grp_map_settings"));
			
		// enable map
		$public = new ilCheckboxInputGUI($this->lng->txt("grp_enable_map"),
			"enable_map");
		$public->setValue("1");
		$public->setChecked($this->object->getEnableGroupMap());
		$form->addItem($public);

		// map location
		$loc_prop = new ilLocationInputGUI($this->lng->txt("grp_map_location"),
			"location");
		$loc_prop->setLatitude($latitude);
		$loc_prop->setLongitude($longitude);
		$loc_prop->setZoom($zoom);
		$form->addItem($loc_prop);
		
		$form->addCommandButton("saveMapSettings", $this->lng->txt("save"));
		
		$this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
	}

	public function saveMapSettingsObject()
	{
		global $ilCtrl, $ilUser;

		$this->object->setLatitude(ilUtil::stripSlashes($_POST["location"]["latitude"]));
		$this->object->setLongitude(ilUtil::stripSlashes($_POST["location"]["longitude"]));
		$this->object->setLocationZoom(ilUtil::stripSlashes($_POST["location"]["zoom"]));
		$this->object->setEnableGroupMap(ilUtil::stripSlashes($_POST["enable_map"]));
		$this->object->update();
		
		$ilCtrl->redirect($this, "editMapSettings");
	}
	
	/**
	* Members map
	*/
	public function membersMapObject()
	{
		global $tpl;
		
		$this->setSubTabs('members');
		$this->tabs_gui->setTabActive('members');
		
		include_once("./Services/Maps/classes/class.ilMapUtil.php");
		if (!ilMapUtil::isActivated() || !$this->object->getEnableGroupMap())
		{
			return;
		}
		
		$map = ilMapUtil::getMapGUI();
		$map->setMapId("group_map")
			->setWidth("700px")
			->setHeight("500px")
			->setLatitude($this->object->getLatitude())
			->setLongitude($this->object->getLongitude())
			->setZoom($this->object->getLocationZoom())
			->setEnableTypeControl(true)
			->setEnableNavigationControl(true)
			->setEnableCentralMarker(true);
		
		
		$member_ids = $this->object->getGroupMemberIds();
		$admin_ids = $this->object->getGroupAdminIds();
		
		// fetch all users data in one shot to improve performance
		$members = $this->object->getGroupMemberData($member_ids);
		foreach($member_ids as $user_id)
		{
			$map->addUserMarker($user_id);
		}
		$tpl->setContent($map->getHTML());
		$tpl->setLeftContent($map->getUserListHTML());
	}
	
	
	/**
	 * edit info
	 *
	 * @access public
	 * @return
	 */
	public function editInfoObject()
	{
		global $ilErr,$ilAccess;

		$this->checkPermission('write');
		
		$this->setSubTabs('settings');
		$this->tabs_gui->setTabActive('settings');
		$this->tabs_gui->setSubTabActive('grp_info_settings');
	 	
	 	$form = $this->initInfoEditor();
		$this->tpl->setContent($form->getHTML());
	}
	
	/**
	 * init info editor
	 *
	 * @access protected
	 * @return
	 */
	protected function initInfoEditor()
	{		
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($this->ctrl->getFormAction($this,'updateInfo'));
		$form->setTitle($this->lng->txt('grp_general_informations'));
		$form->addCommandButton('updateInfo',$this->lng->txt('save'));
		$form->addCommandButton('cancel',$this->lng->txt('cancel'));
		
		$area = new ilTextAreaInputGUI($this->lng->txt('grp_information'),'important');
		$area->setInfo($this->lng->txt('grp_information_info'));
		$area->setValue($this->object->getInformation());
		$area->setRows(8);
		$area->setCols(80);
		$form->addItem($area);
		
		return $form;
	}
	
	/**
	 * update info 
	 *
	 * @access public
	 * @return
	 */
	public function updateInfoObject()
	{
		$this->checkPermission('write');
		
		$this->object->setInformation(ilUtil::stripSlashes($_POST['important']));
		$this->object->update();
		
		ilUtil::sendSuccess($this->lng->txt("settings_saved"));
		$this->editInfoObject();
		return true;
	}
	
	/////////////////////////////////////////////////////////// Member section /////////////////////
	public function readMemberData($ids,$role = 'admin',$selected_columns = null)
	{
		include_once('./Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
		$privacy = ilPrivacySettings::_getInstance();

		if($this->show_tracking)
		{
			include_once 'Services/Tracking/classes/class.ilLPStatusWrapper.php';
			$completed = ilLPStatusWrapper::_lookupCompletedForObject($this->object->getId());
			$in_progress = ilLPStatusWrapper::_lookupInProgressForObject($this->object->getId());
			$failed = ilLPStatusWrapper::_lookupFailedForObject($this->object->getId());
		}
		
		if($privacy->enabledGroupAccessTimes())
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

		foreach($ids as $usr_id)
		{
			$name = ilObjUser::_lookupName($usr_id);
			$tmp_data['firstname'] = $name['firstname'];
			$tmp_data['lastname'] = $name['lastname'];
			$tmp_data['login'] = ilObjUser::_lookupLogin($usr_id);
			$tmp_data['notification'] = $this->object->members_obj->isNotificationEnabled($usr_id) ? 1 : 0;
			$tmp_data['usr_id'] = $usr_id;
			$tmp_data['login'] = ilObjUser::_lookupLogin($usr_id);

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

			if($privacy->enabledGroupAccessTimes())
			{
				if(isset($progress[$usr_id]['ts']) and $progress[$usr_id]['ts'])
				{
					$tmp_data['access_time'] = ilDatePresentation::formatDate(
						$tmp_date = new ilDateTime($progress[$usr_id]['ts'],IL_CAL_UNIX));
					$tmp_data['access_time_unix'] = $tmp_date->get(IL_CAL_UNIX);
				}
				else
				{
					$tmp_data['access_time'] = $this->lng->txt('no_date');
					$tmp_data['access_time_unix'] = 0;
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
	 * edit members
	 *
	 * @access public
	 * @return
	 */
	public function membersObject()
	{
		global $ilUser, $ilToolbar, $lng, $ilCtrl;
		
		include_once('./Modules/Group/classes/class.ilGroupParticipants.php');
		include_once('./Modules/Group/classes/class.ilGroupParticipantsTableGUI.php');
		
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
		
		$part = ilGroupParticipants::_getInstanceByObjId($this->object->getId());

		$this->setSubTabs('members');
		$this->tabs_gui->setTabActive('members');
		$this->tabs_gui->setSubTabActive('grp_edit_members');
		
		$this->tpl->addBlockfile('ADM_CONTENT','adm_content','tpl.grp_edit_members.html','Modules/Group');
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
		$ilToolbar->addButton($this->lng->txt("grp_search_users"),
			$this->ctrl->getLinkTargetByClass('ilRepositorySearchGUI','start'));
		
		$ilToolbar->addSeparator();
			
		// print button
		$ilToolbar->addButton($this->lng->txt("grp_print_list"),
			$this->ctrl->getLinkTarget($this, 'printMembers'));

		$this->addMailToMemberButton($ilToolbar, "members", true);

		$this->setShowHidePrefs();
		
		
		// Waiting list table
		include_once('./Modules/Group/classes/class.ilGroupWaitingList.php');
		$waiting_list = new ilGroupWaitingList($this->object->getId());
		if(count($wait = $waiting_list->getAllUsers()))
		{
			include_once('./Services/Membership/classes/class.ilWaitingListTableGUI.php');
			if($ilUser->getPref('grp_wait_hide'))
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
			$table_gui->setTitle($this->lng->txt('grp_header_waiting_list'),'icon_usr.svg',$this->lng->txt('group_new_registrations'));
			$this->tpl->setVariable('TABLE_SUB',$table_gui->getHTML());
		}		

		
		// Subscriber table
		if($part->getSubscribers())
		{
			include_once('./Services/Membership/classes/class.ilSubscriberTableGUI.php');
			if($ilUser->getPref('grp_subscriber_hide'))
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

		if(count($part->getAdmins()))
		{
			if($ilUser->getPref('grp_admin_hide'))
			{
				$table_gui = new ilGroupParticipantsTableGUI($this,'admin',false,false);
				$this->ctrl->setParameter($this,'admin_hide',0);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('show'));
				$this->ctrl->clearParameters($this);
			}
			else
			{
				$table_gui = new ilGroupParticipantsTableGUI($this,'admin',true,false);
				$this->ctrl->setParameter($this,'admin_hide',1);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('hide'));
				$this->ctrl->clearParameters($this);
			}
			$table_gui->setTitle($this->lng->txt('grp_admins'),'icon_usr.svg',$this->lng->txt('grp_admins'));
			$table_gui->parse($this->readMemberData($part->getAdmins()));
			$this->tpl->setVariable('ADMINS',$table_gui->getHTML());	
		}
		
		if($GLOBALS['rbacreview']->getNumberOfAssignedUsers(array($this->object->getDefaultMemberRole())))
		{
			if($ilUser->getPref('grp_member_hide'))
			{
				$table_gui = new ilGroupParticipantsTableGUI($this,'member',false,$this->show_tracking,$this->object->getDEfaultMemberRole());
				$this->ctrl->setParameter($this,'member_hide',0);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('show'));
				$this->ctrl->clearParameters($this);
			}
			else
			{
				$table_gui = new ilGroupParticipantsTableGUI($this,'member',true,$this->show_tracking,$this->object->getDefaultMemberRole());
				$this->ctrl->setParameter($this,'member_hide',1);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('hide'));
				$this->ctrl->clearParameters($this);
			}
				
			$table_gui->setTitle($this->lng->txt('grp_members'),'icon_usr.svg',$this->lng->txt('grp_members'));
			$table_gui->parse($this->readMemberData($GLOBALS['rbacreview']->assignedUsers($this->object->getDefaultMemberRole()),
				null, $table_gui->getSelectedColumns()));
			$this->tpl->setCurrentBlock('member_block');
			$this->tpl->setVariable('MEMBERS',$table_gui->getHTML());	
			$this->tpl->parseCurrentBlock();
		}
		
		foreach(ilGroupParticipants::getMemberRoles($this->object->getRefId()) as $role_id)
		{
			// Do not show table if no user is assigned
			if(!($GLOBALS['rbacreview']->getNumberOfAssignedUsers(array($role_id))))
			{
				continue;
			}
			if($ilUser->getPref('grp_role_hide'.$role_id))
			{
				$table_gui = new ilGroupParticipantsTableGUI($this,'role',false,$this->show_tracking,$role_id);
				$this->ctrl->setParameter($this,'role_hide_'.$role_id,0);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('show'));
				$this->ctrl->clearParameters($this);
			}
			else
			{
				$table_gui = new ilGroupParticipantsTableGUI($this,'role',true,$this->show_tracking,$role_id);
				$this->ctrl->setParameter($this,'role_hide_'.$role_id,1);
				$table_gui->addHeaderCommand($this->ctrl->getLinkTarget($this,'members'),
					$this->lng->txt('hide'));
				$this->ctrl->clearParameters($this);
			}
				
			$table_gui->setTitle(ilObject::_lookupTitle($role_id),'icon_usr.gif',ilObject::_lookupTitle($role_id));
			$table_gui->parse($this->readMemberData($GLOBALS['rbacreview']->assignedUsers($role_id)));
			$this->tpl->setCurrentBlock('member_block');
			$this->tpl->setVariable('MEMBERS',$table_gui->getHTML());	
			$this->tpl->parseCurrentBlock();
		}

		$this->tpl->setVariable('TXT_SELECTED_USER',$this->lng->txt('grp_selected_users'));
		$this->tpl->setVariable('BTN_FOOTER_EDIT',$this->lng->txt('edit'));
		$this->tpl->setVariable('BTN_FOOTER_VAL',$this->lng->txt('remove'));
		$this->tpl->setVariable('BTN_FOOTER_MAIL',$this->lng->txt('grp_mem_send_mail'));
		$this->tpl->setVariable('ARROW_DOWN',ilUtil::getImagePath('arrow_downright.svg'));
		
	}
	
	/**
	 * assign subscribers
	 *
	 * @access public
	 * @return
	 */
	public function assignSubscribersObject()
	{
		global $lng, $ilIliasIniFile,$ilUser;

		$this->checkPermission('write');
		
		if(!count($_POST['subscribers']))
		{
			ilUtil::sendFailure($this->lng->txt('no_checkbox'));
			$this->membersObject();
			return false;
		}
		
		include_once './Modules/Group/classes/class.ilGroupMembershipMailNotification.php';
		foreach($_POST['subscribers'] as $usr_id)
		{
			$this->object->members_obj->sendNotification(
				ilGroupMembershipMailNotification::TYPE_ACCEPTED_SUBSCRIPTION_MEMBER,
				$usr_id
			);

			$this->object->members_obj->add($usr_id,IL_GRP_MEMBER);
			$this->object->members_obj->deleteSubscriber($usr_id);

		}
		ilUtil::sendSuccess($this->lng->txt("grp_msg_applicants_assigned"),true);
		$this->ctrl->redirect($this,'members');
		return true;
	}
	
	/**
	 * refuse subscribers
	 *
	 * @access public
	 * @return
	 */
	public function refuseSubscribersObject()
	{
		global $lng;

		$this->checkPermission('write');
		
		if(!count($_POST['subscribers']))
		{
			ilUtil::sendFailure($this->lng->txt('no_checkbox'));
			$this->membersObject();
			return false;
		}
		
		include_once './Modules/Group/classes/class.ilGroupMembershipMailNotification.php';
		foreach($_POST['subscribers'] as $usr_id)
		{
			$this->object->members_obj->sendNotification(
				ilGroupMembershipMailNotification::TYPE_REFUSED_SUBSCRIPTION_MEMBER,
				$usr_id
			);
			$this->object->members_obj->deleteSubscriber($usr_id);
		}
		ilUtil::sendSuccess($this->lng->txt("grp_msg_applicants_removed"));
		$this->membersObject();
		return true;
		
	}
	
	/**
	 * add from waiting list 
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function assignFromWaitingListObject()
	{
		$this->checkPermission('write');
		
		if(!count($_POST["waiting"]))
		{
			ilUtil::sendFailure($this->lng->txt("no_checkbox"));
			$this->membersObject();
			return false;
		}
		
		include_once('./Modules/Group/classes/class.ilGroupWaitingList.php');
		$waiting_list = new ilGroupWaitingList($this->object->getId());

		include_once './Modules/Group/classes/class.ilGroupMembershipMailNotification.php';

		$added_users = 0;
		foreach($_POST["waiting"] as $user_id)
		{
			if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user_id,false))
			{
				continue;
			}
			if($this->object->members_obj->isAssigned($user_id))
			{
				continue;
			}
			$this->object->members_obj->add($user_id,IL_GRP_MEMBER);
			$this->object->members_obj->sendNotification(
				ilGroupMembershipMailNotification::TYPE_ACCEPTED_SUBSCRIPTION_MEMBER,
				$user_id
			);
			$waiting_list->removeFromList($user_id);

			++$added_users;
		}
		if($added_users)
		{
			ilUtil::sendSuccess($this->lng->txt("grp_users_added"), true);
			$this->ctrl->redirect($this, "members");

			return true;
		}
		else
		{
			ilUtil::sendFailure($this->lng->txt("grp_users_already_assigned"));
			$this->searchObject();

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
		$this->checkPermission('write');
		
		if(!count($_POST['waiting']))
		{
			ilUtil::sendFailure($this->lng->txt('no_checkbox'));
			$this->membersObject();
			return false;
		}
		
		include_once('./Modules/Group/classes/class.ilGroupWaitingList.php');
		$waiting_list = new ilGroupWaitingList($this->object->getId());

		include_once './Modules/Group/classes/class.ilGroupMembershipMailNotification.php';
		foreach($_POST["waiting"] as $user_id)
		{
			$waiting_list->removeFromList($user_id);
			$this->object->members_obj->sendNotification(
				ilGroupMembershipMailNotification::TYPE_REFUSED_SUBSCRIPTION_MEMBER,
				$user_id
			);
		}
		
		ilUtil::sendSuccess($this->lng->txt('grp_users_removed_from_list'), true);
		$this->ctrl->redirect($this, "members");
		return true;
	}
	
	/**
	 * delete selected members
	 *
	 * @access public
	 */
	public function confirmDeleteMembersObject()
	{
		$this->checkPermission('write');
		
		$participants_to_delete = (array) array_unique(array_merge((array) $_POST['admins'],(array) $_POST['members'], (array) $_POST['roles']));
		
		if(!count($participants_to_delete))
		{
			ilUtil::sendFailure($this->lng->txt('no_checkbox'));
			$this->membersObject();
			return false;
		}
		
		// Check last admin
		$admins = (array) ilGroupParticipants::_getInstanceByObjId($this->object->getId())->getAdmins();
		
		$admins_after = (array) array_diff($admins, $participants_to_delete);
		if(!count($admins_after) and count($admins))		
		{
			ilUtil::sendFailure($this->lng->txt('grp_err_administrator_required'));
			$this->membersObject();
			return false;
		}

		$this->setSubTabs('members');
		$this->tabs_gui->setTabActive('members');
		$this->tabs_gui->setSubTabActive('grp_edit_members');
		
		include_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
		$confirm = new ilConfirmationGUI();
		$confirm->setFormAction($this->ctrl->getFormAction($this,'deleteMembers'));
		$confirm->setHeaderText($this->lng->txt('grp_dismiss_member'));
		$confirm->setConfirm($this->lng->txt('confirm'),'deleteMembers');
		$confirm->setCancel($this->lng->txt('cancel'),'members');
		
		foreach($this->readMemberData(array_merge((array) $_POST['admins'],(array) $_POST['members'], (array) $_POST['roles'])) as $participants)
		{
			$confirm->addItem('participants[]',
				$participants['usr_id'],
				$participants['lastname'].', '.$participants['firstname'].' ['.$participants['login'].']',
				ilUtil::getImagePath('icon_usr.svg'));
		}
		
		$this->tpl->setContent($confirm->getHTML());
	}
	
	/**
	 * delete members
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function deleteMembersObject()
	{
		$this->checkPermission('write');
		
		if(!count($_POST['participants']))
		{
			ilUtil::sendFailure($this->lng->txt('no_checkbox'));
			$this->membersObject();
			return true;
		}
	
		$this->object->members_obj->deleteParticipants($_POST['participants']);
		
		// Send notification
		include_once './Modules/Group/classes/class.ilGroupMembershipMailNotification.php';
		foreach($_POST['participants'] as $part)
		{
			$this->object->members_obj->sendNotification(
				ilGroupMembershipMailNotification::TYPE_DISMISS_MEMBER,
				$part
			);
		}
		
		
		ilUtil::sendSuccess($this->lng->txt("grp_msg_membership_annulled"), true);
		$this->ctrl->redirect($this, "members");
		return true;
	}
	
	/**
	 * show send mail
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function sendMailToSelectedUsersObject()
	{
		if(isset($_GET['member_id']))
		{
			$_POST['participants'] = array($_GET['member_id']);
		}
		else
		{
			$_POST['participants'] = array_unique(array_merge((array) $_POST['admins'],
				(array) $_POST['members'],
				(array) $_POST['roles'],
				(array) $_POST['waiting'],
				(array) $_POST['subscribers']));
		}
		if (!count($_POST['participants']))
		{
			ilUtil::sendFailure($this->lng->txt("no_checkbox"));
			$this->membersObject();
			return false;
		}
		foreach($_POST['participants'] as $usr_id)
		{
			$rcps[] = ilObjUser::_lookupLogin($usr_id);
		}

        require_once 'Services/Mail/classes/class.ilMailFormCall.php';
		ilUtil::redirect(ilMailFormCall::getRedirectTarget(
			$this, 
			'members',
			array(), 
			array('type' => 'new', 'rcp_to' => implode(',',$rcps),'sig' => $this->createMailSignature())));
		return true;
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
			$ilUser->writePref('grp_admin_hide',(int) $_GET['admin_hide']);
		}
		if(isset($_GET['member_hide']))
		{
			$ilUser->writePref('grp_member_hide',(int) $_GET['member_hide']);
		}
		if(isset($_GET['subscriber_hide']))
		{
			$ilUser->writePref('grp_subscriber_hide',(int) $_GET['subscriber_hide']);
		}
		if(isset($_GET['wait_hide']))
		{
			$ilUser->writePref('grp_wait_hide',(int) $_GET['wait_hide']);
		}
	}
	
	/**
	 * edit one member 
	 *
	 * @access public
	 */
	public function editMemberObject()
	{
		$_POST['members'] = array((int) $_GET['member_id']);
		$this->editMembersObject();
	}
	
	/**
	 * edit member(s)
	 *
	 * @access public
	 * @return
	 */
	public function editMembersObject()
	{
		$this->checkPermission('write');
		
		$post_participants = array_unique(array_merge((array) $_POST['admins'],(array) $_POST['members'], (array) $_POST['roles']));
		$real_participants = ilGroupParticipants::_getInstanceByObjId($this->object->getId())->getParticipants();
		$participants = array_intersect((array) $post_participants, (array) $real_participants);
		
		if(!count($participants))
		{
			ilUtil::sendFailure($this->lng->txt('no_checkbox'));
			$this->membersObject();
			return false;
		}
		
		$this->setSubTabs('members');
		$this->tabs_gui->setTabActive('members');
		$this->tabs_gui->setSubTabActive('grp_edit_members');
		
		include_once('./Modules/Group/classes/class.ilGroupEditParticipantsTableGUI.php');
		$table_gui = new ilGroupEditParticipantsTableGUI($this);
		$table_gui->setTitle($this->lng->txt('grp_mem_change_status'),'icon_usr.svg',$this->lng->txt('grp_mem_change_status'));
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
		$this->checkPermission('write');
		
		if(!count($_POST['participants']))
		{
			ilUtil::sendFailure($this->lng->txt('no_checkbox'));
			$this->membersObject();
			return false;
		}
		
		// Check minimum one admin
		$has_admin = false;
		$admin_role = $this->object->getDefaultAdminRole();
		foreach(ilGroupParticipants::_getInstanceByObjId($this->object->getId())->getAdmins() as $admin_id)
		{
			if(!isset($_POST['roles'][$admin_id]))
			{
				$has_admin = true;
				break;
			}
			if(in_array($admin_role,$_POST['roles'][$admin_id]))
			{
				$has_admin = true;
				break;
			}
		}
		
		if(!$has_admin and ilGroupParticipants::_getInstanceByObjId($this->object->getId())->getCountAdmins())
		{
			ilUtil::sendFailure($this->lng->txt('grp_min_one_admin'));
			$_POST['members'] = $_POST['participants'];
			$this->editMembersObject();
			return false;
		}
	
		$admin_role = $this->object->getDefaultAdminRole();

		$notifications = $_POST['notification'] ? $_POST['notification'] : array();
		foreach($_POST['participants'] as $usr_id)
		{
			// Check if a status changed mail is required
			$notification = false;
			if($this->object->members_obj->isAdmin($usr_id) and !in_array($admin_role,(array) $_POST['roles'][$usr_id]))
			{
				$notification = true;
			}
			if(!$this->object->members_obj->isAdmin($usr_id) and in_array($admin_role,(array) $_POST['roles'][$usr_id]))
			{
				$notification = true;
			}
			
			// TODO: check no role, owner
			$this->object->members_obj->updateRoleAssignments($usr_id,(array) $_POST['roles'][$usr_id]);
			
			// Disable notification for all of them
			$this->object->members_obj->updateNotification($usr_id,0);
			
			if($this->object->members_obj->isAdmin($usr_id) and in_array($usr_id,$notifications))
			{
				$this->object->members_obj->updateNotification($usr_id,1);
			}
			
			if($notification)
			{
				include_once './Modules/Group/classes/class.ilGroupMembershipMailNotification.php';
				$this->object->members_obj->sendNotification(
					ilGroupMembershipMailNotification::TYPE_STATUS_CHANGED,
					$usr_id
				);
			}
		}
		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
		$this->ctrl->redirect($this, "members");
		return true;		
	}
	
	/**
	 * update status 
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function updateStatusObject()
	{
		$this->checkPermission('write');
		
		$notification = $_POST['notification'] ? $_POST['notification'] : array();
		foreach($this->object->members_obj->getAdmins() as $admin_id)
		{
			$this->object->members_obj->updateNotification($admin_id,(int) in_array($admin_id,$notification));
		}
		ilUtil::sendSuccess($this->lng->txt('settings_saved'));
		$this->membersObject();
	}
	




	/**
	* canceledObject is called when operation is canceled, method links back
	* @access	public
	*/
	function canceledObject()
	{
		$return_location = $_GET["cmd_return_location"];
		if (strcmp($return_location, "") == 0)
		{
			$return_location = "";
		}

		$this->ctrl->redirect($this, $return_location);
	}



	/**
	* leave Group
	* @access public
	*/
	public function leaveObject()
	{
		global $ilUser;
		
		$this->checkPermission('leave');
		
		$part = ilGroupParticipants::_getInstanceByObjId($this->object->getId());
		if($part->isLastAdmin($ilUser->getId()))
		{
			ilUtil::sendFailure($this->lng->txt('grp_err_administrator_required'));
			$this->viewObject();
			return false;
		}
		
		$this->tabs_gui->setTabActive('grp_btn_unsubscribe');
		
		include_once "Services/Utilities/classes/class.ilConfirmationGUI.php";
		$cgui = new ilConfirmationGUI();		
		$cgui->setHeaderText($this->lng->txt('grp_dismiss_myself'));
		$cgui->setFormAction($this->ctrl->getFormAction($this));
		$cgui->setCancel($this->lng->txt("cancel"), "cancel");
		$cgui->setConfirm($this->lng->txt("grp_btn_unsubscribe"), "unsubscribe");		
		$this->tpl->setContent($cgui->getHTML());	
	}
	
	/**
	 * unsubscribe from group
	 *
	 * @access public
	 * @return
	 */
	public function unsubscribeObject()
	{
		global $ilUser,$tree, $ilCtrl;
		
		$this->checkPermission('leave');
		
		$this->object->members_obj->delete($ilUser->getId());
		
		include_once './Modules/Group/classes/class.ilGroupMembershipMailNotification.php';
		$this->object->members_obj->sendNotification(
			ilGroupMembershipMailNotification::TYPE_UNSUBSCRIBE_MEMBER,
			$ilUser->getId()
		);
		$this->object->members_obj->sendNotification(
			ilGroupMembershipMailNotification::TYPE_NOTIFICATION_UNSUBSCRIBE,
			$ilUser->getId()
		);
		
		ilUtil::sendSuccess($this->lng->txt('grp_msg_membership_annulled'),true);
		$ilCtrl->setParameterByClass("ilrepositorygui", "ref_id",
			$tree->getParentId($this->object->getRefId()));
		$ilCtrl->redirectByClass("ilrepositorygui", "");
	}
	

	/**
	* displays confirmation formular with users that shall be assigned to group
	* @access public
	*/
	function assignMemberObject()
	{
		$user_ids = $_POST["id"];

		if (empty($user_ids[0]))
		{
			// TODO: jumps back to grp content. go back to last search result
			$this->ilErr->raiseError($this->lng->txt("no_checkbox"),$this->ilErr->MESSAGE);
		}

		foreach ($user_ids as $new_member)
		{
			if (!$this->object->addMember($new_member,$this->object->getDefaultMemberRole()))
			{
				$this->ilErr->raiseError("An Error occured while assigning user to group !",$this->ilErr->MESSAGE);
			}
		}

		unset($_SESSION["saved_post"]);

		ilUtil::sendSuccess($this->lng->txt("grp_msg_member_assigned"),true);
		$this->ctrl->redirect($this,'members');
	}

	/**
	* displays confirmation formular with users that shall be assigned to group
	* @access public
	*/
	public function addUserObject($user_ids, $a_type)
	{
		if (empty($user_ids[0]))
		{
			$GLOBALS['lng']->loadLanguageModule('search');
			ilUtil::sendFailure($this->lng->txt('search_err_user_not_exist'),true);
			return false;
		}

		$part = ilGroupParticipants::_getInstanceByObjId($this->object->getId());
		$assigned = FALSE;
		foreach((array) $user_ids as $new_member)
		{
			if($part->isAssigned($new_member))
			{
				continue;
			}
			switch($a_type)
			{
				case $this->object->getDefaultAdminRole():
					$part->add($new_member, IL_GRP_ADMIN);
					include_once './Modules/Group/classes/class.ilGroupMembershipMailNotification.php';
					$part->sendNotification(
						ilGroupMembershipMailNotification::TYPE_ADMISSION_MEMBER, 
						$new_member
					);
					$assigned = TRUE;
					break;
				
				default:
					$part->add($new_member, IL_GRP_MEMBER);
					include_once './Modules/Group/classes/class.ilGroupMembershipMailNotification.php';
					$part->sendNotification(
						ilGroupMembershipMailNotification::TYPE_ADMISSION_MEMBER, 
						$new_member
					);
					$assigned = TRUE;
					break;
			}
		}
		
		if($assigned)
		{
			ilUtil::sendSuccess($this->lng->txt("grp_msg_member_assigned"),true);
		}
		else
		{
			ilUtil::sendSuccess($this->lng->txt('grp_users_already_assigned'),TRUE);
		}
		$this->ctrl->redirect($this,'members');
	}

	/**
	* adds applicant to group as member
	* @access	public
	*/
	function refuseApplicantsObject()
	{
		$user_ids = $_POST["user_id"];

		if (empty($user_ids[0]))
		{
			$this->ilErr->raiseError($this->lng->txt("no_checkbox"),$this->ilErr->MESSAGE);
		}
		include_once 'Services/Mail/classes/class.ilMail.php';
		$mail = new ilMail($_SESSION["AccountId"]);

		foreach ($user_ids as $new_member)
		{
			$user =& $this->ilias->obj_factory->getInstanceByObjId($new_member);

			$this->object->deleteApplicationListEntry($new_member);
			$mail->sendMail($user->getLogin(),"","","Membership application refused: Group ".$this->object->getTitle(),"Your application has been refused.",array(),array('system'));
		}

		ilUtil::sendSuccess($this->lng->txt("grp_msg_applicants_removed"),true);
		$this->ctrl->redirect($this,'members');
	}

	// get tabs
	function getTabs(&$tabs_gui)
	{
		global $rbacsystem, $ilUser, $ilAccess, $lng, $ilHelp;
		
		$ilHelp->setScreenIdComponent("grp");

		if ($rbacsystem->checkAccess('read',$this->ref_id))
		{
			$tabs_gui->addTab("view_content", $lng->txt("content"),
				$this->ctrl->getLinkTarget($this, ""));
		}
		if ($rbacsystem->checkAccess('visible',$this->ref_id))
		{
			$tabs_gui->addTarget("info_short",
								 $this->ctrl->getLinkTargetByClass(
								 array("ilobjgroupgui", "ilinfoscreengui"), "showSummary"),
								 "infoScreen",
								 "", "",false);
		}


		if ($ilAccess->checkAccess('write','',$this->object->getRefId()))
		{
			$tabs_gui->addTarget("settings",
				$this->ctrl->getLinkTarget($this, "edit"), array("edit", "editMapSettings"), get_class($this),
				"");
		}

		
		$is_participant = ilGroupParticipants::_isParticipant($this->ref_id, $ilUser->getId());
			
		// Members
		if($ilAccess->checkAccess('write', '', $this->ref_id))
		{
			$tabs_gui->addTarget('members', $this->ctrl->getLinkTarget($this, 'members'), array(), get_class($this));
		}
		else if($is_participant)
		{
			$this->tabs_gui->addTarget(
				'members',
				$this->ctrl->getLinkTargetByClass('ilUsersGalleryGUI','view'),
				'',
				'ilUsersGalleryGUI'
			);
		}
		// learning progress
		include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
		if(ilLearningProgressAccess::checkAccess($this->object->getRefId(), $is_participant))
		{
			$tabs_gui->addTarget('learning_progress',
								 $this->ctrl->getLinkTargetByClass(array('ilobjgroupgui','illearningprogressgui'),''),
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

		/*
		if ($rbacsystem->checkAccess('write',$this->object->getRefId()))
		{
			
			
			$tabs_gui->addTarget('export',
								 $this->ctrl->getLinkTarget($this,'listExportFiles'),
								 array('listExportFiles','exportXML','confirmDeleteExportFile','downloadExportFile'),
								 get_class($this));
		}
		*/
		// parent tabs (all container: edit_permission, clipboard, trash
		parent::getTabs($tabs_gui);

		if($ilAccess->checkAccess('join','',$this->object->getRefId()) and
			!$this->object->members_obj->isAssigned($ilUser->getId()))
		{
			include_once './Modules/Group/classes/class.ilGroupWaitingList.php';
			if(ilGroupWaitingList::_isOnList($ilUser->getId(), $this->object->getId()))
			{
				$tabs_gui->addTab(
					'leave',
					$this->lng->txt('membership_leave'),
					$this->ctrl->getLinkTargetByClass('ilgroupregistrationgui','show','')
				);
					
			}
			else
			{			
				
				$tabs_gui->addTarget("join",
									 $this->ctrl->getLinkTargetByClass('ilgroupregistrationgui', "show"), 
									 'show',
									 "");
			}
		}
		if($ilAccess->checkAccess('leave','',$this->object->getRefId()) and
			$this->object->members_obj->isMember($ilUser->getId()))
		{
			$tabs_gui->addTarget("grp_btn_unsubscribe",
								 $this->ctrl->getLinkTarget($this, "leave"), 
								 '',
								 "");
		}
	}

	// IMPORT FUNCTIONS

	function importFileObject2()
	{
		if(!is_array($_FILES['xmldoc']))
		{
			ilUtil::sendFailure($this->lng->txt("import_file_not_valid"));
			$this->createObject();
			return false;
		}
		
		include_once './Modules/Group/classes/class.ilObjGroup.php';

		if($ref_id = ilObjGroup::_importFromFile($_FILES['xmldoc'],(int) $_GET['ref_id']))
		{
			$this->ctrl->setParameter($this, "ref_id", $ref_id);
			ilUtil::sendSuccess($this->lng->txt("import_grp_finished"),true);
			ilUtil::redirect($this->ctrl->getLinkTarget($this,'edit','',false,false));
		}
		
		ilUtil::sendFailure($this->lng->txt("import_file_not_valid"));
		$this->createObject();
	}	
	
/**
* Creates the output form for group member export
*
* Creates the output form for group member export
*
*/
	function exportObject()
	{
		$this->tpl->addBlockfile("ADM_CONTENT", "adm_content", "tpl.grp_members_export.html",
			"Modules/Group");
		$this->tpl->setCurrentBlock("adm_content");
		$this->tpl->setVariable("FORMACTION", $this->getFormAction("export",$this->ctrl->getFormAction($this)));
		$this->tpl->setVariable("BUTTON_EXPORT", $this->lng->txt("export_group_members"));
		$this->tpl->parseCurrentBlock();
	}
	
/**
* Exports group members to Microsoft Excel file
*
* Exports group members to Microsoft Excel file
*
*/
	function exportMembersObject()
	{
		$title = preg_replace("/\s/", "_", $this->object->getTitle());
		include_once "./Services/Excel/classes/class.ilExcelWriterAdapter.php";
		$adapter = new ilExcelWriterAdapter("export_" . $title . ".xls");
		$workbook = $adapter->getWorkbook();
		// Creating a worksheet
		$format_bold =& $workbook->addFormat();
		$format_bold->setBold();
		$format_percent =& $workbook->addFormat();
		$format_percent->setNumFormat("0.00%");
		$format_datetime =& $workbook->addFormat();
		$format_datetime->setNumFormat("DD/MM/YYYY hh:mm:ss");
		$format_title =& $workbook->addFormat();
		$format_title->setBold();
		$format_title->setColor('black');
		$format_title->setPattern(1);
		$format_title->setFgColor('silver');
		$worksheet =& $workbook->addWorksheet();
		$column = 0;
		$profile_data = array("email", "gender", "firstname", "lastname", "person_title", "institution", 
			"department", "street", "zipcode","city", "country", "phone_office", "phone_home", "phone_mobile",
			"fax", "matriculation");
		foreach ($profile_data as $data)
		{
			$worksheet->writeString(0, $column++, $this->cleanString($this->lng->txt($data)), $format_title);
		}
		$member_ids = $this->object->getGroupMemberIds();
		$row = 1;
		foreach ($member_ids as $member_id)
		{
			$column = 0;
			$member =& $this->ilias->obj_factory->getInstanceByObjId($member_id);
			if ($member->getPref("public_email")=="y")
			{
				$worksheet->writeString($row, $column++, $this->cleanString($member->getEmail()));
			}
			else
			{
				$column++;
			}
			$worksheet->writeString($row, $column++, $this->cleanString($this->lng->txt("gender_" . $member->getGender())));
			$worksheet->writeString($row, $column++, $this->cleanString($member->getFirstname()));
			$worksheet->writeString($row, $column++, $this->cleanString($member->getLastname()));
			$worksheet->writeString($row, $column++, $this->cleanString($member->getUTitle()));
			if ($member->getPref("public_institution")=="y")
			{
				$worksheet->writeString($row, $column++, $this->cleanString($member->getInstitution()));
			}
			else
			{
				$column++;
			}
			if ($member->getPref("public_department")=="y")
			{
				$worksheet->writeString($row, $column++, $this->cleanString($member->getDepartment()));
			}
			else
			{
				$column++;
			}
			if ($member->getPref("public_street")=="y")
			{
				$worksheet->writeString($row, $column++, $this->cleanString($member->getStreet()));
			}
			else
			{
				$column++;
			}
			if ($member->getPref("public_zip")=="y")
			{
				$worksheet->writeString($row, $column++, $this->cleanString($member->getZipcode()));
			}
			else
			{
				$column++;
			}
			if ($member->getPref("public_city")=="y")
			{
				$worksheet->writeString($row, $column++, $this->cleanString($member->getCity()));
			}
			else
			{
				$column++;
			}
			if ($member->getPref("public_country")=="y")
			{
				$worksheet->writeString($row, $column++, $this->cleanString($member->getCountry()));
			}
			else
			{
				$column++;
			}
			if ($member->getPref("public_phone_office")=="y")
			{
				$worksheet->writeString($row, $column++, $this->cleanString($member->getPhoneOffice()));
			}
			else
			{
				$column++;
			}
			if ($member->getPref("public_phone_home")=="y")
			{
				$worksheet->writeString($row, $column++, $this->cleanString($member->getPhoneHome()));
			}
			else
			{
				$column++;
			}
			if ($member->getPref("public_phone_mobile")=="y")
			{
				$worksheet->writeString($row, $column++, $this->cleanString($member->getPhoneMobile()));
			}
			else
			{
				$column++;
			}
			if ($member->getPref("public_fax")=="y")
			{
				$worksheet->writeString($row, $column++, $this->cleanString($member->getFax()));
			}
			else
			{
				$column++;
			}
			if ($member->getPref("public_matriculation")=="y")
			{
				$worksheet->writeString($row, $column++, $this->cleanString($member->getMatriculation()));
			}
			else
			{
				$column++;
			}
			$row++;
		}
		$workbook->close();
	}
	
	/**
	* Clean output string from german umlauts
	*
	* Clean output string from german umlauts. Replaces �� -> ae etc.
	*
	* @param string $str String to clean
	* @return string Cleaned string
	*/
	function cleanString($str)
	{
		return str_replace(array("��","��","��","��","��","��","��"), array("ae","oe","ue","ss","Ae","Oe","Ue"), $str);
	}

	/**
	* set sub tabs
	*/


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
		global $rbacsystem, $ilUser, $ilSetting;
		
		$this->tabs_gui->setTabActive('info_short');

		if(!$rbacsystem->checkAccess("visible", $this->ref_id))
		{
			$this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
		}

		include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
		$info = new ilInfoScreenGUI($this);
		
		if(strlen($this->object->getInformation()))
		{
			$info->addSection($this->lng->txt('grp_general_informations'));
			$info->addProperty($this->lng->txt('grp_information'), nl2br(
								ilUtil::makeClickable ($this->object->getInformation(), true)));
		}

		$info->enablePrivateNotes();
		$info->enableLearningProgress(true);

		$info->addSection($this->lng->txt('group_registration'));
		$info->showLDAPRoleGroupMappingInfo();

		if(!$this->object->isRegistrationEnabled())
		{
			$info->addProperty($this->lng->txt('group_registration_mode'),
				$this->lng->txt('grp_reg_deac_info_screen'));
			
		}
		else
		{
			switch($this->object->getRegistrationType())
			{
				case GRP_REGISTRATION_DIRECT:
					$info->addProperty($this->lng->txt('group_registration_mode'),
									   $this->lng->txt('grp_reg_direct_info_screen'));
					break;
													   
				case GRP_REGISTRATION_REQUEST:
					$info->addProperty($this->lng->txt('group_registration_mode'),
									   $this->lng->txt('grp_reg_req_info_screen'));
					break;
	
				case GRP_REGISTRATION_PASSWORD:
					$info->addProperty($this->lng->txt('group_registration_mode'),
									   $this->lng->txt('grp_reg_passwd_info_screen'));
					break;
					
			}
			/*			
			$info->addProperty($this->lng->txt('group_registration_time'),
				ilDatePresentation::formatPeriod(
					$this->object->getRegistrationStart(),
					$this->object->getRegistrationEnd()));
			*/
			if($this->object->isRegistrationUnlimited())
			{
				$info->addProperty($this->lng->txt('group_registration_time'),
					$this->lng->txt('grp_registration_unlimited'));
			}
			elseif($this->object->getRegistrationStart()->getUnixTime() < time())
			{
				$info->addProperty($this->lng->txt("group_registration_time"),
								   $this->lng->txt('cal_until').' '.
								   ilDatePresentation::formatDate($this->object->getRegistrationEnd()));
			}
			elseif($this->object->getRegistrationStart()->getUnixTime() >= time())
			{
				$info->addProperty($this->lng->txt("group_registration_time"),
								   $this->lng->txt('cal_from').' '.
								   ilDatePresentation::formatDate($this->object->getRegistrationStart()));
			}
			if ($this->object->isMembershipLimited()) 
			{
				if($this->object->getMinMembers())
				{
					$info->addProperty($this->lng->txt("mem_min_users"), 
						$this->object->getMinMembers());
				}
				if($this->object->getMaxMembers())
				{
					$info->addProperty($this->lng->txt("mem_free_places"),
									   max(0,$this->object->getMaxMembers() - $this->object->members_obj->getCountMembers()));
				}				
			}
			
			if($this->object->getCancellationEnd())
			{			
				$info->addProperty($this->lng->txt('grp_cancellation_end'),
					ilDatePresentation::formatDate( $this->object->getCancellationEnd()));
			}
		}
		
		// Confirmation
		include_once('Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
		$privacy = ilPrivacySettings::_getInstance();
		
		include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');
		if($privacy->groupConfirmationRequired() or ilCourseDefinedFieldDefinition::_getFields($this->object->getId()) or $privacy->enabledGroupExport())
		{
			include_once('Services/PrivacySecurity/classes/class.ilExportFieldsInfo.php');
			
			$field_info = ilExportFieldsInfo::_getInstanceByType($this->object->getType());
		
			$this->lng->loadLanguageModule('ps');
			$info->addSection($this->lng->txt('grp_user_agreement_info'));
			$info->addProperty($this->lng->txt('ps_export_data'),$field_info->exportableFieldsToInfoString());
			
			if($fields = ilCourseDefinedFieldDefinition::_fieldsToInfoString($this->object->getId()))
			{
				$info->addProperty($this->lng->txt('ps_grp_user_fields'),$fields);
			}
		}
		

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
			if((bool)$_REQUEST["grp_ntf"])
			{
				$noti->activateUser();
			}
			else
			{
				$noti->deactivateUser();
			}
		}
		ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
		$this->ctrl->redirect($this, "infoScreen");
	}

	/**
	 * goto target group
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
	 * init create/edit form
	 *
	 * @access protected
	 * @param string edit or create
	 * @return
	 */
	protected function initForm($a_mode = 'edit')
	{
		global $ilUser,$tpl,$tree;
		
		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		
		$form = new ilPropertyFormGUI();
		switch($a_mode)
		{
			case 'edit':
				$form->setFormAction($this->ctrl->getFormAction($this,'update'));
				break;
				
			default:
				$form->setTableWidth('600px');
				$form->setFormAction($this->ctrl->getFormAction($this,'save'));
				break;
		}
		
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
		
		// Group type
		$grp_type = new ilRadioGroupInputGUI($this->lng->txt('grp_typ'),'grp_type');
		
		if($a_mode == 'edit')
		{
			$type = ($this->object->getGroupType() ? $this->object->getGroupType() : $this->object->readGroupStatus());
		}
		else
		{
			$type = ($this->object->getGroupType() ? $this->object->getGroupType() : GRP_TYPE_PUBLIC);
		}
		
		$grp_type->setValue($type);
		$grp_type->setRequired(true);

		
		// PUBLIC GROUP
		$opt_public = new ilRadioOption($this->lng->txt('grp_public'),GRP_TYPE_PUBLIC,$this->lng->txt('grp_public_info'));
		$grp_type->addOption($opt_public);


		// CLOSED GROUP
		$opt_closed = new ilRadioOption($this->lng->txt('grp_closed'),GRP_TYPE_CLOSED,$this->lng->txt('grp_closed_info'));
		$grp_type->addOption($opt_closed);

		$form->addItem($grp_type);

		if($a_mode == 'edit')
		{
			// Group registration ############################################################
			$pres = new ilFormSectionHeaderGUI();
			$pres->setTitle($this->lng->txt('grp_setting_header_registration'));
			$form->addItem($pres);

			// Registration type
			$reg_type = new ilRadioGroupInputGUI($this->lng->txt('group_registration_mode'),'registration_type');
			$reg_type->setValue($this->object->getRegistrationType());

			$opt_dir = new ilRadioOption($this->lng->txt('grp_reg_direct'),GRP_REGISTRATION_DIRECT);#$this->lng->txt('grp_reg_direct_info'));
			$reg_type->addOption($opt_dir);

			$opt_pass = new ilRadioOption($this->lng->txt('grp_pass_request'),GRP_REGISTRATION_PASSWORD);
			$pass = new ilTextInputGUI($this->lng->txt("password"),'password');
			$pass->setInfo($this->lng->txt('grp_reg_password_info'));
			$pass->setValue($this->object->getPassword());
			$pass->setSize(10);
			$pass->setMaxLength(32);
			$opt_pass->addSubItem($pass);
			$reg_type->addOption($opt_pass);

			$opt_req = new ilRadioOption($this->lng->txt('grp_reg_request'),GRP_REGISTRATION_REQUEST,$this->lng->txt('grp_reg_request_info'));
			$reg_type->addOption($opt_req);

			$opt_deact = new ilRadioOption($this->lng->txt('grp_reg_no_selfreg'),GRP_REGISTRATION_DEACTIVATED,$this->lng->txt('grp_reg_disabled_info'));
			$reg_type->addOption($opt_deact);

			// Registration codes
			$reg_code = new ilCheckboxInputGUI($this->lng->txt('grp_reg_code'),'reg_code_enabled');
			$reg_code->setChecked($this->object->isRegistrationAccessCodeEnabled());
			$reg_code->setValue(1);
			$reg_code->setInfo($this->lng->txt('grp_reg_code_enabled_info'));
			$form->addItem($reg_type);

			// Registration codes
			if(!$this->object->getRegistrationAccessCode())
			{
				include_once './Services/Membership/classes/class.ilMembershipRegistrationCodeUtils.php';
				$this->object->setRegistrationAccessCode(ilMembershipRegistrationCodeUtils::generateCode());
			}
			$reg_link = new ilHiddenInputGUI('reg_code');
			$reg_link->setValue($this->object->getRegistrationAccessCode());
			$form->addItem($reg_link);

			$link = new ilCustomInputGUI($this->lng->txt('grp_reg_code_link'));
			include_once './Services/Link/classes/class.ilLink.php';
			$val = ilLink::_getLink($this->object->getRefId(),$this->object->getType(),array(),'_rcode'.$this->object->getRegistrationAccessCode());
			$link->setHTML('<font class="small">'.$val.'</font>');
			$reg_code->addSubItem($link);
			$form->addItem($reg_code);


			// time limit
			$time_limit = new ilCheckboxInputGUI($this->lng->txt('grp_reg_limited'),'reg_limit_time');
//			$time_limit->setOptionTitle($this->lng->txt('grp_reg_limit_time'));
			$time_limit->setChecked($this->object->isRegistrationUnlimited() ? false : true);

			$this->lng->loadLanguageModule('dateplaner');
			include_once './Services/Form/classes/class.ilDateDurationInputGUI.php';
			$tpl->addJavaScript('./Services/Form/js/date_duration.js');
			$dur = new ilDateDurationInputGUI($this->lng->txt('grp_reg_period'),'reg');
			$dur->setStartText($this->lng->txt('cal_start'));
			$dur->setEndText($this->lng->txt('cal_end'));
			$dur->setShowTime(true);
			$dur->setStart($this->object->getRegistrationStart());
			$dur->setEnd($this->object->getRegistrationEnd());

			$time_limit->addSubItem($dur);
			$form->addItem($time_limit);
			
			// cancellation limit		
			$cancel = new ilDateTimeInputGUI($this->lng->txt('grp_cancellation_end'), 'cancel_end');
			$cancel->setInfo($this->lng->txt('grp_cancellation_end_info'));
			$cancel_end = $this->object->getCancellationEnd();
			$cancel->enableDateActivation('', 'cancel_end_tgl', (bool)$cancel_end);
			if($cancel_end)
			{
				$cancel->setDate($cancel_end);
			}
			$form->addItem($cancel);

			// max member
			$lim = new ilCheckboxInputGUI($this->lng->txt('reg_grp_max_members_short'),'registration_membership_limited');
			$lim->setValue(1);
//			$lim->setOptionTitle($this->lng->txt('reg_grp_max_members'));
			$lim->setChecked($this->object->isMembershipLimited());

			$min = new ilTextInputGUI($this->lng->txt('reg_grp_min_members'),'registration_min_members');
			$min->setSize(3);
			$min->setMaxLength(4);
			$min->setValue($this->object->getMinMembers() ? $this->object->getMinMembers() : '');
			$min->setInfo($this->lng->txt('grp_subscription_min_members_info'));			
			$lim->addSubItem($min);

			$max = new ilTextInputGUI($this->lng->txt('reg_grp_max_members'),'registration_max_members');
			$max->setValue($this->object->getMaxMembers() ? $this->object->getMaxMembers() : '');
			//$max->setTitle($this->lng->txt('members'));
			$max->setSize(3);
			$max->setMaxLength(4);
			$max->setInfo($this->lng->txt('grp_reg_max_members_info'));
			$lim->addSubItem($max);

			/*
			$wait = new ilCheckboxInputGUI($this->lng->txt('grp_waiting_list'),'waiting_list');
			$wait->setValue(1);
			//$wait->setOptionTitle($this->lng->txt('grp_waiting_list'));
			$wait->setInfo($this->lng->txt('grp_waiting_list_info'));
			$wait->setChecked($this->object->isWaitingListEnabled() ? true : false);
			$lim->addSubItem($wait);
			$form->addItem($lim);
			*/
			 
			$wait = new ilRadioGroupInputGUI($this->lng->txt('grp_waiting_list'), 'waiting_list');
			
			$option = new ilRadioOption($this->lng->txt('none'), 0);
			$wait->addOption($option);
			
			$option = new ilRadioOption($this->lng->txt('grp_waiting_list_no_autofill'), 1);
			$option->setInfo($this->lng->txt('grp_waiting_list_info'));
			$wait->addOption($option);
			
			$option = new ilRadioOption($this->lng->txt('grp_waiting_list_autofill'), 2);
			$option->setInfo($this->lng->txt('grp_waiting_list_autofill_info'));
			$wait->addOption($option);
			
			if($this->object->hasWaitingListAutoFill())
			{
				$wait->setValue(2);
			}
			else if($this->object->isWaitingListEnabled())
			{
				$wait->setValue(1);
			}
			
			$lim->addSubItem($wait);
			
			$form->addItem($lim);			
			

			// Group presentation
			$hasParentMembership = 
				(
					$tree->checkForParentType($this->object->getRefId(),'crs') ||
					$tree->checkForParentType($this->object->getRefId(),'grp')
				);
			
			$pres = new ilFormSectionHeaderGUI();
			$pres->setTitle($this->lng->txt('grp_setting_header_presentation'));
			$form->addItem($pres);
			
			// presentation type							
			$view_type = new ilRadioGroupInputGUI($this->lng->txt('grp_presentation_type'),'view_mode');		
			if($hasParentMembership)
			{								
				switch($this->object->getViewMode())
				{
					case ilContainer::VIEW_SESSIONS:							
						$course_view_mode = ': '.$this->lng->txt('cntr_view_sessions');
						break;

					case ilContainer::VIEW_SIMPLE:
						$course_view_mode = ': '.$this->lng->txt('cntr_view_simple');
						break;

					case ilContainer::VIEW_BY_TYPE:
						$course_view_mode = ': '.$this->lng->txt('cntr_view_by_type');
						break;
				}																		
				
				$opt = new ilRadioOption($this->lng->txt('grp_view_inherit').$course_view_mode,ilContainer::VIEW_INHERIT);
				$opt->setInfo($this->lng->txt('grp_view_inherit_info'));
				$view_type->addOption($opt);
			}	
			
			if($hasParentMembership &&
				$this->object->getViewMode(false) == ilContainer::VIEW_INHERIT)
			{
				$view_type->setValue(ilContainer::VIEW_INHERIT);
			}
			else
			{
				$view_type->setValue($this->object->getViewMode(true));
			}
			
			$opt = new ilRadioOption($this->lng->txt('cntr_view_simple'),ilContainer::VIEW_SIMPLE);
			$opt->setInfo($this->lng->txt('grp_view_info_simple'));
			$view_type->addOption($opt);
			
			$opt = new ilRadioOption($this->lng->txt('cntr_view_by_type'),  ilContainer::VIEW_BY_TYPE);
			$opt->setInfo($this->lng->txt('grp_view_info_by_type'));
			$view_type->addOption($opt);
			$form->addItem($view_type);

			
			// Sorting
			$sorting_settings = array();
			if($hasParentMembership)
			{
				$sorting_settings[] = ilContainer::SORT_INHERIT;
			}
			$sorting_settings[] = ilContainer::SORT_TITLE;
			$sorting_settings[] = ilContainer::SORT_CREATION;
			$sorting_settings[] = ilContainer::SORT_MANUAL;
			$this->initSortingForm($form, $sorting_settings);

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
						ilObjectServiceSettingsGUI::TAG_CLOUD
					)
				);

			// Notification Settings
			/*$notification = new ilFormSectionHeaderGUI();
			$notification->setTitle($this->lng->txt('grp_notification'));
			$form->addItem($notification);*/
		
			// Show members type
			$mail_type = new ilRadioGroupInputGUI($this->lng->txt('grp_mail_type'), 'mail_type');
			$mail_type->setValue($this->object->getMailToMembersType());

			$mail_tutors = new ilRadioOption($this->lng->txt('grp_mail_tutors_only'), ilObjGroup::MAIL_ALLOWED_TUTORS,
				$this->lng->txt('grp_mail_tutors_only_info'));
			$mail_type->addOption($mail_tutors);

			$mail_all = new ilRadioOption($this->lng->txt('grp_mail_all'),  ilObjGroup::MAIL_ALLOWED_ALL,
				$this->lng->txt('grp_mail_all_info'));
			$mail_type->addOption($mail_all);
			$form->addItem($mail_type);
		}
		
		switch($a_mode)
		{
			case 'create':
				$form->setTitle($this->lng->txt('grp_new'));
				$form->setTitleIcon(ilUtil::getImagePath('icon_grp.svg'));
		
				$form->addCommandButton('save',$this->lng->txt('grp_new'));
				$form->addCommandButton('cancel',$this->lng->txt('cancel'));
				break;
			
			case 'edit':
				$form->setTitle($this->lng->txt('grp_edit'));
				$form->setTitleIcon(ilUtil::getImagePath('icon_grp.svg'));
				
				// Edit ecs export settings
				include_once 'Modules/Group/classes/class.ilECSGroupSettings.php';
				$ecs = new ilECSGroupSettings($this->object);		
				$ecs->addSettingsToForm($form, 'grp');
			
				$form->addCommandButton('update',$this->lng->txt('save'));
				$form->addCommandButton('cancel',$this->lng->txt('cancel'));
				break;
		}
		return $form;
	}
	
	/**
	 * load settings
	 *
	 * @access public
	 * @return
	 */
	public function load(ilPropertyFormGUI $a_form)
	{
		$this->object->setTitle(ilUtil::stripSlashes($_POST['title']));
		$this->object->setDescription(ilUtil::stripSlashes($_POST['desc']));
		$this->object->setGroupType(ilUtil::stripSlashes($_POST['grp_type']));
		$this->object->setRegistrationType(ilUtil::stripSlashes($_POST['registration_type']));
		$this->object->setPassword(ilUtil::stripSlashes($_POST['password']));
		$this->object->enableUnlimitedRegistration((bool) !$_POST['reg_limit_time']);
		$this->object->setRegistrationStart($this->loadDate('start'));
		$this->object->setRegistrationEnd($this->loadDate('end'));
		$this->object->enableMembershipLimitation((bool) $_POST['registration_membership_limited']);
		$this->object->setMinMembers((int) $_POST['registration_min_members']);
		$this->object->setMaxMembers((int) $_POST['registration_max_members']);		
		$this->object->enableRegistrationAccessCode((bool) $_POST['reg_code_enabled']);
		$this->object->setRegistrationAccessCode(ilUtil::stripSlashes($_POST['reg_code']));
		$this->object->setViewMode(ilUtil::stripSlashes($_POST['view_mode']));
		$this->object->setMailToMembersType((int) $_POST['mail_type']);
		
		$cancel_end = $a_form->getItemByPostVar('cancel_end');
		if($_POST[$cancel_end->getActivationPostVar()])
		{
			$dt = $cancel_end->getDate()->get(IL_CAL_DATETIME);
			$this->object->setCancellationEnd(new ilDate($dt, IL_CAL_DATETIME));
		}
		else
		{
			$this->object->setCancellationEnd(null);
		}
		
		switch((int)$_POST['waiting_list'])
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
		
		$dt['year'] = (int) $_POST['reg'][$a_field]['date']['y'];
		$dt['mon'] = (int) $_POST['reg'][$a_field]['date']['m'];
		$dt['mday'] = (int) $_POST['reg'][$a_field]['date']['d'];
		$dt['hours'] = (int) $_POST['reg'][$a_field]['time']['h'];
		$dt['minutes'] = (int) $_POST['reg'][$a_field]['time']['m'];
		$dt['seconds'] = (int) $_POST['reg'][$a_field]['time']['s'];
		
		$date = new ilDateTime($dt,IL_CAL_FKT_GETDATE,$ilUser->getTimeZone());
		return $date;		
	}
	
	/**
	 * set sub tabs
	 *
	 * @access protected
	 * @param
	 * @return
	 */
	protected function setSubTabs($a_tab)
	{
		global $rbacsystem,$ilUser,$ilAccess;
	
		switch($a_tab)
		{
			case 'members':
				// for admins
				if($ilAccess->checkAccess('write','',$this->object->getRefId()))
				{
					$this->tabs_gui->addSubTabTarget("grp_edit_members",
						$this->ctrl->getLinkTarget($this,'members'),
						"members",
						get_class($this));
				}
				// for all
				$this->tabs_gui->addSubTabTarget(
					'grp_members_gallery',
					$this->ctrl->getLinkTargetByClass('ilUsersGalleryGUI','view'),
					'',
					'ilUsersGalleryGUI'
				);
				
				// members map
				include_once("./Services/Maps/classes/class.ilMapUtil.php");
				if (ilMapUtil::isActivated() &&
					$this->object->getEnableGroupMap())
				{
					$this->tabs_gui->addSubTabTarget("grp_members_map",
						$this->ctrl->getLinkTarget($this,'membersMap'),
						"membersMap", get_class($this));
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
					$this->tabs_gui->addSubTabTarget('grp_export_members',
													$this->ctrl->getLinkTargetByClass('ilmemberexportgui','show'),
													"", 'ilmemberexportgui');
				}

				break;

			case 'settings':
				$this->tabs_gui->addSubTabTarget("grp_settings",
												 $this->ctrl->getLinkTarget($this,'edit'),
												 "edit", get_class($this));

				$this->tabs_gui->addSubTabTarget("grp_info_settings",
												 $this->ctrl->getLinkTarget($this,'editInfo'),
												 "editInfo", get_class($this));

				// custom icon
				if ($this->ilias->getSetting("custom_icons"))
				{
					$this->tabs_gui->addSubTabTarget("grp_icon_settings",
													 $this->ctrl->getLinkTarget($this,'editGroupIcons'),
													 "editGroupIcons", get_class($this));
				}
				
				include_once("./Services/Maps/classes/class.ilMapUtil.php");
				if (ilMapUtil::isActivated())
				{
					$this->tabs_gui->addSubTabTarget("grp_map_settings",
												 $this->ctrl->getLinkTarget($this,'editMapSettings'),
												 "editMapSettings", get_class($this));
				}

				$this->tabs_gui->addSubTabTarget('groupings',
												 $this->ctrl->getLinkTargetByClass('ilobjcoursegroupinggui','listGroupings'),
												 'listGroupings',
												 get_class($this));

				include_once('Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
				include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');
				// only show if export permission is granted
				if(ilPrivacySettings::_getInstance()->checkExportAccess($this->object->getRefId()) or ilCourseDefinedFieldDefinition::_hasFields($this->object->getId()))
				{
					$this->tabs_gui->addSubTabTarget('grp_custom_user_fields',
													$this->ctrl->getLinkTargetByClass('ilobjectcustomuserfieldsgui'),
													'',
													'ilobjectcustomuserfieldsgui');
				}



				break;
				
				
		}
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
		
		// Disable aggrement if is not member of group
		if(!$this->object->members_obj->isAssigned($ilUser->getId()))
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
		if(($privacy->groupConfirmationRequired() or ilCourseDefinedFieldDefinition::_hasFields($this->object->getId())) 
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
	 * Handle member view
	 * @return 
	 */
	public function prepareOutput()
	{
		global $rbacsystem;
		if(!$this->getCreationMode())
		{
			/*
			include_once './Services/Container/classes/class.ilMemberViewSettings.php';
			$settings = ilMemberViewSettings::getInstance();
			if($settings->isActive() and $settings->getContainer() != $this->object->getRefId())
			{
				$settings->setContainer($this->object->getRefId());
				$rbacsystem->initMemberView();				
			}
			*/
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
		$link .= $this->lng->txt('grp_mail_permanent_link');
		$link .= chr(13).chr(10).chr(13).chr(10);
		include_once 'Services/Link/classes/class.ilLink.php';
		$link .= ilLink::_getLink($this->object->getRefId());
		return rawurlencode(base64_encode($link));
	}
	
	protected function initHeaderAction($a_sub_type = null, $a_sub_id = null) 
	{
		global $ilSetting, $ilUser;
		
		$lg = parent::initHeaderAction($a_sub_type, $a_sub_id);
				
		include_once('./Modules/Group/classes/class.ilGroupParticipants.php');
		if(ilGroupParticipants::_isParticipant($this->ref_id, $ilUser->getId()))
		{				
			include_once "Services/Membership/classes/class.ilMembershipNotifications.php";			
			if(ilMembershipNotifications::isActive())
			{
				$noti = new ilMembershipNotifications($this->ref_id);					
				if(!$noti->isCurrentUserActive())
				{
					$lg->addHeaderIcon("not_icon",
						ilUtil::getImagePath("notification_off.svg"),
						$this->lng->txt("grp_notification_deactivated"));

					$this->ctrl->setParameter($this, "grp_ntf", 1);
					$caption = "grp_activate_notification";
				}
				else
				{				
					$lg->addHeaderIcon("not_icon",
						ilUtil::getImagePath("notification_on.svg"),
						$this->lng->txt("grp_notification_activated"));

					$this->ctrl->setParameter($this, "grp_ntf", 0);
					$caption = "grp_deactivate_notification";
				}

				if($noti->canCurrentUserEdit())
				{
					$lg->addCustomCommand($this->ctrl->getLinkTarget($this, "saveNotification"),
						$caption);
				}

				$this->ctrl->setParameter($this, "grp_ntf", "");
			}
		}		
		
		return $lg;
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
	
	/**
	 * Init attendance list object
	 * 
	 * @return ilAttendanceList 
	 */
	protected function initAttendanceList()
	{
		include_once('./Modules/Group/classes/class.ilGroupParticipants.php');
		$members_obj = ilGroupParticipants::_getInstanceByObjId($this->object->getId());
		
		include_once('./Modules/Group/classes/class.ilGroupWaitingList.php');
		$waiting_list = new ilGroupWaitingList($this->object->getId());
		
		include_once 'Services/Membership/classes/class.ilAttendanceList.php';
		$list = new ilAttendanceList($this, $members_obj, $waiting_list);		
		$list->setId('grpmemlst');
				
		$list->setTitle($this->lng->txt('grp_members_print_title'),
			$this->lng->txt('obj_grp').': '.$this->object->getTitle());		
						
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
			$this->lng->loadLanguageModule('trac');		
			$list->addPreset('progress', $this->lng->txt('learning_progress'), true);
		}
		
		include_once('./Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
		$privacy = ilPrivacySettings::_getInstance();
		if($privacy->enabledGroupAccessTimes())
		{
			$list->addPreset('access', $this->lng->txt('last_access'), true);
		}
		
		return $list;
	}
	
	public function getAttendanceListUserData($a_user_id)
	{		
		$data = $this->members_data[$a_user_id];
		$data['access'] = $data['access_time'];
		$data['progress'] = $this->lng->txt($data['progress']);
		
		return $data;
	}
	
	function printMembersOutputObject()
	{				
		$list = $this->initAttendanceList();		
		$list->initFromForm();
		$list->setCallback(array($this, 'getAttendanceListUserData'));	
		
		$part = ilGroupParticipants::_getInstanceByObjId($this->object->getId());
		$this->members_data = $this->readMemberData($part->getParticipants());
		$list->getNonMemberUserData($this->members_data);
		
		echo $list->getFullscreenHTML();
		exit();	
	}

	/**
	 * returns all local roles [role_id] => title
	 * @return array
	 */
	protected function getLocalRoles()
	{
		$local_roles = $this->object->getLocalGroupRoles(false);
		$grp_member = $this->object->getDefaultMemberRole();
		$grp_roles = array();

		//put the group member role to the top of the crs_roles array
		if(in_array($grp_member, $local_roles))
		{
			$grp_roles[$grp_member] = ilObjRole::_getTranslation(array_search ($grp_member, $local_roles));
			unset($local_roles[$grp_roles[$grp_member]]);
		}

		foreach($local_roles as $title => $role_id)
		{
			$grp_roles[$role_id] = ilObjRole::_getTranslation($title);
		}
		return $grp_roles;
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
		global $ilAccess, $ilUser, $rbacsystem;
		include_once 'Services/Mail/classes/class.ilMail.php';
		$mail = new ilMail($ilUser->getId());

		if(
		($ilAccess->checkAccess('write','',$this->object->getRefId()) or
			$this->object->getMailToMembersType() == ilObjGroup::MAIL_ALLOWED_ALL) and
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
			ilUtil::sendFailure($this->lng->txt("no_checkbox"));
			$this->membersObject();

			return false;
		}

		$this->lng->loadLanguageModule('mmbr');

		$this->checkPermission('write');
		$this->setSubTabs('members');
		$this->tabs_gui->setTabActive('members');
		$this->tabs_gui->setSubTabActive('grp_edit_members');

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
	}

	public function confirmAssignSubscribersObject()
	{
		if(!is_array($_POST["subscribers"]))
		{
			ilUtil::sendFailure($this->lng->txt("no_checkbox"));
			$this->membersObject();

			return false;
		}
		$this->checkPermission('write');
		$this->setSubTabs('members');
		$this->tabs_gui->setTabActive('members');
		$this->tabs_gui->setSubTabActive('grp_edit_members');

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
		$this->tabs_gui->setSubTabActive('grp_edit_members');

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
			ilUtil::sendFailure($this->lng->txt("no_checkbox"));
			$this->membersObject();

			return false;
		}
		$this->checkPermission('write');
		$this->setSubTabs('members');
		$this->tabs_gui->setTabActive('members');
		$this->tabs_gui->setSubTabActive('grp_edit_members');

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
} // END class.ilObjGroupGUI
?>
