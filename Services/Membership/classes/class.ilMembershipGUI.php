<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Base class for member tab content
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de> 
 *
 */
class ilMembershipGUI
{
	/**
	 * @var ilObject
	 */
	private $repository_object = null;
	
	/**
	 * @var ilObjectGUI 
	 */
	private $repository_gui = null;
	
	/**
	 * @var ilLanguage
	 */
	protected $lng = null;
	
	/**
	 * @var ilCtrl
	 */
	protected $ctrl = null;
	
	/**
	 * @var ilLogger
	 */
	protected $logger = null;
	
	/**
	 * @var ilTemplate
	 */
	protected $tpl;
	
	/**
	 * @var ilAccessHandler
	 */
	protected $access;
	
	
	/**
	 * Constructor
	 * @param ilObject $repository_obj
	 */
	public function __construct(ilObjectGUI $repository_gui, ilObject $repository_obj)
	{
		$this->repository_gui = $repository_gui;
		$this->repository_object = $repository_obj;
		
		$this->lng = $GLOBALS['DIC']['lng'];
		$this->lng->loadLanguageModule('crs');
		$this->lng->loadLanguageModule($this->getParentObject()->getType());
		
		$this->tpl = $GLOBALS['DIC']['tpl'];
		
		$this->ctrl = $GLOBALS['DIC']['ilCtrl'];
		
		$this->logger = ilLoggerFactory::getLogger($this->getParentObject()->getType());
		
		$this->access = $GLOBALS['DIC']->access();
	}
	
	/**
	 * Get parent gui
	 * @return ilObjectGUI
	 */
	public function getParentGUI()
	{
		return $this->repository_gui;
	}
	
	/**
	 * Get parent object
	 * @return ilObject
	 */
	public function getParentObject()
	{
		return $this->repository_object;
	}
	
	/**
	 * Get member object
	 * @return ilParticipants
	 */
	public function getMembersObject()
	{
		if($this->participants instanceof ilParticipants)
		{
			return $this->participants;
		}
		include_once './Services/Membership/classes/class.ilParticipants.php';
		return $this->participants = ilParticipants::getInstanceByObjId($this->getParentObject()->getId());
	}
	
	/**
	 * Check permission
	 * @param type $a_permission
	 * @param type $a_cmd
	 * @param type $a_type
	 * @param type $a_ref_id
	 */
	protected function checkPermissionBool($a_permission, $a_cmd = '', $a_type = '', $a_ref_id = 0)
	{
		if(!$a_ref_id)
		{
			$a_ref_id = $this->getParentObject()->getRefId();
		}
		return $this->access->checkAccess($a_permission, $a_cmd, $a_ref_id);
	}
	
	/**
	 * Execute command
	 */
	public function executeCommand()
	{
		/**
		 * @var ilTabsGUI
		 */
		global $ilUser, $ilErr, $ilAccess, $rbacsystem, $ilTabs;
		
		$cmd = $this->ctrl->getCmd('participants');
		$next_class = $this->ctrl->getNextClass();
		
		switch($next_class)
		{
			case 'ilrepositorysearchgui':
				
				include_once('./Services/Search/classes/class.ilRepositorySearchGUI.php');
				include_once './Services/Membership/classes/class.ilParticipants.php';
				$rep_search = new ilRepositorySearchGUI();

				$participants = $this->getMembersObject();
				if(
					$participants->isAdmin($GLOBALS['ilUser']->getId()) ||
					$ilAccess->checkAccess('manage_members','', $this->getParentObject()->getRefId())
				)
				{
					$rep_search->setCallback(
						$this,
						'assignMembers',
						$this->getParentGUI()->getLocalRoles()
					);
				}
				else
				{
					//#18445 excludes admin role
					$rep_search->setCallback(
						$this,
						'assignMembers',
					    $this->getLocalRoles(array($this->getParentObject()->getDefaultAdminRole()))
					);
				}
				
				// Set tabs
				$this->ctrl->setReturn($this,'participants');
				$ret = $this->ctrl->forwardCommand($rep_search);
				break;
			
			
			case 'ilmailmembersearchgui':

				$ilTabs->clearTargets();
				$ilTabs->setBackTarget(
					$this->lng->txt('btn_back'),
					$this->ctrl->getLinkTarget($this,'')
				);
				
				include_once 'Services/Mail/classes/class.ilMail.php';
				$mail = new ilMail($ilUser->getId());
				include_once 'Modules/Course/classes/class.ilCourseConstants.php';
				if(
					!(
						$this->getParentObject()->getMailToMembersType() == ilCourseConstants::MAIL_ALLOWED_ALL ||
						$ilAccess->checkAccess('manage_members',"",$this->getParentObject()->getRefId())
					) ||  !$rbacsystem->checkAccess('internal_mail',$mail->getMailObjectReferenceId())
				)
				{
					$ilErr->raiseError($this->lng->txt("msg_no_perm_read"),$ilErr->MESSAGE);
				}
				
				include_once './Services/Contact/classes/class.ilMailMemberSearchGUI.php';
				include_once './Services/Contact/classes/class.ilMailMemberCourseRoles.php';
				
				// @todo: fix mail course roles object
				$mail_search = new ilMailMemberSearchGUI($this, $this->getParentObject()->getRefId(), new ilMailMemberCourseRoles());
				$mail_search->setObjParticipants(
					ilParticipants::getInstanceByObjId($this->getParentObject()->getId())
				);
				$this->ctrl->forwardCommand($mail_search);
				break;
				
			case 'ilusersgallerygui':
				
				$this->setSubTabs($GLOBALS['ilTabs']);
				$tabs = $GLOBALS['DIC']->tabs()->setSubTabActive(
					$this->getParentObject()->getType().'_members_gallery'
				);
				
				$is_admin       = (bool)$ilAccess->checkAccess('manage_members', '', $this->getParentObject()->getRefId());
				$is_participant = (bool)ilParticipants::_isParticipant($this->getParentObject()->getRefId(), $ilUser->getId());
				if(
					!$is_admin &&
					(
						$this->getParentObject()->getShowMembers() == 0 ||
						!$is_participant
					)
				)
				{
					$ilErr->raiseError($this->lng->txt('msg_no_perm_read'), $ilErr->MESSAGE);
				}

				$this->showMailToMemberToolbarButton($GLOBALS['ilToolbar'], 'jump2UsersGallery');

				require_once 'Services/User/classes/class.ilUsersGalleryGUI.php';
				require_once 'Services/User/classes/class.ilUsersGalleryParticipants.php';


				$provider    = new ilUsersGalleryParticipants($this->getParentObject()->getMembersObject());
				$gallery_gui = new ilUsersGalleryGUI($provider);
				$this->ctrl->forwardCommand($gallery_gui);
				break;
				
			case 'ilcourseparticipantsgroupsgui':

				$this->setSubTabs($GLOBALS['ilTabs']);
				
				
				include_once './Modules/Course/classes/class.ilCourseParticipantsGroupsGUI.php';
				$cmg_gui = new ilCourseParticipantsGroupsGUI($this->getParentObject()->getRefId());
				if($cmd == "show" || $cmd = "")
				{
					$this->showMailToMemberToolbarButton($GLOBALS['ilToolbar']);
				}
				$this->ctrl->forwardCommand($cmg_gui);
				break;
				
			case 'ilsessionoverviewgui':								

				$this->setSubTabs($GLOBALS['ilTabs']);

				include_once './Services/Membership/classes/class.ilParticipants.php';
				$prt = ilParticipants::getInstanceByObjId($this->getParentObject()->getId());
			
				include_once('./Modules/Session/classes/class.ilSessionOverviewGUI.php');
				$overview = new ilSessionOverviewGUI($this->getParentObject()->getRefId(), $prt);
				$this->ctrl->forwardCommand($overview);				
				break;
			
			case 'ilmemberexportgui':

				$this->setSubTabs($GLOBALS['ilTabs']);

				include_once('./Services/Membership/classes/Export/class.ilMemberExportGUI.php');
				$export = new ilMemberExportGUI($this->getParentObject()->getRefId());
				$this->ctrl->forwardCommand($export);
				break;

			case 'ilobjectcustomuserfieldsgui':
				$this->setSubTabs($GLOBALS['ilTabs']);
				$this->activateSubTab($this->getParentObject()->getType()."_member_administration");
				$this->ctrl->setReturn($this,'participants');

				include_once './Services/Membership/classes/class.ilObjectCustomUserFieldsGUI.php';
				$cdf_gui = new ilObjectCustomUserFieldsGUI($this->getParentGUI()->object->getId());
				$this->ctrl->forwardCommand($cdf_gui);
				break;
				
			default:
				$this->setSubTabs($GLOBALS['DIC']['ilTabs']);

				//exclude mailMembersBtn cmd from this check
				if(
					$cmd != "mailMembersBtn" &&
					$cmd != 'membersMap'
				)
				{
					$this->checkPermission('manage_members');
				}
				else
				{
					$this->checkPermission('read');
				}

				$this->$cmd();
				break;
		}
	}
	
	/**
	 * Show participant table, subscriber table, wating list table;
	 */
	protected function participants()
	{
		$this->initParticipantTemplate();
		$this->showParticipantsToolbar();
		$this->activateSubTab($this->getParentObject()->getType()."_member_administration");
		
		// show waiting list table
		$waiting = $this->parseWaitingListTable();
		if($waiting instanceof ilWaitingListTableGUI)
		{
			$this->tpl->setVariable('TABLE_WAIT', $waiting->getHTML());
		}
		
		// show subscriber table
		$subscriber = $this->parseSubscriberTable();
		if($subscriber instanceof ilSubscriberTableGUI)
		{
			$this->tpl->setVariable('TABLE_SUB', $subscriber->getHTML());
		}
		
		// show member table
		$table = $this->initParticipantTableGUI();
		$table->setTitle($this->lng->txt($this->getParentObject()->getType().'_mem_tbl_header'));
		$table->setFormAction($this->ctrl->getFormAction($this));
		$table->parse();
		
		// filter commands
		$table->setFilterCommand('participantsApplyFilter');
		$table->setResetCommand('participantsResetFilter');
		
		$this->tpl->setVariable('MEMBERS', $table->getHTML());
	}
	
	/**
	 * Apply filter for participant table
	 */
	protected function participantsApplyFilter()
	{
		$table = $this->initParticipantTableGUI();
		$table->resetOffset();
		$table->writeFilterToSession();
		
		$this->participants();
	}
	
	/**
	 * reset participants filter
	 */
	protected function participantsResetFilter()
	{
		$table = $this->initParticipantTableGUI();
		$table->resetOffset();
		$table->resetFilter();
		
		$this->participants();
	}


	/**
	 * Edit one participant
	 */
	protected function editMember()
	{
		$this->activateSubTab($this->getParentObject()->getType()."_member_administration");
		return $this->editParticipants(array($_REQUEST['member_id']));
	}
	
	/**
	 * Edit participants
	 * @param array $post_participants
	 */
	protected function editParticipants($post_participants = array())
	{
		if(!$post_participants)
		{
			$post_participants = (array) $_POST['participants'];
		}

		$real_participants = $this->getMembersObject()->getParticipants();
		$participants = array_intersect((array) $post_participants, (array) $real_participants);
		
		if(!count($participants))
		{
			ilUtil::sendFailure($this->lng->txt('no_checkbox'),true);
			$this->ctrl->redirect($this,'participants');
		}
		$table = $this->initEditParticipantTableGUI($participants);
		$this->tpl->setContent($table->getHTML());
		return true;
	}
	
	/**
	 * update members
	 *
	 * @access public
	 * @param
	 * @return
	 */
	public function updateParticipants()
	{
		global $rbacsystem, $rbacreview, $ilUser, $ilAccess;
                
		if(!count($_POST['participants']))
		{
			ilUtil::sendFailure($this->lng->txt('no_checkbox'),true);
			$this->ctrl->redirect($this,'participants');
		}
		
		$notifications = $_POST['notification'] ? $_POST['notification'] : array();
		$passed = $_POST['passed'] ? $_POST['passed'] : array();
		$blocked = $_POST['blocked'] ? $_POST['blocked'] : array();
		$contact = $_POST['contact'] ? $_POST['contact'] : array();
		
		// Determine whether the user has the 'edit_permission' permission
		$hasEditPermissionAccess = 
			(
				$ilAccess->checkAccess('edit_permission','',$this->getParentObject()->getRefId()) or
				$this->getMembersObject()->isAdmin($ilUser->getId())
			);

		// Get all assignable local roles of the object, and
		// determine the role id of the course administrator role.
		$assignableLocalRoles = array();
        $adminRoleId = $this->getParentObject()->getDefaultAdminRole();
		foreach ($this->getLocalTypeRole(false) as $title => $role_id)
		{
			$assignableLocalRoles[$role_id] = $title;
		}
                
		// Validate the user ids and role ids in the post data
		foreach($_POST['participants'] as $usr_id)
		{
			$memberIsAdmin = $rbacreview->isAssigned($usr_id, $adminRoleId);
                        
			// If the current user doesn't have the 'edit_permission' 
			// permission, make sure he doesn't remove the course
			// administrator role of members who are course administrator.
			if (! $hasEditPermissionAccess && $memberIsAdmin &&
				! in_array($adminRoleId, $_POST['roles'][$usr_id])
			)
			{
				$_POST['roles'][$usr_id][] = $adminRoleId;
			}
                        
			// Validate the role ids in the post data
			foreach ((array) $_POST['roles'][$usr_id] as $role_id)
			{
				if(!array_key_exists($role_id, $assignableLocalRoles))
				{
					ilUtil::sendFailure($this->lng->txt('msg_no_perm_perm'),true);
					$this->ctrl->redirect($this, 'participants');
		        }
		        if(!$hasEditPermissionAccess && 
					$role_id == $adminRoleId &&
					!$memberIsAdmin)
				{
					ilUtil::sendFailure($this->lng->txt('msg_no_perm_perm'));
					$this->ctrl->redirect($this, 'participants');
				}
			}
		}
		
		$has_admin = false;
		foreach($this->getMembersObject()->getAdmins() as $admin_id)
		{
			if(!isset($_POST['roles'][$admin_id]))
			{
				$has_admin = true;
				break;
			}
			if(in_array($adminRoleId,$_POST['roles'][$admin_id]))
			{
				$has_admin = true;
				break;
			}
		}
		
		if(!$has_admin)
		{
			ilUtil::sendFailure($this->lng->txt($this->getParentObject()->getType().'_min_one_admin'),true);
			$this->ctrl->redirect($this, 'participants');
		}

		foreach($_POST['participants'] as $usr_id)
		{
			$this->getMembersObject()->updateRoleAssignments($usr_id,(array) $_POST['roles'][$usr_id]);
			
			// Disable notification for all of them
			$this->getMembersObject()->updateNotification($usr_id,0);
			if(($this->getMembersObject()->isTutor($usr_id) or $this->getMembersObject()->isAdmin($usr_id)) and in_array($usr_id,$notifications))
			{
				$this->getMembersObject()->updateNotification($usr_id,1);
			}
			
			$this->getMembersObject()->updateBlocked($usr_id,0);
			if((!$this->getMembersObject()->isAdmin($usr_id) and !$this->getMembersObject()->isTutor($usr_id)) and in_array($usr_id,$blocked))
			{
				$this->getMembersObject()->updateBlocked($usr_id,1);
			}
			
			if($this instanceof ilCourseMembershipGUI)
			{
				$this->getMembersObject()->updatePassed($usr_id,in_array($usr_id,$passed),true);
				$this->getMembersObject()->sendNotification(
					$this->getMembersObject()->NOTIFY_STATUS_CHANGED,
					$usr_id);
			}
			
			if(
				($this->getMembersObject()->isAdmin($usr_id) ||
				$this->getMembersObject()->isTutor($usr_id)) &&
				in_array($usr_id, $contact)
			)
			{	
				$this->getMembersObject()->updateContact($usr_id,TRUE);
			}
			else
			{
				$this->getMembersObject()->updateContact($usr_id,FALSE);
			}
			
			$this->updateLPFromStatus($usr_id,in_array($usr_id,$passed));	
		}
		ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"),true);
		$this->ctrl->redirect($this, "participants");
	}
	
	/**
	 * Show confirmation screen for participants deletion
	 */
	protected function confirmDeleteParticipants()
	{
		global $ilAccess, $ilUser;
		
		$participants = (array) $_POST['participants'];
		
		if(!count($participants))
		{
			ilUtil::sendFailure($this->lng->txt('no_checkbox'),true);
			$this->ctrl->redirect($this, 'participants');
		}

		// Check last admin
		if(!$this->getMembersObject()->checkLastAdmin($participants))
		{
			ilUtil::sendFailure($this->lng->txt($this->getParentObject()->getType().'_at_least_one_admin'),true);
			$this->ctrl->redirect($this, 'participants');
		}
		
		// Access check for admin deletion
		if(
			!$ilAccess->checkAccess(
				'edit_permission', 
				'',
				$this->getParentObject()->getRefId()) &&
			!$this->getMembersObject()->isAdmin($GLOBALS['ilUser']->getId())
		)
		{
			foreach ($participants as $usr_id)
			{
				if($this->getMembersObject()->isAdmin($usr_id))
				{
					ilUtil::sendFailure($this->lng->txt("msg_no_perm_perm"),true);
					$this->ctrl->redirect($this, 'participants');
				}
			}
		}

		
		include_once('./Services/Utilities/classes/class.ilConfirmationGUI.php');
		$confirm = new ilConfirmationGUI();
		$confirm->setFormAction($this->ctrl->getFormAction($this,'confirmDeleteParticipants'));
		$confirm->setHeaderText($this->lng->txt($this->getParentObject()->getType().'_header_delete_members'));
		$confirm->setConfirm($this->lng->txt('confirm'),'deleteParticipants');
		$confirm->setCancel($this->lng->txt('cancel'),'participants');
		
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
	
	protected function deleteParticipants()
	{
		global $rbacreview, $rbacsystem, $ilAccess, $ilUser;
                
		$participants = (array) $_POST['participants'];
		
		if(!is_array($participants) or !count($participants))
		{
			ilUtil::sendFailure($this->lng->txt("no_checkbox"),true);
			$this->ctrl->redirect($this, 'participants');
		}
		
		// If the user doesn't have the edit_permission and is not administrator, he may not remove
		// members who have the course administrator role
		if (
			!$ilAccess->checkAccess('edit_permission', '', $this->getParentObject()->getRefId()) && 
			!$this->getMembersObject()->isAdmin($GLOBALS['ilUser']->getId())
		)
		{
			foreach($participants as $part)
			{
				if($this->getMembersObject()->isAdmin($part))
				{
					ilUtil::sendFailure($this->lng->txt('msg_no_perm_perm'),true);
					$this->ctrl->redirect($this, 'participants');
				}
			}
		}
        
		if(!$this->getMembersObject()->deleteParticipants($participants))
		{
			ilUtil::sendFailure('Error deleting participants.', true);
			$this->ctrl->redirect($this, 'participants');
		}
		else
		{
			foreach((array) $_POST["participants"] as $usr_id)
			{
				$mail_type = 0;
				// @todo more generic
				switch($this->getParentObject()->getType())
				{
					case 'crs':
						$mail_type = $this->getMembersObject()->NOTIFY_DISMISS_MEMBER;
						break;
					case 'grp':
						include_once './Modules/Group/classes/class.ilGroupMembershipMailNotification.php';
						$mail_type = ilGroupMembershipMailNotification::TYPE_DISMISS_MEMBER;
						break;
				}
				$this->getMembersObject()->sendNotification($mail_type, $usr_id);
			}
		}
		ilUtil::sendSuccess($this->lng->txt($this->getParentObject()->getType()."_members_deleted"), true);
		$this->ctrl->redirect($this, "participants");

		return true;
		
	}
	
	/**
	 * Send mail to selected users
	 */
	protected function sendMailToSelectedUsers()
	{
		if($_POST['participants'])
		{
			$participants = (array) $_POST['participants'];
		}
		elseif($_GET['member_id'])
		{
			$participants = array($_GET['member_id']);
		}
		elseif($_POST['subscribers'])
		{
			$participants = (array) $_POST['subscribers'];
		}
		elseif($_POST['waiting'])
		{
			$participants = (array) $_POST['waiting'];
		}

		if (!count($participants))
		{
			ilUtil::sendFailure($this->lng->txt("no_checkbox"), true);
			$this->ctrl->redirect($this, 'participants');
		}
		
		foreach($participants as $usr_id)
		{
			$rcps[] = ilObjUser::_lookupLogin($usr_id);
		}

		require_once 'Services/Mail/classes/class.ilMailFormCall.php';
		require_once 'Modules/Course/classes/class.ilCourseMailTemplateTutorContext.php';

		ilMailFormCall::setRecipients($rcps);
		ilUtil::redirect(
			ilMailFormCall::getRedirectTarget(
				$this, 
				'participants',
				array(),
				array(
					'type'   => 'new',
					'sig' => $this->createMailSignature()
				),
				array(
					ilMailFormCall::CONTEXT_KEY => ilCourseMailTemplateTutorContext::ID,
					'ref_id' => $this->getParentObject()->getRefId(),
					'ts'     => time()
				)
			)
		);		
	}
	
	/**
	 * Members map
	 */
	protected function membersMap()
	{
		global $tpl;
		$this->activateSubTab($this->getParentObject()->getType()."_members_map");
		include_once("./Services/Maps/classes/class.ilMapUtil.php");
		if (!ilMapUtil::isActivated() || !$this->getParentObject()->getEnableMap())
		{
			return;
		}
		
		$map = ilMapUtil::getMapGUI();
		$map->setMapId("course_map")
			->setWidth("700px")
			->setHeight("500px")
			->setLatitude($this->getParentObject()->getLatitude())
			->setLongitude($this->getParentObject()->getLongitude())
			->setZoom($this->getParentObject()->getLocationZoom())
			->setEnableTypeControl(true)
			->setEnableNavigationControl(true)
			->setEnableCentralMarker(true);

		include_once './Services/Membership/classes/class.ilParticipants.php';
		$members = ilParticipants::getInstanceByObjId($this->getParentObject()->getId())->getParticipants();
		foreach((array) $members as $user_id)
		{
			$map->addUserMarker($user_id);
		}

		$tpl->setContent($map->getHTML());
		$tpl->setLeftContent($map->getUserListHTML());
	}
	
	/**
	 * Mail to members view
	 * @global type $ilToolbar
	 */
	protected function mailMembersBtn()
	{
		global $ilToolbar;
		
		$this->showMailToMemberToolbarButton($GLOBALS['ilToolbar'], 'mailMembersBtn');
	}
	
	
	
	
	/**
	 * Show participants toolbar
	 */
	protected function showParticipantsToolbar()
	{
		global $ilToolbar;
		
		include_once './Services/Search/classes/class.ilRepositorySearchGUI.php';
		ilRepositorySearchGUI::fillAutoCompleteToolbar(
			$this,
			$ilToolbar,
			array(
				'auto_complete_name'	=> $this->lng->txt('user'),
				'user_type'				=> $this->getParentGUI()->getLocalRoles(),
				'user_type_default'		=> $this->getDefaultRole(),
				'submit_name'			=> $this->lng->txt('add')
			)
		);
		
		// spacer
		$ilToolbar->addSeparator();

		// search button
		$ilToolbar->addButton(
			$this->lng->txt($this->getParentObject()->getType()."_search_users"),
			$this->ctrl->getLinkTargetByClass(
				'ilRepositorySearchGUI',
				'start')
		);
			
		// separator
		$ilToolbar->addSeparator();
			
		// print button
		#$ilToolbar->addButton(
		#	$this->lng->txt($this->getParentObject()->getType(). "_print_list"),
		#	$this->ctrl->getLinkTarget($this, 'printMembers'));
		
		$this->showMailToMemberToolbarButton($ilToolbar, 'participants', false);
	}

	
	
	/**
	 * Show mail to member toolbar button
	 */
	protected function showMailToMemberToolbarButton(ilToolbarGUI $toolbar, $a_back_cmd = null, $a_separator = false)
	{
		global $ilUser, $rbacsystem, $ilAccess;
		include_once 'Services/Mail/classes/class.ilMail.php';
		$mail = new ilMail($ilUser->getId());

		if(
			($this->getParentObject()->getMailToMembersType() == 1) ||
			(
				$ilAccess->checkAccess('manage_members',"",$this->getParentObject()->getRefId()) &&
				$rbacsystem->checkAccess('internal_mail',$mail->getMailObjectReferenceId())
			)
		)
		{

			if($a_separator)
			{
				$toolbar->addSeparator();
			}

			if($a_back_cmd)
			{
				$this->ctrl->setParameter($this, "back_cmd", $a_back_cmd);
			}

			$toolbar->addButton(
				$this->lng->txt("mail_members"),
				$this->ctrl->getLinkTargetByClass('ilMailMemberSearchGUI','')
			);
		}
	}
	
	/**
	 * @todo better implementation
	 * Create Mail signature
	 */
	public function createMailSignature()
	{
		return $this->getParentGUI()->createMailSignature();
	}
	
	/**
	 * add member tab
	 * @param ilTabsGUI $tabs
	 */
	public function addMemberTab(ilTabsGUI $tabs, $a_is_participant = false)
	{
		global $ilAccess;
		
		include_once './Services/Mail/classes/class.ilMail.php';
		$mail = new ilMail($GLOBALS['ilUser']->getId());
		
		if($ilAccess->checkAccess('manage_members', '' , $this->getParentObject()->getRefId()))
		{
			$tabs->addTab(
				'members',
				$this->lng->txt('members'),
				$this->ctrl->getLinkTarget($this,'')
			);
		}
		elseif(
			(bool) $this->getParentObject()->getShowMembers() && $a_is_participant
		)
		{
			$tabs->addTab(
				'members',
				$this->lng->txt('members'),
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilusersgallerygui'), 'view')
			);
		}
		elseif(
			$this->getParentObject()->getMailToMembersType() == 1 &&
			$GLOBALS['rbacsystem']->checkAccess('internal_mail',$mail->getMailObjectReferenceId ()) &&
			$a_is_participant
		)
		{
			$tabs->addTab(
				'members',
				$this->lng->txt('members'),
				$this->ctrl->getLinkTarget($this, "mailMembersBtn")
			);
		}
	}
	
	/**
	 * Set sub tabs
	 */
	protected function setSubTabs(ilTabsGUI $tabs)
	{
		global $ilAccess;
		
		if($ilAccess->checkAccess('manage_members','',$this->getParentObject()->getRefId()))
		{
			$tabs->addSubTabTarget(
				$this->getParentObject()->getType()."_member_administration",
				$this->ctrl->getLinkTarget($this,'participants'),
				"members", 
				get_class($this)
			);

			// show group overview
			if($this instanceof ilCourseMembershipGUI)
			{
				$tabs->addSubTabTarget(
					"crs_members_groups",
					$this->ctrl->getLinkTargetByClass("ilCourseParticipantsGroupsGUI", "show"),
					"", 
					"ilCourseParticipantsGroupsGUI"
				);
			}
			
			$childs = (array) $GLOBALS['tree']->getChildsByType($this->getParentObject()->getRefId(),'sess');
			if(count($childs))
			{
				$tabs->addSubTabTarget(
					'events',
					$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilsessionoverviewgui'),'listSessions'),
					'',
					'ilsessionoverviewgui'
				);
			}

			$tabs->addSubTabTarget(
				$this->getParentObject()->getType().'_members_gallery',
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilUsersGalleryGUI')),
				'view',
				'ilUsersGalleryGUI'
			);
		}
		else if($this->getParentObject()->getShowMembers())
		{
			// gallery
			$tabs->addSubTabTarget(
				$this->getParentObject()->getType().'_members_gallery',
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilUsersGalleryGUI')),
				'view',
				'ilUsersGalleryGUI'
			);
		}
		
		include_once './Services/Maps/classes/class.ilMapUtil.php';
		if(ilMapUtil::isActivated() && $this->getParentObject()->getEnableMap())
		{
			$tabs->addSubTabTarget(
				$this->getParentObject()->getType().'_members_map',
				$this->ctrl->getLinkTarget($this,'membersMap'),
				"membersMap", 
				get_class($this)
			);
		}
		
		include_once 'Services/PrivacySecurity/classes/class.ilPrivacySettings.php';
		if(ilPrivacySettings::_getInstance()->checkExportAccess($this->getParentObject()->getRefId()))
		{
			$tabs->addSubTabTarget(
				'export_members',
				$this->ctrl->getLinkTargetByClass(array(get_class($this),'ilmemberexportgui'),'show'),
				'',
				'ilmemberexportgui'
			);
		}
	}
	
	/**
	 * Required for member table guis.
	 * Has to be refactored and should be locate in ilObjCourse, ilObjGroup instead of GUI
	 * @return array
	 */
	public function readMemberData(array $usr_ids, array $columns)
	{
		return $this->getParentGUI()->readMemberData($usr_ids, $columns);
	}
	
	/**
	 * Get parent roles
	 * @return type
	 */
	public function getLocalRoles()
	{
		return $this->getParentGUI()->getLocalRoles();
	}
	
	/**
	 * Parse table of subscription request
	 */
	protected function parseSubscriberTable()
	{
		if(!$this->getMembersObject()->getSubscribers())
		{
			ilLoggerFactory::getLogger('mmbr')->debug('No subscriber found');
			return null;
		}
		include_once './Services/Membership/classes/class.ilSubscriberTableGUI.php';
		$subscriber = new ilSubscriberTableGUI($this, $this->getParentObject(),true);
		$subscriber->setTitle($this->lng->txt('group_new_registrations'));
		$subscriber->readSubscriberData();
		return $subscriber;
	}
	
	/**
	 * Show subscription confirmation
	 * @return boolean
	 */
	public function confirmAssignSubscribers()
	{
		if(!is_array($_POST["subscribers"]))
		{
			ilUtil::sendFailure($this->lng->txt("crs_no_subscribers_selected"),true);
			$this->ctrl->redirect($this, 'participants');
		}

		include_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
		$c_gui = new ilConfirmationGUI();

		// set confirm/cancel commands
		$c_gui->setFormAction($this->ctrl->getFormAction($this, "assignSubscribers"));
		$c_gui->setHeaderText($this->lng->txt("info_assign_sure"));
		$c_gui->setCancel($this->lng->txt("cancel"), "participants");
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
	
	/**
	 * Refuse subscriber confirmation
	 * @return boolean
	 */
	public function confirmRefuseSubscribers()
	{
		if(!is_array($_POST["subscribers"]))
		{
			ilUtil::sendFailure($this->lng->txt("crs_no_subscribers_selected"),true);
			$this->ctrl->redirect($this, 'participants');
		}

		$this->lng->loadLanguageModule('mmbr');

		include_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
		$c_gui = new ilConfirmationGUI();

		// set confirm/cancel commands
		$c_gui->setFormAction($this->ctrl->getFormAction($this, "refuseSubscribers"));
		$c_gui->setHeaderText($this->lng->txt("info_refuse_sure"));
		$c_gui->setCancel($this->lng->txt("cancel"), "participants");
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
	
	/**
	 * Refuse subscribers
	 * @global type $rbacsystem
	 * @return boolean
	 */
	protected function refuseSubscribers()
	{
		global $rbacsystem;

		if(!$_POST['subscribers'])
		{
			ilUtil::sendFailure($this->lng->txt("crs_no_subscribers_selected"),true);
			$this->ctrl->redirect($this, 'participants');
		}
	
		if(!$this->getMembersObject()->deleteSubscribers($_POST["subscribers"]))
		{
			ilUtil::sendFailure($GLOBALS['ilErr']->getMessage(),true);
			$this->ctrl->redirect($this, 'participants');
		}
		else
		{
			foreach($_POST['subscribers'] as $usr_id)
			{
				if($this instanceof ilCourseMembershipGUI)
				{
					$this->getMembersObject()->sendNotification($this->getMembersObject()->NOTIFY_DISMISS_SUBSCRIBER, $usr_id);
				}
				else
				{
					include_once './Modules/Group/classes/class.ilGroupMembershipMailNotification.php';
					$this->getMembersObject()->sendNotification(
						ilGroupMembershipMailNotification::TYPE_REFUSED_SUBSCRIPTION_MEMBER,
						$usr_id
					);
				}
			}
		}

		ilUtil::sendSuccess($this->lng->txt("crs_subscribers_deleted"),true);
		$this->ctrl->redirect($this, 'participants');
	}
	
	/**
	 * Do assignment of subscription request
	 * @global type $rbacsystem
	 * @global type $ilErr
	 * @return boolean
	 */
	public function assignSubscribers()
	{
		global $ilErr;
		
		if(!is_array($_POST["subscribers"]))
		{
			ilUtil::sendFailure($this->lng->txt("crs_no_subscribers_selected"),true);
			$this->ctrl->redirect($this, 'participants');
		}
		
		if(!$this->getMembersObject()->assignSubscribers($_POST["subscribers"]))
		{
						$this->object->members_obj->add($usr_id,IL_GRP_MEMBER);
			$this->object->members_obj->deleteSubscriber($usr_id);

			
			
			ilUtil::sendFailure($ilErr->getMessage(),true);
			$this->ctrl->redirect($this, 'participants');
		}
		else
		{
			foreach($_POST["subscribers"] as $usr_id)
			{
				if($this instanceof ilCourseMembershipGUI)
				{
					$this->getMembersObject()->sendNotification($this->getMembersObject()->NOTIFY_ACCEPT_SUBSCRIBER, $usr_id);
					$this->getParentObject()->checkLPStatusSync($usr_id);
				}
				else
				{
					include_once './Modules/Group/classes/class.ilGroupMembershipMailNotification.php';
					$this->getMembersObject()->sendNotification(
						ilGroupMembershipMailNotification::TYPE_ACCEPTED_SUBSCRIPTION_MEMBER,
						$usr_id
					);
				}
			}
		}
		ilUtil::sendSuccess($this->lng->txt("crs_subscribers_assigned"),true);
		$this->ctrl->redirect($this, 'participants');
	}
	
	/**
	 * Parse table of subscription request
	 * @return ilWaitingListTableGUI
	 */
	protected function parseWaitingListTable()
	{
		$wait = $this->initWaitingList();
		
		if(!$wait->getCountUsers())
		{
			return null;
		}
		
		include_once './Services/Membership/classes/class.ilWaitingListTableGUI.php';
		$waiting_table = new ilWaitingListTableGUI($this, $this->getParentObject(), $wait);
		$waiting_table->setUsers($wait->getAllUsers());
		$waiting_table->setTitle($this->lng->txt('crs_waiting_list'));
		
		return $waiting_table;
	}
	
	/**
	 * Assign from waiting list (confirmatoin) 
	 * @return boolean
	 */
	public function confirmAssignFromWaitingList()
	{
		if(!is_array($_POST["waiting"]))
		{
			ilUtil::sendFailure($this->lng->txt("crs_no_users_selected"),true);
			$this->ctrl->redirect($this,'participants');
		}

		
		include_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
		$c_gui = new ilConfirmationGUI();

		// set confirm/cancel commands
		$c_gui->setFormAction($this->ctrl->getFormAction($this, "assignFromWaitingList"));
		$c_gui->setHeaderText($this->lng->txt("info_assign_sure"));
		$c_gui->setCancel($this->lng->txt("cancel"), "participants");
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
	
	/**
	 * Assign from waiting list
	 * @global type $rbacsystem
	 * @return boolean
	 */
	public function assignFromWaitingList()
	{
		if(!count($_POST["waiting"]))
		{
			ilUtil::sendFailure($this->lng->txt("crs_no_users_selected"),true);
			$this->ctrl->redirect($this,'participants');
		}
		
		$waiting_list = $this->initWaitingList();

		$added_users = 0;
		foreach($_POST["waiting"] as $user_id)
		{
			if(!$tmp_obj = ilObjectFactory::getInstanceByObjId($user_id,false))
			{
				continue;
			}
			if($this->getMembersObject()->isAssigned($user_id))
			{
				continue;
			}
			
			if($this instanceof ilCourseMembershipGUI)
			{
				$this->getMembersObject()->add($user_id,IL_CRS_MEMBER);
				$this->getMembersObject()->sendNotification($this->getMembersObject()->NOTIFY_ACCEPT_USER,$user_id);
				$this->getParentObject()->checkLPStatusSync($user_id);
			}
			else
			{
				include_once './Modules/Group/classes/class.ilGroupMembershipMailNotification.php';
				$this->getMembersObject()->add($user_id,IL_GRP_MEMBER);
				$this->getMembersObject()->sendNotification(
					ilGroupMembershipMailNotification::TYPE_ACCEPTED_SUBSCRIPTION_MEMBER,
					$user_id
				);
			}
			$waiting_list->removeFromList($user_id);
			++$added_users;
		}

		if($added_users)
		{
			ilUtil::sendSuccess($this->lng->txt("crs_users_added"),true);
			$this->ctrl->redirect($this, 'participants');
		}
		else
		{
			ilUtil::sendFailure($this->lng->txt("crs_users_already_assigned"),true);
			$this->ctrl->redirect($this, 'participants');
		}
	}
	
	/**
	 * Refuse from waiting list (confirmation)
	 * @return boolean
	 */
	public function confirmRefuseFromList()
	{
		if(!is_array($_POST["waiting"]))
		{
			ilUtil::sendFailure($this->lng->txt("no_checkbox"),true);
			$this->ctrl->redirect($this, 'participants');
		}

		$this->lng->loadLanguageModule('mmbr');

		include_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
		$c_gui = new ilConfirmationGUI();

		// set confirm/cancel commands
		$c_gui->setFormAction($this->ctrl->getFormAction($this, "refuseFromList"));
		$c_gui->setHeaderText($this->lng->txt("info_refuse_sure"));
		$c_gui->setCancel($this->lng->txt("cancel"), "participants");
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
	
	/**
	 * refuse from waiting list
	 *
	 * @access public
	 * @return
	 */
	protected function refuseFromList()
	{
		global $ilUser;
		
		if(!count($_POST['waiting']))
		{
			ilUtil::sendFailure($this->lng->txt('no_checkbox'),true);
			$this->ctrl->redirect($this, 'participants');
		}
		
		$waiting_list = $this->initWaitingList();

		foreach($_POST["waiting"] as $user_id)
		{
			$waiting_list->removeFromList($user_id);
			
			if($this instanceof ilCourseWaitingList)
			{
				$this->getMembersObject()->sendNotification($this->getMembersObject()->NOTIFY_DISMISS_SUBSCRIBER,$user_id);
			}
			else
			{
				include_once './Modules/Group/classes/class.ilGroupMembershipMailNotification.php';
				$this->getMembersObject()->sendNotification(
					ilGroupMembershipMailNotification::TYPE_REFUSED_SUBSCRIPTION_MEMBER,
					$user_id
				);
			}
			
		}
		ilUtil::sendSuccess($this->lng->txt('crs_users_removed_from_list'),true);
		$this->ctrl->redirect($this, 'participants');
	}
	
	/**
	 * Add selected users to user clipboard
	 */
	protected function addToClipboard()
	{
		$users = (array) $_POST['participants'];
		if(!count($users))
		{
			ilUtil::sendFailure($this->lng->txt('select_one'),true);
			$this->ctrl->redirect($this, 'participants');
		}
		include_once './Services/User/classes/class.ilUserClipboard.php';
		$clip = ilUserClipboard::getInstance($GLOBALS['ilUser']->getId());
		$clip->add($users);
		$clip->save();
		
		$this->lng->loadLanguageModule('user');
		ilUtil::sendSuccess($this->lng->txt('clipboard_user_added'),true);
		$this->ctrl->redirect($this, 'participants');
		
	}

	/**
	 * @return null
	 */
	protected function getDefaultRole()
	{
		return null;
	}

	/**
	 * @param string $a_sub_tab
	 */
	protected function activateSubTab($a_sub_tab)
	{
		/**
		 * @var ilTabsGUI $tabs
		 */
		$tabs = $GLOBALS['DIC']['ilTabs'];
		$tabs->activateSubTab($a_sub_tab);
	}

	/**
	 * Checks Perrmission
	 * If not granted redirect to parent gui
	 *
	 * @param string $a_permission
	 * @param string $a_cmd
	 */
	protected function checkPermission($a_permission, $a_cmd = "")
	{
		/**
		 * @var $ilAccess ilAccessHandler
		 */
		$ilAccess = $GLOBALS['DIC']['ilAccess'];

		if(!$ilAccess->checkAccess($a_permission, $a_cmd, $this->getParentGUI()->ref_id))
		{
			ilUtil::sendFailure($this->lng->txt('no_permission'), true);
			$this->ctrl->redirect($this->getParentGUI());
		}
	}
}
?>