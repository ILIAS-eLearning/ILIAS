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
	 * Constructor
	 * @param ilObject $repository_obj
	 */
	public function __construct(ilObjectGUI $repository_gui, ilObject $repository_obj)
	{
		$this->repository_gui = $repository_gui;
		$this->repository_object = $repository_obj;
		
		$this->lng = $GLOBALS['DIC']['lng'];
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
				
			default:
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
		
		
		$GLOBALS['tpl']->setContent('Hallo');
		
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
	 * Mail to members view
	 * @global type $ilToolbar
	 */
	protected function mailMembersBtn()
	{
		global $ilToolbar;
		
		$this->showMailToMemberToolbarButton($GLOBALS['ilToolbar'], 'mailMembersBtn');
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
}
?>