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
	 * var ilParticipants
	 */
	
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
		
		$this->ctrl = $GLOBALS['DIC']['ilCtrl'];
		
		$this->logger = ilLoggerFactory::getLogger($this->getParentObject()->getType());
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
					$ilAccess->checkAccess('write','', $this->getParentObject()->getRefId())
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

				if(
					!(
						$this->getParentObject()->getMailToMembersType() == ilCourseConstants::MAIL_ALLOWED_ALL ||
						$ilAccess->checkAccess('write',"",$this->getParentObject()->getRefId())
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
				
				$is_admin       = (bool)$ilAccess->checkAccess('write', '', $this->getParentObject()->getRefId());
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
				
			default:
				$this->setSubTabs($GLOBALS['DIC']['ilTabs']);
				$this->$cmd();
				break;
		}
	}
	
	/**
	 * Show participant table, subscriber table, wating list table;
	 */
	protected function participants()
	{
		$this->showParticipantsToolbar();
		
		// show waiting list table
		
		// show subscriber table
		
		// show member table
		include_once '';
		
		
		
	}
	
	/**
	 * Members map
	 */
	protected function membersMap()
	{
		global $tpl;

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
		$ilToolbar->addButton(
			$this->lng->txt($this->getParentObject()->getType(). "_print_list"),
			$this->ctrl->getLinkTarget($this, 'printMembers'));
		
		$this->showMailToMemberToolbarButton($ilToolbar, 'participants', true);
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
				$ilAccess->checkAccess('write',"",$this->getParentObject()->getRefId()) &&
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
		
		if($ilAccess->checkAccess('write', '' , $this->getParentObject()->getRefId()))
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
		
		if($ilAccess->checkAccess('write','',$this->getParentObject()->getRefId()))
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
}
?>