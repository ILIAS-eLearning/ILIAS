<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilMailMemberSearchGUI
 * @author Nadia Matuschek <nmatuschek@databay.de>
 *
**/
class ilMailMemberSearchGUI
{
	/**
	 * @var mixed
	 */
	protected $mail_roles;

	/**
	 * @var ilObjGroupGUI|ilObjCourseGUI
	 */
	protected $gui;

	/**
	 * @var ilAbstractMailMemberRoles
	 */
	protected $objMailMemberRoles;
	/**
	 * @var null object ilCourseParticipants || ilGroupParticipants
	 */
	protected $objParticipants = NULL;

	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilTemplate
	 */
	protected $tpl;

	/**
	 * @var ilLanguage
	 */
	protected $lng;
	
	/**
	 * @var ilAccessHandler
	 */
	protected $access;

	/**
	 * ilMailMemberSearchGUI constructor.
	 * @param ilObjGroupGUI|ilObjCourseGUI $gui
	 * @param                           $ref_id
	 * @param ilAbstractMailMemberRoles $objMailMemberRoles
	 */
	public function __construct($gui, $ref_id, ilAbstractMailMemberRoles $objMailMemberRoles)
	{
		global $DIC;

		$this->ctrl   = $DIC['ilCtrl'];
		$this->tpl    = $DIC['tpl'];
		$this->lng    = $DIC['lng'];
		$this->access = $DIC['ilAccess'];

		$this->lng->loadLanguageModule('mail');
		$this->lng->loadLanguageModule('search');

		$this->gui    = $gui;
		$this->ref_id = $ref_id;

		$this->objMailMemberRoles = $objMailMemberRoles;
		$this->mail_roles = $objMailMemberRoles->getMailRoles($ref_id);
	}

	/**
	 * @return bool
	 */
	public function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		$this->ctrl->setReturn($this,'');
		
		switch($next_class)
		{
			default:
				switch($cmd)
				{
					case 'sendMailToSelectedUsers':
						$this->sendMailToSelectedUsers();
						break;

					case 'showSelectableUsers':
						$this->showSelectableUsers();
						break;
					
					case 'nextMailForm':
						$this->nextMailForm();
						break;
			
					case 'cancel':
						$this->redirectToParentReferer();
						break;
					
					default:
						if(isset($_GET['returned_from_mail']) && $_GET['returned_from_mail'] == '1')
						{
							$this->redirectToParentReferer();
						}	
						$this->showSearchForm();
						break;
				}	
				break;
		}
		return true;
	}
	
	private function redirectToParentReferer()
	{
		$redirect_target = $this->getStoredReferer();
		$this->unsetStoredReferer();
		ilUtil::redirect($redirect_target);
	}
	
	/**
	 * 
	 */
	public function storeReferer()
	{
		$referer = ilSession::get('referer');
		ilSession::set('ilMailMemberSearchGUIReferer', $referer);
	}
	
	/**
	 * @return bool || redirect target 
	 */
	private function getStoredReferer()
	{
		$stored_referer = ilSession::get('ilMailMemberSearchGUIReferer');
		return (strlen($stored_referer) ? $stored_referer : false);
	}
	
	/**
	 * 
	 */
	private function unsetStoredReferer()
	{
		ilSession::set('ilMailMemberSearchGUIReferer','');
	}
	
	/**
	 * 
	 */
	protected function nextMailForm()
	{
		$form = $this->initMailToMembersForm();
		if($form->checkInput())
		{
			if($form->getInput('mail_member_type') == 'mail_member_roles')
			{
				if(count($form->getInput('roles')) > 0)
				{
					$role_mail_boxes = array();
					$roles = $form->getInput('roles');
					foreach($roles as $role_id)
					{
						$mailbox = $this->objMailMemberRoles->getMailboxRoleAddress($role_id);
						$role_mail_boxes[] = $mailbox;
					}

					require_once 'Services/Mail/classes/class.ilMailFormCall.php';
					$_SESSION['mail_roles'] = $role_mail_boxes;

					ilUtil::redirect(ilMailFormCall::getRedirectTarget(
						$this, 'showSearchForm',
						array('type' => 'role'),
						array(
							'type'   => 'role',
							'rcp_to' => implode(',', $role_mail_boxes),
							'sig'    => $this->gui->createMailSignature()
						),
						$this->generateContextArray()
					));
				}
				else
				{
					$form->setValuesByPost();
					ilUtil::sendFailure($this->lng->txt('no_checkbox'));
					$this->showSearchForm();
					return;
				}
			}
			else
			{
				$this->showSelectableUsers();
				return;
			}
		}

		$form->setValuesByPost();
		$this->showSearchForm();
	}
	
	/**
	 * @return array
	 */
	protected function generateContextArray()
	{
		$context_array = array();
		require_once 'Modules/Course/classes/class.ilCourseMailTemplateTutorContext.php';
		if($this->access->checkAccess('write',"",$this->ref_id) )
		{
			$context_array = array(
				ilMailFormCall::CONTEXT_KEY => ilCourseMailTemplateTutorContext::ID,
				'ref_id'                    => $this->ref_id,
				'ts'                        => time()
			);
		}

		return $context_array;
	}
	
	/**
	 * 
	 */
	protected function showSelectableUsers()
	{
		include_once './Services/Contact/classes/class.ilMailMemberSearchTableGUI.php';
		include_once './Services/Contact/classes/class.ilMailMemberSearchDataProvider.php';
		
		$this->tpl->getStandardTemplate();
		$tbl = new ilMailMemberSearchTableGUI($this, 'showSelectableUsers');
		$provider = new ilMailMemberSearchDataProvider($this->getObjParticipants());
		$tbl->setData($provider->getData());

		$this->tpl->setContent($tbl->getHTML());
	}

	/**
	 * @return bool
	 */
	protected function sendMailToSelectedUsers()
	{
		if(!count($_POST['user_ids']))
		{
			ilUtil::sendFailure($this->lng->txt("no_checkbox"));
			$this->showSelectableUsers();
			return false;
		}

		$rcps = array();
		foreach($_POST['user_ids'] as $usr_id)
		{
			$rcps[] = ilObjUser::_lookupLogin($usr_id);
		}

		if(!count(array_filter($rcps)))
		{
			ilUtil::sendFailure($this->lng->txt("no_checkbox"));
			$this->showSelectableUsers();
			return false;
		}

		require_once 'Services/Mail/classes/class.ilMailFormCall.php';
		ilMailFormCall::setRecipients($rcps);

		ilUtil::redirect(ilMailFormCall::getRedirectTarget(
			$this, 'members',
			array(),
			array(
				'type' => 'new',
				'sig'  => ''
			),
			$this->generateContextArray()
		));

		return true;
	}
	
	/**
	 * 
	 */
	protected function showSearchForm()
	{
		$this->storeReferer();

		$form = $this->initMailToMembersForm();
		$this->tpl->setContent($form->getHTML());
	}

	/**
	 * @return null
	 */
	protected function getObjParticipants()
	{
		return $this->objParticipants;
	}

	/**
	 * @param null $objParticipants ilCourseParticipants || ilGroupParticipants
	 */
	public function setObjParticipants($objParticipants)
	{
		$this->objParticipants = $objParticipants;
	}
	
	/**
	 * @return ilPropertyFormGUI
	 */
	protected function initMailToMembersForm()
	{
		$this->lng->loadLanguageModule('mail');

		include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
		$form = new ilPropertyFormGUI();
		$form->setTitle($this->lng->txt('mail_members'));

		$form->setFormAction($this->ctrl->getFormAction($this, 'nextMailForm'));

		$radio_grp = $this->getMailRadioGroup();

		$form->addItem($radio_grp);
		$form->addCommandButton('nextMailForm', $this->lng->txt('continue'));
		$form->addCommandButton('cancel', $this->lng->txt('cancel'));

		return $form;
	}

	/**
	 * @return mixed
	 */
	private function getMailRoles()
	{
		return $this->mail_roles;
	}
	
	/**
	 * @return ilRadioGroupInputGUI
	 */
	protected function getMailRadioGroup()
	{
		$mail_roles = $this->getMailRoles();

		$radio_grp   = new ilRadioGroupInputGUI('', 'mail_member_type');
		$radio_roles = new ilRadioOption($this->objMailMemberRoles->getRadioOptionTitle(), 'mail_member_roles');
		foreach($mail_roles as $role)
		{
			$chk_role     = new ilCheckboxInputGUI($role['form_option_title'], 'roles[]');
			$chk_role->setValue($role['role_id']);
			$chk_role->setInfo($role['mailbox']);
			$radio_roles->addSubItem($chk_role);
		}

		$radio_sel_users = new ilRadioOption($this->lng->txt('mail_sel_users'), 'mail_sel_users');

		$radio_grp->setValue('mail_member_roles');
		$radio_grp->addOption($radio_roles);
		$radio_grp->addOption($radio_sel_users);

		return $radio_grp;
	}
}